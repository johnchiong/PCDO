const { app, BrowserWindow, ipcMain } = require('electron')
const path = require('path')
const { spawn } = require('child_process')
const fs = require('fs')

let mainWindow

function createWindow() {
    mainWindow = new BrowserWindow({
        width: 820,
        height: 640,
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            nodeIntegration: false,
            contextIsolation: true
        }
    })
    mainWindow.loadFile(path.join(__dirname, 'resources', 'installer.html'))
    mainWindow.on('closed', () => { mainWindow = null })
}

app.whenReady().then(createWindow)

// Path to bundled PHP binary
const phpBinary = path.join(__dirname, 'resources', 'php', process.platform === 'win32' ? 'php.exe' : 'php')

ipcMain.handle('run-step', async (ev, step) => {
    const laravelRoot = path.join(__dirname, '..') // Laravel project root

    if (step === 'cleanup-after-fail') {
        try {
            const dbPath = path.join(laravelRoot, 'database', 'database.sqlite')
            if (fs.existsSync(dbPath)) fs.unlinkSync(dbPath)
            return { ok: true, msg: 'cleanup done' }
        } catch (e) {
            return { ok: false, msg: e.message }
        }
    }

    if (step === 'ensure-sqlite') {
        const dbPath = path.join(laravelRoot, 'database', 'database.sqlite')
        try {
            if (!fs.existsSync(path.dirname(dbPath))) fs.mkdirSync(path.dirname(dbPath), { recursive: true })
            if (!fs.existsSync(dbPath)) fs.writeFileSync(dbPath, '')
            return { ok: true, msg: `sqlite at ${dbPath}` }
        } catch (e) {
            return { ok: false, msg: e.message }
        }
    }

    if (step === 'migrate-sqlite') {
        return await runPhp(['artisan', 'migrate', '--database=sqlite_local', '--force'], laravelRoot)
    }

    if (step === 'sync-database') {
        return await runPhp(['artisan', 'sync:database'], laravelRoot)
    }

    if (step === 'start-server') {
        const server = spawn(process.execPath, ['-S'], { detached: true })
        return { ok: true, msg: 'server started' }
    }

    if (step === 'serve-artisan') {
        const artisan = spawn(phpBinary, ['artisan', 'serve', '--host=127.0.0.1', '--port=8000'], {
            cwd: laravelRoot,
            stdio: ['ignore', 'pipe', 'pipe']
        })
        artisan.stdout.on('data', d => mainWindow.webContents.send('log', String(d)))
        artisan.stderr.on('data', d => mainWindow.webContents.send('error', String(d)))
        return { ok: true, pid: artisan.pid, msg: 'artisan serve started' }
    }

    if (step === 'open-app') {
        const url = 'http://127.0.0.1:8000'
        mainWindow.loadURL(url)
        return { ok: true, msg: `loaded ${url}` }
    }

    return { ok: false, msg: 'unknown step' }
})

function runPhp(args, cwd) {
    return new Promise((resolve) => {
        const proc = spawn(phpBinary, args, { cwd, env: process.env })
        let out = ''
        let err = ''
        proc.stdout.on('data', d => { out += d.toString(); mainWindow.webContents.send('log', d.toString()) })
        proc.stderr.on('data', d => { err += d.toString(); mainWindow.webContents.send('error', d.toString()) })
        proc.on('close', code => {
            if (code === 0) resolve({ ok: true, msg: out.trim() || 'ok' })
            else resolve({ ok: false, msg: err.trim() || `exit ${code}` })
        })
    })
}
