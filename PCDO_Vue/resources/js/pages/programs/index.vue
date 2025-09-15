<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  programs: Array<{
    id: number
    name: string
    cooperatives_count: number
  }>
}>()

const programDescriptions: Record<string, string> = {
  USAD: 'Upgrading Support for Advancement and Development of Enterprises in Cooperative',
  LICAP: 'Livelihood Credit Assistance Program',
  COPSE: 'Cooperative Program For Sustainable Enterprise',
  SULONG: 'Sustained Livelihood Opportunities and Growth',
  PCLRP: 'Provincial Cooperative Livelihood Recovery Program'
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Programs', href: '#' },
]
</script>

<template>
  <Head title="Programs" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-6">
      <!-- Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="program in props.programs"
          :key="program.id"
          class="rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 hover:shadow-xl hover:-translate-y-1 transform transition-all"
        >
          <!-- Header strip -->
          <div class="h-2 rounded-t-2xl bg-gradient-to-r from-blue-500 to-indigo-500"></div>

          <div class="p-5 flex flex-col h-full">
            <!-- Program Acronym -->
            <h2 class="text-xl font-bold mb-1 text-gray-900 dark:text-gray-100">
              {{ program.name }}
            </h2>

            <!-- Program Full Name -->
            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">
              {{ programDescriptions[program.name] }}
            </p>

            <!-- Footer row -->
            <div class="mt-auto flex items-center justify-between">
              <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                Active Cooperatives
              </span>
              <span
                class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200"
              >
                {{ program.cooperatives_count }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
