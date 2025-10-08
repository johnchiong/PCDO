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

const programGradients: Record<number, string> = {
    1: 'from-yellow-400 to-orange-500',
    2: 'from-blue-500 to-indigo-500',
    3: 'from-emerald-500 to-teal-600',
    4: 'from-red-400 to-pink-500',
    5: 'from-green-300 to-green-600'
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Programs', href: '/programs' },
    { title: props.program.name, href: '#' },
]
</script>

<template>

    <Head :title="program.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
            <div class="px-4 md:px-4 pt-5">
                <div class=" p-5 pb-2">

                    <!-- Program Header -->
                    <div
                        class="relative bg-gray-100 dark:bg-gray-800/80 border ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 rounded-xl shadow-m mb-6">

                        <!-- Gradient Top Bar -->
                        <div
                            :class="`absolute top-0 left-0 w-full h-2 rounded-t-xl bg-gradient-to-r ${programGradients[program.id] || 'from-blue-500 to-indigo-500'}`">
                        </div>

                        <!-- Header Content -->
                        <div class="relative px-6 py-5 flex items-center justify-between">
                            <!-- Left side: title & description -->
                            <div>
                                <h1
                                    class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
                                    <Pin class="w-8 h-8 text-red-600 dark:text-red-400" />
                                    {{ program.name }}
                                </h1>
                                <p
                                    class="text-gray-700 dark:text-gray-400 text-lg md:text-m font-medium leading-relaxed max-w-2xl">
                                    <em>{{ programDescriptions[program.name] }}</em>
                                </p>
                            </div>

                            <!-- Right side: Add Cooperative Button -->
                            <div>
                                <Link :href="`/programs/${program.id}/cooperatives/create`"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-md transition">
                                <Plus class="w-4 h-4 text-green-300 dark:text-green-400" />
                                Add Cooperative
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cooperatives List -->
                <div
                    class="mr-6 ml-6 bg-gray-100 dark:bg-gray-800 rounded-lg shadow-md border-2 border-gray-200 dark:border-gray-700 p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-6">
                        Cooperatives under this Program
                    </h2>

                    <div class="hidden md:block overflow-x-auto rounded-2xl">
                        <Table class="min-w-full border-separate border-spacing-0 text-sm">
                            <TableHeader
                                class="bg-gray-200/90 dark:bg-gray-700/50 border-b border-gray-500 dark:border-gray-500 text-gray-700 dark:text-gray-400">
                                <TableRow>
                                    <TableHead class="py-3 pl-6"> # </TableHead>
                                    <TableHead class="pl-30 py-3"> Cooperative Name </TableHead>
                                    <TableHead class="pl-30 py-3"> Status </TableHead>
                                    <TableHead class="pl-30 py-3"> Actions </TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody class="bg-white dark:bg-gray-900/50">
                                <TableRow v-for="(coop, index) in cooperatives" :key="coop.id"
                                    class="hover:bg-gray-300 dark:hover:bg-gray-700 transition-colors bg-gray-200/ dark:bg-gray-800">
                                    <TableCell class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ index + 1 }}
                                    </TableCell>
                                    <TableCell class="pl-30 py-3 font-medium text-gray-900 dark:text-gray-100">{{
                                        coop.name
                                    }}</TableCell>
                                    <TableCell class="pl-30 py-3">
                                        <span v-if="coop.program_status === 'Finished'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                                        bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                                            <CheckCircle class="w-3 h-3" /> <!-- ✅ finished icon -->
                                            {{ coop.program_status }}
                                        </span>

                                        <span v-else-if="coop.program_status === 'Ongoing'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                                        bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                                            <CircleDashed class="w-3 h-3 animate-spin" /> <!-- ⭕ ongoing icon -->
                                            {{ coop.program_status }}
                                        </span>
                                    </TableCell>
                                    <TableCell class="pl-30 py-3">
                                        <Link :href="`/programs/${program.id}/cooperatives/${coop.id}/checklist`"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-md transition">
                                            <Upload class="w-4 h-4"/>
                                            <span>Upload Checklist</span>
                                        </Link>
                                    </TableCell>
                                </TableRow>

                                <TableRow v-if="cooperatives.length === 0">
                                    <TableCell colspan="4"
                                        class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                        🚫 No cooperatives enrolled in this program yet.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
