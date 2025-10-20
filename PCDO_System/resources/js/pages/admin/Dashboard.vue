<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '/admin' },
];


// Props from controller
const {
    users,
    recentLogs,
    totalUsers
} = defineProps<{
    users: Array<{
        id: number
        name: string
        email: string
        role: string | null
        created_at: string
    }>
    recentLogs: Array<{
        id: number
        table_name: string
        action: string
        record_id: string | null
        created_at: string
    }>
    totalUsers: number
}>();

// New user form state
const name = ref('');
const email = ref('');
const role = ref<'admin' | 'officer' | 'user'>('user');
const password = ref('');
const creating = ref(false);

// Simple client-side validation errors display
const errors = ref<Record<string, string>>({});

function resetForm() {
    name.value = '';
    email.value = '';
    role.value = 'user';
    password.value = '';
    errors.value = {};
}

async function createUser() {
    creating.value = true;
    errors.value = {};

    // minimal client-side checks (server will validate)
    if (!name.value) errors.value.name = 'Name is required';
    if (!email.value) errors.value.email = 'Email is required';
    if (!password.value || password.value.length < 6) errors.value.password = 'Password must be at least 6 characters';

    if (Object.keys(errors.value).length > 0) {
        creating.value = false;
        return;
    }

    router.post('/admin/users', {
        name: name.value,
        email: email.value,
        role: role.value,
        password: password.value,
    }, {
        onFinish: () => creating.value = false,
        onSuccess: () => {
            resetForm();
        },
        onError: (serverErrors: any) => {
            // Map server validation errors to local errors for display
            errors.value = serverErrors || {};
        }
    });
}

function formatDate(dt: string) {
    return new Date(dt).toLocaleString();
}
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
            <div class="grid gap-4 md:grid-cols-4 mt-6 px-4 pb-6">

                <!-- Total Users -->
                <div class="col-span-4 md:col-span-1 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Total Users</h3>
                    <p class="text-2xl font-extrabold text-blue-600 mt-3">{{ totalUsers }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">All registered users</p>
                </div>

                <!-- Create User Card -->
                <div class="col-span-4 md:col-span-1 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Create User</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Quick add</p>
                    </div>

                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">Name</label>
                            <input v-model="name" type="text" class="mt-1 block w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
                            <p v-if="errors.name" class="text-xs text-red-500 mt-1">{{ errors.name }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">Email</label>
                            <input v-model="email" type="email" class="mt-1 block w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
                            <p v-if="errors.email" class="text-xs text-red-500 mt-1">{{ errors.email }}</p>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">Role</label>
                            <select v-model="role" class="mt-1 block w-full rounded-lg p-2 bg-white dark:bg-gray-700 border">
                                <option value="user">User</option>
                                <option value="officer">Officer</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600 dark:text-gray-300">Password</label>
                            <input v-model="password" type="password" class="mt-1 block w-full rounded-lg p-2 bg-white dark:bg-gray-700 border" />
                            <p v-if="errors.password" class="text-xs text-red-500 mt-1">{{ errors.password }}</p>
                        </div>

                        <div class="flex justify-end">
                            <button @click="createUser"
                                    class="px-4 py-2 rounded-lg font-medium bg-blue-600 text-white shadow-md hover:opacity-95"
                                    :disabled="creating">
                                <span v-if="!creating">Create</span>
                                <span v-else>Creating...</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="col-span-4 md:col-span-2 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6 overflow-x-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">All Users</h2>
                        <a href="/admin/users" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Manage users</a>
                    </div>

                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-500 dark:text-gray-300">
                                <th class="py-2">Name</th>
                                <th class="py-2">Email</th>
                                <th class="py-2">Role</th>
                                <th class="py-2">Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-t border-gray-200 dark:border-gray-700">
                                <td class="py-3">{{ user.name }}</td>
                                <td class="py-3">{{ user.email }}</td>
                                <td class="py-3">{{ user.role ?? 'user' }}</td>
                                <td class="py-3">{{ formatDate(user.created_at) }}</td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="4" class="py-6 text-center text-gray-500">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Sync Logs -->
                <div class="col-span-4 bg-gray-50 dark:bg-gray-800 rounded-2xl shadow-lg p-6 md:col-span-4">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Recent Sync Logs</h2>
                        <a href="/sync/status" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">See Sync Status</a>
                    </div>

                    <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                        <li v-for="log in recentLogs" :key="log.id" class="py-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ log.table_name }} — <span class="text-xs text-gray-500 dark:text-gray-400">{{ log.action }}</span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Record ID: {{ log.record_id ?? '-' }}</p>
                                </div>
                                <div class="text-xs text-gray-400">{{ formatDate(log.created_at) }}</div>
                            </div>
                        </li>
                        <li v-if="recentLogs.length === 0" class="py-4 text-center text-gray-500">No logs yet.</li>
                    </ul>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
