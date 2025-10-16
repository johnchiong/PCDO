const { contextBridge, ipcRenderer } = require('electron')
contextBridge.exposeInMainWorld('electronAPI', {
    runStep: (step) => ipcRenderer.invoke('run-step', step),
    onLog: (cb) => ipcRenderer.on('log', (e, d) => cb(d)),
    onError: (cb) => ipcRenderer.on('error', (e, d) => cb(d))
})
