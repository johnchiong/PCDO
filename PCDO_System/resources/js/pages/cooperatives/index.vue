<script setup lang="ts"> 
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

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
    breadcrumbs: { title: string; href?: string }[];
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

    <AppLayout :Breadcrumbs="breadcrumbs">
        <div class="flex items-center justify-between p-6">
            <div class="relative w-200"> 
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 h-4 w-4" />
                <InputField
                    v-model="searchQuery"
                    placeholder="Search cooperatives..."
                    class="pl-9 pr-3"
                />
            </div>

            <div>
                <Button @click="goToCreatePage" class="bg-blue-600 text-white hover:bg-blue-700 disabled:bg-blue-900 disabled:text-gray-400 px-6 py-2 rounded-lg">
                    Create
                </Button>
            </div>
            <Link :href="`/cooperatives/import`">
                <Button>Import Data</Button>
            </Link>
            <Link :href="`/cooperatives/export`">
                <Button>Export Data</Button>
            </Link>
        </div>

        <div class="p-6">
            <Table>
                <TableCaption>A list of all Cooperatives</TableCaption>
                <TableHeader>
                    <TableRow>
                        <TableHead>Name</TableHead>
                        <TableHead>Program</TableHead>
                        <TableHead>Type</TableHead>
                        <TableHead>Holder</TableHead>
                        <TableHead>Member Count</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="coop in paginatedCooperatives" :key="coop.id">
                        <TableCell>{{ coop.id}}</TableCell>
                        <TableCell class="font-medium">{{ coop.name }}</TableCell>
                        <TableCell>{{ coop.type}}</TableCell>
                        <TableCell>{{ coop.holder}}</TableCell>
                        <TableCell>{{ coop.member_count}}</TableCell>
                        <TableCell class="flex items-center gap-2">
                            <template v-if="coop.has_ongoing_program">
                                <span class="text-green-600 font-bold">Ongoing</span>
                            </template>

                            <template v-else>
                                <span class="text-red-600 font-bold">—</span>
                            </template>
                        </TableCell>
                        <TableCell class="text-right">
                            <Link :href="`/cooperatives/${coop.id}/show`" class="text-blue-600 hover:underline">
                                View
                            </Link>
                            <Link :href="`/cooperatives/${coop.id}/edit`" class="text-green-600 hover:underline">
                                Edit
                            </Link>
                            <Link :href="`/cooperatives/${coop.id}/delete`" class="text-red-600 hover:underline">
                                Delete
                            </Link>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="paginatedCooperatives.length === 0">
                        <TableCell colspan="6" class="text-center text-gray-500 py-4">
                            No Cooperatives found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            <div class="flex justify-end items-center space-x-2 mt-4">
                <Button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">Previous</Button>
                <span>Page {{ currentPage }} of {{ totalPages }}</span>
                <Button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">Next</Button>
            </div>
        </div>
    </AppLayout>
</template>