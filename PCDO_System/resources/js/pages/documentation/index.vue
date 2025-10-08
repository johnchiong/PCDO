<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps<{
    programs: Array<{
        id: number
        name: string
        completed_coops_count: number
    }>
}>()

const programDescriptions: Record<string, string> = {
    USAD: 'Upgrading Support for Advancement and Development of Enterprises in Cooperative',
    LICAP: 'Livelihood Credit Assistance Program',
    COPSE: 'Cooperative Program For Sustainable Enterprise',
    SULONG: 'Sustained Livelihood Opportunities and Growth',
    PCLRP: 'Provincial Cooperative Livelihood Recovery Program'
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Documentation', href: '#' },
]
</script>

<template>
    <Head title="Documentation" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <Link v-for="program in props.programs" :key="program.id" :href="`/documentation/${program.id}`" class="rounded-2xl shadow-md border border-gray-300 dark:border-gray-700 
                 bg-gray-200 dark:bg-gray-800 
                 hover:shadow-xl hover:-translate-y-1 transform transition-all block">
                <div class="h-2 rounded-t-2xl bg-gradient-to-r from-blue-500 to-indigo-500"></div>

                <div class="p-5 flex flex-col h-full">
                    <h2 class="text-xl font-bold mb-1 text-gray-900 dark:text-gray-100">
                        {{ program.name }}
                    </h2>

                    <p class="text-gray-700 dark:text-gray-300 text-sm mb-4">
                        {{ programDescriptions[program.name] }}
                    </p>

                    <div class="mt-auto flex items-center justify-between">
                        <span class="text-sm font-medium text-green-700 dark:text-green-400">
                            Completed Cooperatives
                        </span>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold 
                       bg-blue-100 text-blue-700 
                       dark:bg-blue-900 dark:text-blue-200">
                            {{ program.completed_coops_count }}
                        </span>
                    </div>
                </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
