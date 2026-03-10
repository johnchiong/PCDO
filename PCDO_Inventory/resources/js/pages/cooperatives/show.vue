<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/InventoryLayout.vue'
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Details', href: `` }
]

const props = defineProps<{
    cooperative: any
    reportingDate: any
    reportingDateId: number
}>()

function groupByCategory(inventories: any[]) {
    const grouped: Record<string, any[]> = {}

    inventories.forEach(item => {
        if (!grouped[item.category]) {
            grouped[item.category] = []
        }
        grouped[item.category].push(item)
    })

    return grouped
}
</script>

<template>

    <Head :title="cooperative.coop_name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="show-page-wrapper">
            <div class="coop-header">

                <!-- LEFT SIDE -->
                <div class="coop-header-left">
                    <h1 class="coop-title">
                        {{ cooperative.name }}
                    </h1>

                    <p class="coop-description">
                        Cooperative Inventory Details
                    </p>
                </div>

                <!-- RIGHT SIDE -->
                <div class="coop-header-right">
                    <span class="report-label">Reporting Period</span>

                    <span class="report-badge">
                        {{ reportingDate.reporting_month }}/{{ reportingDate.reporting_year }}
                    </span>
                </div>

            </div>

            <div class="instance-card">
                <h2>Details</h2>
                <table class="details-info-table">
                    <tbody>
                        <tr>
                            <td>Address</td>
                            <td>{{ cooperative.barangay.name }}, {{ cooperative.city.name }}, {{
                                cooperative.province.name }}, {{ cooperative.region.name }}</td>
                        </tr>
                        <tr>
                            <td>Email</td>
                            <td>{{ cooperative.email }}</td>
                        </tr>
                        <tr>
                            <td>Contact Number</td>
                            <td>{{ cooperative.number }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Instances -->
            <div v-for="instance in cooperative.instances" :key="instance.id" class="instance-card">
                <details v-for="(items, category) in groupByCategory(instance.inventories)" :key="category" open>
                    <summary>{{ category }}</summary>
                    <table class="inventory-data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Quantity</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Guarantor Agency</th>
                                <th>Acquire Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in items" :key="item.id">
                                <td>{{ item.name }}</td>
                                <td>{{ item.quantity }}</td>
                                <td>₱ {{ item.value }}</td>
                                <td>{{ item.status }}</td>
                                <td>{{ item.guarantor_agency }}</td>
                                <td>{{ item.acquired_date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </details>
            </div>

            <!-- Back Button -->
            <button @click="$inertia.visit(`/cooperatives?reporting_date_id=${reportingDateId}`)" class="back-btn">
                Back to Cooperatives
            </button>
        </div>
    </AppLayout>
</template>