<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import cooperative from '@/routes/cooperative';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Cooperative',
        href: cooperative.index().url,
    },
];
</script>

<template>
    <Head title="Cooperative" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6" >
            <Link :href="cooperative.create().url"><Button>Create a Cooperative</Button></Link>
        </div>
    </AppLayout>
</template>
