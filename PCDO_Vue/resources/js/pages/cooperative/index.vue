<script setup lang="ts"> 
import { ref, computed } from 'vue'
import Button from '@/components/ui/button/Button.vue';
import InputField from '@/components/ui/input/Input.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import cooperative from '@/routes/cooperative';
import { type BreadcrumbItem } from '@/types';
import { CheckCircle, XCircle, CircleDashed, Search } from 'lucide-vue-next';
import { Head, Link } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCaption,
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
];

// Search query state
const searchQuery = ref('')

// Computed filtered cooperatives
const filteredCooperatives = computed(() => {
    if (!searchQuery.value) return props.cooperatives
    return props.cooperatives.filter(coop =>
        coop.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        coop.program?.name?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        coop.status.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})
</script>

<template>
    <Head title="Cooperative" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Header with Search and Create Button -->
        <div class="flex items-center justify-between p-6">
            <div class="relative w-200"> 
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 h-4 w-4" />
                <InputField 
                    v-model="searchQuery"
                    placeholder="Search cooperatives..."
                    class="pl-9 pr-3"
                />
            </div>

            <!-- Create Cooperative Button -->
            <Link :href="cooperative.create().url">
                <Button>Create a Cooperative</Button>
            </Link>
        </div>

        <!-- Table -->
        <div class="p-6">
            <Table>
                <TableCaption>A list of all cooperatives</TableCaption>
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
                        <TableCell class="font-medium">{{ coop.name }}</TableCell>
                        <TableCell>{{ coop.program?.name ?? 'N/A' }}</TableCell>
                        <TableCell class="flex items-center gap-2">
                            <template v-if="coop.status === 'Complete'">
                                <CheckCircle class="text-green-500" />
                                <span class="text-green-600 font-bold">Complete</span>
                            </template>

                            <template v-else-if="coop.status === 'Pending'">
                                <CircleDashed class="text-yellow-500 animate-spin" />
                                <span class="text-yellow-500 font-bold">Pending</span>
                            </template>

                            <template v-else>
                                <XCircle class="text-red-500" />
                                <span class="text-red-600 font-bold">Incomplete</span>
                            </template>
                        </TableCell>
                        <TableCell class="text-right">
                            <Link :href="`/cooperative/${coop.id}/document`" class="text-blue-600 hover:underline">
                                View Documents
                            </Link>
                        </TableCell>
                    </TableRow>

                    <!-- Show message if no results -->
                    <TableRow v-if="filteredCooperatives.length === 0">
                        <TableCell colspan="4" class="text-center text-gray-500 py-4">
                            No cooperatives found.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </AppLayout>
</template>
