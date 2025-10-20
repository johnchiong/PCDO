<script setup lang="ts">
import AppLayout from '@/layouts/AdminLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Admin', href: '/admin' }]

const props = defineProps<{
  users?: {
    data: Array<{
      id: number
      name: string
      email: string
      roles: string[]
      created_at: string
    }>
  }
  recentLogs?: {
    data: Array<{
      id: number
      table_name: string
      operation: string
      user_id: string
      record_id: string | null
      created_at: string
      changes?: string | null
    }>
  }
  roles?: Array<{ id: number; name: string }>
  filters?: { search?: string }
}>()

const users = ref(props.users ?? { data: [] })
const recentLogs = ref(props.recentLogs ?? { data: [] })
const roles = ref(props.roles ?? [])
const search = ref(props.filters?.search ?? '')

const filteredUsers = computed(() => {
  if (!search.value.trim()) return users.value.data
  const term = search.value.toLowerCase()
  return users.value.data.filter(
    (u) =>
      u.name.toLowerCase().includes(term) ||
      u.email.toLowerCase().includes(term) ||
      u.roles.some((r) => r.toLowerCase().includes(term))
  )
})

watch(search, () => {
  router.visit('/admin', {
    method: 'get',
    data: { search: search.value },
    preserveScroll: true,
    preserveState: true,
    replace: true,
  })
})

const name = ref('')
const email = ref('')
const role = ref<string>(roles.value?.[0]?.name ?? 'user')
const password = ref('')
const creating = ref(false)
const errors = ref<Record<string, string>>({})

function refreshPage() {
  router.visit('/admin', {
    method: 'get',
    data: { search: search.value },
    preserveScroll: true,
    preserveState: false,
  })
}

function resetForm() {
  name.value = ''
  email.value = ''
  role.value = roles.value?.[0]?.name ?? 'user'
  password.value = ''
  errors.value = {}
}

function createUser() {
  creating.value = true
  errors.value = {}
  if (!name.value) errors.value.name = 'Name is required'
  if (!email.value) errors.value.email = 'Email is required'
  if (!password.value || password.value.length < 6)
    errors.value.password = 'Password must be at least 6 characters'
  if (Object.keys(errors.value).length > 0) {
    creating.value = false
    return
  }
  router.post(
    '/admin/users',
    { name: name.value, email: email.value, role: role.value, password: password.value },
    {
      preserveScroll: true,
      onFinish: () => (creating.value = false),
      onSuccess: () => {
        resetForm()
        refreshPage()
      },
      onError: (serverErrors: any) => (errors.value = serverErrors || {}),
    }
  )
}

function deleteUser(id: number) {
  if (!confirm('Are you sure you want to delete this user?')) return
  router.delete(`/admin/users/${id}`, {
    preserveScroll: true,
    preserveState: false,
    onSuccess: () => refreshPage(),
  })
}

function formatDate(dt?: string) {
  if (!dt) return '-'
  return new Date(dt).toLocaleString()
}

function formatChanges(changes?: string | null) {
  if (!changes) return '-'
  try {
    const data = JSON.parse(changes)
    return Object.entries(data)
      .map(([key, value]) => `${key}: ${JSON.stringify(value)}`)
      .join(', ')
  } catch {
    return changes
  }
}
</script>

<template>
  <Head title="Admin Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen px-4 py-6">
      <div class="grid gap-4 md:grid-cols-3">
        <div class="col-span-3 md:col-span-1 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">Create User</h3>
          <div class="space-y-3">
            <input v-model="name" placeholder="Name" type="text" class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
            <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
            <input v-model="email" placeholder="Email" type="email" class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
            <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
            <select v-model="role" class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border">
              <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
            </select>
            <input v-model="password" placeholder="Password" type="password" class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
            <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
            <button
              @click="createUser"
              class="w-full sm:w-auto px-4 py-2 rounded-lg font-medium bg-blue-600 text-white shadow-md hover:opacity-95"
              :disabled="creating"
            >
              <span v-if="!creating">Create</span>
              <span v-else>Creating...</span>
            </button>
          </div>
        </div>

        <div class="col-span-3 md:col-span-2 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6 overflow-x-auto">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">All Users</h2>
            <input
              v-model="search"
              placeholder="Search users..."
              class="rounded-lg px-3 py-2 bg-white dark:bg-gray-700 border w-full sm:w-64"
            />
          </div>

          <table class="hidden sm:table min-w-full text-sm">
            <thead>
              <tr class="text-left text-gray-500 dark:text-gray-300">
                <th class="py-2">Name</th>
                <th class="py-2">Email</th>
                <th class="py-2">Roles</th>
                <th class="py-2">Created</th>
                <th class="py-2 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in filteredUsers" :key="user.id" class="border-b border-gray-200 dark:border-gray-700">
                <td class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ user.roles.join(', ') }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
                <td class="text-right">
                  <button @click="deleteUser(user.id)" class="text-red-500 hover:underline">Delete</button>
                </td>
              </tr>
              <tr v-if="filteredUsers.length === 0">
                <td colspan="5" class="py-6 text-center text-gray-500">No users found.</td>
              </tr>
            </tbody>
          </table>

          <div class="sm:hidden space-y-4">
            <div
              v-for="user in filteredUsers"
              :key="user.id"
              class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex justify-between items-start w-full"
            >
              <div class="w-[85%] break-words">
                <p class="font-semibold text-gray-800 dark:text-gray-100 text-base">{{ user.name }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Roles: {{ user.roles.join(', ') || '-' }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ formatDate(user.created_at) }}</p>
              </div>
              <button @click="deleteUser(user.id)" class="text-red-500 text-sm font-medium self-center">Delete</button>
            </div>
            <div v-if="filteredUsers.length === 0" class="py-4 text-center text-gray-500">No users found.</div>
          </div>
        </div>

        <div class="col-span-3 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6 overflow-hidden">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Recent Sync Logs</h2>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                <li v-for="log in recentLogs.data ?? []" :key="log.id" class="py-3">
                <div class="flex justify-between items-start">
                    <div class="w-[85%] break-words">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                        Table {{ log.table_name }} — <span class="text-xs text-gray-500 dark:text-gray-400">{{ log.operation }}</span>
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Record ID: {{ log.record_id ?? '-' }}</p>
                    </div>
                    <div class="text-right">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">User {{ log.user_id }}</p>
                    <p class="text-xs text-gray-400 whitespace-nowrap">{{ formatDate(log.created_at) }}</p>
                    </div>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 whitespace-pre-line break-words">
                        Changes: {{ formatChanges(log.changes) }}
                    </p>
                </div>
                </li>
                <li v-if="(recentLogs.data ?? []).length === 0" class="py-4 text-center text-gray-500">No logs yet.</li>
            </ul>
            </div>
      </div>
    </div>
  </AppLayout>
</template>
