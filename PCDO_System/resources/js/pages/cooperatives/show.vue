<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import type { Cooperative, Details } from '@/types/cooperatives';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps<{
    breadcrumbs?: BreadcrumbItem[],
    cooperative: Cooperative,
    details: Details,
    programs: { id: number; name: string }[]
}>()

const openDropdown = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggleDropdown() {
    openDropdown.value = !openDropdown.value
}

function closeDropdown() {
    openDropdown.value = false
}

function onDocumentClick(e: MouseEvent) {
    if (!dropdownRef.value) return
    // if clicked inside dropdownRef, do nothing
    if (dropdownRef.value.contains(e.target as Node)) return
    // otherwise close
    closeDropdown()
}

function onKeyDown(e: KeyboardEvent) {
    if (e.key === 'Escape') closeDropdown()
}

onMounted(() => {
    document.addEventListener('click', onDocumentClick)
    document.addEventListener('keydown', onKeyDown)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', onDocumentClick)
    document.removeEventListener('keydown', onKeyDown)
})
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <div
                class="bg-gray-100 dark:bg-gray-900 shadow-lg rounded-2xl p-8 border border-gray-300 dark:border-gray-700">
                <!-- Header -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        🏢 Cooperative Details
                    </h1>

                    <!-- Right-side actions -->
                    <div class="flex items-center gap-3 relative">
                        <!-- ID Badge -->
                        <span class="inline-flex gap-2 px-4 py-2 rounded-full text-sm font-medium
                         bg-indigo-100 text-indigo-700 dark:bg-indigo-800 dark:text-indigo-200">
                            ID: {{ cooperative.id }}
                        </span>

                        <!-- Edit Button (FIRST) -->
                        <Link :href="`/cooperatives/${cooperative.id}/edit`" class="inline-flex gap-2 px-4 py-2 rounded-lg 
                     bg-gray-300 dark:bg-green-700 
                     text-green-700 dark:text-gray-200 
                     hover:bg-gray-400 dark:hover:bg-green-600 
                     text-sm font-medium transition">
                        <SquarePen class="w-4 h-4" />
                        Edit
                        </Link>

                        <!-- Add Program Dropdown (SECOND) -->
                        <div class="relative" ref="dropdownRef">
                            <button @click.stop="toggleDropdown" type="button" aria-haspopup="menu"
                                :aria-expanded="openDropdown"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium transition">
                                Add Program
                                <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>

                            <div v-if="openDropdown"
                                class="absolute right-0 mt-2 w-56 bg-gray-100 dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden z-20 border border-gray-300 dark:border-gray-700">
                                <ul>
                                    <li v-for="program in (programs || [])" :key="program.id">
                                        <Link
                                            :href="`/programs/${program.id}/cooperatives/create?cooperative_id=${cooperative.id}`"
                                            class="block px-4 py-2 text-sm text-gray-800 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">
                                        {{ program.name }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <!-- end dropdown -->
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-gray-800 dark:text-gray-200">
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cooperative Name</p>
                        <p class="text-lg font-semibold">{{ cooperative.name }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cooperative Holder</p>
                        <p class="text-lg font-semibold">{{ cooperative.holder || '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Cooperative Type</p>
                        <p class="text-lg font-semibold">{{ details.coop_type }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Category</p>
                        <p class="text-lg font-semibold">{{ details.status_category }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Bond of Membership</p>
                        <p class="text-lg font-semibold">{{ details.bond_of_membership }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Area of Operation</p>
                        <p class="text-lg font-semibold">{{ details.area_of_operation }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Citizenship</p>
                        <p class="text-lg font-semibold">{{ details.citizenship }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Member Count</p>
                        <p class="text-lg font-semibold">{{ details.members_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Asset</p>
                        <p class="text-lg font-semibold">₱{{ details.total_asset.toLocaleString() }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Surplus</p>
                        <p class="text-lg font-semibold">₱{{ details.net_surplus.toLocaleString() }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
