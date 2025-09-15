<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import cooperative from '@/routes/cooperative'
import { type BreadcrumbItem } from '@/types'
import { ref, computed } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Cooperative', href: cooperative.index().url },
  { title: 'Documents', href: '' },
]

interface Cooperative {
  id: number
  name: string
  program: {
    name: string
    min_amount: number
    max_amount: number
    grace_period: number
    term_months: number
  }
  loan?: Loan | null
}

interface Loan {
  id: number
  amount: number
}

interface Upload {
  id: number
  file_name: string
  mime_type: string
}

interface ChecklistItem {
  id: number
  name: string
  upload?: Upload | null
}

const props = defineProps<{
  cooperative: Cooperative
  checklistItems: ChecklistItem[]
}>()

// Loan form
const loanForm = useForm({
  amount: props.cooperative?.loan?.amount || null,
  grace_period: 0 as number, // default: no grace
})

const setLoanMin = () => {
  loanForm.amount = props.cooperative?.program?.min_amount
}
const setLoanMax = () => {
  loanForm.amount = props.cooperative?.program?.max_amount
}

function submitLoan() {
  loanForm.post(`/cooperative/${props.cooperative.id}/loan`, {
    onSuccess: () => router.reload(),
  })
}

// Upload forms
const forms = ref(
  props.checklistItems.map((item) =>
    useForm({
      checklist_item_id: item.id,
      file: null as File | null,
    })
  )
)

function handleFileChange(event: Event, index: number) {
  const input = event.target as HTMLInputElement
  if (input?.files?.length) {
    forms.value[index].file = input.files[0]
  }
}

function submitFile(index: number) {
  forms.value[index].post(`/cooperative/${props.cooperative.id}/upload`, {
    forceFormData: true,
    onSuccess: () => {
      forms.value[index].reset()
      router.reload()
    },
  })
}

function deleteFile(uploadId: number) {
  router.delete(`/cooperative/uploads/${uploadId}`, {
    onSuccess: () => router.reload(),
  })
}

function finishUploads() {
  router.visit('/cooperative')
}

// some for one required doc, every for all docs
const allUploadsDone = computed(() =>
  props.checklistItems.some(item => item.upload)
)
</script>

<template>
  <Head :title="`Cooperative Documents - ${props.cooperative?.name ?? ''}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div>
        <h2 class="text-2xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
          Documents for Cooperative: {{ props.cooperative?.name }}
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
          Program: {{ props.cooperative?.program?.name }}
        </p>
      </div>

      <!-- Loan Amount Section -->
      <div class="border rounded-2xl shadow-sm p-6 
                  bg-gray-100 dark:bg-[#0f172a] 
                  border-gray-300 dark:border-[#1e293b]">
        <h4 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">
          Loan Amount
        </h4>

        <!-- If Loan Already Exists -->
        <div v-if="props.cooperative.loan" class="bg-gray-200 dark:bg-[#1e293b] p-4 rounded-lg 
                    border border-gray-300 dark:border-[#334155]">
          <p class="text-gray-900 dark:text-gray-100">
            Loan Amount: <strong>₱{{ props.cooperative.loan.amount.toFixed(2) }}</strong>
          </p>
          <p class="text-gray-600 text-sm dark:text-gray-400">
            This loan amount cannot be changed.
          </p>
        </div>

        <!-- If No Loan Exists -->
        <div v-else>
          <template v-if="allUploadsDone">
            <form @submit.prevent="submitLoan" class="space-y-6">
              <div>
                <label for="loanAmount" class="block text-sm font-medium mb-1 text-gray-900 dark:text-gray-100">
                  Enter Loan Amount
                </label>
                <input id="loanAmount" type="number" v-model="loanForm.amount"
                  :min="props.cooperative.program.min_amount" :max="props.cooperative.program.max_amount" step="0.01"
                  required class="border rounded-lg p-2 w-full mt-1
                         bg-gray-50 dark:bg-[#1e293b]
                         border-gray-300 dark:border-[#334155]
                         text-gray-900 dark:text-gray-100
                         focus:ring-2 focus:ring-blue-500 focus:outline-none" placeholder="Enter loan amount" />
              </div>

              <!-- Grace Period Radio Group -->
              <div>
                <span class="block text-sm font-medium mb-2 text-gray-900 dark:text-gray-100">
                  Grace Period
                </span>
                <div class="flex gap-4">
                  <label class="flex-1 cursor-pointer">
                    <div :class="[
                        'p-3 rounded-lg border transition',
                        loanForm.grace_period === 0
                          ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                          : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'
                      ]">
                      <input type="radio" v-model="loanForm.grace_period" :value="0" class="hidden" />
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100">No Grace Period</p>
                    </div>
                  </label>

                  <label class="flex-1 cursor-pointer">
                    <div :class="[
                        'p-3 rounded-lg border transition',
                        loanForm.grace_period === 4
                          ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30'
                          : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800'
                      ]">
                      <input type="radio" v-model="loanForm.grace_period" :value="4" class="hidden" />
                      <p class="text-sm font-medium text-gray-900 dark:text-gray-100">4-Month Grace Period</p>
                    </div>
                  </label>
                </div>
              </div>

              <!-- Buttons -->
              <div class="flex justify-between items-center gap-2">
                <div class="flex gap-2">
                  <Button type="button" @click="setLoanMin" class="bg-gray-200 dark:bg-[#334155] border border-gray-300 dark:border-[#475569]
                     text-gray-800 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-[#3b445c]">
                    Min: ₱{{ props.cooperative.program.min_amount }}
                  </Button>
                  <Button type="button" @click="setLoanMax" class="bg-gray-200 dark:bg-[#334155] border border-gray-300 dark:border-[#475569]
                     text-gray-800 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-[#3b445c]">
                    Max: ₱{{ props.cooperative.program.max_amount }}
                  </Button>
                </div>
                <Button type="submit" :disabled="loanForm.processing" class="bg-blue-600 text-white hover:bg-blue-700 disabled:bg-blue-900 disabled:text-gray-400">
                  Save Loan
                </Button>
              </div>

              <p v-if="loanForm.amount &&
                (loanForm.amount < props.cooperative.program.min_amount ||
                  loanForm.amount > props.cooperative.program.max_amount)" class="text-red-600 text-sm mt-2">
                Loan must be between ₱{{ props.cooperative.program.min_amount }} and ₱{{ props.cooperative.program.max_amount }}.
              </p>
            </form>
          </template>

          <template v-else>
            <div class="text-yellow-700 dark:text-yellow-400 
                        bg-yellow-100 dark:bg-[#1e293b] 
                        border border-yellow-300 dark:border-[#334155] 
                        p-3 rounded-lg text-sm">
              Please upload all required documents before entering the loan amount.
            </div>
          </template>
        </div>
      </div>

      <!-- Upload Checklist Items -->
      <div v-for="(item, index) in props.checklistItems" :key="item.id" class="border rounded-2xl shadow-sm p-6 
                  bg-gray-100 dark:bg-[#0f172a] 
                  border-gray-300 dark:border-[#1e293b]">
        <h5 class="font-semibold text-lg mb-3 text-gray-900 dark:text-gray-100">
          {{ item.name }}
        </h5>

        <!-- Already Uploaded -->
        <div v-if="item.upload" class="mb-3 flex items-center justify-between">
          <p class="text-sm text-gray-800 dark:text-gray-300">
            Uploaded File: <strong>{{ item.upload.file_name }}</strong>
          </p>
          <div class="flex gap-4">
            <a :href="`/cooperative/uploads/{{ item.upload.id }}/download`" class="text-blue-600 dark:text-blue-400 hover:underline">
              Download
            </a>
            <button type="button" @click="deleteFile(item.upload.id)" class="text-red-600 hover:underline">
              Delete
            </button>
          </div>
        </div>

        <!-- Upload Form -->
        <form @submit.prevent="submitFile(index)" class="flex items-center gap-4">
          <input type="hidden" name="checklist_item_id" :value="item.id" />
          <input type="file" name="file" class="border p-2 rounded-lg w-full
                   bg-gray-50 dark:bg-[#1e293b]
                   border-gray-300 dark:border-[#334155]
                   text-gray-900 dark:text-gray-100
                   disabled:bg-gray-200 disabled:text-gray-400
                   dark:disabled:bg-[#0f172a] dark:disabled:text-gray-500
                   disabled:cursor-not-allowed" @change="handleFileChange($event, index)" required
            :disabled="index > 0 && !props.checklistItems[index - 1].upload" :title="index > 0 && !props.checklistItems[index - 1].upload
              ? 'Upload the previous document first'
              : ''" />
          <Button type="submit" :disabled="forms[index].processing" class="bg-blue-600 text-white hover:bg-blue-700 disabled:bg-blue-900 disabled:text-gray-400">
            {{ item.upload ? 'Replace' : 'Upload' }}
          </Button>
        </form>
      </div>

      <!-- Save Button -->
      <div class="flex justify-end mt-6 p-6">
        <Button @click="finishUploads" class="bg-blue-600 text-white hover:bg-blue-700 disabled:bg-blue-900 disabled:text-gray-400 px-6 py-2 rounded-lg">
          Save
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
