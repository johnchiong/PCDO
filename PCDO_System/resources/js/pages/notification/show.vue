<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  notification: {
    id: number;
    subject: string;
    body: string;
    type: string;
    created_at: string;
    schedule?: {
      coop_program?: {
        email?: string;
        cooperative?: {
          name: string;
        };
      };
    };
  };
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Notifications', href: '/notifications' },
  { title: props.notification.subject, href: `/notifications/${props.notification.id}` },
]

// Utility function to detect due status from body
const getDueStatus = (body: string) => {
  if (body.toLowerCase().includes("overdue")) return "overdue"
  if (body.toLowerCase().includes("due today")) return "dueToday"
  if (body.toLowerCase().includes("before due")) return "beforeDue"
  return null
}
</script>

<template>
  <Head :title="notification.subject" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full mx-auto p-6 sm:p-10 space-y-6">
      <!-- Card -->
      <div
        class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">

        <!-- Header -->
        <div
          class="flex items-center justify-between px-14 py-6 border-b border-gray-200 dark:border-gray-700 
                 bg-gradient-to-r from-indigo-500/10 to-purple-500/10 dark:from-indigo-900/30 dark:to-purple-900/30">
          <div class="flex items-center space-x-4">
            <div
              class="p-3 rounded-full bg-indigo-100 text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300 text-xl">
              💳
            </div>
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100">
              {{ notification.subject }}
            </h2>
          </div>
          <span
            class="px-4 py-1.5 rounded-full text-sm font-semibold uppercase tracking-wide"
            :class="{
              'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': notification.type === 'info',
              'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300': notification.type === 'success',
              'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300': notification.type === 'warning',
              'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300': notification.type === 'error',
            }"
          >
            {{ notification.type }}
          </span>
        </div>

        <!-- Meta Info -->
        <div class="px-14 py-5 text-sm text-gray-500 dark:text-gray-400 space-y-2">
          <p>📅 {{ new Date(notification.created_at).toLocaleString() }}</p>
          <p v-if="notification.schedule?.coop_program?.cooperative">
            🏢 Cooperative:
            <span class="font-medium text-gray-800 dark:text-gray-200">
              {{ notification.schedule.coop_program.cooperative.name }}
            </span>
          </p>
          <p v-if="notification.schedule?.coop_program?.email">
            ✉️ Send to:
            <span class="font-medium text-indigo-600 dark:text-indigo-300">
              {{ notification.schedule.coop_program.email }}
            </span>
          </p>
        </div>

        <!-- Status Highlight -->
        <div
          v-if="getDueStatus(notification.body)"
          class="px-14 py-6 text-center text-xl font-bold"
          :class="{
            'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300': getDueStatus(notification.body) === 'overdue',
            'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300': getDueStatus(notification.body) === 'dueToday',
            'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300': getDueStatus(notification.body) === 'beforeDue',
          }"
        >
          {{ getDueStatus(notification.body) === 'overdue' ? '⚠️ Overdue Payment' :
             getDueStatus(notification.body) === 'dueToday' ? '📌 Payment Due Today' :
             '✅ Upcoming Payment' }}
        </div>

        <!-- Letter Body -->
        <div class="px-14 py-10 text-gray-700 dark:text-gray-300 leading-relaxed space-y-6 text-lg">

          <div class="space-y-3">
            <p
              v-for="line in notification.body.split('\n')"
              :key="line"
              class="text-lg"
              :class="{
                'text-green-600 dark:text-green-400 font-semibold': line.toLowerCase().includes('amount to pay') && !line.toLowerCase().includes('penalty'),
                'text-red-600 dark:text-red-400 font-semibold': line.toLowerCase().includes('penalty')
              }"
            >
              {{ line }}
            </p>
          </div>

          <p>Thank you,<br />PCDO Management</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
