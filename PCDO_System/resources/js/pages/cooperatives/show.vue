<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import type { Cooperative, Details } from '@/types/cooperatives';
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';

// Props
const props = defineProps<{
  breadcrumbs?: BreadcrumbItem[],
  cooperative: Cooperative,
  details: Details,
  programs: { id: number; name: string }[]
}>()

// Flash messages
const page = usePage();
const flash = computed(() => page.props.flash as { success?: string; error?: string; info?: string });

// Dropdown control
const openDropdown = ref(false)
const dropdownRef = ref<HTMLElement | null>(null)

function toggleDropdown() {
  openDropdown.value = !openDropdown.value
}

function closeDropdown() {
  openDropdown.value = false
}

function onDocumentClick(e: MouseEvent) {
  if (!dropdownRef.value) return;
  if (dropdownRef.value.contains(e.target as Node)) return;
  closeDropdown();
}

function onKeyDown(e: KeyboardEvent) {
  if (e.key === 'Escape') closeDropdown();
}

onMounted(() => {
  document.addEventListener('click', onDocumentClick);
  document.addEventListener('keydown', onKeyDown);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', onDocumentClick);
  document.removeEventListener('keydown', onKeyDown);
});

// Navigation
function goToEditPage(id: string) {
  router.visit(`/cooperatives/${id}/edit`);
}
function goToMemberPage(id: string) {
  router.visit(`/cooperatives/${id}/members`);
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
  <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7x7 p-6">
      <div class="bg-gray-50 dark:bg-gray-800/80 border ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 rounded-xl shadow-m px-6 py-5 mb-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-3">
            <Building2 class="w-10 h-10 text-emerald-600 dark:text-emerald-400 flex-shrink-0" />
            Cooperative Details
          </h1>

          <!-- Right-side Actions -->
          <div class="flex items-center gap-3 relative">
            <!-- ID Badge -->
            <span class="inline-flex gap-2 px-4 py-2 rounded-full text-sm font-medium
              bg-indigo-200/40 text-lime-700 dark:bg-lime-800 dark:text-fuchsia-200">
              ID: {{ cooperative.id }}
            </span>

            <DropdownMenu>
              <!-- Main Dropdown Trigger -->
              <DropdownMenuTrigger asChild>
                <button
                  class="inline-flex items-center justify-between gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-400 text-sm font-medium transition w-36">
                  <span class="flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Actions
                  </span>
                  <ChevronDown class="w-4 h-4" />
                </button>
              </DropdownMenuTrigger>

              <!-- Main Dropdown Content -->
              <DropdownMenuContent side="bottom" align="end"
                class="w-52 bg-white dark:bg-gray-900 shadow-xl rounded-lg border border-gray-200 dark:border-gray-700 p-1">
                <!-- Edit -->
                <DropdownMenuItem asChild>
                  <button @click="goToEditPage(cooperative.id)"
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <SquarePen class="w-4 h-4 text-green-600 dark:text-green-400 shrink-0" />
                    Edit
                  </button>
                </DropdownMenuItem>

                <!-- Members -->
                <DropdownMenuItem asChild>
                  <button @click="goToMemberPage(cooperative.id)"
                    class="w-full flex items-center gap-3 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    <Users class="w-4 h-4 text-orange-600 dark:text-orange-400 shrink-0" /> Members
                  </button>
                </DropdownMenuItem>

                <!-- Click-to-open Programs Dropdown -->
                <DropdownMenuItem asChild>
                  <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                      <button
                        class="w-full flex items-center justify-between gap-2 px-4 py-2 text-sm text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <span class="flex items-center gap-2">
                          <Plus class="w-4 h-4 text-purple-600 dark:text-purple-400 shrink-0" />
                          Add Program
                        </span>
                        <ChevronDown class="w-4 h-4" />
                      </button>
                    </DropdownMenuTrigger>

                    <DropdownMenuContent side="bottom" align="end"
                      class="w-51 bg-white dark:bg-gray-900 shadow-xl rounded-lg border border-gray-200 dark:border-gray-700 p-1 mt-1">
                      <DropdownMenuItem asChild v-for="program in programs || []" :key="program.id">
                        <Link :href="`/programs/${program.id}/cooperatives/create?cooperative_id=${cooperative.id}`"
                          class="block w-full px-4 py-2 text-sm text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        {{ program.name }}
                        </Link>
                      </DropdownMenuItem>
                    </DropdownMenuContent>
                  </DropdownMenu>
                </DropdownMenuItem>
              </DropdownMenuContent>
            </DropdownMenu>

          </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6 text-gray-800 dark:text-gray-200">
          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cooperative Name</p>
            <p class="text-lg font-semibold">{{ cooperative.name }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cooperative Holder</p>
            <p class="text-lg font-semibold">{{ cooperative.holder || '-' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Cooperative Type</p>
            <p class="text-lg font-semibold">{{ details?.coop_type || '-' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Category</p>
            <p class="text-lg font-semibold">{{ details?.status_category || '-' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bond of Membership</p>
            <p class="text-lg font-semibold">{{ details?.bond_of_membership || '-' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Area of Operation</p>
            <p class="text-lg font-semibold">{{ details?.area_of_operation || '-' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Citizenship</p>
            <p class="text-lg font-semibold">{{ details?.citizenship || '-' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Member Count</p>
            <p class="text-lg font-semibold">{{ details?.members_count || 0 }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Asset</p>
            <p class="text-lg font-semibold">₱{{ details?.total_asset?.toLocaleString() || '0.00' }}</p>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Surplus</p>
            <p class="text-lg font-semibold">₱{{ details?.net_surplus?.toLocaleString() || '0.00' }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
  </AppLayout>
</template>
