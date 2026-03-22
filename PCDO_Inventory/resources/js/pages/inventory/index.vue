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
    inventoryNames: { id: number, name: string, category: string }[]
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
    const q = Number(quantity) || 0
    const options = []

    for (let i = 0; i <= q; i++) {
        const servicable = q - i
        const unservicable = i
        options.push({
            label: `Servicable ${servicable} | Unserviceable ${unservicable}`,
            value: servicable
        })
    }
    return options
}


const filteredProvinces = computed(() =>
    props.provinces.filter(p => String(p.region_code) === String(form.region_code))
)

const filteredCities = computed(() =>
    props.cities.filter(c => String(c.province_code) === String(form.province_code))
)

const filteredBarangays = computed(() =>
    props.barangays.filter(b => String(b.city_code) === String(form.city_code))
)

const categories = [
    { label: 'Equipment', value: 'Equipment' },
    { label: 'Machinery', value: 'Machinery' },
    { label: 'Facilities', value: 'Facilities' }
]

function getItemsByCategory(category: string) {
    return form.inventoryItem.filter(item => item.category === category)
}

function addItem(category: string) {
    form.inventoryItem.push({
        id: Date.now(),
        category: category,
        name: '',
        guarantor_agency: '',
        location: '',
        value: 0,
        quantity: 0,
        status: 0,
        acquired_date: ''
    })
}

function removeEquipment(id: number) {
    const index = form.inventoryItem.findIndex(item => item.id === id)
    if (index !== -1) form.inventoryItem.splice(index, 1)
}

function retakeForm() {
    router.visit('/inventory')
}


function isAcronym(text: string) {
    return /^[A-Z0-9&.\-]{2,10}$/.test(text.trim())
}

async function confirmAcronym(field: string, value: string) {

    if (!isAcronym(value)) return true

    return confirm(
        `This "${value}" in ${field} appears to be an acronym.\n\n` +
        `We require the full name. Continue submitting?`
    )

}

async function submit() {
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
            !item.quantity ||
            item.status === null ||
            !item.acquired_date
        ) {
            toast.error(`Please fill all fields for Inventory Item #${index + 1}: ${item.name || 'Unnamed Item'}`)
            return
        }
    }

    if (form.inventoryItem.some(item => item.acquired_date > today)) {
        toast.error("Acquired date cannot be in the future")
        return
    }

    if (form.inventoryItem.some(item => item.value < 0)) {
        toast.error("Value cannot be negative")
        return
    }

    if (form.inventoryItem.some(item => item.quantity < 0)) {
        toast.error("Quantity cannot be negative")
        return
    }

    if (form.inventoryItem.some(item => item.status !== null && item.status < 0)) {
        toast.error("Status cannot be negative")
        return
    }

    if (form.inventoryItem.some(item => item.status !== null && item.status > item.quantity)) {
        toast.error("Status cannot be greater than quantity")
        return
    }

    if (!await confirmAcronym("Cooperative Name", form.name)) return

    for (const item of form.inventoryItem) {
        if (!await confirmAcronym("Guarantor Agency", item.guarantor_agency)) {
            return
        }
    }

    form.post('/inventory', {
        onSuccess: () => {
            toast.success('Inventory saved successfully')
        }
    })
}

const nameSearchState = reactive<Record<number, string>>({})
const nameOpenState = reactive<Record<number, boolean>>({})

function getNameOptions(category: string) {
    if (!props.inventoryNames) return []
    return props.inventoryNames.filter(
        item => item.category === category
    )
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

                <!-- INVENTORY SECTIONS -->
                <div v-for="category in categories" :key="category.value" class="form-card">
                    <div class="section-header">
                        <h2 class="section-title">{{ category.label }}</h2>
                    </div>
                    <!-- SHOW ITEMS ONLY IF ADDED -->
                    <div v-for="(item, index) in getItemsByCategory(category.value)" :key="item.id"
                        class="equipment-card">
                        <div class="form-grid">
                            <label class="form-label">{{ category.label }} #{{ index + 1 }}:
                                <span v-if="item.name">{{ item.name }}</span>
                                <span v-else class="equipment-unnamed">Unnamed Item</span></label>

                            <button type="button" class="remove-x-btn" @click="removeEquipment(item.id)">
                                ✕
                            </button>
                            <div>
                                <label class="form-label">Name</label>
                                <SelectSearch :items="getNameOptions(category.value)" itemLabelKey="name"
                                    itemKeyProp="id" v-model:search="item.name" v-model:open="nameOpenState[item.id]"
                                    @select="val => item.name = val.name" />
                            </div>
                            <div>
                                <label class="form-label">Guaranteeing Agency</label>
                                <Input class="form-input" v-model="item.guarantor_agency" />
                            </div>
                            <div>
                                <label class="form-label">Location</label>
                                <Input class="form-input" v-model="item.location" />
                            </div>
                            <div>
                                <label class="form-label">Value</label>
                                <Input class="form-input" type="number" v-model="item.value" />
                            </div>
                            <div>
                                <label class="form-label">Quantity</label>
                                <Input class="form-input" type="number" v-model="item.quantity" />
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select v-model="item.status" class="form-select" :disabled="item.quantity === 0">
                                    <option value="">Select Status</option>
                                    <option v-for="option in getStatusOptions(item.quantity)" :key="option.value"
                                        :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Acquired Date</label>
                                <Input class="form-input" type="date" v-model="item.acquired_date" :max="today" />
                            </div>
                        </div>
                    </div>
                    <button type="button" class="add-btn" @click="addItem(category.value)">
                        + Add {{ category.label }}
                    </button>
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