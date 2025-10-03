<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head, useForm, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import amortizations from '@/routes/amortizations'

// Types
interface Schedule {
  id: number
  due_date: string
  installment: number
  penalty_amount?: number
  is_paid?: boolean
  date_paid?: string | null
  balance?: number
  amount_paid?: number
}

interface CoopProgram {
  id: number
  cooperative_name: string
  program_name: string
  loan_amount: number
  term_months?: number
  grace_period?: number
  schedules: Schedule[]
  expected_end_date?: string | null
}

const props = defineProps<{ coopProgram: CoopProgram }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Payments', href: amortizations.index().url },
  { title: 'Amortization', href: '#' },
]

// Forms
const scheduleForms = props.coopProgram.schedules.map(() => useForm({ amount_paid: '' }))
const notificationForm = useForm({})

// Format date
function formatDate(dateStr?: string) {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

// Expected End Date
const expectedEndDate = computed(() => {
  if (!props.coopProgram?.schedules?.length) return null
  const lastSchedule = props.coopProgram.schedules[props.coopProgram.schedules.length - 1]
  return lastSchedule?.due_date || null
})

// Get form index skipping grace periods
function getFormIndex(rowIndex: number) {
  const grace = props.coopProgram.grace_period || 0
  const formIndex = rowIndex - grace
  return formIndex >= 0 ? formIndex : 0
}

// Compute all periods with carry-over dues
const allPeriods = computed(() => {
  const periods: { type: 'grace' | 'schedule'; label: string; data?: Schedule; totalDue?: number }[] = []
  let carryOver = 0
  const grace = props.coopProgram.grace_period || 0

  // Grace periods
  for (let i = 1; i <= grace; i++) {
    periods.push({ type: 'grace', label: `Grace Period ${i}` })
  }

  // Schedule rows with dues including carry-over
  props.coopProgram.schedules.forEach((s, i) => {
    const installment = Number(s.installment) || 0
    const penalty = Number(s.penalty_amount) || 0
    const paid = Number(s.amount_paid) || 0

    const dues = installment + penalty + carryOver
    const unpaid = Math.max(0, Math.round(dues - paid))

    carryOver = unpaid > 0 ? unpaid : 0

    periods.push({
      type: 'schedule',
      label: `Period ${i + 1}`,
      data: s,
      totalDue: dues
    })
  })

  return periods
})

// Status display
function getStatus(schedule: Schedule) {
  if (schedule.is_paid || (schedule.balance ?? 0) === 0) {
    return schedule.date_paid
      ? `Paid on ${formatDate(schedule.date_paid)}`
      : 'Paid'
  }

  if ((schedule.balance || 0) > 0 && (schedule.amount_paid || 0) > 0) {
    return `Partially Paid (Balance: ₱${Math.round(schedule.balance || 0).toLocaleString()})`
  }

  const dueDate = schedule.due_date ? new Date(schedule.due_date) : new Date()
  return new Date() > dueDate ? 'Overdue' : 'Pending'
}
// Actions
function markPaid(scheduleId: number) {
  router.post(`/schedules/${scheduleId}/mark-paid`, { preserveScroll: true })
}
//Toggle Penalty
function togglePenalty(scheduleId: number, hasPenalty: boolean, row: Schedule) {
  router.post(`/schedules/${scheduleId}/penalty`,
    hasPenalty ? { remove: true } : { add: true },
    { preserveScroll: true }
  )

  // Update local UI immediately
  if (hasPenalty) {
    row.penalty_amount = 0
  } else {
    row.penalty_amount = Number(row.installment) * 0.01
  }
}
//Note Payments
function notePayment(scheduleId: number, rowIndex: number) {
  const form = scheduleForms[getFormIndex(rowIndex)]
  if (!form) return
  form.post(`/schedules/${scheduleId}/note-payment`, { preserveScroll: true })
}
//Send Notification
function sendNotification(scheduleId: number) {
  router.post(`/schedules/${scheduleId}/send-notif`, { preserveScroll: true })
}
</script>

<template>

  <Head title="Loan Tracker" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">

      <!-- Loan Information Card -->
      <div class="border rounded-2xl shadow-md p-6 
    bg-gray-200 dark:bg-gray-900 
    border-gray-200 dark:border-gray-700">

        <!-- Header -->
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Loan Tracker
          </h2>
          <p class="text-m text-gray-500 dark:text-gray-400">
            Program: <span class="font-medium text-gray-800 dark:text-gray-200">
              {{ coopProgram.program_name }}
            </span>
          </p>
          <p class="text-m text-gray-500 dark:text-gray-400">
            Cooperative: <span class="font-medium text-gray-800 dark:text-gray-200">
              {{ coopProgram.cooperative_name }}
            </span>
          </p>
          <p class="mt-2 text-lg font-semibold text-indigo-600 dark:text-indigo-400">
            ₱{{ Math.round(coopProgram.loan_amount).toLocaleString() }}
          </p>
        </div>

        <!-- Grid of Key Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <!-- Start Date -->
          <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Start Date</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ formatDate(coopProgram.schedules?.[0]?.due_date) }}
            </p>
          </div>

          <!-- Grace Period -->
          <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Grace Period</p>
            <p class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-300">
              {{ coopProgram.grace_period || 0 }} months
            </p>
          </div>

          <!-- Loan Term -->
          <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Loan Term</p>
            <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ coopProgram.term_months || 0 }} months
            </p>
          </div>

          <!-- Expected End Date -->
          <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-xl shadow-sm">
            <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Expected End Date</p>
            <p class="mt-1 text-lg font-semibold text-purple-600 dark:text-purple-300">
              {{ formatDate((coopProgram as any).expected_end_date) || 'N/A' }}
            </p>
          </div>
        </div>

        <hr class="my-6 border-t border-gray-300 dark:border-gray-700" />

        <!-- Amortization Table (inside same card) -->
        <h3 class="text-lg font-semibold mb-4">Payment Schedule</h3>
        <div class="overflow-x-auto">
          <Table class="w-full border-collapse">
            <TableHeader>
              <TableRow>
                <TableHead>Period</TableHead>
                <TableHead>Due Date</TableHead>
                <TableHead>Amount</TableHead>
                <TableHead>Penalty</TableHead>
                <TableHead>Dues</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Actions</TableHead>
                <TableHead>Reminder</TableHead>
              </TableRow>
            </TableHeader>

            <TableBody>
              <TableRow v-for="(row, index) in allPeriods" :key="index" :class="[
                'transition-colors',
                row.data?.is_paid ? 'bg-green-50 dark:bg-green-900/20' : '',
                index % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-900'
              ]">
                <!-- Grace row -->
                <template v-if="row.type === 'grace'">
                  <TableCell class=" font-medium text-indigo-600">{{ row.label }}</TableCell>
                  <TableCell colspan="7" class=" text-center text-yellow-600 font-semibold">
                    🌿 No payment due (Grace Period)
                  </TableCell>
                </template>

                <!-- Schedule row -->
                <template v-else>
                  <TableCell class="font-semibold text-gray-700 dark:text-gray-200">
                    {{ row.label }}
                  </TableCell>
                  <TableCell>{{ formatDate(row.data?.due_date) }}</TableCell>
                  <TableCell class="font-medium">₱{{ Math.round(row.data?.installment || 0).toLocaleString()
                  }}
                  </TableCell>

                  <!-- Penalty -->
                  <TableCell>
                    <div v-if="row.data" class="flex items-center gap-3">
                      <span class="font-medium text-gray-700 dark:text-gray-300">
                        ₱{{ Math.round(row.data.penalty_amount || 0).toLocaleString() }}
                      </span>
                      <Button size="sm" :disabled="row.data.is_paid"
                        @click="togglePenalty(row.data.id, row.data.penalty_amount! > 0, row.data!)" :class="[
                          'flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold shadow transition',
                          row.data.penalty_amount! > 0
                            ? 'bg-red-600 hover:bg-red-700 text-white'
                            : 'bg-orange-500 hover:bg-orange-600 text-white'
                        ]">
                        <span v-if="row.data.penalty_amount! > 0">✕ Remove</span>
                        <span v-else>＋ Add</span>
                      </Button>
                    </div>
                  </TableCell>

                  <!-- Dues -->
                  <TableCell class="font-semibold text-indigo-700 dark:text-indigo-300">
                    ₱{{ (Number(row.data?.installment) || 0).toLocaleString() }}
                    <span v-if="row.data?.penalty_amount && row.data.penalty_amount > 0">
                      + ₱{{ (Number(row.data.penalty_amount) || 0).toLocaleString() }}
                    </span>
                    = <strong>₱{{ row.totalDue !== undefined ? Math.round(row.totalDue).toLocaleString() : 0
                    }}</strong>
                  </TableCell>

                  <!-- Status -->
                  <TableCell>
                    <span :class="[
                      'inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold',
                      row.data?.is_paid
                        ? 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200'
                        : getStatus(row.data!) === 'Overdue'
                          ? 'bg-red-100 text-red-700 dark:bg-red-800 dark:text-red-200'
                          : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-700 dark:text-yellow-200'
                    ]">
                      {{ getStatus(row.data!) }}
                    </span>
                  </TableCell>

                  <!-- Actions -->
                  <TableCell>
                    <!-- Show Pay / Mark Paid buttons only if not paid and no partial payment -->
                    <div v-if="row.data && !row.data.is_paid && !(row.data.amount_paid && (row.data.balance || 0) > 0)"
                      class="flex flex-col gap-2">
                      <input type="number" v-model.number="scheduleForms[getFormIndex(index)].amount_paid"
                        placeholder="Enter amount"
                        class="w-36 px-3 py-2 border rounded-xl text-sm focus:ring-2 focus:ring-indigo-500" />

                      <Button size="sm" class="w-36 bg-blue-600 hover:bg-blue-700 text-white rounded-full"
                        @click="notePayment(row.data!.id, index)">
                        💸 Pay
                      </Button>

                      <Button size="sm" class="w-36 bg-green-600 hover:bg-green-700 text-white rounded-full"
                        @click="markPaid(row.data!.id)">
                        ✅ Mark Paid
                      </Button>
                    </div>

                    <!-- If partially paid -->
                    <div v-else-if="row.data?.amount_paid && (row.data.balance || 0) > 0"
                      class="text-yellow-600 font-semibold">
                      ⚠️ Partially Paid
                    </div>

                    <!-- If fully paid -->
                    <div v-else-if="row.data?.is_paid" class="text-gray-500 italic">
                      ✔ Paid
                    </div>
                  </TableCell>
                  <!-- Reminder -->
                  <TableCell>
                    <Button size="sm" class="px-4 py-1.5 rounded-full text-sm font-semibold 
                  bg-red-700 hover:bg-red-700 
                  text-white shadow-sm" @click="sendNotification(row.data!.id)">
                      🔔 Send Reminder
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
