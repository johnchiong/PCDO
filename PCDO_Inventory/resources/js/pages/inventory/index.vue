<script setup lang="ts">
import AppLayout from '@/layouts/InventoryLayout.vue'
import { ref, computed, reactive, watch } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import SelectSearch from '@/components/SelectSearch.vue'
import type { Regions, Provinces, Cities, Barangays, CoopDetails } from '@/types/inventory'
import { BreadcrumbItem } from '@/types'
import { toast } from "vue-sonner"
// import { useDrafts } from '@/composables/useDrafts'
// import { usePolling } from '@/composables/usePolling'
import Label from '@/components/ui/label/Label.vue'
import Input from '@/components/ui/input/Input.vue'
import { usePage, router } from '@inertiajs/vue3'


const page = usePage<{ flash: { success?: string } }>()
const submitted = computed(() => !!page.props.flash?.success)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/inventory' }
]

/**
 * Props from backend and typescript interfaces.
 */

const today = new Date().toISOString().split('T')[0]

const props = defineProps<{
    regions: Regions[]
    provinces: Provinces[]
    cities: Cities[]
    barangays: Barangays[]
    inventory?: CoopDetails | null
}>()

/**
 * Normalize incoming backend data and provide default values.
 */
const normalized = computed(() => ({
    name: props.inventory?.name ?? '',
    region_code: props.inventory?.region_code ?? '1700000000',
    province_code: props.inventory?.province_code ?? '1705300000',
    city_code: props.inventory?.city_code ?? '',
    barangay_code: props.inventory?.barangay_code ?? '',
    email: props.inventory?.email ?? '@gmail.com',
    number: props.inventory?.number ?? '',
    inventoryItem: props.inventory?.inventoryItem ?? []
}))

/**
 * Initialize form with normalized data.
 */

const form = useForm({
    name: normalized.value.name,
    region_code: normalized.value.region_code,
    province_code: normalized.value.province_code,
    city_code: normalized.value.city_code,
    barangay_code: normalized.value.barangay_code,
    email: normalized.value.email,
    number: normalized.value.number,
    inventoryItem: normalized.value.inventoryItem.length
        ? normalized.value.inventoryItem
        : []
})

/**
 * Location selection state.
 */
const searchState = reactive({
    region_code: '',
    province_code: '',
    city_code: '',
    barangay_code: ''
})

const openState = reactive({
    region_code: false,
    province_code: false,
    city_code: false,
    barangay_code: false
})

const dependencyMap = {
    region_code: ["province_code", "city_code", "barangay_code"],
    province_code: ["city_code", "barangay_code"],
    city_code: ["barangay_code"],
    barangay_code: []
} as const

type LocationFields = "region_code" | "province_code" | "city_code" | "barangay_code"

function onSelect(field: LocationFields, payload: { id: string; name: string }) {
    form[field] = String(payload.id)
    searchState[field] = payload.name
    openState[field] = false

    dependencyMap[field].forEach(dep => {
        form[dep] = ""
        searchState[dep] = ""
    })
}

function getStatusOptions(quantity: number) {
    const q = Number(quantity) || 1
    const options: string[] = []

    for (let servicable = q; servicable >= 0; servicable--) {
        const unservicable = q - servicable

        if (servicable === 0) {
            options.push(`Unservicable ${unservicable}`)
        } else if (unservicable === 0) {
            options.push(`Servicable ${servicable}`)
        } else {
            options.push(`Servicable ${servicable} | Unservicable ${unservicable}`)
        }
    }

    return options
}

watch(() => form.inventoryItem.map(item => item.quantity), () => {
    form.inventoryItem.forEach(item => {
        item.status = ""
    })
})

const filteredProvinces = computed(() =>
    props.provinces.filter(p => String(p.region_code) === String(form.region_code))
)

const filteredCities = computed(() =>
    props.cities.filter(c => String(c.province_code) === String(form.province_code))
)

const filteredBarangays = computed(() =>
    props.barangays.filter(b => String(b.city_code) === String(form.city_code))
)

const categoryOptions = ['Equipment', 'Machinery', 'Facilities']

/**
 * Equipment management
 */

function addEquipment() {
    form.inventoryItem.push({
        id: Date.now(),
        category: '',
        name: '',
        guarantor_agency: '',
        location: '',
        value: 0,
        quantity: 0,
        status: '',
        acquired_date: ''
    })
}

function removeEquipment(index: number) {
    form.inventoryItem.splice(index, 1)
}

function retakeForm() {
    router.visit('/inventory')
}

function submit() {
    if (!form.name.trim()) {
        toast.error("Cooperative Name is required")
        return
    }

    if (!form.email.trim()) {
        toast.error("Email is required")
        return
    }

    if (!form.number.trim()) {
        toast.error("Contact number is required")
        return
    }

    for (const [index, item] of form.inventoryItem.entries()) {
        if (
            !item.category ||
            !item.name.trim() ||
            !item.guarantor_agency.trim() ||
            !item.location.trim() ||
            !item.value ||
            !item.quantity
        ) {
            toast.error(`Please fill all fields for Inventory Item #${index + 1}`)
            return
        }
    }

    form.post('/inventory', {
        onSuccess: () => toast.success('Inventory saved successfully')
    })
}
</script>

<template>

    <Head title="Inventory Form" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="inventory-wrapper">
            <div class="gov-header">
                <div class="flex items-center justify-between mb-4">
                    <img src="/img/province_of_palawan_logo.png" alt="Palawan Logo" style="height:70px">

                    <div style="text-align:center; line-height:1.3;">
                        <div><strong>Republic of the Philippines</strong></div>
                        <div>Provincial Government of Palawan</div>
                        <div><strong>PROVINCIAL COOPERATIVE DEVELOPMENT OFFICE</strong></div>
                        <div>Capitol Bldg., Puerto Princesa City</div>
                        <div style="color:#1a73e8;">pcdo.palawan@gmail.com</div>
                        <div>(048) 434-4173</div>
                    </div>

                    <img src="/img/pcdo_logo.png" alt="PCDO Logo" style="height:70px">
                </div>
            </div>

            <div class="inventory-header">

                <h1 class="inventory-title">INVENTORY FORM</h1>
                <p class="inventory-subtitle">
                    Create or update cooperative inventory details
                </p>

            </div>
            <!-- SUCCESS MESSAGE -->
            <div v-if="submitted" class="form-card" style="text-align:center">
                <h2 style="color:#188038">Form Submitted</h2>
                <p>Your inventory has been successfully recorded.</p>
                <button @click="retakeForm" class="add-btn">
                    Submit Another Response
                </button>
            </div>

            <!-- FORM -->
            <form v-else @submit.prevent="submit">
                <!-- COOPERATIVE INFO -->
                <div class="form-card">
                    <div class="form-grid">
                        <div>
                            <label class="form-label">Cooperative Name</label>
                            <Input class="form-input" v-model="form.name" />
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <Input class="form-input" type="email" v-model="form.email" />
                        </div>
                        <div>
                            <label class="form-label">Contact Number</label>
                            <Input class="form-input" v-model="form.number" />
                        </div>
                    </div>
                </div>

                <!-- LOCATION -->
                <div class="form-card">
                    <div class="form-grid">
                        <div>
                            <label class="form-label">Region</label>

                            <SelectSearch :items="regions" itemLabelKey="name" itemKeyProp="code"
                                v-model:search="searchState.region_code" :modelValue="form.region_code"
                                v-model:open="openState.region_code" @select="val => onSelect('region_code', val)" />

                        </div>
                        <div>
                            <label class="form-label">Province</label>
                            <SelectSearch :items="filteredProvinces" itemLabelKey="name" itemKeyProp="code"
                                v-model:search="searchState.province_code" :modelValue="form.province_code"
                                v-model:open="openState.province_code"
                                @select="val => onSelect('province_code', val)" />

                        </div>
                        <div>
                            <label class="form-label">City</label>

                            <SelectSearch :items="filteredCities" itemLabelKey="name" itemKeyProp="code"
                                v-model:search="searchState.city_code" :modelValue="form.city_code"
                                v-model:open="openState.city_code" @select="val => onSelect('city_code', val)" />

                        </div>
                        <div>
                            <label class="form-label">Barangay</label>

                            <SelectSearch :items="filteredBarangays" itemLabelKey="name" itemKeyProp="code"
                                v-model:search="searchState.barangay_code" :modelValue="form.barangay_code"
                                v-model:open="openState.barangay_code"
                                @select="val => onSelect('barangay_code', val)" />
                        </div>
                    </div>
                </div>

                <!-- EQUIPMENT -->
                <div class="form-card">
                    <div style="display:flex; justify-content:space-between; align-items:center">
                        <h1 class="section-title">Equipment</h1>
                        <button type="button" class="add-btn" @click="addEquipment">
                            + Add Equipment
                        </button>
                    </div>

                    <!-- EQUIPMENT LIST -->
                    <div v-for="(equipment, index) in form.inventoryItem" :key="equipment.id">

                        <hr v-if="index > 0" class="equipment-divider">

                        <div class="equipment-card">
                            <label class="form-label">Equipment #{{ index + 1 }}</label>

                            <button type="button" class="remove-x-btn" @click="removeEquipment(index)">
                                ✕
                            </button>

                            <div class="form-grid">
                                <div>
                                    <label class="form-label">Category</label>

                                    <select v-model="equipment.category" class="form-select">
                                        <option value="">Select Category</option>
                                        <option v-for="option in categoryOptions" :key="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Name</label>
                                    <Input class="form-input" v-model="equipment.name" />
                                </div>
                                <div>
                                    <label class="form-label">Guarantor Agency</label>
                                    <Input class="form-input" v-model="equipment.guarantor_agency" />
                                </div>
                                <div>
                                    <label class="form-label">Location</label>
                                    <Input class="form-input" v-model="equipment.location" />
                                </div>
                                <div>
                                    <label class="form-label">Value</label>
                                    <Input class="form-input" type="number" v-model="equipment.value" />
                                </div>
                                <div>
                                    <label class="form-label">Quantity</label>
                                    <Input class="form-input" type="number" v-model="equipment.quantity" />
                                </div>
                                <div>
                                    <label class="form-label">Status</label>
                                    <select v-model="equipment.status" class="form-select"
                                        :disabled="equipment.quantity === 0">

                                        <option value="">Select Status</option>

                                        <option v-for="option in getStatusOptions(equipment.quantity)" :key="option"
                                            :value="option">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Acquired Date</label>

                                    <Input class="form-input" type="date" v-model="equipment.acquired_date"
                                        :max="today" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SUBMIT -->

                <div style="margin-top:25px">

                    <button type="submit" class="submit-btn">
                        Save Inventory
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>