import { ref, onMounted, onUnmounted } from 'vue'

export function useSyncStatus() {
    const isOnline = ref(false)
    const isSyncing = ref(false)
    const lastSync = ref<string | null>(null)
    const status = ref('Offline')
    let intervalId: ReturnType<typeof setInterval> | null = null
    let syncingManually = false

    async function refreshStatus() {
        try {
            const res = await fetch('/sync/status', { cache: 'no-cache' })
            if (!res.ok) throw new Error()
            const data = await res.json()

            isOnline.value = data.online
            isSyncing.value = data.syncing
            lastSync.value = data.last_sync
            status.value = data.online ? 'Online' : 'Offline'

            if (data.online && !data.syncing && !syncingManually) {
                syncingManually = true
                await autoSync()
                syncingManually = false
            }
        } catch {
            isOnline.value = false
            status.value = 'Offline'
        }
    }

    async function autoSync() {
        try {
            isSyncing.value = true
            const res = await fetch('/sync/trigger', { method: 'POST' })
            if (res.ok) await refreshStatus()
        } catch {
        } finally {
            isSyncing.value = false
        }
    }

    onMounted(() => {
        refreshStatus()
        if (!intervalId)
            intervalId = setInterval(refreshStatus, 10000)
    })

    onUnmounted(() => {
        if (intervalId) {
            clearInterval(intervalId)
            intervalId = null
        }
    })

    return { isOnline, isSyncing, lastSync, status, refreshStatus, autoSync }
}