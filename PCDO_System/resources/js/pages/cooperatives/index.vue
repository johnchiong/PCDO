<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { BreadcrumbItem } from '@/types'
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Cooperatives', href: '' },
]

interface Cooperative {
    id: number;
    name: string;
    type: string;
    holder: string;
    member_count: number;
    has_ongoing_program: boolean;
}

const props = defineProps<{
    cooperatives: Cooperative[];
}>();

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredCooperatives = computed(() => {
    if (!searchQuery.value) {
        return props.cooperatives;
    }
    return props.cooperatives.filter(coop =>
        coop.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        coop.id.toString().includes(searchQuery.value) ||
        (coop.holder?.toLowerCase().includes(searchQuery.value.toLowerCase() ?? false))
    );
});

const paginatedCooperatives = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value;
    return filteredCooperatives.value.slice(start, start + itemsPerPage.value);
});

const totalPages = computed(() => {
    return Math.ceil(filteredCooperatives.value.length / itemsPerPage.value);
});

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

function goToCreatePage() {
    router.visit('/cooperatives/create');
}
</script>

<template>

    <Head :title="`| ${$page.component}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Top Actions -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
            <!-- Search -->
            <div class="relative w-full md:w-80">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 h-4 w-4" />
                <InputField v-model="searchQuery" placeholder="Search cooperatives..."
                    class="pl-9 pr-3 w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500" />
            </div>

            <!-- Buttons -->
            <div class="flex flex-wrap gap-3">
                <Button @click="goToCreatePage"
                    class="bg-indigo-600 text-white hover:bg-indigo-700 px-5 py-2 rounded-lg shadow">
                    Create
                </Button>
                <Link :href="`/cooperatives/import`">
                <Button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg shadow">
                    Import Data
                </Button>
                </Link>
                <Link :href="`/cooperatives/export`">
                <Button class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-lg shadow">
                    Export Data
                </Button>
                </Link>
            </div>
        </div>

        <!-- Table Card -->
        <div class="p-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-2xl overflow-hidden">
                <Table>
                    <TableCaption class="text-lg font-semibold p-4">List of Cooperatives</TableCaption>
                    <TableHeader class="bg-gray-100 dark:bg-gray-700">
                        <TableRow>
                            <TableHead>ID</TableHead>
                            <TableHead class="pl-20">Name</TableHead>
                            <TableHead class="pl-20">Type</TableHead>
                            <TableHead class="pl-20">Holder</TableHead>
                            <TableHead class="pl-20">Member Count</TableHead>
                            <TableHead class="pl-20">Status</TableHead>
                            <TableHead class="pl-22">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="coop in paginatedCooperatives" :key="coop.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <TableCell class="font-medium text-gray-600">{{ coop.id }}</TableCell>
                            <TableCell class="font-semibold text-gray-900 dark:text-gray-100 pl-20">{{ coop.name }}
                            </TableCell>
                            <TableCell class="pl-20">{{ coop.type }}</TableCell>
                            <TableCell class="pl-20">{{ coop.holder }}</TableCell>
                            <TableCell class="pl-20">{{ coop.member_count }}</TableCell>
                            <TableCell class="pl-20">
                                <span v-if="coop.has_ongoing_program" class="px-3 py-1 text-xs font-medium rounded-full
                                bg-green-100 text-green-700 
                                dark:bg-green-700 dark:text-green-100">
                                    Ongoing
                                </span>
                                <span v-else class="px-3 py-1 text-xs font-medium rounded-full
                                bg-red-100 text-red-700 
                                dark:bg-red-700 dark:text-red-100">
                                    Inactive
                                </span>
                            </TableCell>

                            <TableCell class="text-right space-x-2 pl-20">
                                <Link :href="`/cooperatives/${coop.id}/show`" class="px-3 py-1 rounded-lg 
                                bg-blue-500 text-white hover:bg-blue-600 
                                dark:bg-blue-600 dark:hover:bg-blue-500">
                                View
                                </Link>
                                <Link :href="`/cooperatives/${coop.id}/edit`" class="px-3 py-1 rounded-lg 
                                bg-green-500 text-white hover:bg-green-600 
                                dark:bg-green-600 dark:hover:bg-green-500">
                                Edit
                                </Link>
                                <Link :href="`/cooperatives/${coop.id}/delete`" class="px-3 py-1 rounded-lg 
                                bg-red-500 text-white hover:bg-red-600 
                                dark:bg-red-600 dark:hover:bg-red-500">
                                Delete
                                </Link>
                            </TableCell>
                        </TableRow>

                            <TableRow v-if="paginatedCooperatives.length === 0">
                                <TableCell colspan="7" class="text-center text-gray-500 py-6">
                                    No Cooperatives found.
                                </TableCell>
                            </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-6">
                <span class="text-sm text-gray-600 dark:text-gray-300">
                    Showing {{ (currentPage - 1) * itemsPerPage + 1 }}
                    -
                    {{ Math.min(currentPage * itemsPerPage, filteredCooperatives.length) }}
                    of {{ filteredCooperatives.length }}
                </span>
                <div class="flex items-center gap-2">
                    <Button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)" class="px-4 py-2 rounded-lg 
                   bg-white border border-gray-300 text-gray-700 
                   hover:bg-gray-100 hover:text-black 
                   dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 
                   dark:hover:bg-gray-700
                   disabled:opacity-50 disabled:cursor-not-allowed">
                        Previous
                    </Button>
                    <span class="text-sm text-gray-700 dark:text-gray-300">
                        Page {{ currentPage }} of {{ totalPages }}
                    </span>
                    <Button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)" class="px-4 py-2 rounded-lg 
                   bg-white border border-gray-300 text-gray-700 
                   hover:bg-gray-100 hover:text-black 
                   dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 
                   dark:hover:bg-gray-700
                   disabled:opacity-50 disabled:cursor-not-allowed">
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
