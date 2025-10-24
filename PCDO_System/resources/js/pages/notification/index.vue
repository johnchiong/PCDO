<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps<{
  notifications: Array<{
    id: number;
    subject: string;
    body: string;
    type: string;
    created_at: string;
    read?: boolean;
    schedule?: {
      coop_program?: {
        cooperative?: {
          name: string;
        };
      };
    };
  }>;
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Notifications', href: '/notifications' },
]

//  helper to truncate
function truncate(text: string, length: number = 80) {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

//  helper to format full date and time
function formatDateTime(dateStr: string) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

//  helper to format only date for grouping
function formatDateOnly(dateStr: string) {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

//  group notifications by date
const groupedNotifications = props.notifications.reduce((groups: Record<string, typeof props.notifications>, notification) => {
  const date = formatDateOnly(notification.created_at)
  if (!groups[date]) groups[date] = []
  groups[date].push(notification)
  return groups
}, {})

// sort dates descending
const sortedDates = Object.keys(groupedNotifications).sort((a, b) => new Date(b).getTime() - new Date(a).getTime())
</script>

<template>
  <Head title="Notifications" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
      <div class="p-6 space-y-6">
        <p class="text-gray-700 dark:text-gray-200">All Notifications</p>

        <div class="border rounded-sm shadow-sm bg-gray-200 dark:bg-gray-800">
          <template v-for="date in sortedDates" :key="date">
            <!-- Date header -->
            <div class="bg-gray-200 dark:bg-gray-900/70 px-4 py-2 text-gray-700 dark:text-gray-300 font-semibold text-sm">
              {{ date }}
            </div>

            <!-- Notifications for this date -->
            <div v-for="notification in groupedNotifications[date]" :key="notification.id">
              <Link :href="`/notifications/${notification.id}`">
              <div class="p-4 flex items-start gap-4 transition hover:bg-gray-100 dark:hover:bg-gray-700 bg-gray-50 dark:bg-gray-800/70">
                <!-- Indicator -->
                <div class="mt-2">
                  <span class="inline-block h-3 w-3 rounded-full"
                    :class="notification.read ? 'bg-gray-400' : 'bg-blue-500 animate-pulse'"></span>
                </div>

                <!-- Content -->
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900 dark:text-gray-100">
                    {{ notification.subject }}
                  </h4>
                  <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ truncate(notification.body, 100) }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ formatDateTime(notification.created_at) }}
                  </p>
                </div>
              </div>
              </Link>
            </div>
          </template>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
