<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { BreadcrumbItem } from '@/types'

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Payments', href: '' }]

interface CoopProgram {
  id: number
  cooperative_name: string
  program_name: string
  loan_amount: number
  status: string
  has_schedule: boolean
  coop_program_id: number
}

const props = defineProps<{ coopPrograms: CoopProgram[] }>()

const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)

const filteredPrograms = computed(() => {
  if (!searchQuery.value) return props.coopPrograms
  return props.coopPrograms.filter(p =>
    p.cooperative_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    p.program_name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
    p.id.toString().includes(searchQuery.value)
  )
})

const paginatedPrograms = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredPrograms.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => Math.ceil(filteredPrograms.value.length / itemsPerPage.value))

function goToPage(page: number) {
  if (page >= 1 && page <= totalPages.value) currentPage.value = page
}
</script>

<template>

  <Head title="Amortization List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Top Actions -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-6">
      <div class="relative w-full md:w-80">
        <InputField v-model="searchQuery" placeholder="Search cooperatives or programs..." />
      </div>
    </div>

    <!-- Table Card -->
    <div class="p-6">
      <div class="bg-white dark:bg-gray-800 shadow rounded-2xl overflow-hidden">
        <Table>
          <TableCaption class="text-lg font-semibold p-4">List of Cooperatives</TableCaption>
          <TableHeader class="bg-gray-100 dark:bg-gray-700">
            <TableRow>
              <TableHead>ID</TableHead>
              <TableHead class="pl-20">Cooperative</TableHead>
              <TableHead class="pl-20">Program</TableHead>
              <TableHead class="pl-20">Loan Amount</TableHead>
              <TableHead class="pl-20">Status</TableHead>
              <TableHead class="pl-22">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="program in paginatedPrograms" :key="program.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700">
              <TableCell class="font-medium text-gray-600">{{ program.id }}</TableCell>
              <TableCell class="font-semibold pl-20">{{ program.cooperative_name }}</TableCell>
              <TableCell class="pl-20">{{ program.program_name }}</TableCell>
              <TableCell class="pl-20">₱{{ (program.loan_amount ?? 0).toLocaleString() }}</TableCell>
              <TableCell class="pl-20 flex items-center gap-2">
                <CheckCircle v-if="program.status === 'Finished'" class="text-green-500 w-4 h-4" />
                <CircleDashed v-else-if="program.status === 'Ongoing'" class="text-yellow-500 w-4 h-4 animate-spin" />
                <XCircle v-else class="text-red-500 w-4 h-4" />
                <span :class="program.status === 'Ongoing' ? 'text-yellow-500' : 'text-red-500'">
                  {{ program.status }}
                </span>
              </TableCell>
              <TableCell class="pl-20 space-x-2">
                <Link :href="`/amortizations/${program.coop_program_id}`"
                  class="px-3 py-1 rounded-lg bg-blue-500 text-white hover:bg-blue-600">
                View Amortization
                </Link>
              </TableCell>
            </TableRow>

            <TableRow v-if="paginatedPrograms.length === 0">
              <TableCell colspan="6" class="text-center text-gray-500 py-6">No records found.</TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Pagination (same as Cooperative index) -->
      <div class="flex justify-between items-center mt-6">
        <span class="text-sm text-gray-600 dark:text-gray-300">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} - {{ Math.min(currentPage * itemsPerPage,
            filteredPrograms.length) }} of {{ filteredPrograms.length }}
        </span>
        <div class="flex items-center gap-2">
          <Button :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">Previous</Button>
          <span class="text-sm text-gray-700 dark:text-gray-300">Page {{ currentPage }} of {{ totalPages }}</span>
          <Button :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">Next</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>