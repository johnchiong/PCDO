<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/InventoryLayout.vue'
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Cooperatives', href: '/cooperatives' }
]

const props = defineProps<{
    cooperatives: any[]
    inventoryCounts: Record<number, number>
    inventoryStatus: Record<number, any>
    reportingDate: any
    reportingDates: any[]
    selectedReportingDate: number
    categories: { value: string; label: string }[]
    inventoryNames: Record<number, any[]>
    regions: any[],
    provinces: any[],
    cities: any[],
    filters: any
}>()

const showModal = ref(false)
const form = ref({
    reporting_month: '',
    reporting_year: ''
})
const selectedDate = ref(props.selectedReportingDate)

const selectedCategory = ref('all');

const dynamicCategories = computed(() => [
    { value: 'all', label: 'All' },
    ...props.categories
]);

const filteredCooperatives = computed(() => {
    if (selectedCategory.value === 'all') return props.cooperatives;

    return props.cooperatives.filter(coop => {
        const status = props.inventoryStatus[coop.id];
        if (!status) return false;

        return (status[selectedCategory.value]?.servicable ?? 0) > 0 ||
            (status[selectedCategory.value]?.unservicable ?? 0) > 0;
    });
});

function filterDate() {
    router.get('/cooperatives', {
        reporting_date_id: selectedDate.value
    }, { preserveState: true })
}

function openCoop(id: number) {
    router.visit(`/cooperatives/${id}?reporting_date_id=${props.selectedReportingDate}`)
}

function openModal() {
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

function submitReportingDate() {
    if (!form.value.reporting_month || !form.value.reporting_year) return

    router.post('/reporting-dates', form.value, {
        onSuccess: () => {
            closeModal()
        }
    })
}

function handleDateInput(event: Event) {
    const value = (event.target as HTMLInputElement).value

    if (!value) return

    const date = new Date(value)

    form.value.reporting_month = String(date.getMonth() + 1)
    form.value.reporting_year = String(date.getFullYear())
}

function totalServicable(coopId: number) {
    const status = props.inventoryStatus[coopId]
    if (!status) return 0

    return Object.values(status).reduce((sum: number, cat: any) => {
        return sum + (cat.servicable ?? 0)
    }, 0)
}

function totalUnservicable(coopId: number) {
    const status = props.inventoryStatus[coopId]
    if (!status) return 0

    return Object.values(status).reduce((sum: number, cat: any) => {
        return sum + (cat.unservicable ?? 0)
    }, 0)
}

const filterForm = ref({
    reporting_date_id: props.selectedReportingDate,
    region_code: props.filters.region_code ?? '',
    province_code: props.filters.province_code ?? '',
    city_code: props.filters.city_code ?? ''
})

function applyFilters() {
    router.get('/cooperatives', filterForm.value, {
        preserveState: true,
        replace: true
    })
}
</script>

<template>

    <Head title="Cooperatives" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="coop-page">
            <div class="coop-header">

                <div class="coop-header-left">
                    <h1 class="coop-title">
                        Cooperatives
                    </h1>

                    <p class="coop-description">
                        Manage cooperative inventory reports.
                    </p>
                </div>

                <div class="coop-header-right">
                    <span class="report-label">Reporting Period</span>
                    <span class="report-badge">
                        {{ reportingDate?.reporting_month }}/{{ reportingDate?.reporting_year }}
                    </span>
                </div>

            </div>

            <div class="coop-card">
                <!-- HEADER -->
                <div class="coop-card-header">
                    <div class="coop-filter">
                        <div class="coop-filter">
                            <select v-model="selectedDate" @change="filterDate" class="coop-select">
                                <option v-for="date in reportingDates" :key="date.id" :value="date.id">
                                    {{ date.reporting_month }}/{{ date.reporting_year }}
                                </option>
                            </select>

                            <select v-model="selectedCategory" class="coop-select ml-2">
                                <option v-for="cat in dynamicCategories" :key="cat.value" :value="cat.value">
                                    {{ cat.label }}
                                </option>
                            </select>
                            <select v-model="filterForm.region_code" @change="applyFilters" class="coop-select ml-2">
                                <option value="">All Regions</option>
                                <option v-for="r in regions" :value="r.code">{{ r.name }}</option>
                            </select>

                            <select v-model="filterForm.province_code" @change="applyFilters" class="coop-select ml-2">
                                <option value="">All Provinces</option>
                                <option v-for="p in provinces" :value="p.code">{{ p.name }}</option>
                            </select>

                            <select v-model="filterForm.city_code" @change="applyFilters" class="coop-select ml-2">
                                <option value="">All Cities</option>
                                <option v-for="c in cities" :value="c.code">{{ c.name }}</option>
                            </select>
                        </div>
                    </div>
                    <button @click="openModal" class="coop-btn-primary">
                        Add Reporting Date
                    </button>
                </div>


                <!-- TABLE -->
                <table class="coop-table">
                    <thead>
                        <tr>
                            <th>Cooperative</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Inventory Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="filteredCooperatives.length === 0">
                            <td colspan="4" class="coop-empty">
                                No Cooperative Registered on Form
                            </td>
                        </tr>
                        <tr v-for="coop in filteredCooperatives" :key="coop.id" class="coop-row"
                            @click="openCoop(coop.id)">
                            <td>{{ coop.name }}</td>
                            <td>
                                <div v-if="props.inventoryNames[coop.id]">
                                    <div v-for="item in props.inventoryNames[coop.id]" :key="item.name">
                                        {{ item.name }}
                                    </div>
                                </div>

                                <span v-else>-</span>
                            </td>
                            <td>
                                <span v-if="selectedCategory === 'all'">
                                    Serviceable {{ totalServicable(coop.id) }}
                                    |
                                    Unserviceable {{ totalUnservicable(coop.id) }}
                                </span>

                                <span v-else>
                                    Serviceable {{ inventoryStatus[coop.id]?.[selectedCategory]?.servicable ?? 0 }}
                                    |
                                    Unservicable {{ inventoryStatus[coop.id]?.[selectedCategory]?.unservicable ?? 0 }}
                                </span>
                            </td>

                            <td>{{ inventoryCounts[coop.id] ?? 0 }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- SIMPLE PAGINATION -->
                <div class="coop-pagination">
                    <div class="pagination-info">
                        Showing 1–{{ cooperatives.length }} of {{ cooperatives.length }} cooperatives
                    </div>
                    <div class="pagination-controls">
                        <button class="pagination-btn">Previous</button>

                        <button class="pagination-btn active">1</button>

                        <button class="pagination-btn">Next</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL -->
        <div v-if="showModal" class="modal-overlay">
            <div class="modal-box">
                <h2 class="modal-title">
                    Add Reporting Date
                </h2>
                <input type="month" @change="handleDateInput" class="modal-input" />
                <div class="modal-actions">
                    <button @click="closeModal" class="modal-cancel">
                        Cancel
                    </button>

                    <button @click="submitReportingDate" class="modal-save">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>