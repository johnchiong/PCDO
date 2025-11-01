<script setup lang="ts">
import AppLayout from '@/layouts/AdminLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Admin', href: '/admin' }]

const props = defineProps<{
    users?: {
        data: Array<{
            id: number
            name: string
            email: string
            roles: { id: number; name: string }[]
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
            executed_at: string
            changes?: string | null
        }>
        current_page?: number
        last_page?: number
        next_page_url?: string | null
        prev_page_url?: string | null
        links?: Array<{ url: string | null; label: string; active: boolean }>
    }
    roles?: Array<{ id: number; name: string }>
    filters?: { search?: string }
}>()

const users = ref(props.users ?? { data: [] })
const recentLogs = ref(props.recentLogs ?? { data: [] })

const page = usePage()
const currentUser = page.props.auth?.user
const currentRole = currentUser?.roles?.[0]?.name ?? ''

const showPageSelector = ref(false)
const pageSelectorList = ref<number[]>([])

const roles = ref(
    (props.roles ?? []).filter((r) => {
        if (currentRole === 'superadmin') return ['admin', 'officer'].includes(r.name)
        if (currentRole === 'admin') return ['officer'].includes(r.name)
        return false
    })
)

const search = ref(props.filters?.search ?? '')

const filteredUsers = computed(() => {
    if (!search.value.trim()) return users.value.data
    const term = search.value.toLowerCase()
    return users.value.data.filter(
        (u) =>
            u.name.toLowerCase().includes(term) ||
            u.email.toLowerCase().includes(term) ||
            u.roles.some((r) => r.name.toLowerCase().includes(term))
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
const role = ref<string>(roles.value?.[0]?.name ?? '')
const password = ref('')
const creating = ref(false)
const errors = ref<Record<string, string>>({})

function refreshPage(url?: string) {
    router.visit(url || '/admin', {
        method: 'get',
        data: { search: search.value },
        preserveScroll: true,
        preserveState: false,
    })
}

function resetForm() {
    name.value = ''
    email.value = ''
    role.value = roles.value?.[0]?.name ?? ''
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
    if (!role.value) errors.value.role = 'Role is required'
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

function formatChanges(changes?: string | null): { key: string; value: string }[] {
    if (!changes) return []
    try {
        const data = JSON.parse(changes)
        if (typeof data === 'object' && data !== null) {
            return Object.entries(data).map(([key, value]) => {
                let displayValue: string

                if (value === null) displayValue = 'null'
                else if (Array.isArray(value) || typeof value === 'object') {
                    displayValue = JSON.stringify(value, null, 0)
                } else {
                    displayValue = String(value)
                }

                if (key === 'file_content' && displayValue.length > 100) {
                    displayValue = displayValue.slice(0, 100) + '... [truncated]'
                }

                return { key, value: displayValue }
            })
        }

        return [{ key: 'value', value: JSON.stringify(data) }]
    } catch {
        return [{ key: 'raw', value: changes.slice(0, 500) }]
    }
}

function goToLogsPage(url: string) {
    if (!url) return
    router.visit(url, {
        method: 'get',
        preserveScroll: true,
        preserveState: false,
        replace: true,
    })
}

function openPageSelector(start: number, end: number) {
    pageSelectorList.value = Array.from({ length: end - start - 1 }, (_, i) => start + i + 1)
    showPageSelector.value = true
}

const isMobile = ref(window.innerWidth < 640)

window.addEventListener('resize', () => {
    isMobile.value = window.innerWidth < 640

})

const showValueModal = ref(false)
const valueModalData = ref<{ key: string; value: string }>({ key: '', value: '' })

function openValueModal(change: { key: string; value: string }) {
    valueModalData.value = change
    showValueModal.value = true
}

</script>

<template>

    <Head title="Admin Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen px-4 py-6">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="col-span-3 md:col-span-1 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-3">Create User</h3>
                    <div v-if="roles.length === 0" class="text-sm text-gray-500 dark:text-gray-400">
                        You are not allowed to create users.
                    </div>
                    <div v-else class="space-y-3">
                        <input v-model="name" placeholder="Name" type="text"
                            class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
                        <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
                        <input v-model="email" placeholder="Email" type="email"
                            class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
                        <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                        <select v-model="role" class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border">
                            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
                        </select>
                        <p v-if="errors.role" class="text-xs text-red-500 mt-1">{{ errors.role }}</p>
                        <input v-model="password" placeholder="Password" type="password"
                            class="w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
                        <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
                        <button @click="createUser"
                            class="w-full sm:w-auto px-4 py-2 rounded-lg font-medium bg-blue-600 text-white shadow-md hover:opacity-95"
                            :disabled="creating">
                            <span v-if="!creating">Create</span>
                            <span v-else>Creating...</span>
                        </button>
                    </div>
                </div>

                <div
                    class="col-span-3 md:col-span-2 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6 overflow-x-auto">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-2">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">All Users</h2>
                        <input v-model="search" placeholder="Search users..."
                            class="rounded-lg px-3 py-2 bg-white dark:bg-gray-700 border w-full sm:w-64" />
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
                            <tr v-for="user in filteredUsers" :key="user.id"
                                class="border-b border-gray-200 dark:border-gray-700">
                                <td class="font-medium text-gray-900 dark:text-gray-100">{{ user.name }}</td>
                                <td>{{ user.email }}</td>
                                <td>{{user.roles.map(r => r.name).join(', ')}}</td>
                                <td>{{ formatDate(user.created_at) }}</td>
                                <td class="text-right">
                                    <button @click="deleteUser(user.id)"
                                        class="text-red-500 hover:underline">Delete</button>
                                </td>
                            </tr>
                            <tr v-if="filteredUsers.length === 0">
                                <td colspan="5" class="py-6 text-center text-gray-500">No users found.</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="sm:hidden space-y-4">
                        <div v-for="user in filteredUsers" :key="user.id"
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 flex justify-between items-start w-full">
                            <div class="w-[85%] break-words">
                                <p class="font-semibold text-gray-800 dark:text-gray-100 text-base">{{ user.name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ user.email }}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Roles: {{user.roles.map(r =>
                                    r.name).join(', ') || '-'}}</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ formatDate(user.created_at)
                                }}</p>
                            </div>
                            <button @click="deleteUser(user.id)"
                                class="text-red-500 text-sm font-medium self-center">Delete</button>
                        </div>
                        <div v-if="filteredUsers.length === 0" class="py-4 text-center text-gray-500">No users found.
                        </div>
                    </div>
                </div>

                <div class="col-span-3 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6 overflow-hidden">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Recent Sync Logs</h2>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="log in recentLogs.data ?? []" :key="log.id" class="py-3">
                            <div
                                class="p-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                                <div class="space-y-1">
                                    <div class="flex justify-between items-start">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                            Table
                                        </p>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                            User {{ log.user_id }}
                                        </p>
                                    </div>

                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ log.table_name }}
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            — {{ log.operation }}
                                        </span>
                                    </p>

                                    <div class="flex justify-between items-start">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            Record ID: {{ log.record_id ?? '-' }}
                                        </p>
                                        <p class="text-xs text-gray-400 whitespace-nowrap">
                                            Executed At: {{ formatDate(log.executed_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-xs mt-1 flex flex-wrap items-center gap-1 !pl-0 !ml-0">
                                    <template v-if="formatChanges(log.changes).length > 0">
                                        <span v-for="change in formatChanges(log.changes)" :key="change.key"
                                            @click="openValueModal(change)"
                                            class="px-2 py-[2px] rounded-lg border select-none
                                            bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100
                                            border-gray-300 dark:border-gray-600
                                            hover:bg-blue-100 dark:hover:bg-blue-800
                                            transition cursor-pointer text-ellipsis overflow-hidden whitespace-nowrap max-w-[220px]"
                                            :title="`${change.key}: ${change.value}`">
                                            <span class="font-medium text-blue-700 dark:text-blue-400">{{ change.key
                                                }}</span>:
                                            <span>{{ change.value }}</span>
                                        </span>
                                    </template>
                                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                                </div>
                            </div>
                        </li>
                        <li v-if="(recentLogs.data ?? []).length === 0" class="py-4 text-center text-gray-500">No logs
                            yet.</li>
                    </ul>

                    <div v-if="(recentLogs?.last_page ?? 1) > 1"
                        class="flex items-center justify-center mt-4 flex-wrap gap-2">
                        <button v-if="recentLogs?.prev_page_url" @click="goToLogsPage(recentLogs.prev_page_url)"
                            class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90 flex items-center gap-1">
                            <span v-if="isMobile">←</span>
                            <span v-else>← Prev</span>
                        </button>

                        <template v-if="(recentLogs?.last_page ?? 1) <= 10">
                            <button v-for="pageNum in recentLogs?.last_page ?? 1" :key="pageNum"
                                @click="goToLogsPage(`/admin?logs_page=${pageNum}&search=${search}`)" :class="[
                                    'px-3 py-1 rounded-md border',
                                    (recentLogs?.current_page ?? 1) === pageNum
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90'
                                ]">
                                {{ pageNum }}
                            </button>
                        </template>

                        <template v-else>
                            <button v-for="pageNum in (isMobile ? [1] : [1, 2, 3])" :key="'start-' + pageNum"
                                @click="goToLogsPage(`/admin?logs_page=${pageNum}&search=${search}`)" :class="[
                                    'px-3 py-1 rounded-md border',
                                    (recentLogs?.current_page ?? 1) === pageNum
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90'
                                ]">
                                {{ pageNum }}
                            </button>

                            <button v-if="(recentLogs?.current_page ?? 1) > (isMobile ? 3 : 5)"
                                @click="openPageSelector(1, (recentLogs?.current_page ?? 1) - 1)"
                                class="px-3 py-1 rounded-md border bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90">
                                ...
                            </button>

                            <template
                                v-for="pageNum in [(recentLogs?.current_page ?? 1) - 1, (recentLogs?.current_page ?? 1), (recentLogs?.current_page ?? 1) + 1]">
                                <button
                                    v-if="pageNum > (isMobile ? 1 : 3) && pageNum < (recentLogs?.last_page ?? 1) - (isMobile ? 1 : 2)"
                                    :key="'mid-' + pageNum"
                                    @click="goToLogsPage(`/admin?logs_page=${pageNum}&search=${search}`)" :class="[
                                        'px-3 py-1 rounded-md border',
                                        (recentLogs?.current_page ?? 1) === pageNum
                                            ? 'bg-blue-600 text-white border-blue-600'
                                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90'
                                    ]">
                                    {{ pageNum }}
                                </button>
                            </template>

                            <button
                                v-if="(recentLogs?.current_page ?? 1) < (recentLogs?.last_page ?? 1) - (isMobile ? 2 : 4)"
                                @click="openPageSelector((recentLogs?.current_page ?? 1), (recentLogs?.last_page ?? 1))"
                                class="px-3 py-1 rounded-md border bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90">
                                ...
                            </button>

                            <button
                                v-for="pageNum in (isMobile
                                    ? [(recentLogs?.last_page ?? 1)]
                                    : [(recentLogs?.last_page ?? 1) - 2, (recentLogs?.last_page ?? 1) - 1, (recentLogs?.last_page ?? 1)])"
                                :key="'end-' + pageNum"
                                @click="goToLogsPage(`/admin?logs_page=${pageNum}&search=${search}`)" :class="[
                                    'px-3 py-1 rounded-md border',
                                    (recentLogs?.current_page ?? 1) === pageNum
                                        ? 'bg-blue-600 text-white border-blue-600'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90'
                                ]">
                                {{ pageNum }}
                            </button>
                        </template>

                        <button v-if="recentLogs?.next_page_url" @click="goToLogsPage(recentLogs.next_page_url)"
                            class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:opacity-90 flex items-center gap-1">
                            <span v-if="isMobile">→</span>
                            <span v-else>Next →</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Selector Modal -->
        <teleport to="body">
            <div v-if="showPageSelector" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[999]"
                @click.self="showPageSelector = false">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-xl max-h-[70vh] w-[90%] sm:w-64 overflow-y-auto">
                    <h3 class="text-lg font-semibold mb-2 text-center text-gray-800 dark:text-gray-100">
                        Jump to page
                    </h3>

                    <div class="grid grid-cols-4 gap-2">
                        <button v-for="pageNum in pageSelectorList" :key="'select-' + pageNum"
                            @click="goToLogsPage(`/admin?logs_page=${pageNum}&search=${search}`); showPageSelector = false"
                            class="px-2 py-1 rounded-md border text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-blue-600 hover:text-white transition">
                            {{ pageNum }}
                        </button>
                    </div>

                    <div class="mt-3 text-center">
                        <button @click="showPageSelector = false"
                            class="mt-2 text-sm text-gray-500 hover:text-gray-800 dark:hover:text-gray-300">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
        <!-- Value Viewer Modal -->
        <teleport to="body">
            <div v-if="showValueModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[1000]"
                @click.self="showValueModal = false">
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-xl w-[90%] sm:w-[400px] max-w-[95%] max-h-[80vh] overflow-auto">
                    <h3 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-100">
                        {{ valueModalData.key }}
                    </h3>
                    <div
                        class="text-sm text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-900 rounded-lg p-2 whitespace-pre-wrap break-words font-mono">
                        {{ valueModalData.value }}
                    </div>
                    <div class="mt-3 text-center">
                        <button @click="showValueModal = false"
                            class="px-4 py-1 rounded-md bg-blue-600 text-white hover:opacity-90">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </AppLayout>
</template>
