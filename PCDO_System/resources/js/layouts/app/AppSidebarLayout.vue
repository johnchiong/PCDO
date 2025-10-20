<script setup lang="ts">
import AppContent from '@/components/AppContent.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'
import { useSyncStatus } from '@/composables/useSyncStatus'
import type { BreadcrumbItemType } from '@/types'
import { computed } from 'vue'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const { isOnline, isSyncing, lastSync, status } = useSyncStatus()

const statusColor = computed(() => {
    if (isSyncing.value) return 'bg-blue-500'
    return isOnline.value ? 'bg-green-500' : 'bg-red-500'
})

const formattedLastSync = computed(() => {
    if (!lastSync.value) return null
    try {
        return new Date(lastSync.value).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: Intl.DateTimeFormat().resolvedOptions().timeZone
        })
    } catch {
        return null
    }
})
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />

        <AppContent variant="sidebar" class="overflow-x-hidden">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <div class="flex items-center gap-2 text-sm">
                    <div
                        :class="[
                            'w-2.5 h-2.5 rounded-full transition-all duration-300',
                            statusColor
                        ]"
                    />
                    <span
                        class="font-medium transition-colors duration-300"
                        :class="{ 'text-gray-700': isOnline, 'text-red-500': !isOnline }"
                    >
                        {{ isSyncing ? 'Syncing…' : status }}
                    </span>

                    <span v-if="formattedLastSync" class="text-xs text-gray-400">
                        • Last sync: {{ formattedLastSync }}
                    </span>
                </div>
            </div>

            <slot />
        </AppContent>
    </AppShell>
</template>
