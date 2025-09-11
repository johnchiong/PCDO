<script setup lang="ts">
import { ref, computed } from 'vue'
import Button from '@/components/ui/button/Button.vue';
import InputField from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import cooperative from '@/routes/cooperative';
import { type BreadcrumbItem } from '@/types';
import { CheckCircle, XCircle, CircleDashed, Search, ArrowRightToLine, ArrowLeftToLine } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

interface Program {
    name: string
}

interface Cooperative {
    id: number
    name: string
    program?: Program
    status: string
}

const props = defineProps<{
    cooperatives: Cooperative[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cooperative',
        href: cooperative.index().url,
    },
]

// Search state
const searchQuery = ref('')

// Pagination state
const currentPage = ref(1)
const itemsPerPage = ref(10)

// Filter cooperatives by search
const filteredCooperatives = computed(() => {
    if (!searchQuery.value) return props.cooperatives
    return props.cooperatives.filter(coop =>
        coop.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        coop.program?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        coop.status.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

// Paginated cooperatives
const paginatedCooperatives = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    return filteredCooperatives.value.slice(start, start + itemsPerPage.value)
})

// Total pages
const totalPages = computed(() => Math.ceil(filteredCooperatives.value.length / itemsPerPage.value))

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page
    }
}
</script>

<template>
    <Head title="Cooperative" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Container -->
        <div class="p-6 space-y-6">
            <!-- Search & Button -->
            <div class="flex items-center justify-between">
                <div class="relative w-80">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-400 h-4 w-4"
                    />
                    <InputField
                        v-model="searchQuery"
                        placeholder="Search cooperatives..."
                        class="pl-9 pr-3 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl shadow-sm 
                               text-gray-800 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-blue-500"
                    />
                </div>

                <Link :href="cooperative.create().url">
                    <Button
                        class="rounded-xl px-4 py-2 shadow-sm bg-blue-600 hover:bg-blue-700 text-white"
                    >
                        Create a Cooperative
                    </Button>
                </Link>
            </div>

            <!-- Table Container -->
            <div
                class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-md rounded-xl p-4"
            >
                <Table>
                    <TableHeader class="bg-gray-200 dark:bg-gray-800">
                        <TableRow>
                            <TableHead class="text-gray-600 dark:text-gray-400">Name</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-30">Program</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-30">Status</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-30">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="coop in paginatedCooperatives"
                            :key="coop.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            <TableCell class="font-medium text-gray-900 dark:text-gray-100">
                                {{ coop.name }}
                            </TableCell>
                            <TableCell class="text-gray-600 dark:text-gray-400 pl-30">
                                {{ coop.program?.name ?? 'N/A' }}
                            </TableCell>
                            <TableCell class="flex items-center gap-2 pl-30">
                                <template v-if="coop.status === 'Complete'">
                                    <CheckCircle class="text-green-500" />
                                    <span class="text-green-600 dark:text-green-400 font-semibold">Complete</span>
                                </template>

                                <template v-else-if="coop.status === 'Pending'">
                                    <CircleDashed class="text-yellow-500 animate-spin" />
                                    <span class="text-yellow-600 dark:text-yellow-400 font-semibold">Pending</span>
                                </template>

                                <template v-else>
                                    <XCircle class="text-red-500" />
                                    <span class="text-red-600 dark:text-red-400 font-semibold">Incomplete</span>
                                </template>
                            </TableCell>
                            <TableCell class="pl-30">
                                <Link
                                    :href="`/cooperative/${coop.id}/document`"
                                    class="text-blue-600 dark:text-blue-400 hover:underline transition"
                                >
                                    View Documents
                                </Link>
                            </TableCell>
                        </TableRow>

                        <TableRow v-if="paginatedCooperatives.length === 0">
                            <TableCell
                                colspan="4"
                                class="text-center text-gray-500 dark:text-gray-500 py-4 italic"
                            >
                                No cooperatives found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div class="flex justify-between items-center mt-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
                        {{ Math.min(currentPage * itemsPerPage, filteredCooperatives.length) }} of
                        {{ filteredCooperatives.length }} cooperatives
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            :disabled="currentPage === 1"
                            @click="goToPage(currentPage - 1)"
                            class="px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 
                                   hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50"
                        >
                            <ArrowLeftToLine class="h-5 w-5 stroke-current text-gray-700 dark:text-gray-200" />
                        </Button>
                        <span class="text-gray-800 dark:text-gray-200 font-medium">
                            Page {{ currentPage }} of {{ totalPages }}
                        </span>
                        <Button
                            :disabled="currentPage === totalPages"
                            @click="goToPage(currentPage + 1)"
                            class="px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 
                                   hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50"
                        >
                            <ArrowRightToLine class="h-5 w-5 stroke-current text-gray-700 dark:text-gray-200" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>