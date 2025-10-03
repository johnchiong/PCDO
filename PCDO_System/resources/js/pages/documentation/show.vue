<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps<{
    program: {
        id: number
        name: string
        description: string
    },
    cooperatives: Array<{
        id: number
        name: string
        program_status: string
        year?: string
        files?: Array<{ id: number, name: string, url: string }>
    }>,
    selectedYear?: string
}>()

const programDescriptions: Record<string, string> = {
    USAD: 'Upgrading Support for Advancement and Development of Enterprises in Cooperative',
    LICAP: 'Livelihood Credit Assistance Program',
    COPSE: 'Cooperative Program For Sustainable Enterprise',
    SULONG: 'Sustained Livelihood Opportunities and Growth',
    PCLRP: 'Provincial Cooperative Livelihood Recovery Program'
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Documentation', href: '/documentation' },
    { title: props.program.name, href: '#' },
]

// Build year options (this year and past 4)
const currentYear = new Date().getFullYear()
const years = Array.from({ length: 5 }, (_, i) => currentYear - i)

// Selected year state (sync with backend)
const selectedYear = ref(props.selectedYear ?? '')

// Reactively update when dropdown changes
watch(selectedYear, (newYear) => {
    router.get(`/documentation/${props.program.id}`, { year: newYear || undefined })
})
</script>

<template>
    <Head :title="program.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Program Header -->
            <div
                class="bg-gray-100 dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 p-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3">
                    📌 {{ program.name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                    {{ programDescriptions[program.name] }}
                </p>
            </div>

            <!-- Completed Cooperatives -->
            <div
                class="bg-gray-100 dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        ✅ Completed Cooperatives
                    </h2>

                    <!-- Year Filter Dropdown -->
                    <select
                        v-model="selectedYear"
                        class="border rounded px-3 py-2 text-sm dark:bg-gray-900 dark:text-gray-200"
                    >
                        <option value="">All Years</option>
                        <option v-for="y in years" :key="y" :value="String(y)">
                            {{ y }}
                        </option>
                    </select>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <Table>
                        <TableHeader class="bg-gray-300 dark:bg-gray-900">
                            <TableRow>
                                <TableHead class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">#</TableHead>
                                <TableHead class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Cooperative Name</TableHead>
                                <TableHead class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Status</TableHead>
                                <TableHead class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Year</TableHead>
                                <TableHead class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Action</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="(coop, index) in cooperatives" :key="coop.id"
                                class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors bg-gray-200 dark:bg-gray-800">
                                <TableCell class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    {{ index + 1 }}
                                </TableCell>
                                <TableCell class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">
                                    {{ coop.name }}
                                </TableCell>
                                <TableCell class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                                        bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                        ✅ Finished
                                    </span>
                                </TableCell>
                                <TableCell class="px-6 py-4">
                                    {{ coop.year ?? '—' }}
                                </TableCell>
                                <TableCell class="px-6 py-4">
                                    <Link :href="`/documentation/history?program_id=${program.id}&coop_id=${coop.id}`"
                                        class="px-3 py-1 text-xs font-medium rounded bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200 hover:bg-blue-200 dark:hover:bg-blue-800 transition">
                                        View History
                                    </Link>
                                </TableCell>
                            </TableRow>

                            <TableRow v-if="cooperatives.length === 0">
                                <TableCell colspan="5" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                    🚫 No completed cooperatives for this program yet.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
