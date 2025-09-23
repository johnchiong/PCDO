<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
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
    const unpaid = Math.round((s.installment || 0) + (s.penalty_amount || 0) + carryOver - (s.amount_paid || 0))
    carryOver = Math.max(0, unpaid)
    periods.push({ type: 'schedule', label: `Period ${i + 1}`, data: s, totalDue: unpaid })
  })

  return periods
})

// Status display
function getStatus(schedule: Schedule) {
  if (schedule.is_paid && schedule.date_paid) return `Paid on ${formatDate(schedule.date_paid)}`
  if ((schedule.balance || 0) > 0 && (schedule.amount_paid || 0) > 0)
    return `Partially Paid (Balance: ₱${Math.round(schedule.balance || 0).toLocaleString()})`
  const dueDate = schedule.due_date ? new Date(schedule.due_date) : new Date()
  return new Date() > dueDate ? 'Overdue' : 'Pending'
}

// Actions
function markPaid(scheduleId: number) {
  router.post(`/schedules/${scheduleId}/mark-paid`, { preserveScroll: true })
}
function togglePenalty(scheduleId: number) {
  router.post(`/schedules/${scheduleId}/penalty`, { preserveScroll: true })
}
function notePayment(scheduleId: number, rowIndex: number) {
  const form = scheduleForms[getFormIndex(rowIndex)]
  if (!form) return
  form.post(`/schedules/${scheduleId}/note-payment`, { preserveScroll: true })
}
function sendNotification() {
  notificationForm.post(`/loans/${props.coopProgram.id}/send-notif`, { preserveScroll: true })
}
</script>

<template>
<Head title="Loan Tracker" />
<AppLayout :breadcrumbs="breadcrumbs">
  <div class="p-6 space-y-8">

    <!-- Loan Info -->
    <div class="border rounded-2xl shadow-sm p-6 bg-gray-100 dark:bg-[#0f172a]">
      <h2 class="text-2xl font-semibold mb-2">{{ coopProgram.program_name }}</h2>
      <p>Cooperative: <strong>{{ coopProgram.cooperative_name }}</strong></p>
      <p>Loan Amount: <strong>₱{{ Math.round(coopProgram.loan_amount).toLocaleString() }}</strong></p>
      <p>Term: <strong>{{ coopProgram.term_months || 0 }} months</strong></p>
      <p>Grace Period: <strong>{{ coopProgram.grace_period || 0 }} months</strong></p>
    </div>

    <!-- Amortization Table -->
    <div class="border rounded-2xl shadow-sm p-6 bg-gray-100 dark:bg-[#0f172a]">
      <h3 class="text-lg font-semibold mb-4">Payment Schedule</h3>
      <div class="overflow-x-auto">
        <Table>
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
              index % 2 === 0 ? 'bg-gray-100 dark:bg-[#1e293b]/50' : ''
            ]">

              <!-- Grace row -->
              <template v-if="row.type === 'grace'">
                <TableCell class="font-medium">{{ row.label }}</TableCell>
                <TableCell colspan="7" class="text-center text-yellow-600 font-semibold">
                  No payment due (Grace Period)
                </TableCell>
              </template>

              <!-- Schedule row -->
              <template v-else>
                <TableCell>{{ row.label }}</TableCell>
                <TableCell>{{ formatDate(row.data?.due_date) }}</TableCell>
                <TableCell>₱{{ Math.round(row.data?.installment || 0).toLocaleString() }}</TableCell>
                <TableCell>
                  <div v-if="row.data" class="flex items-center gap-2">
                    <span>₱{{ Math.round(row.data.penalty_amount || 0).toLocaleString() }}</span>
                    <Button size="sm" :disabled="row.data.is_paid" @click="togglePenalty(row.data.id)" class="bg-red-500 text-white">
                      {{ row.data.penalty_amount! > 0 ? 'Remove' : 'Add' }}
                    </Button>
                  </div>
                </TableCell>

                <!-- Dues = installment + penalty + carry-over -->
                <TableCell>₱{{ row.totalDue !== undefined ? Math.round(row.totalDue).toLocaleString() : 0 }}</TableCell>

                <!-- Status -->
                <TableCell>
                  <span :class="[
                    'px-2 py-1 rounded text-xs font-semibold',
                    row.data?.is_paid
                      ? 'bg-green-200 text-green-800 dark:bg-green-800 dark:text-green-200'
                      : getStatus(row.data!) === 'Overdue'
                        ? 'bg-red-200 text-red-800 dark:bg-red-800 dark:text-red-200'
                        : 'bg-yellow-200 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-200'
                  ]">
                    {{ getStatus(row.data!) }}
                  </span>
                </TableCell>

                <!-- Actions -->
                <TableCell>
                  <div v-if="row.data && !row.data.is_paid && ((row.data.balance || 0) === 0 || (row.data.amount_paid || 0) === 0)" class="flex flex-col gap-2">
                    <input type="number" v-model.number="scheduleForms[getFormIndex(index)].amount_paid"
                      placeholder="Enter amount" class="w-32 px-2 py-1 border rounded-md" />
                    <Button size="sm" class="w-36 bg-blue-600 hover:bg-blue-700 text-white"
                      @click="notePayment(row.data!.id, index)">Pay</Button>
                    <Button size="sm" class="w-36 bg-green-600 hover:bg-green-700 text-white"
                      @click="markPaid(row.data!.id)">Mark Paid</Button>
                  </div>
                  <div v-else-if="row.data?.amount_paid && (row.data.balance || 0) > 0" class="text-yellow-600 font-semibold">
                    Partially Paid - Balance: ₱{{ Math.round(row.data.balance || 0).toLocaleString() }}
                  </div>
                  <div v-else-if="row.data?.is_paid" class="text-gray-500 italic">Paid</div>
                </TableCell>

                <!-- Reminder -->
                <TableCell>
                  <Button size="sm" class="text-white bg-gray-200 dark:bg-[#7C0A02] border border-gray-300 dark:border-[#475569] hover:bg-gray-300 dark:hover:bg-[#833d3d]" @click="sendNotification">Send Reminder</Button>
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
