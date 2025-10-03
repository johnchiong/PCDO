<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
    programs: Array<{
        id: number
        name: string
        completed_coops: Array<{
            id: number
            name: string
            completed_at: string
            files?: Array<{ id: number, name: string, url: string }>
        }>
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Documentation', href: '/documentation' },
    { title: 'History', href: '#' },
]

const years = [2023, 2024, 2025]

const programsWithYears = props.programs.map(program => {
    const completed_by_year: Record<number, any[]> = {}
    const coops = program.completed_coops || []

    years.forEach(year => {
        completed_by_year[year] = coops.filter(coop => {
            return new Date(coop.completed_at).getFullYear() === year
        })
    })

    return { ...program, completed_by_year }
})
</script>

<template>
  <Head title="Programs History" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <h1 class="text-2xl font-bold mb-4">Programs History</h1>

      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 dark:border-gray-700">
          <thead>
            <tr class="bg-gray-200 dark:bg-gray-800">
              <th class="px-4 py-2 text-left">Program</th>
              <th v-for="year in years" :key="year" class="px-4 py-2 text-center">{{ year }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="program in programsWithYears" :key="program.id" class="border-t border-gray-300 dark:border-gray-700">
              <td class="px-4 py-2 text-left font-medium text-gray-900 dark:text-gray-100">{{ program.name }}</td>
              <td v-for="year in years" :key="year" class="px-4 py-2 text-center">
                <div class="flex justify-center gap-1 flex-wrap">
                  <span v-for="coop in program.completed_by_year[year]" :key="coop.id"
                        class="w-4 h-4 bg-green-500 rounded-full cursor-pointer"
                        :title="coop.name + ' (' + coop.completed_at + ')'"></span>
                  <span v-if="program.completed_by_year[year].length === 0" class="text-gray-400">—</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
