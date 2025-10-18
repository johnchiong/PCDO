<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { BreadcrumbItem } from '@/types'

const props = defineProps<{
    years: Array<{
        year: string
        cooperatives: Array<{
            id: number
            name: string
            program_name: string
            completed_at: string
        }>
    }>
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Documentation', href: '#' },
]
</script>

<template>
  <Head title="Documentation" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-gray-50 dark:bg-gray-900 min-h-screen px-6 py-8">
      <!-- Page Title -->
      <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100">
          Documentation by Year
        </h1>
        <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
          Browse completed cooperative documentation organized by year.
        </p>
      </div>

      <!-- No Data State -->
      <div v-if="!props.years || props.years.length === 0" class="flex flex-col items-center justify-center py-20">
        <p class="text-gray-500 dark:text-gray-400 text-lg">
          No documentation data available.
        </p>
      </div>

      <!-- Year Dropdowns -->
      <div v-else class="max-w-6x2 mx-auto space-y-4">
        <DropdownMenu v-for="yearGroup in props.years" :key="yearGroup.year">
          <!-- Trigger Button -->
          <DropdownMenuTrigger asChild>
            <button
              class="w-full flex items-center justify-between px-6 py-3 rounded-xl 
                     bg-indigo-600 text-white font-semibold shadow-sm
                     hover:bg-indigo-700 active:bg-indigo-800
                     focus:outline-none focus:ring-2 focus:ring-indigo-400 
                     transition duration-200"
            >
              <span class="text-lg">{{ yearGroup.year }}</span>
              <ChevronDown class="w-5 h-5 opacity-90" />
            </button>
          </DropdownMenuTrigger>

          <!-- Dropdown Content -->
          <DropdownMenuContent
            side="bottom"
            align="start"
            class="w-302 mt-2 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
          >
            <div v-if="yearGroup.cooperatives.length > 0">
              <Table>
                <TableCaption>
                  <span class="text-gray-600 dark:text-gray-400 text-sm">
                    Completed cooperatives for {{ yearGroup.year }}
                  </span>
                </TableCaption>

                <TableHeader>
                  <TableRow class="bg-gray-100 dark:bg-gray-700">
                    <TableHead class="text-gray-700 dark:text-gray-200">Name</TableHead>
                    <TableHead class="text-gray-700 dark:text-gray-200">Program</TableHead>
                    <TableHead class="text-gray-700 dark:text-gray-200">Completed At</TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow
                    v-for="coop in yearGroup.cooperatives"
                    :key="coop.id"
                    @click="$inertia.get(`/documentation/cooperative/${coop.id}`)"
                    class="cursor-pointer hover:bg-indigo-50 dark:hover:bg-gray-700 transition"
                  >
                    <TableCell class="font-medium text-gray-800 dark:text-gray-100">{{ coop.name }}</TableCell>
                    <TableCell class="text-gray-700 dark:text-gray-300">{{ coop.program_name }}</TableCell>
                    <TableCell class="text-gray-600 dark:text-gray-400">{{ coop.completed_at }}</TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <div v-else class="p-5 text-gray-500 dark:text-gray-400 text-center text-sm">
              No cooperatives completed yet for this year.
            </div>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>
    </div>
  </AppLayout>
</template>
