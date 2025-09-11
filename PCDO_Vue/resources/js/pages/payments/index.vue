<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Link, Head } from '@inertiajs/vue3'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    ArrowRightToLine,
    ArrowLeftToLine,
    CircleAlert,
    CheckCircle,
    XCircle,
    CircleDashed,
} from 'lucide-vue-next'
import payments from '@/routes/payments'
import { Button } from '@/components/ui/button'

// Types
interface Schedule {
    id: number
    due_date: string
    is_paid: boolean
    paid_at?: string | null
}

interface Program {
    name: string
}

interface Loan {
    id: number
    amount: number
    program: Program
    schedules: Schedule[]
}

interface Cooperative {
    id: number
    name: string
    loan?: Loan | null
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Payments', href: payments.index().url },
]

// Props
const props = defineProps<{ cooperatives: Cooperative[] }>()

// Pagination
const currentPage = ref(1)
const itemsPerPage = ref(10)

const paginatedCooperatives = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    return props.cooperatives.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() =>
    Math.ceil(props.cooperatives.length / itemsPerPage.value)
)

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) currentPage.value = page
}

// Format date
function formatDate(dateStr: string): string {
    if (!dateStr) return '—'
    const date = new Date(dateStr)
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    })
}

// Get next payment info
function getPaymentInfo(loan?: Loan | null) {
    if (!loan?.schedules?.length) {
        return { date: '—', status: 'No Records' }
    }

    const now = new Date()

    // Find first unpaid schedule
    const nextUnpaid = loan.schedules.find((s) => !s.is_paid)
    if (nextUnpaid) {
        const dueDate = new Date(nextUnpaid.due_date)
        const status = dueDate < now ? 'Overdue' : 'Pending'
        return {
            date: formatDate(nextUnpaid.due_date),
            status,
        }
    }

    // If all paid, get last payment
    const lastPaid = loan.schedules
        .filter((s) => s.is_paid && s.paid_at)
        .sort(
            (a, b) =>
                new Date(b.paid_at!).getTime() - new Date(a.paid_at!).getTime()
        )[0]

    return {
        date: lastPaid?.paid_at ? formatDate(lastPaid.paid_at) : '—',
        status: 'Paid',
    }
}

// Map status to icon + color
function getStatusMeta(status: string) {
    switch (status) {
        case 'Overdue':
            return { icon: CircleAlert, class: 'text-red-600', label: 'Overdue' }
        case 'Paid':
            return { icon: CheckCircle, class: 'text-green-600', label: 'Completed' }
        case 'No Records':
            return { icon: XCircle, class: 'text-gray-500', label: 'No Records' }
        case 'Pending':
        default:
            return { icon: CircleDashed, class: 'text-yellow-600', label: 'Pending' }
    }
}
</script>

<template>
    <Head title="Payments" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Container -->
        <div class="p-6 space-y-6">
            <!-- Table Container -->
            <div
                class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-md rounded-xl p-4"
            >
                <Table>
                    <TableHeader class="bg-gray-200 dark:bg-gray-800">
                        <TableRow>
                            <TableHead class="text-gray-600 dark:text-gray-400">Cooperative Name</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-23">Kind of Loan</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-23">Amount</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-23">Next Payments</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-23">Status</TableHead>
                            <TableHead class="text-gray-600 dark:text-gray-400 pl-23">Action</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow
                            v-for="coop in paginatedCooperatives"
                            :key="coop.id"
                            class="hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            <TableCell class="font-medium text-gray-900 dark:text-gray-100">
                                {{ coop.name }}
                            </TableCell>
                            <TableCell class="text-gray-700 dark:text-gray-400 pl-23">
                                {{ coop.loan?.program?.name ?? 'No Loan' }}
                            </TableCell>
                            <TableCell class="text-gray-900 dark:text-gray-100 pl-23">
                                <span v-if="coop.loan">
                                    ₱{{ coop.loan.amount.toLocaleString() }}
                                </span>
                                <span v-else class="text-gray-500 dark:text-gray-500">—</span>
                            </TableCell>
                            <TableCell class="text-gray-700 dark:text-gray-400 pl-23">
                                {{ coop.loan ? getPaymentInfo(coop.loan).date : '—' }}
                            </TableCell>
                            <TableCell class="pl-23">
                                <div
                                    class="flex items-center gap-2 font-semibold"
                                    :class="getStatusMeta(getPaymentInfo(coop.loan).status).class"
                                >
                                    <component
                                        :is="getStatusMeta(getPaymentInfo(coop.loan).status).icon"
                                        class="w-5 h-5"
                                        :class="getPaymentInfo(coop.loan).status === 'Pending' ? 'animate-spin' : ''"
                                    />
                                    {{ getStatusMeta(getPaymentInfo(coop.loan).status).label }}
                                </div>
                            </TableCell>
                            <TableCell class="pl-23">
                                <Link
                                    v-if="coop.loan"
                                    :href="`/payments/${coop.loan.id}/amortization`"
                                >
                                    <Button
                                        class="rounded-xl px-4 py-2 shadow-sm bg-blue-600 hover:bg-blue-700 text-white"
                                    >
                                        View Amortization
                                    </Button>
                                </Link>
                                <span v-else class="text-gray-500 dark:text-gray-500 text-sm italic">No Amortization</span>
                            </TableCell>
                        </TableRow>

                        <!-- Empty state -->
                        <TableRow v-if="paginatedCooperatives.length === 0">
                            <TableCell
                                colspan="6"
                                class="text-center text-gray-500 dark:text-gray-500 py-4 italic"
                            >
                                No payments found.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>

                <!-- Pagination -->
                <div class="flex justify-between items-center mt-6">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to
                        {{ Math.min(currentPage * itemsPerPage, props.cooperatives.length) }}
                        of {{ props.cooperatives.length }} payments
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            :disabled="currentPage === 1"
                            @click="goToPage(currentPage - 1)"
                            class="px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 
                                   hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50"
                        >
                            <ArrowLeftToLine class="h-5 w-5 stroke-current text-gray-700 dark:text-gray-200" />
                        </Button>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">
                            Page {{ currentPage }} of {{ totalPages }}
                        </span>
                        <Button
                            :disabled="currentPage === totalPages"
                            @click="goToPage(currentPage + 1)"
                            class="px-3 py-1 rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 
                                   hover:bg-gray-200 dark:hover:bg-gray-700 disabled:opacity-50"
                        >
                            <ArrowRightToLine class="h-5 w-5 stroke-current text-gray-700 dark:text-gray-200" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>