<script setup lang="ts">
import AppContent from '@/components/AppContent.vue'
import AppShell from '@/components/AppShell.vue'
import AppSidebar from '@/components/AppSidebar.vue'
import AppSidebarHeader from '@/components/AppSidebarHeader.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />

        <AppContent variant="sidebar" class="overflow-x-hidden">
            <div class="flex items-center justify-between px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            </div>

            <slot />
        </AppContent>
    </AppShell>
</template>
