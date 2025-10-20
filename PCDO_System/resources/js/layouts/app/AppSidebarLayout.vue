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

const { isOnline} = useSyncStatus()

const statusColor = computed(() => {
    return isOnline.value ? 'bg-green-500' : 'bg-red-500'
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
                </div>
            </div>

            <slot />
        </AppContent>
    </AppShell>
</template>
