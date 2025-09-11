<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import notifications from '@/routes/notifications';

// Example placeholder data (replace with props/data later)
const sampleNotifications = [
  {
    id: 1,
    title: "Payment Reminder",
    message: "Your loan installment for Sept 15 is due tomorrow.",
    type: "reminder",
    date: "2025-09-10",
    read: false,
  },
  {
    id: 2,
    title: "Loan Approved",
    message: "Your cooperative loan application has been approved.",
    type: "success",
    date: "2025-09-01",
    read: true,
  },
];

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Notifications',
    href: notifications.index().url,
  },
];
</script>

<template>
  <Head title="Notifications" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div>
        <p class="text-gray-600 dark:text-gray-400">
          All Notifications
        </p>
      </div>

      <!-- Notification List -->
      <div class="border rounded-2xl shadow-sm bg-gray-50 dark:bg-[#0f172a] 
                  border-gray-200 dark:border-[#1e293b] divide-y 
                  divide-gray-200 dark:divide-[#334155]">
        
        <div v-for="notification in sampleNotifications" :key="notification.id"
          class="p-4 flex items-start gap-4 hover:bg-gray-100 dark:hover:bg-[#1e293b] transition">
          
          <!-- Status Dot -->
          <div class="mt-2">
            <span
              class="inline-block h-3 w-3 rounded-full"
              :class="notification.read 
                ? 'bg-gray-400' 
                : 'bg-blue-500 animate-pulse'"
            ></span>
          </div>

          <!-- Content -->
          <div class="flex-1">
            <h4 class="font-medium text-gray-900 dark:text-gray-100">
              {{ notification.title }}
            </h4>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              {{ notification.message }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
              {{ notification.date }}
            </p>
          </div>

          <!-- Type Badge -->
          <span
            class="px-2 py-1 text-xs rounded-lg font-semibold self-start"
            :class="{
              'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100': notification.type === 'reminder',
              'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100': notification.type === 'success',
              'bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100': notification.type === 'warning',
              'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-100': notification.type === 'error',
            }"
          >
            {{ notification.type }}
          </span>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
