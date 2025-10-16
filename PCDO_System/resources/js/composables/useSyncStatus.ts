import { ref, onMounted } from 'vue'

export function useSyncStatus() {
    const isOnline = ref(false)
    const status = ref<'Online' | 'Offline'>('Offline')

    async function checkStatus() {
        try {
            const res = await fetch('/ping', { method: 'GET', cache: 'no-cache' })
            if (res.ok) {
                status.value = 'Online'
                isOnline.value = true
            } else {
                status.value = 'Offline'
                isOnline.value = false
            }
        } catch {
            status.value = 'Offline'
            isOnline.value = false
        }
    }

    onMounted(() => {
        checkStatus()
        setInterval(checkStatus, 10000)
    })

    return { isOnline, status, checkStatus }
}
