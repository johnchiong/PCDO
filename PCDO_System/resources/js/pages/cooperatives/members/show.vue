<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import type { Member } from '@/types/cooperatives'
import { router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Button from '@/components/ui/button/Button.vue'

const page = usePage()
const flash = computed(() => page.props.flash as { success?: string; error?: string; info?: string })
const showFileModal = ref(false)
const selectedFile = ref<any | null>(null)

const props = defineProps<{
  breadcrumbs?: BreadcrumbItem[]
  cooperative: { id: string }
  member: Member
}>()

const fullName = computed(() =>
  [props.member.first_name, props.member.middle_name, props.member.last_name].filter(Boolean).join(' ')
)

function display(value: any) {
  if (value === null || value === undefined || value === '' || value === 'null') return 'N/A'
  return value
}

function goToEditPage(id: number) {
  router.visit(`/cooperatives/${props.cooperative.id}/members/${id}/edit`)
}

function openFileModal(file: any) {
  selectedFile.value = file
  showFileModal.value = true
}

function closeFileModal() {
  selectedFile.value = null
  showFileModal.value = false
}

function downloadFile(file: any) {
  window.open(`/cooperatives/${props.cooperative.id}/members/${props.member.id}/files/${file.id}/download`, '_blank')
  closeFileModal()
}

function deleteFile(file: any) {
  if (!confirm(`Are you sure you want to delete "${file.file_name}"?`)) return
  router.delete(`/cooperatives/${props.cooperative.id}/members/${props.member.id}/files/${file.id}`, {
    preserveScroll: true,
    onSuccess: () => closeFileModal(),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
      <div class="max-w-7x7 p-6">
        <div
          class="bg-gray-50 dark:bg-gray-800/80 border ring-1 ring-gray-300 dark:ring-gray-700 rounded-xl px-6 py-5 mb-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-3">
              <Users class="w-10 h-10 text-orange-600 dark:text-orange-400" />
              Cooperative Member Details
            </h1>
            <div class="flex items-center gap-3">
              <span
                class="inline-flex gap-2 px-4 py-2 rounded-full text-sm font-medium bg-indigo-200/40 text-lime-700 dark:bg-lime-800 dark:text-fuchsia-200">
                ID: <span class="font-semibold">{{ member.id }}</span>
              </span>
              <button @click="goToEditPage(member.id)"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600">
                <SquarePen class="w-4 h-4" /> Edit
              </button>
              <a :href="`/cooperatives/${props.cooperative.id}/members/${member.id}/biodata/pdf`" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg
                bg-rose-600 text-white hover:bg-rose-700dark:bg-rose-500 dark:hover:bg-rose-60">
                <FileDown class="w-4 h-4" />
                Download Bio-Data
              </a>
            </div>
          </div>

          <h2 class="text-xl font-semibold mb-3 border-b border-gray-600 pb-1">Personal Information</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
            <div>
              <p class="label">Full Name</p>
              <p class="value">{{ display(fullName) }}</p>
            </div>
            <div>
              <p class="label">Position</p>
              <p class="value">{{ display(member.position) }}</p>
            </div>
            <div>
              <p class="label">Contact</p>
              <p class="value">{{ display(member.contact) }}</p>
            </div>
            <div>
              <p class="label">Email</p>
              <p class="value">{{ display(member.email) }}</p>
            </div>
            <div>
              <p class="label">Marital Status</p>
              <p class="value">{{ display(member.marital_status) }}</p>
            </div>
            <div>
              <p class="label">Street</p>
              <p class="value">{{ display(member.street) }}</p>
            </div>
            <div>
              <p class="label">City</p>
              <p class="value">{{ display(member.city) }}</p>
            </div>
            <div>
              <p class="label">Telephone</p>
              <p class="value">{{ display(member.telephone) }}</p>
            </div>
            <div>
              <p class="label">Birthdate</p>
              <p class="value">{{ display(member.birth_date) }}</p>
            </div>
            <div>
              <p class="label">Age</p>
              <p class="value">{{ display(member.age) }}</p>
            </div>
            <div>
              <p class="label">Sex</p>
              <p class="value">{{ display(member.sex) }}</p>
            </div>
            <div>
              <p class="label">Citizenship</p>
              <p class="value">{{ display(member.citizenship) }}</p>
            </div>
            <div>
              <p class="label">Birthplace</p>
              <p class="value">{{ display(member.birthplace) }}</p>
            </div>
            <div>
              <p class="label">Height</p>
              <p class="value">{{ display(member.height) }}</p>
            </div>
            <div>
              <p class="label">Weight</p>
              <p class="value">{{ display(member.weight) }}</p>
            </div>
            <div>
              <p class="label">Religion</p>
              <p class="value">{{ display(member.religion) }}</p>
            </div>
            <div>
              <p class="label">Spouse Name</p>
              <p class="value">{{ display(member.spouse_name) }}</p>
            </div>
            <div>
              <p class="label">Spouse Occupation</p>
              <p class="value">{{ display(member.spouse_occupation) }}</p>
            </div>
            <div>
              <p class="label">Spouse Age</p>
              <p class="value">{{ display(member.spouse_age) }}</p>
            </div>
            <div>
              <p class="label">Father's Name</p>
              <p class="value">{{ display(member.father_name) }}</p>
            </div>
            <div>
              <p class="label">Father's Occupation</p>
              <p class="value">{{ display(member.father_occupation) }}</p>
            </div>
            <div>
              <p class="label">Father's Age</p>
              <p class="value">{{ display(member.father_age) }}</p>
            </div>
            <div>
              <p class="label">Mother's Name</p>
              <p class="value">{{ display(member.mother_name) }}</p>
            </div>
            <div>
              <p class="label">Mother's Occupation</p>
              <p class="value">{{ display(member.mother_occupation) }}</p>
            </div>
            <div>
              <p class="label">Mother's Age</p>
              <p class="value">{{ display(member.mother_age) }}</p>
            </div>
            <div>
              <p class="label">Parent Address</p>
              <p class="value">{{ display(member.parent_address) }}</p>
            </div>
            <div>
              <p class="label">Emergency Contact</p>
              <p class="value">{{ display(member.emergency_name) }} - {{ display(member.emergency_contact) }}</p>
            </div>
            <div>
              <p class="label">Representative</p>
              <p class="value">{{ member.is_representative ? 'Yes' : 'No' }}</p>
            </div>
            <div>
              <p class="label">Active Year</p>
              <p class="value">{{ display(member.active_year) }}</p>
            </div>
          </div>

          <h2 class="text-xl font-semibold mt-10 mb-3 border-b border-gray-600 pb-1">Dependents</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-6">
            <div>
              <p class="label">Dependent 1</p>
              <p class="value">{{ display(member.dependent1_name) }}</p>
            </div>
            <div>
              <p class="label">Relationship</p>
              <p class="value">{{ display(member.dependent1_relationship) }}</p>
            </div>
            <div>
              <p class="label">Birthdate</p>
              <p class="value">{{ display(member.dependent1_birthdate) }}</p>
            </div>
            <div>
              <p class="label">Age</p>
              <p class="value">{{ display(member.dependent1_age) }}</p>
            </div>
            <div>
              <p class="label">Dependent 2</p>
              <p class="value">{{ display(member.dependent2_name) }}</p>
            </div>
            <div>
              <p class="label">Relationship</p>
              <p class="value">{{ display(member.dependent2_relationship) }}</p>
            </div>
            <div>
              <p class="label">Birthdate</p>
              <p class="value">{{ display(member.dependent2_birthdate) }}</p>
            </div>
            <div>
              <p class="label">Age</p>
              <p class="value">{{ display(member.dependent2_age) }}</p>
            </div>
          </div>

          <h2 class="text-xl font-semibold mt-10 mb-3 border-b border-gray-600 pb-1">Educational Background</h2>
          <div class="space-y-4">
            <div>
              <p class="label">Elementary</p>
              <p class="value">{{ display(member.elementary_name) }} ({{ display(member.elementary_start) }} - {{
                display(member.elementary_end) }})</p>
              <p class="value text-sm">{{ display(member.elementary_degree) }}</p>
            </div>
            <div>
              <p class="label">High School</p>
              <p class="value">{{ display(member.hs_name) }} ({{ display(member.hs_start) }} - {{ display(member.hs_end)
              }})</p>
              <p class="value text-sm">{{ display(member.hs_degree) }}</p>
            </div>
            <div>
              <p class="label">College</p>
              <p class="value">{{ display(member.college_name) }} ({{ display(member.college_start) }} - {{
                display(member.college_end) }})</p>
              <p class="value text-sm">{{ display(member.college_degree) }}</p>
            </div>
            <div>
              <p class="label">Vocational / Course</p>
              <p class="value">{{ display(member.course_name) }} ({{ display(member.course_start) }} - {{
                display(member.course_end) }})</p>
              <p class="value text-sm">{{ display(member.course_degree) }}</p>
            </div>
            <div>
              <p class="label">Others</p>
              <p class="value">{{ display(member.others_name) }} ({{ display(member.others_start) }} - {{
                display(member.others_end) }})</p>
              <p class="value text-sm">{{ display(member.others_degree) }}</p>
            </div>
          </div>

          <h3 class="font-semibold text-lg mt-10 mb-2">Employment History</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p>Company 1</p>
              <p>{{ display(member.company1_name) }}</p>
              <p>{{ display(member.company1_position) }}</p>
              <p>{{ display(member.company1_start) }} - {{ display(member.company1_end) }}</p>
              <p>{{ display(member.company1_rfl) }}</p>
            </div>
            <div>
              <p>Company 2</p>
              <p>{{ display(member.company2_name) }}</p>
              <p>{{ display(member.company2_position) }}</p>
              <p>{{ display(member.company2_start) }} - {{ display(member.company2_end) }}</p>
              <p>{{ display(member.company2_rfl) }}</p>
            </div>
            <div>
              <p>Company 3</p>
              <p>{{ display(member.company3_name) }}</p>
              <p>{{ display(member.company3_position) }}</p>
              <p>{{ display(member.company3_start) }} - {{ display(member.company3_end) }}</p>
              <p>{{ display(member.company3_rfl) }}</p>
            </div>
            <div>
              <p>Company 4</p>
              <p>{{ display(member.company4_name) }}</p>
              <p>{{ display(member.company4_position) }}</p>
              <p>{{ display(member.company4_start) }} - {{ display(member.company4_end) }}</p>
              <p>{{ display(member.company4_rfl) }}</p>
            </div>
            <div>
              <p>Company 5</p>
              <p>{{ display(member.company5_name) }}</p>
              <p>{{ display(member.company5_position) }}</p>
              <p>{{ display(member.company5_start) }} - {{ display(member.company5_end) }}</p>
              <p>{{ display(member.company5_rfl) }}</p>
            </div>
          </div>

          <h2 class="text-xl font-semibold mt-10 mb-3 border-b border-gray-600 pb-1">Character References</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="label">Reference 1</p>
              <p class="value">{{ display(member.ref1_name) }}</p>
              <p class="value text-sm">{{ display(member.ref1_company) }}</p>
              <p class="value text-sm">{{ display(member.ref1_position) }}</p>
              <p class="value text-sm">{{ display(member.ref1_contact) }}</p>
            </div>
            <div>
              <p class="label">Reference 2</p>
              <p class="value">{{ display(member.ref2_name) }}</p>
              <p class="value text-sm">{{ display(member.ref2_company) }}</p>
              <p class="value text-sm">{{ display(member.ref2_position) }}</p>
              <p class="value text-sm">{{ display(member.ref2_contact) }}</p>
            </div>
          </div>

          <h2 class="text-xl font-semibold mt-10 mb-3 border-b border-gray-600 pb-1">Uploaded Files</h2>
          <div>
            <ul v-if="member.files?.length" class="space-y-2">
              <li v-for="file in member.files" :key="file.id"
                class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm">
                <div>
                  <p class="font-medium">{{ file.file_name }}</p>
                  <p class="text-xs text-gray-500">{{ file.file_type }}</p>
                </div>
                <Button type="button" @click="openFileModal(file)"
                  class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg">
                  View
                </Button>
              </li>
            </ul>
            <p v-else class="text-sm text-gray-500 italic">No files uploaded</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
