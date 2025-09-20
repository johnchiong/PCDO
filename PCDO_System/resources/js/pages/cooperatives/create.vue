<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Label from '@/components/ui/label/Label.vue';

interface Cooperative {
    id: string;
    name: string;
}

interface Details {
    id: string;
    region_id: number,
    province_id: number,
    municipality_id: number,
    barangay_id: number,
    asset_size: string;
    coop_type: string;
    status_category: string;
    bond_of_membership: string;
    area_of_operation: string;
    citizenship: string;
    members_count: number;
    total_asset: number;
    net_surplus: number;
}

interface fullForm extends Details {
    name: string
}

interface Regions {
    id: number;
    name: string;
}

interface Provinces {
    id: number;
    name: string;
    region_id: number;
}

interface Municipalities {
    id: number;
    name: string;
    province_id: number;
}

interface Barangays {
    id: number;
    name: string;
    municipality_id: number;
}

const props = defineProps<{
    cooperatives: Cooperative[];
    regions: Regions[];
    provinces: Provinces[];
    municipalities: Municipalities[];
    barangays: Barangays[];
    breadcrumbs: { title: string; href?: string }[];
}>();

enum CoopType {
  Credit = 'Credit',
  Consumer = 'Consumers', 
  Producers = 'Producers', 
  Marketing = 'Marketing', 
  Service = 'Service', 
  Multipurpose = 'Multipurpose', 
  Advocacy = 'Advocacy', 
  Agrarian_Reform = 'Agrarian Reform', 
  Bank = 'Bank', 
  Diary = 'Diary', 
  Education = 'Education', 
  Electric = 'Electric', 
  Financial = 'Financial', 
  Fishermen = 'Fishermen', 
  Health_Services = 'Health Services', 
  Housing = 'Housing', 
  Insurance = 'Insurance', 
  Water_Service = 'Water Service', 
  Workers = 'Workers', 
  Others = 'Others'
}

enum AssetSize {
  Micro = 'Micro',
  Small = 'Small',
  Medium = 'Medium',
  Large = 'Large',
  Unclassified = 'Unclassified',
}

enum Status_Category {
  Reporting = 'Reporting',
  Non_Reporting = 'Non-Reporting',
  New = 'New',
}

enum BondOfMembership {
  Residential = 'Residential',
  Insitutional = 'Insitutional',
  Associational = 'Associational',
  Occupational = 'Occupational',
  Unspecified = 'Unspecified',
}

enum AreaOfOperation {
  Municipal = 'Municipal',
  Provincial = 'Provincial',
}

enum Citizenship {
  Filipino = 'Filipino',
  Foreign = 'Foreign',
}

const form = useForm<fullForm>({
    id: '',
    name: '',
    region_id: 0,
    province_id: 0,
    municipality_id: 0,
    barangay_id: 0,
    asset_size: '',
    coop_type: '',
    status_category: Status_Category.New,
    bond_of_membership: BondOfMembership.Residential,
    area_of_operation: AreaOfOperation.Municipal,
    citizenship: Citizenship.Filipino,
    members_count: 0,
    total_asset: 0,
    net_surplus: 0,
})

const searchRegion = ref('');
const searchProvince = ref('');
const searchMunicipality = ref('');
const searchBarangay = ref('');
const searchAssetSize = ref('');
const searchCoopType = ref('');
const searchStatusCategory = ref('');
const searchBondOfMembership = ref('');
const searchAreaOfOperation = ref('');
const searchCitizenship = ref('');

const dropDownRegionOpen = ref(false);
const dropDownProvinceOpen = ref(false);
const dropDownMunicipalityOpen = ref(false);
const dropDownBarangayOpen = ref(false);
const dropDownAssetSizeOpen = ref(false);
const dropDownCoopTypeOpen = ref(false);
const dropDownStatusCategoryOpen = ref(false);
const dropDownBondOfMembershipOpen = ref(false);
const dropDownAreaOfOperationOpen = ref(false);
const dropDownCitizenshipOpen = ref(false);

const selectedRegion = ref<string|number>('');
const selectedProvince = ref<string|number>('');
const selectedMunicipality = ref<string|number>('');
const selectedBarangay = ref<string|number>('');
const selectedAssetSize = ref('');
const selectedStatusCategory = ref('');
const selectedCoopType = ref('');
const selectedBondOfMembership = ref('');
const selectedAreaOfOperation = ref('');
const selectedCitizenship = ref('');

const regionRef = ref<HTMLElement | null>(null)
const provinceRef = ref<HTMLElement | null>(null)
const municipalityRef = ref<HTMLElement | null>(null)
const barangayRef = ref<HTMLElement | null>(null)
const assetSizeRef = ref<HTMLElement | null>(null)
const statusCategoryRef = ref<HTMLElement | null>(null)
const coopTypeRef = ref<HTMLElement | null>(null)
const bondOfMembershipRef = ref<HTMLElement | null>(null)
const areaOfOperationRef = ref<HTMLElement | null>(null)
const citizenshipRef = ref<HTMLElement | null>(null)

const filteredCooperativesId = computed(() => {
    if (!form.id) {
        return props.cooperatives;
    }
    return props.cooperatives.filter(coop =>
        coop.id.toString().includes(form.id)
    );
});

const filteredCooperativesName = computed(() => {
    if (!form.name) {
        return props.cooperatives;
    }
    return props.cooperatives.filter(coop =>
        coop.name.toLowerCase().includes(form.name.toLowerCase())
    );
});

const filteredRegionName = computed(() => {
    if (!searchRegion.value) {
        return props.regions;
    }
    return props.regions.filter(r =>
        r.name.toLowerCase().includes(searchRegion.value.toLowerCase())
    );
});

const filteredProvinceName = computed(() => {
    let provinces = props.provinces
    if (selectedRegion.value) {
        provinces = provinces.filter(
            p => p.region_id === selectedRegion.value
        )
    }
    if (!searchProvince.value) {
        return provinces;
    }
    return provinces.filter(p =>
        p.name.toLowerCase().includes(searchProvince.value.toLowerCase())
    );
});

const filteredMunicipalityName = computed(() => {
    let municipalities = props.municipalities
    if (selectedProvince.value) {
        municipalities = municipalities.filter(
            m => m.province_id === selectedProvince.value
        )
    }
    if (!searchMunicipality.value) {
        return municipalities;
    }
    return municipalities.filter(m =>
        m.name.toLowerCase().includes(searchMunicipality.value.toLowerCase())
    );
});

const filteredBarangayName = computed(() => {
    let barangays = props.barangays
    if (selectedMunicipality.value) {
        barangays = barangays.filter(
            b => b.municipality_id === selectedMunicipality.value
        )
    }
    if (!searchBarangay.value) {
        return barangays;
    }
    return barangays.filter(b =>
        b.name.toLowerCase().includes(searchBarangay.value.toLowerCase())
    );
});

const filteredAssetSize = computed(() => {
    if (!searchAssetSize.value) {
        return Object.values(AssetSize);
    }
    return Object.values(AssetSize).filter(size =>
        size.toLowerCase().includes(searchAssetSize.value.toLowerCase())
    )
})

const filteredCoopType = computed(() => {
    if (!searchCoopType.value) {
        return Object.values(CoopType);
    }
    return Object.values(CoopType).filter(size =>
        size.toLowerCase().includes(searchCoopType.value.toLowerCase())
    )
})

const filteredStatusCategory = computed(() => {
    if (!searchStatusCategory.value) {
        return Object.values(Status_Category);
    }
    return Object.values(Status_Category).filter(size =>
        size.toLowerCase().includes(searchStatusCategory.value.toLowerCase())
    )
})

const filteredBondOfMembership = computed(() => {
    if (!searchBondOfMembership.value) {
        return Object.values(BondOfMembership);
    }
    return Object.values(BondOfMembership).filter(size =>
        size.toLowerCase().includes(searchBondOfMembership.value.toLowerCase())
    )
})

const filteredAreaOfOperation = computed(() => {
    if (!searchAreaOfOperation.value) {
        return Object.values(AreaOfOperation);
    }
    return Object.values(AreaOfOperation).filter(size =>
        size.toLowerCase().includes(searchAreaOfOperation.value.toLowerCase())
    )
})

const filteredCitizen = computed(() => {
    if (!searchCitizenship.value) {
        return Object.values(Citizenship);
    }
    return Object.values(Citizenship).filter(citizen =>
        citizen.toLowerCase().includes(searchCitizenship.value.toLowerCase())
    )
})

const selectMunicipality = (name: string, id: number) => {
    form.municipality_id = id;
    searchMunicipality.value = name;
    dropDownMunicipalityOpen.value = false;
}

const selectAssetSize = (size: AssetSize) => {
    form.asset_size = size;
    searchAssetSize.value = size;
    dropDownAssetSizeOpen.value = false;
}

const selectCoopType = (type: CoopType) => {
    form.coop_type = type;
    searchCoopType.value = type;
    dropDownCoopTypeOpen.value = false;
}

const selectStatusCategory = (status: Status_Category) => {
    form.status_category = status;
    searchStatusCategory.value = status;
    dropDownStatusCategoryOpen.value = false;
}

const selectBondOfMembership = (bond: BondOfMembership) => {
    form.bond_of_membership = bond;
    searchBondOfMembership.value = bond;
    dropDownBondOfMembershipOpen.value = false;
}

const selectAreaOfOperation = (area: AreaOfOperation) => {
    form.area_of_operation = area;
    searchAreaOfOperation.value = area;
    dropDownAreaOfOperationOpen.value = false;
}

const selectCitizenship = (citizenship: Citizenship) => {
    form.citizenship = citizenship;
    searchCitizenship.value = citizenship;
    dropDownCitizenshipOpen.value = false;
}

const handleSubmit =  () => {
    form.post('/cooperatives');
};

const isIdFormatValid = (query: string) => {
    const regex = /^\d{4}-\d{4,}$/;
    return regex.test(query);
};

const onInputRegion = () => {
    if (searchRegion.value && filteredRegionName.value.length > 0) {
        dropDownRegionOpen.value = true;
    } else {
        dropDownRegionOpen.value = false;
    }
};

const onInputProvince = () => {
    if (searchProvince.value && filteredProvinceName.value.length > 0) {
        dropDownProvinceOpen.value = true;
    } else {
        dropDownProvinceOpen.value = false;
    }
};


const onInputMunicipality = () => {
    if (searchMunicipality.value && filteredMunicipalityName.value.length > 0) {
        dropDownMunicipalityOpen.value = true;
    } else {
        dropDownMunicipalityOpen.value = false;
    }
};

const onInputBarangay = () => {
    if (searchBarangay.value && filteredBarangayName.value.length > 0) {
        dropDownBarangayOpen.value = true;
    } else {
        dropDownBarangayOpen.value = false;
    }
};

const onInputAssetSize = () => {
    if (searchAssetSize.value) {
        dropDownAssetSizeOpen.value = true;
    } else {
        dropDownAssetSizeOpen.value = false;
    }
}

const onInputCoopType = () => {
    if (searchCoopType.value) {
        dropDownCoopTypeOpen.value = true;
    } else {
        dropDownCoopTypeOpen.value = false;
    }
}

const onInputStatusCategory = () => {
    if (searchStatusCategory.value) {
        dropDownStatusCategoryOpen.value = true;
    } else {
        dropDownStatusCategoryOpen.value = false;
    }
}

const onInputBondOfMembership = () => {
    if (searchBondOfMembership.value) {
        dropDownBondOfMembershipOpen.value = true;
    } else {
        dropDownBondOfMembershipOpen.value = false;
    }
}

const onInputAreaOfOperation = () => {
    if (searchAreaOfOperation.value) {
        dropDownAreaOfOperationOpen.value = true;
    } else {
        dropDownAreaOfOperationOpen.value = false;
    }
}

const onInputCitizen = () => {
    if (searchCitizenship.value) {
        dropDownCitizenshipOpen.value = true;
    } else {
        dropDownCitizenshipOpen.value = false;
    }
}


const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as Node
    if (regionRef.value && !regionRef.value.contains(target)) {
        dropDownRegionOpen.value = false
    }    
    if (provinceRef.value && !provinceRef.value.contains(target)) {
        dropDownProvinceOpen.value = false
    }    
    if (municipalityRef.value && !municipalityRef.value.contains(target)) {
        dropDownMunicipalityOpen.value = false
    }
    if (barangayRef.value && !barangayRef.value.contains(target)) {
        dropDownBarangayOpen.value = false
    }
    if (assetSizeRef.value && !assetSizeRef.value.contains(target)) {
        dropDownAssetSizeOpen.value = false
    }
    if (coopTypeRef.value && !coopTypeRef.value.contains(target)) {
        dropDownCoopTypeOpen.value = false
    }
    if (statusCategoryRef.value && !statusCategoryRef.value.contains(target)) {
        dropDownStatusCategoryOpen.value = false
    }
    if (bondOfMembershipRef.value && !bondOfMembershipRef.value.contains(target)) {
        dropDownBondOfMembershipOpen.value = false
    }
    if (areaOfOperationRef.value && !areaOfOperationRef.value.contains(target)) {
        dropDownAreaOfOperationOpen.value = false
    }
    if (citizenshipRef.value && !citizenshipRef.value.contains(target)) {
        dropDownCitizenshipOpen.value = false
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head :title="`| ${$page.component}`" />

    <AppLayout :Breadcrumbs="breadcrumbs">
        <form @submit.prevent="handleSubmit">
            <div class="items-center justify-between p-6 space-y-4">
                <div class="relative w-200"> 
                    <Label for="id" class="mb-1 block text-sm font-medium text-gray-300">
                        Enter Valid Register Number
                    </Label>
                    <Input
                        id="id"
                        name="id"
                        v-model="form.id"
                        placeholder="Enter Register Number To Create Cooperative"
                        class="pl-9 pr-3"
                    />
                </div>
                <div class="text-sm text-green-600" v-if="filteredCooperativesId.length === 0 && form.id && isIdFormatValid(form.id)">
                    No Duplicate Found, You can create a new cooperative with <strong>ID: {{ form.id }}</strong>
                </div>
                <div class="text-sm text-red-600" v-if="isIdFormatValid(form.id) === false && form.id">
                    Invalid Format: e.g. 0000-0002 or 0000-00000002. 
                </div>
                <div>
                    <TableRow v-for="coop in filteredCooperativesId" :key="coop.id">
                        <TableCell>{{ coop.id}}</TableCell>
                    </TableRow>
                </div>
                <div v-if="filteredCooperativesId.length === 0 && isIdFormatValid(form.id)">
                    <Label for="name" class="mb-1 block text-sm font-medium text-gray-300">
                        Enter Valid Name
                    </Label>
                    <Input
                        id="name"
                        name="name"
                        v-model="form.name"
                        placeholder="Enter Name To Create Cooperative"
                        class="pl-9 pr-3"
                    />
                    <div>
                        <TableRow v-for="coop in filteredCooperativesName" :key="coop.id">
                            <TableCell>{{ coop.name}}</TableCell>
                        </TableRow>
                    </div>
                    <div class="text-sm text-green-600" v-if="filteredCooperativesName.length === 0 && form.name">
                        No Duplicate Found, You can create a new cooperative with <strong>Name: {{ form.name }}</strong>
                    </div>
                </div>
                <div v-if="filteredCooperativesId.length === 0 && isIdFormatValid(form.id) && filteredCooperativesName.length === 0 && form.name">
                    <div class="items-center justify-between p-6 space-y-4">
                    <!-- Searchable Input with Scrollable Dropdown -->
                        <div class="relative" ref="regionRef">
                            <Label for="region" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Region
                            </Label>
                            <Input
                            v-model="searchRegion"
                            @focus="dropDownRegionOpen = true"
                            @input="onInputRegion"
                            placeholder="Search Region"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownRegionOpen && filteredRegionName.length > 0" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="reg in filteredRegionName"
                                        :key="reg.id"
                                        @click="selectMunicipality(reg.name, reg.id)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ reg.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="items-center justify-between p-6 space-y-4">
                    </div>
                    </div>
                    <!-- Searchable Input with Scrollable Dropdown -->
                        <div class="relative" ref="provinceRef">
                            <Label for="province" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Province
                            </Label>
                            <Input
                            v-model="searchProvince"
                            :disabled="!selectedRegion"
                            @focus="dropDownProvinceOpen = true"
                            @input="onInputProvince"
                            placeholder="Search Province"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownProvinceOpen && filteredProvinceName.length > 0" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="pro in filteredProvinceName"
                                        :key="pro.id"
                                        @click="selectMunicipality(pro.name, pro.id)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ pro.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                        </div>
                        <div class="items-center justify-between p-6 space-y-4">
                    <!-- Searchable Input with Scrollable Dropdown -->
                        <div class="relative" ref="municipalityRef">
                            <Label for="municipality" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Municipality
                            </Label>
                            <Input
                            v-model="searchMunicipality"
                            :disabled="!selectedProvince"
                            @focus="dropDownMunicipalityOpen = true"
                            @input="onInputMunicipality"
                            placeholder="Search Municipality"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownMunicipalityOpen && filteredMunicipalityName.length > 0" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="muni in filteredMunicipalityName"
                                        :key="muni.id"
                                        @click="selectMunicipality(muni.name, muni.id)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ muni.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="items-center justify-between p-6 space-y-4">
                    <!-- Searchable Input with Scrollable Dropdown -->
                        <div class="relative" ref="barangayRef">
                            <Label for="barangay" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Barangay
                            </Label>
                            <Input
                            v-model="searchBarangay"
                            :disabled="selectMunicipality"
                            @focus="dropDownBarangayOpen = true"
                            @input="onInputBarangay"
                            placeholder="Search Barangay"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownBarangayOpen && filteredBarangayName.length > 0" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="bar in filteredBarangayName"
                                        :key="bar.id"
                                        @click="selectMunicipality(bar.name, bar.id)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ bar.name }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative" ref="assetSizeRef">
                            <Label for="asset_size" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Asset Size
                            </Label>
                            <Input
                            v-model="searchAssetSize"
                            @focus="dropDownAssetSizeOpen = true"
                            @input="onInputAssetSize"
                            placeholder="Select Asset Size"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownAssetSizeOpen" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="size in filteredAssetSize"
                                        :key="size"
                                        @click="selectAssetSize(size)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ size }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative" ref="coopTypeRef">
                            <Label for="coop_type" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Coop Type
                            </Label>
                            <Input
                            v-model="searchCoopType"
                            @focus="dropDownCoopTypeOpen = true"
                            @input="onInputCoopType"
                            placeholder="Select Coop Type"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownCoopTypeOpen" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="type in filteredCoopType"
                                        :key="type"
                                        @click="selectCoopType(type)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ type }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative" ref="statusCategoryRef">
                            <Label for="status_category" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Status Category
                            </Label>
                            <Input
                            v-model="searchStatusCategory"
                            @focus="dropDownStatusCategoryOpen = true"
                            @input="onInputStatusCategory"
                            placeholder="Select Status Category"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownStatusCategoryOpen" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="status in filteredStatusCategory"
                                        :key="status"
                                        @click="selectStatusCategory(status)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ status }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative" ref="bondOfMembershipRef">
                            <Label for="bond_of_membership" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Bond of Membership
                            </Label>
                            <Input
                            v-model="searchBondOfMembership"
                            @focus="dropDownBondOfMembershipOpen = true"
                            @input="onInputBondOfMembership"
                            placeholder="Select Bond Of Membership"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownBondOfMembershipOpen" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="bond in filteredBondOfMembership"
                                        :key="bond"
                                        @click="selectBondOfMembership(bond)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ bond }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative" ref="areaOfOperationRef">
                            <Label for="area_of_operation" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Area of Operation
                            </Label>
                            <Input
                            v-model="searchAreaOfOperation"
                            @focus="dropDownAreaOfOperationOpen = true"
                            @input="onInputAreaOfOperation"
                            placeholder="Select Area Of Operation"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownAreaOfOperationOpen" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="area in filteredAreaOfOperation"
                                        :key="area"
                                        @click="selectAreaOfOperation(area)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ area }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative" ref="citizenshipRef">
                            <Label for="citizenship" class="mb-1 block text-sm font-medium text-gray-300">
                                Select Citizenship
                            </Label>
                            <Input
                            id="citizenship"
                            name="citizen"
                            v-model="searchCitizenship"
                            @focus="dropDownCitizenshipOpen = true"
                            @input="onInputCitizen"
                            placeholder="Select Citizen"
                            class="pl-9 pr-3 w-full py-2 rounded-md border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                            <div v-if="dropDownCitizenshipOpen" class="absolute w-full mt-1 border border-gray-300 bg-gray shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
                                <ul>
                                    <li
                                        v-for="citizenship in filteredCitizen"
                                        :key="citizenship"
                                        @click="selectCitizenship(citizenship)"
                                        class="px-4 py-2 cursor-pointer hover:bg-gray-200"
                                        >
                                        {{ citizenship }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="relative">
                            <Label for="member_counts" class="mb-1 block text-sm font-medium text-gray-300">
                                Enter Member Count: 
                            </Label>
                            <Input id="member_counts" name="members_count" v-model="form.members_count"/>
                        </div>

                        <div class="relative">
                            <Label for="total_asset" class="mb-1 block text-sm font-medium text-gray-300">
                                Enter Total Asset: 
                            </Label>
                            <Input id="total_asset" name="total_asset" v-model="form.total_asset"/>
                        </div>

                        <div class="relative">
                            <Label for="net_surplus" class="mb-1 block text-sm font-medium text-gray-300">
                                Enter Net Surplus: 
                            </Label>
                            <Input id="net_surplus" name="net_surplus" v-model="form.net_surplus"/>

                        </div>
                    </div>
                    <button type="submit">
                        Create Cooperative
                    </button>
                </div>
            </div>
        </form>
    </AppLayout>
</template>