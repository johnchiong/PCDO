<script setup lang="ts"> 
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';

defineProps({
  cooperatives: Array,
  breadcrumbs: Array,
})



const searchQuery = ref('')

const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredCooperatives = computed(() => {
    if (!searchQuery.value) {
        return cooperatives;
    }
    return cooperatives.filter(coop => 
        coop.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        coop.holder.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});
</script>

<template>
    <Head :title="`| ${$page.component}`" />

    <AppLayout :breadcrumbs="breadcrumbs">">
        <div class="flex items-center justify-between p-6">
            <div class="relative w-200"> 
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 h-4 w-4" />
                <InputField 
                    v-model="searchQuery"
                    placeholder="Search cooperatives..."
                    class="pl-9 pr-3"
                />
            </div>

            <Link :href="route('cooperatives.create')">
                <Button>Add New Cooperative</Button>
            </Link>
            <Link :href="route('cooperatives.import')">
                <Button>Import Data</Button>
            </Link>
            <Link :href="route('cooperatives.export')">
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
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="coop in filteredCooperatives" :key="coop.id">
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
                            <Link :href="route('cooperatives.show', coop.id)" class="text-blue-600 hover:underline">
                                View
                            </Link>
                            <Link :href="route('cooperatives.edit'), coop.id" class="text-green-600 hover:underline">
                                Edit
                            </Link>
                            <Link :href="route('cooperatives.delete', coop.id)" class="text-red-600 hover:underline">
                                Delete
                            </Link>
                        </TableCell>
                    </TableRow>

                    <TableRow v-if="filteredCooperatives.length === 0">
                        <TableCell colspan="4" class="text-center text-gray-500 py-4">
                            No Cooperatives found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>