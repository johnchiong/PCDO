<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import payments from '@/routes/payments';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';


const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Payments',
        href: payments.index().url,
    },
];
</script>

<template>
    <Head title="Payments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <h1 class="text-2xl font-bold">Payments Page</h1>
            <p class="mt-4 text-gray-600">This is the Payments page content.</p>
        </div>
    </AppLayout>
</template>
