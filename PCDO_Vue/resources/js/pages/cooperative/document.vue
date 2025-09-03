<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/components/ui/button/Button.vue'
import { Head, useForm, router, Link } from '@inertiajs/vue3'
import cooperative from '@/routes/cooperative'
import { type BreadcrumbItem } from '@/types'
import { ref } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Cooperative', href: cooperative.index().url },
  { title: 'Create Cooperative', href: cooperative.create().url },
  { title: 'Documents', href: '' },
]

interface Cooperative {
  id: number
  name: string
  program: { name: string }
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

// forms for each checklist item
const forms = ref(
  props.checklistItems.map((item) =>
    useForm({
      checklist_item_id: item.id,
      file: null as File | null,
    })
  )
)

// Handle file selection
function handleFileChange(event: Event, index: number) {
  const input = event.target as HTMLInputElement
  if (input?.files?.length) {
    forms.value[index].file = input.files[0]
  }
}

// Submit upload (new or replace)
function submitFile(index: number) {
  forms.value[index].post(`/cooperative/${props.cooperative.id}/upload`, {
    forceFormData: true,
    onSuccess: () => {
      forms.value[index].reset()
      router.reload() // reload so uploads refresh
    },
  })
}

// Delete upload
function deleteFile(uploadId: number) {
  router.delete(`/cooperative/uploads/${uploadId}`, {
    onSuccess: () => router.reload(),
  })
}

// Finish uploading and go back
function finishUploads() {
  router.visit('/cooperative')
}
</script>

<template>

  <Head :title="`Cooperative Documents - ${props.cooperative?.name ?? ''}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h2 class="text-2xl font-semibold mb-2">
        Documents for Cooperative: {{ props.cooperative?.name }}
      </h2>
      <p class="text-gray-600 dark:text-gray-300 mb-6">
        Program: {{ props.cooperative?.program?.name }}
      </p>

      <div v-for="(item, index) in props.checklistItems" :key="item.id" class="border rounded-lg p-4 mb-4">
        <h5 class="font-semibold text-lg mb-3">{{ item.name }}</h5>

        <!-- If upload exists -->
        <div v-if="item.upload" class="mb-3 flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Uploaded File: <strong>{{ item.upload.file_name }}</strong>
            </p>
          </div>
          <div class="flex gap-4">
            <a :href="`/cooperative/uploads/${item.upload.id}/download`" class="text-blue-600 hover:underline">
              Download
            </a>
            <button type="button" @click="deleteFile(item.upload.id)" class="text-red-600 hover:underline">
              Delete
            </button>
          </div>
        </div>

        <!-- Upload/Replace form -->
        <form @submit.prevent="submitFile(index)" class="flex items-center gap-4">
          <input type="hidden" name="checklist_item_id" :value="item.id" />
          <input type="file" name="file" class="border p-2 rounded w-full" @change="handleFileChange($event, index)"
            required />
          <Button type="submit" :disabled="forms[index].processing">
            {{ item.upload ? 'Replace' : 'Upload' }}
          </Button>

        </form>
      </div>
      <!-- Finish button -->
      <div class="flex justify-end mt-6 p-6">
        <Button @click="finishUploads">
          Save
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
