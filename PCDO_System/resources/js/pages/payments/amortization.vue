<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'
import amortizations from '@/routes/amortizations'
import { toast } from "vue-sonner"
import { ref } from 'vue';


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
  program_status?: string
  resolved?: boolean
}

const props = defineProps<{ coopProgram: CoopProgram }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Payments', href: amortizations.index().url },
  { title: 'Amortization', href: '#' },
]

// Flash message on successful amortization generation
onMounted(() => {
  const params = new URLSearchParams(window.location.search)
  if (params.get('flash') === 'amortization_success') {
    toast.success('Amortization generated successfully!')
  }
})

// Forms
const scheduleForms = props.coopProgram.schedules.map(() => useForm({ amount_paid: '' }))

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

const isDelinquent = computed(() => {
  if (!props.coopProgram?.schedules?.length) return false;

  const now = new Date();
  const fourMonthsAgo = new Date();
  fourMonthsAgo.setMonth(now.getMonth() - 4);

  // find if there's any schedule due before 4 months ago that is unpaid
  return props.coopProgram.schedules.some(s => {
    const dueDate = new Date(s.due_date);
    return dueDate < fourMonthsAgo && !s.is_paid;
  });
});

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
function markPaid(scheduleId: number, periodLabel: string) {
  router.post(
    `/schedules/${scheduleId}/mark-paid`,
    { preserveScroll: true },
    {
      onSuccess: () => {
        // ✅ Show toast only after success
        toast.success(`Marked as paid on ${periodLabel} successfully!`)
      },
      onError: () => {
        toast.error('Failed to mark as paid. Please try again.')
      }
    }
  )
}

//Toggle Penalty
function togglePenalty(scheduleId: number, hasPenalty: boolean, row: Schedule) {
  router.post(
    `/schedules/${scheduleId}/penalty`,
    hasPenalty ? { remove: true } : { add: true },
    {
      preserveScroll: true,
      onSuccess: () => {
        toast.success(hasPenalty ? 'Penalty removed.' : 'Penalty added.')

        if (hasPenalty) {
          row.penalty_amount = 0
        } else {
          row.penalty_amount = Number(row.installment) * 0.01
        }
      },
      onError: () => {
        toast.error('Failed to toggle penalty. Please try again.')
      }
    }
  )
}

//Note Payments
function notePayment(scheduleId: number, rowIndex: number, isPartial = false, paidPeriods: string[] = []) {
  const form = scheduleForms[getFormIndex(rowIndex)]
  if (!form) return

  form.post(
    `/schedules/${scheduleId}/note-payment`,
    {
      preserveScroll: true,
      onSuccess: () => {
        if (isPartial) {
          toast.success(`Partial payment noted on ${paidPeriods.join(', ')} successfully!`)
        } else if (paidPeriods.length > 1) {
          toast.success(`Payments noted on ${paidPeriods.join(', ')} successfully!`)
        } else {
          toast.success(`Payment noted on period ${rowIndex + 1} successfully!`)
        }
      },
      onError: () => {
        toast.error('Failed to note payment. Please try again.')
      }
    }
  )
}

//Send Notification
function sendNotification(scheduleId: number) {
  router.post(
    `/schedules/${scheduleId}/send-notif`,
    {},
    {
      preserveScroll: true,
      onSuccess: () => {
        const flash = usePage().props.flash as { success?: string; error?: string }

        if (flash.success) {
          toast.success(flash.success)
        } else if (flash.error) {
          toast.error(flash.error)
        } else {
          toast.success('Reminder sent successfully!')
        }
      },
      onError: () => {
        toast.error('Something went wrong while sending reminder.')
      }
    }
  )
}

// Receipt Modal States
const showReceiptModal = ref(false)
const isSubmitting = ref(false)
const receiptFile = ref<File | null>(null)
const isResolved = computed(() => props.coopProgram.resolved)

// Open Modal
function openReceiptModal() {
  showReceiptModal.value = true
}

// Close Modal
function closeReceiptModal() {
  showReceiptModal.value = false
  receiptFile.value = null
}

// Submit Receipt
async function submitReceipt() {
  if (!receiptFile.value) {
    toast.error("Please upload a receipt before submitting.")
    return
  }

  isSubmitting.value = true
  const formData = new FormData()
  formData.append('receipt', receiptFile.value)

  router.post(`/amortization/${props.coopProgram.id}/resolve`, formData, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success("Loan marked as resolved successfully!");
      closeReceiptModal();
      router.reload(); // 🔁 refresh page to reflect resolved state
    },
    onError: () => {
      toast.error("Failed to resolve loan. Please try again.");
    },
    onFinish: () => {
      isSubmitting.value = false;
    },
  });
}

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement | null
  const file = target?.files?.[0] || null
  receiptFile.value = file
}

</script>

<template>

  <Head title="Loan Tracker" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
      <div class="px-5 md:px-5 pt-5">

        <!-- Loan Information Card -->
        <div
          class="bg-gray-200/40 dark:bg-gray-800/80 border ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 rounded-xl shadow-m px-6 py-5 mb-6">

          <!-- Header -->
          <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
              Loan Tracker
            </h2>
          </div>
          <div
            class="bg-gray-50 dark:bg-gray-800/80 border ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 rounded-xl shadow-m px-6 py-5 mb-6">
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


            <hr class="my-6 border-t border-gray-300 dark:border-gray-700" />

            <!-- Grid of Key Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
              <!-- Start Date -->
              <div
                class="bg-gray-200/50 dark:bg-gray-700 p-4 rounded-xl ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Start Date</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ formatDate(coopProgram.schedules?.[0]?.due_date) }}
                </p>
              </div>

              <!-- Grace Period -->
              <div
                class="bg-gray-200/50 dark:bg-gray-700 p-4 rounded-xl ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Grace Period</p>
                <p class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-300">
                  {{ coopProgram.grace_period || 0 }} months
                </p>
              </div>

              <!-- Loan Term -->
              <div
                class="bg-gray-200/50 dark:bg-gray-700 p-4 rounded-xl ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Loan Term</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">
                  {{ coopProgram.term_months || 0 }} months
                </p>
              </div>

              <!-- Expected End Date -->
              <div
                class="bg-gray-200/50 dark:bg-gray-700 p-4 rounded-xl ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 shadow-sm">
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Expected End Date</p>
                <p class="mt-1 text-lg font-semibold text-purple-600 dark:text-purple-300">
                  {{ formatDate((coopProgram as any).expected_end_date) || 'N/A' }}
                </p>
              </div>
            </div>
          </div>

          <!-- Amortization Table (inside same card) -->
          <h3 class="text-lg font-semibold mb-4">Payment Schedule</h3>
          <div class="bg-white dark:bg-gray-800 shadow-xl border border-gray-200 dark:border-gray-700 
         rounded-2xl overflow-hidden">
            <Table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700 shadow-lg">
              <!-- Header -->
              <TableHeader class="bg-gray-100 dark:bg-gray-700/50">
                <TableRow>
                  <TableHead class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-200">Period
                  </TableHead>
                  <TableHead class="px-4 py-3">Due Date</TableHead>
                  <TableHead class="px-4 py-3">Amount</TableHead>
                  <TableHead class="px-4 py-3">Penalty</TableHead>
                  <TableHead class="px-4 py-3">Dues</TableHead>
                  <TableHead class="px-4 py-3">Status</TableHead>
                  <TableHead class="px-4 py-3">Actions</TableHead>
                  <TableHead class="px-4 py-3">Reminder</TableHead>
                </TableRow>
              </TableHeader>

              <!-- Body -->
              <TableBody>
                <TableRow v-for="(row, index) in allPeriods" :key="index" :class="[
                  'transition-colors',
                  row.data?.is_paid ? 'bg-green-50 dark:bg-green-900/20' : '',
                  index % 2 === 0 ? 'bg-gray-200 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-900'
                ]">

                  <!-- Grace Period Row -->
                  <template v-if="row.type === 'grace'">
                    <TableCell class="px-4 py-3 font-medium text-indigo-400">
                      {{ row.label }}
                    </TableCell>

                    <TableCell colspan="7" class="text-center font-semibold text-yellow-500">
                      <span class="inline-flex items-center gap-2 justify-center">
                        <Leaf class="w-4 h-4 text-green-400" />
                        <span>No payment due (Grace Period)</span>
                      </span>
                    </TableCell>
                  </template>

                  <!-- Regular Payment Row -->
                  <template v-else>
                    <!-- Period -->
                    <TableCell class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">
                      {{ row.label }}
                    </TableCell>

                    <!-- Due Date -->
                    <TableCell class="px-4 py-3">
                      {{ formatDate(row.data?.due_date) }}
                    </TableCell>

                    <!-- Amount -->
                    <TableCell class="px-4 py-3 font-medium">
                      ₱{{ Math.round(row.data?.installment || 0).toLocaleString() }}
                    </TableCell>

                    <!-- Penalty -->
                    <TableCell class="px-4 py-3">
                      <div v-if="row.data" class="flex items-center gap-2">
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
                          <span v-else class="inline-flex items-center gap-1">
                            <Plus class="w-3 h-3" />
                            <span>Add</span>
                          </span>
                        </Button>
                      </div>
                    </TableCell>

                    <!-- Dues -->
                    <TableCell class="font-semibold text-indigo-700 dark:text-indigo-300">
                      ₱{{ Number(row.data?.installment || 0).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                      }) }}
                      <span v-if="row.data?.penalty_amount && row.data.penalty_amount > 0">
                        + ₱{{ Number(row.data.penalty_amount || 0).toLocaleString('en-PH', {
                          minimumFractionDigits: 2,
                          maximumFractionDigits: 2
                        }) }}
                      </span>
                      = <strong>
                        ₱{{ row.totalDue !== undefined
                          ? Number(row.totalDue).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                          })
                          : '0.00'
                        }}
                      </strong>
                    </TableCell>

                    <!-- Status -->
                    <TableCell class="px-4 py-3">
                      <template v-if="isResolved">
                        <span
                          class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-200">
                          <Check class="w-4 h-4" />
                          <span>Resolved</span>
                        </span>
                      </template>

                      <template v-else>
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
                      </template>
                    </TableCell>

                    <!-- Actions -->
                    <TableCell class="px-4 py-3">
                      <template v-if="isResolved">
                        <div class="inline-flex items-center gap-1 text-green-600 italic font-semibold">
                          <Check class="w-4 h-4" />
                          <span>Resolved</span>
                        </div>
                      </template>

                      <template v-else>
                        <div
                          v-if="row.data && !row.data.is_paid && !(row.data.amount_paid && (row.data.balance || 0) > 0)"
                          class="flex flex-col gap-2">
                          <input type="number" v-model.number="scheduleForms[getFormIndex(index)].amount_paid"
                            placeholder="Enter amount"
                            class="w-36 px-3 py-2 border rounded-xl border-gray-400 dark:border-gray-700 text-sm focus:ring-2 focus:ring-indigo-500" />

                          <Button size="sm" class="w-36 bg-blue-600 hover:bg-blue-700 text-white rounded-full"
                            @click="notePayment(row.data!.id, index)">
                            <CircleDollarSign class="w-3 h-3 text-yellow-300" /> Pay
                          </Button>

                          <Button size="sm" class="w-36 bg-green-600 hover:bg-green-700 text-white rounded-full"
                            @click="markPaid(row.data!.id, row.label)">
                            <ReceiptText class="w-3 h-3" /> Mark Paid
                          </Button>
                        </div>

                        <div v-else-if="row.data?.amount_paid && (row.data.balance || 0) > 0"
                          class="inline-flex items-center gap-1 text-yellow-600 font-semibold">
                          <TriangleAlert class="w-4 h-4" />
                          <span>Partially Paid</span>
                        </div>

                        <div v-else-if="row.data?.is_paid"
                          class="inline-flex items-center gap-1 text-emerald-400 italic font-semibold">
                          <Check class="w-5 h-5" />
                          <span>Paid</span>
                        </div>
                      </template>
                    </TableCell>

                    <!-- Reminder -->
                    <TableCell class="px-4 py-3">
                      <template v-if="isResolved">
                        <span class="inline-flex items-center gap-1 text-green-600 italic font-semibold">
                          <Check class="w-5 h-5" />
                          <span>Resolved</span>
                        </span>
                      </template>

                      <template v-else-if="row.data?.is_paid">
                        <span class="inline-flex items-center gap-1 text-emerald-400 italic font-semibold">
                          <Check class="w-5 h-5" />
                          <span>Paid</span>
                        </span>
                      </template>

                      <template v-else>
                        <Button size="sm"
                          class="px-4 py-1.5 rounded-full text-sm font-semibold bg-red-700 hover:bg-red-800 text-white shadow-sm"
                          @click="sendNotification(row.data!.id)">
                          🔔 Send Reminder
                        </Button>
                      </template>
                    </TableCell>
                  </template>
                </TableRow>
              </TableBody>
            </Table>
            <div class="flex justify-end mt-6 p-4">
              <button v-if="!isResolved" type="button" @click="openReceiptModal"
                class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg shadow-md transition">
                Resolve / Nullify Amortization
              </button>
            </div>

            <!-- Receipt Upload Modal -->
            <div v-if="showReceiptModal"
              class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
              <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-[90%] max-w-md p-6 relative">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                  Upload Receipt to Resolve Loan
                </h3>

                <div class="flex flex-col gap-3">
                  <input type="file" accept="image/*,application/pdf" @change="onFileChange"
                    class="border border-gray-300 dark:border-gray-700 rounded-lg p-2 text-sm bg-gray-50 dark:bg-gray-700" />
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    Please upload the official receipt (image or PDF) before marking this loan as resolved.
                  </p>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                  <button @click="closeReceiptModal"
                    class="px-4 py-2 rounded-lg bg-gray-300 hover:bg-gray-400 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100">
                    Cancel
                  </button>

                  <button @click="submitReceipt" :disabled="isSubmitting"
                    class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50">
                    <span v-if="isSubmitting">Submitting...</span>
                    <span v-else>Submit Receipt</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
