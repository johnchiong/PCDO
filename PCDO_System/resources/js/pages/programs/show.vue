<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'

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
    { title: 'Programs', href: '/programs' },
    { title: props.program.name, href: '#' },
]
</script>

<template>

    <Head :title="program.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class=" p-6 space-y-8">

            <!-- Program Header -->
            <div
                class="bg-gray-100 dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 p-8 flex items-center justify-between">

                <!-- Left side: title & description -->
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3">
                        📌 {{ program.name }}
                    </h1>
                    <p class="text-gray-600 dark:text-gray-300 text-lg leading-relaxed">
                        {{ programDescriptions[program.name] }}
                    </p>
                </div>

                <!-- Right side: Add Cooperative Button -->
                <div>
                    <Link :href="`/programs/${program.id}/cooperatives/create`"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                    ➕ Add Cooperative
                    </Link>
                </div>
            </div>

        </div>

        <!-- Cooperatives List -->
        <div
            class="mr-6 ml-6 bg-gray-100 dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
                📋 Cooperatives under this Program
            </h2>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <Table>
                    <TableHeader class="bg-gray-300 dark:bg-gray-900">
                        <TableRow>
                            <TableHead
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">#
                            </TableHead>
                            <TableHead
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Cooperative Name</TableHead>
                            <TableHead
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Status</TableHead>
                            <TableHead
                                class="px-6 py-3 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">
                                Actions</TableHead>
                        </TableRow>
                    </TableHeader>

                    <TableBody>
                        <TableRow v-for="(coop, index) in cooperatives" :key="coop.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors bg-gray-200 dark:bg-gray-800">
                            <TableCell class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ index + 1 }}
                            </TableCell>
                            <TableCell class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ coop.name
                                }}</TableCell>
                            <TableCell class="px-6 py-4">
                                <span v-if="coop.program_status === 'completed'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                                    bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                    <CheckCircle class="w-3 h-3" /> <!-- ✅ finished icon -->
                                    Finished
                                </span>

                                <span v-else-if="coop.program_status === 'ongoing'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                                    bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                                    <CircleDashed class="w-3 h-3 animate-spin" /> <!-- ⭕ ongoing icon -->
                                    Ongoing
                                </span>

                                <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                                    bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                                    <XCircle class="w-3 h-3" /> <!-- ❌ inactive/other -->
                                    {{ coop.program_status }}
                                </span>
                            </TableCell>
                            <TableCell>
                                <Link :href="`/programs/${program.id}/cooperatives/${coop.id}/checklist`"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-md transition">
                                📤 Upload Checklist
                                </Link>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="cooperatives.length === 0">
                            <TableCell colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                🚫 No cooperatives enrolled in this program yet.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>
</template>
