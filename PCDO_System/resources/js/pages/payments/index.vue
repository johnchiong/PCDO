<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] =
  [{ title: 'Payments', href: '#' }]

interface CoopProgram {
  id: number
  cooperative_name: string
  program_name: string
  loan_amount: number
  status: string
  has_schedule: boolean
  coop_program_id: number
  next_due_date: string | null
}

// Search + Filters
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedProgram = ref('')

const props = defineProps<{ coopPrograms: CoopProgram[] }>()

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredPrograms = computed(() => {
  let list = props.coopPrograms

  // Search filter
  if (searchQuery.value) {
    list = list.filter(p =>
      p.cooperative_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.program_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.id.toString().includes(searchQuery.value)
    )
  }

  // Status filter
  if (selectedStatus.value) {
    list = list.filter(p => p.status.toLowerCase() === selectedStatus.value.toLowerCase())
  }

  // Program filter
  if (selectedProgram.value) {
    list = list.filter(p => p.program_name.toLowerCase() === selectedProgram.value.toLowerCase())
  }

  return list
})


const paginatedPrograms = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredPrograms.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => Math.ceil(filteredPrograms.value.length / itemsPerPage.value))

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

function formatNextDue(date: string | null) {
  if (!date || date === 'N/A') return 'N/A'
  const due = new Date(date)
  return due.toLocaleDateString('en-US')
}
</script>

<template>

  <Head title="Amortization List" />

  <AppLayout :breadcrumbs="breadcrumbs">

    <!-- Filter + Actions Card -->
    <div class="p-4">
      <div class="bg-gray-100 dark:bg-gray-900 shadow-lg rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

          <!-- Left side: Search bar -->
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 h-4 w-4" />
            <InputField v-model="searchQuery" placeholder="Search programs..."
              class="pl-9 pr-3 w-full rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 placeholder-gray-500 bg-gray-200" />
          </div>

          <!-- Right side: Filters -->
          <div class="flex flex-wrap items-center gap-3">
            <!-- Program Filter -->
            <select v-model="selectedProgram"
              class="px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 bg-gray-200">
              <option value="">All Programs</option>
              <option v-for="program in [...new Set(props.coopPrograms.map(p => p.program_name))]" :key="program"
                :value="program">
                {{ program }}
              </option>
            </select>

            <!-- Status Filter -->
            <select v-model="selectedStatus"
              class="px-3 py-2 border border-gray-300 rounded-lg text-sm dark:bg-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-indigo-500 bg-gray-200">
              <option value="">All Status</option>
              <option value="Ongoing">Ongoing</option>
              <option value="Finished">Finished</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="p-4">
      <div
        class="bg-gray-100 dark:bg-gray-900 shadow-lg rounded-2xl overflow-hidden border border-gray-300 dark:border-gray-700">
        <Table>
          <TableCaption class="text-lg font-semibold p-4 text-gray-900 dark:text-gray-100">
            📋 List of Cooperatives
          </TableCaption>
          <TableHeader class="bg-gray-300 dark:bg-gray-800">
            <TableRow>
              <TableHead class="px-6 py-3 text-left">ID</TableHead>
              <TableHead class="px-6 py-3 text-left">Cooperative</TableHead>
              <TableHead class="px-6 py-3 text-left">Program</TableHead>
              <TableHead class="px-6 py-3 text-left">Loan Amount</TableHead>
              <TableHead class="px-6 py-3 text-left">Next Due Date</TableHead>
              <TableHead class="px-6 py-3 text-left">Status</TableHead>
              <TableHead class="px-6 py-3 text-left">Actions</TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <TableRow v-for="program in paginatedPrograms" :key="program.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
              <TableCell class="px-6 py-4">{{ program.id }}</TableCell>
              <TableCell class="px-6 py-4 font-semibold">{{ program.cooperative_name }}</TableCell>
              <TableCell class="px-6 py-4">{{ program.program_name }}</TableCell>
              <TableCell class="px-6 py-4">
                ₱{{ (program.loan_amount ?? 0).toLocaleString() }}
              </TableCell>
              <TableCell class="px-6 py-4">
                {{ formatNextDue(program.next_due_date) }}
              </TableCell>
              <TableCell class="px-6 py-4">
                <span v-if="program.status === 'Finished'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300">
                  <CheckCircle class="w-3 h-3" /> <!-- ✅ finished icon -->
                  Finished
                </span>

                <span v-else-if="program.status === 'Ongoing'" class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                bg-yellow-100 text-yellow-700 dark:bg-yellow-900 dark:text-yellow-300">
                  <CircleDashed class="w-3 h-3 animate-spin" /> <!-- ⭕ ongoing icon -->
                  Ongoing
                </span>

                <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-medium rounded-full
                bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300">
                  <XCircle class="w-3 h-3" /> <!-- ❌ inactive/other -->
                  {{ program.status }}
                </span>
              </TableCell>

              <TableCell class="px-6 py-4">
                <Link :href="`/amortizations/${program.coop_program_id}`"
                  class="inline-block px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium shadow hover:bg-indigo-700 transition">
                View Amortization
                </Link>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination -->
      <div class="flex flex-col md:flex-row justify-between items-center mt-6 gap-4">
        <span class="text-sm text-gray-600 dark:text-gray-300">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} -
          {{ Math.min(currentPage * itemsPerPage, filteredPrograms.length) }}
          of {{ filteredPrograms.length }}
        </span>
        <div class="flex items-center gap-2">
          <Button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)"
            class="px-3 py-1 rounded-lg bg-gray-400 dark:bg-gray-400 hover:bg-gray-500 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100">
            Previous
          </Button>
          <span class="text-sm text-gray-700 dark:text-gray-300">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <Button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)"
            class="px-3 py-1 rounded-lg bg-gray-400 dark:bg-gray-400 hover:bg-gray-500 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100">
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>