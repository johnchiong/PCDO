<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm, router } from '@inertiajs/vue3'
import { BreadcrumbItem } from '@/types'
import { computed } from 'vue'

// Interfaces
interface ChecklistItem {
  id: number
  name: string
  upload?: { id: number; file_name: string; mime_type: string } | null
}

interface CoopProgram {
  id: number                 // <-- coop_program.id
  cooperative: { id: number; name: string }
  program?: { id: number; name: string; min_amount: number; max_amount: number } | null
  loan_amount?: number | null
  with_grace?: number | null
}

// Loan form
interface LoanFormData {
  loan_amount: number | null
  with_grace: number
}

// Props
const props = defineProps<{
  cooperative: CoopProgram        // This is the coop_program
  checklistItems: ChecklistItem[]
}>()


// All uploads must be completed before finalizing loan
const allUploadsDone = computed(() =>
  props.checklistItems.some(item => item.upload)
)

// Condition for showing Generate Amortization button
const canGenerateAmortization = computed(() =>
  allUploadsDone.value &&
  typeof props.cooperative.loan_amount === 'number' &&
  (props.cooperative.loan_amount ?? 0) > 0
)

// Disable upload if previous checklist not uploaded
const isDisabled = (index: number) =>
  index > 0 && !props.checklistItems[index - 1].upload

// Forms
const forms = props.checklistItems.map(item =>
  useForm({
    file: null as File | null,
    program_checklist_id: item.id,
  })
)

const loanForm = useForm<LoanFormData>({
  loan_amount: props.cooperative.loan_amount || null,
  with_grace: props.cooperative.with_grace || 0,
})

const amortForm = useForm({})

// Submit loan
function submitLoan() {
  if (!props.cooperative.program) return
  loanForm.post(
    `/programs/${props.cooperative.program.id}/cooperatives/${props.cooperative.cooperative.id}/finalize-loan`,
    { onSuccess: () => router.reload() }
  )
}

// Generate amortization (NEW ROUTE uses coopProgram.id only)
function generateAmortization() {
  amortForm.post(
    `/cooperative-programs/${props.cooperative.id}/generate-amortization`,
    {
      onSuccess: () => router.visit(`/amortizations/${props.cooperative.id}`)
    }
  )
}


// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Programs', href: '/programs' },
  { title: props.cooperative.program?.name || 'N/A', href: `/programs/${props.cooperative.program?.id}` },
  { title: 'Checklist', href: '#' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs" :key="props.cooperative.id">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div>
        <h2 class="text-2xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
          Checklist for {{ props.cooperative.cooperative.name }}
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
          Program: {{ props.cooperative.program?.name || 'N/A' }}
        </p>
      </div>

      <!-- Loan Section -->
      <div v-if="props.cooperative.program"
        class="border rounded-2xl shadow-sm p-6 bg-gray-200 dark:bg-[#0f172a] border-gray-300 dark:border-[#1e293b]">
        <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">
          Finalize Loan
        </h3>

        <!-- Already finalized -->
        <div v-if="typeof props.cooperative.loan_amount === 'number'">
          <p class="text-gray-800 dark:text-gray-200">
            <strong>Loan Amount:</strong>
            ₱{{ props.cooperative.loan_amount.toLocaleString() }}
          </p>
          <p class="text-gray-800 dark:text-gray-200 mt-2">
            <strong>Grace Period:</strong>
            {{ props.cooperative.with_grace === 0
              ? 'No Grace Period'
              : props.cooperative.with_grace + '-Month Grace Period' }}
          </p>
        </div>

        <!-- Loan form -->
        <form v-else-if="allUploadsDone" @submit.prevent="submitLoan" class="space-y-4">
          <!-- Loan Amount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Loan Amount
            </label>
            <input type="number" v-model="loanForm.loan_amount" :min="props.cooperative.program?.min_amount"
              :max="props.cooperative.program?.max_amount" step="0.01" required
              class="w-full border rounded-lg p-2 bg-gray-50 dark:bg-[#1e293b] text-gray-900 dark:text-gray-100"
              placeholder="Enter loan amount" />

            <div v-if="loanForm.errors.loan_amount" class="text-red-600 text-sm mt-1">
              {{ loanForm.errors.loan_amount }}
            </div>

            <div class="flex gap-2 mt-2">
              <button type="button" @click="loanForm.loan_amount = props.cooperative.program?.min_amount || 0"
                class="bg-gray-100 dark:bg-[#334155] border border-gray-300 dark:border-[#475569]
                           text-gray-800 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-[#3b445c] px-3 py-1 rounded">
                Min: ₱{{ props.cooperative.program?.min_amount || 0 }}
              </button>
              <button type="button" @click="loanForm.loan_amount = props.cooperative.program?.max_amount || 0"
                class="bg-gray-100 dark:bg-[#334155] border border-gray-300 dark:border-[#475569]
                           text-gray-800 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-[#3b445c] px-3 py-1 rounded">
                Max: ₱{{ props.cooperative.program?.max_amount || 0 }}
              </button>
            </div>
          </div>

          <!-- Grace Period -->
          <div class="mt-4">
            <span class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">
              Grace Period
            </span>
            <div class="flex gap-4">
              <label class="flex-1 cursor-pointer">
                <div :class="['p-3 rounded-lg border transition',
                  loanForm.with_grace === 0
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                    : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800']">
                  <input type="radio" v-model="loanForm.with_grace" :value="0" class="hidden" />
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">No Grace Period</p>
                </div>
              </label>

              <label class="flex-1 cursor-pointer">
                <div :class="['p-3 rounded-lg border transition',
                  loanForm.with_grace === 4
                    ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                    : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800']">
                  <input type="radio" v-model="loanForm.with_grace" :value="4" class="hidden" />
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">4-Month Grace Period</p>
                </div>
              </label>
            </div>
          </div>

          <div class="flex justify-end mt-4">
            <AlertDialog>
              <AlertDialogTrigger as-child>
                <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow-md"
                  :disabled="loanForm.processing">
                  Finalize Loan
                </button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Finalize Loan?</AlertDialogTitle>
                  <AlertDialogDescription>
                    You are about to finalize the loan for
                    <strong>{{ props.cooperative.cooperative.name }}</strong>.<br />
                    Loan Amount: ₱{{ loanForm.loan_amount?.toLocaleString() }} <br />
                    Grace Period:
                    {{ loanForm.with_grace === 0 ? 'No Grace Period' : loanForm.with_grace + '-Month Grace Period' }}
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                  <AlertDialogAction @click="submitLoan">
                    Confirm Finalize
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        </form>

        <div v-else class="bg-yellow-50 border border-yellow-200 p-4 rounded text-yellow-800">
          Please upload all required documents before finalizing the loan.
        </div>
      </div>

      <!-- Checklist Items -->
      <div v-for="(item, index) in props.checklistItems" :key="item.id"
        class="border rounded-2xl shadow-sm p-6 bg-gray-200 dark:bg-[#0f172a] border-gray-300 dark:border-[#1e293b]">
        <h5 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">
          {{ item.name }}
        </h5>

        <!-- Already uploaded -->
        <div v-if="item.upload" class="mb-3 flex items-center justify-between">
          <p class="text-sm text-gray-800 dark:text-gray-300">
            Uploaded File: <strong>{{ item.upload.file_name }}</strong>
          </p>
          <div class="flex gap-4">
            <a :href="`/programs/${props.cooperative.program?.id}/cooperatives/${props.cooperative.cooperative.id}/checklist/${item.upload.id}/download`"
              class="text-blue-600 dark:text-blue-400 hover:underline">
              Download
            </a>
            <AlertDialog>
              <AlertDialogTrigger as-child>
                <button class="text-red-600 dark:text-red-400 hover:underline">
                  Delete
                </button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>Delete Upload?</AlertDialogTitle>
                  <AlertDialogDescription>
                    This will permanently remove <strong>{{ item.upload.file_name }}</strong>.
                    This action cannot be undone.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>Cancel</AlertDialogCancel>
                  <AlertDialogAction @click="router.delete(
                    `/programs/${props.cooperative.program?.id}/cooperatives/${props.cooperative.cooperative.id}/checklist/${item.upload.id}`,
                    { onSuccess: () => router.reload({ only: ['checklistItems'] }) }
                  )">
                    Confirm Delete
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        </div>

        <!-- Upload form -->
        <form @submit.prevent="forms[index].post(
          `/programs/${props.cooperative.program?.id}/cooperatives/${props.cooperative.cooperative.id}/checklist/upload`,
          {
            forceFormData: true,
            onSuccess: () => { forms[index].reset(); router.reload({ only: ['checklistItems'] }) }
          }
        )" class="flex items-center gap-4">
          <input type="file" name="file" class="border p-2 rounded-lg w-full bg-gray-50 dark:bg-[#1e293b] border-gray-300 dark:border-[#334155]
                   text-gray-900 dark:text-gray-100 disabled:bg-gray-200 disabled:text-gray-400
                   dark:disabled:bg-[#0f172a] dark:disabled:text-gray-500 disabled:cursor-not-allowed"
            @change="forms[index].file = ($event.target as HTMLInputElement).files?.[0] || null"
            :disabled="isDisabled(index)" :title="isDisabled(index) ? 'Upload previous document first' : ''" required />

          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded disabled:bg-gray-400"
            :disabled="forms[index].processing || !forms[index].file">
            {{ item.upload ? 'Replace' : 'Upload' }}
          </button>
        </form>
      </div>

      <!-- Save Progress / Generate Amortization -->
      <div class="flex justify-end mt-6">
        <button v-if="canGenerateAmortization" @click="generateAmortization"
          class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg shadow-md"
          :disabled="amortForm.processing">
          Generate Amortization
        </button>

        <button v-else type="button" @click="router.visit(`/programs/${props.cooperative.program?.id}`)"
          class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg shadow-md">
          Save Progress
        </button>
      </div>
    </div>
  </AppLayout>
</template>
