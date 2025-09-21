<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import cooperatives from '@/routes/cooperatives'
import SelectSearch from '@/components/SelectSearch.vue'
import Label from '@/components/ui/label/Label.vue'
import type { Cooperative, Regions, Provinces, Municipalities, Barangays } from '@/types/cooperatives'
import { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Cooperatives', href: cooperatives.index().url },
  { title: 'Create Cooperatives', href: '' },
]

const props = defineProps<{
    cooperatives: Cooperative[],
    regions: Regions[],
    provinces: Provinces[],
    municipalities: Municipalities[],
    barangays: Barangays[],
}>()

// inertia form
const form = useForm({
    id: '',
    name: '',
    region_id: 0,
    province_id: 0,
    municipality_id: 0,
    barangay_id: 0,
    asset_size: '',
    coop_type: '',
    status_category: 'New',
    bond_of_membership: 'Residential',
    area_of_operation: 'Municipal',
    citizenship: 'Filipino',
    members_count: 0,
    total_asset: 0,
    net_surplus: 0,
})

// dropdown visibility
const showIdDropdown = ref(false)
const showNameDropdown = ref(false)

function hideIdDropdown() {
    setTimeout(() => (showIdDropdown.value = false), 200)
}
function hideNameDropdown() {
    setTimeout(() => (showNameDropdown.value = false), 200)
}

// region search state
const searchRegion = ref('')
const dropDownRegionOpen = ref(false)
const selectedRegion = ref<number | ''>('')

// filter for duplicate checking
const filteredCooperativesId = computed(() =>
    !form.id ? props.cooperatives : props.cooperatives.filter(c => c.id.toString().includes(form.id))
)
const filteredCooperativesName = computed(() =>
    !form.name ? props.cooperatives : props.cooperatives.filter(c =>
        c.name.toLowerCase().includes(form.name.toLowerCase())
    )
)

// helper
function isIdFormatValid(query: string) {
    const regex = /^\d{4}-\d{4,}$/ // e.g. 2024-1234
    return regex.test(query)
}

function onRegionSelect(payload: { name: string, id: number }) {
    selectedRegion.value = payload.id
    form.region_id = payload.id
    searchRegion.value = payload.name
    dropDownRegionOpen.value = false
}

function handleSubmit() {
    form.post('/cooperatives')
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="max-w-7x7 p-6">
            <div class="bg-white dark:bg-gray-800 shadow rounded-2xl p-8">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-6">
                    Create Cooperative
                </h1>

                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <!-- Coop ID -->
                    <div>
                        <Label for="id">Register Number</Label>
                        <div class="relative mt-1">
                            <input id="id" v-model="form.id" placeholder="Enter Register Number (e.g., 2024-1234)"
                                @focus="showIdDropdown = true" @blur="hideIdDropdown"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                            <ul v-if="showIdDropdown"
                                class="absolute z-10 bg-white dark:bg-gray-700 border mt-1 w-full rounded-xl shadow">
                                <li v-for="coop in filteredCooperativesId" :key="coop.id"
                                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                    {{ coop.id }} - {{ coop.name }}
                                </li>
                                <li v-if="filteredCooperativesId.length === 0" class="px-4 py-2 text-gray-500">
                                    No existing data
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Coop Name -->
                    <div>
                        <Label for="name">Cooperative Name</Label>
                        <div class="relative mt-1">
                            <input id="name" v-model="form.name" placeholder="Enter Cooperative Name"
                                @focus="showNameDropdown = true" @blur="hideNameDropdown"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                            <ul v-if="showNameDropdown"
                                class="absolute z-10 bg-white dark:bg-gray-700 border mt-1 w-full rounded-xl shadow">
                                <li v-for="coop in filteredCooperativesName" :key="coop.id"
                                    class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer">
                                    {{ coop.name }} ({{ coop.id }})
                                </li>
                                <li v-if="filteredCooperativesName.length === 0" class="px-4 py-2 text-gray-500">
                                    No existing data
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Additional fields only when valid -->
                    <div v-if="
                        filteredCooperativesId.length === 0 &&
                        isIdFormatValid(form.id) &&
                        filteredCooperativesName.length === 0 &&
                        form.name
                    " class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t">
                        <!-- Region -->
                        <div>
                            <Label for="region" class="mb-2">Select Region</Label>
                            <SelectSearch id="region" :items="props.regions" itemLabelKey="name" itemKeyProp="id"
                                v-model:search="searchRegion" :open="dropDownRegionOpen" @select="onRegionSelect"
                                @update:open="val => dropDownRegionOpen = val" placeholder="Search Region" />
                        </div>

                        <!-- Member Count -->
                        <div>
                            <Label class="mb-2">Member Count</Label>
                            <input v-model="form.members_count" type="number"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <!-- Total Asset -->
                        <div>
                            <Label class="mb-2">Total Asset</Label>
                            <input v-model="form.total_asset" type="number"
                                class="w-full rounded-xl border border-gray-300 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <!-- Submit -->
                        <div class="pt-6 md:col-span-2">
                            <button type="submit"
                                class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl shadow hover:bg-indigo-700 transition">
                                Create Cooperative
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
