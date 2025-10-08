<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'

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

// Dynamic gradients for each program
const programGradients: Record<number, string> = {
  1: 'from-yellow-400 to-orange-500',
  2: 'from-blue-500 to-indigo-500',
  3: 'from-emerald-500 to-teal-600', 
  4: 'from-red-400 to-pink-500',
  5: 'from-green-300 to-green-600'
}


</script>

<template>
  <Head title="Programs" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
      <div class="px-5 md:px-8 pt-5">
        <div class="flex flex-col gap-6 p-6">
          <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Link
              v-for="program in props.programs"
              :key="program.id"
              :href="`/programs/${program.id}`"
              class="rounded-2xl shadow-md border border-gray-300 dark:border-gray-700 
                     bg-gray-50 dark:bg-gray-800 
                     hover:shadow-2xl hover:-translate-y-1.5 transform transition-all block"
            >
              <!-- Dynamic Gradient Top Bar -->
              <div :class="`h-2 rounded-t-2xl bg-gradient-to-r ${programGradients[program.id] || 'from-blue-500 to-indigo-500'}`"></div>

              <div class="p-5 flex flex-col h-full">
                <h2 class="text-xl font-bold mb-2 text-gray-900 dark:text-gray-100">
                  {{ program.name }}
                </h2>

                <p class="text-gray-800 dark:text-gray-300 text-sm leading-relaxed mb-4">
                  {{ programDescriptions[program.name] }}
                </p>

                <div class="mt-auto flex items-center justify-between gap-2">
                  <span class="text-sm font-medium text-gray-800 dark:text-gray-200 flex items-center gap-1">
                    <Handshake class="w-4 h-4 text-gray-600 dark:text-gray-400" /> Active Cooperatives
                  </span>
                  <span class="px-3 py-1 rounded-full text-xs font-semibold 
                               bg-blue-200 text-blue-800 
                               dark:bg-blue-900 dark:text-blue-200 shadow-sm">
                    {{ program.cooperatives_count }}
                  </span>
                </div>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
