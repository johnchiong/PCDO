<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, useForm, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { computed } from 'vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

// Types
interface Schedule {
  id: number
  due_date: string
  amount_due: number
  penalty_amount: number
  is_paid: boolean
  paid_at?: string | null
}
interface Loan {
  id: number
  amount: number
  start_date: string
  term_months: number
  grace_period: number
  cooperative: { name: string }
  program: { name: string }
  schedules: Schedule[]
}
const props = defineProps<{ loan: Loan }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Payments', href: '/payments' },
  { title: 'Amortization', href: '#' },
]

// Forms
const scheduleForms = props.loan.schedules.map(() => useForm({}))
const notificationForm = useForm({})

// Format date
function formatDate(dateStr: string): string {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
}

// Generate full periods (grace + payable)
const allPeriods = computed(() => {
  const result: { type: 'grace' | 'schedule'; label: string; data?: Schedule }[] = []

  for (let i = 1; i <= props.loan.grace_period; i++) {
    result.push({ type: 'grace', label: `Grace Period ${i}` })
  }

  props.loan.schedules.forEach((s, idx) => {
    result.push({
      type: 'schedule',
      label: `Period ${idx + 1}`,
      data: s,
    })
  })

  return result
})

// Check if a row should be disabled
function isRowDisabled(index: number) {
  const actualIndex = index - props.loan.grace_period
  if (actualIndex <= 0) return false
  return !props.loan.schedules.slice(0, actualIndex).every((s) => s.is_paid)
}

// Determine status label
function getStatus(schedule: Schedule) {
  if (schedule.is_paid) return `Paid on ${formatDate(schedule.paid_at ?? '')}`

  const dueDate = new Date(schedule.due_date)
  const today = new Date()
  return today > dueDate ? 'Overdue' : 'Pending'
}

// Methods
function markPaid(scheduleId: number, index: number) {
  scheduleForms[index].post(`/schedules/${scheduleId}/mark-paid`, {
    preserveScroll: true,
    onSuccess: () => router.reload(),
  })
}
function togglePenalty(scheduleId: number, index: number) {
  scheduleForms[index].post(`/schedules/${scheduleId}/penalty`, {
    preserveScroll: true,
    onSuccess: () => router.reload(),
  })
}
function sendNotification() {
  notificationForm.post(`/loans/${props.loan.id}/send-notif`, {
    preserveScroll: true,
    onSuccess: () => router.reload(),
  })
}
</script>

<template>
  <Head title="Loan Tracker" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      
      <!-- Loan Information Card -->
      <div
        class="border rounded-2xl shadow-sm p-6 
               bg-gray-100 dark:bg-[#0f172a] 
               border-gray-300 dark:border-[#1e293b]"
      >
        <h2 class="text-2xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
          Loan Tracker for {{ loan.program.name }}
        </h2>
        <p class="text-gray-700 dark:text-gray-400">
          Cooperative Name: <span class="font-medium">{{ loan.cooperative.name }}</span>
        </p>
        <p class="text-gray-700 dark:text-gray-400">
          Amount: <span class="font-medium">₱{{ loan.amount.toLocaleString() }}</span>
        </p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <div class="bg-gray-200 dark:bg-[#1e293b] p-4 rounded-lg text-sm">
            <p class="text-gray-600 dark:text-gray-400">Start Date</p>
            <p class="font-semibold text-gray-900 dark:text-gray-100">
              {{ formatDate(loan.start_date) }}
            </p>
          </div>
          <div class="bg-gray-200 dark:bg-[#1e293b] p-4 rounded-lg text-sm">
            <p class="text-gray-600 dark:text-gray-400">Grace Period</p>
            <p class="font-semibold text-gray-900 dark:text-gray-100">
              {{ loan.grace_period }} months
            </p>
          </div>
          <div class="bg-gray-200 dark:bg-[#1e293b] p-4 rounded-lg text-sm">
            <p class="text-gray-600 dark:text-gray-400">Loan Term</p>
            <p class="font-semibold text-gray-900 dark:text-gray-100">
              {{ loan.term_months }} months
            </p>
          </div>
        </div>
      </div>

      <!-- Amortization Schedule Table -->
      <div
        class="border rounded-2xl shadow-sm p-6 
               bg-gray-100 dark:bg-[#0f172a] 
               border-gray-300 dark:border-[#1e293b]"
      >
        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
          Payment Schedule
        </h3>

        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Period</TableHead>
                <TableHead>Due Date</TableHead>
                <TableHead>Amount</TableHead>
                <TableHead>Penalty</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Action</TableHead>
                <TableHead>Reminder</TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow
                v-for="(row, index) in allPeriods"
                :key="index"
                :class="[
                  'transition-colors',
                  index % 2 === 0 ? 'bg-gray-200 dark:bg-[#1e293b]/50' : '',
                  row.type === 'schedule' && row.data?.is_paid
                    ? 'bg-green-50 dark:bg-green-900/20'
                    : ''
                ]"
              >
                <!-- Grace Period Row -->
                <template v-if="row.type === 'grace'">
                  <TableCell class="font-medium">{{ row.label }}</TableCell>
                  <TableCell colspan="6" class="text-center text-yellow-600 font-semibold">
                    No payment due (Grace Period)
                  </TableCell>
                </template>

                <!-- Payment Row -->
                <template v-else>
                  <TableCell class="font-medium">{{ row.label }}</TableCell>
                  <TableCell>{{ formatDate(row.data?.due_date ?? '') }}</TableCell>
                  <TableCell>₱{{ row.data?.amount_due.toLocaleString() }}</TableCell>

                  <!-- Penalty Toggle -->
                  <TableCell>
                    <div v-if="row.data" class="flex items-center gap-3">
                      <span class="text-sm font-medium">
                        ₱{{ row.data.penalty_amount.toLocaleString() }}
                      </span>
                      <button
                        type="button"
                        @click="!row.data.is_paid && togglePenalty(row.data.id, index)"
                        :disabled="row.data.is_paid"
                        :class="[
                          'relative inline-flex h-6 w-12 items-center rounded-full transition',
                          row.data.is_paid
                            ? 'bg-gray-400 cursor-not-allowed'
                            : row.data.penalty_amount > 0
                              ? 'bg-red-500 hover:bg-red-600'
                              : 'bg-gray-400 dark:bg-gray-600 hover:bg-gray-500',
                        ]"
                      >
                        <span
                          :class="[
                            'inline-block h-5 w-5 transform rounded-full bg-white shadow transition',
                            row.data.penalty_amount > 0 ? 'translate-x-6' : 'translate-x-1',
                          ]"
                        />
                      </button>
                    </div>
                  </TableCell>

                  <!-- Status -->
                  <TableCell>
                    <span
                      v-if="row.data"
                      :class="[
                        'px-2 py-1 rounded text-xs font-semibold',
                        row.data.is_paid
                          ? 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200'
                          : getStatus(row.data) === 'Overdue'
                            ? 'bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200'
                            : 'bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200',
                      ]"
                    >
                      {{ getStatus(row.data) }}
                    </span>
                    <span v-else class="text-gray-500 italic">No Status</span>
                  </TableCell>

                  <!-- Mark Paid -->
                  <TableCell>
                    <div v-if="row.data?.is_paid" class="text-gray-500 italic">Paid</div>
                    <Button
                      v-else
                      size="sm"
                      variant="default"
                      :disabled="isRowDisabled(index) || scheduleForms[index - loan.grace_period]?.processing"
                      @click="markPaid(row.data!.id, index - loan.grace_period)"
                      class="bg-green-600 text-white hover:bg-green-700 
                             disabled:bg-green-900 disabled:text-gray-400"
                    >
                      Mark Paid
                    </Button>
                  </TableCell>

                  <!-- Send Reminder -->
                  <TableCell>
                    <Button
                      size="sm"
                      variant="secondary"
                      class="bg-gray-200 dark:bg-[#334155] border border-gray-300 dark:border-[#475569] hover:bg-gray-300 dark:hover:bg-[#3b445c]"
                      :disabled="notificationForm.processing"
                      @click="sendNotification"
                    >
                      Send Reminder
                    </Button>
                  </TableCell>
                </template>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>