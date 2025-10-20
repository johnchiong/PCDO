<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import { LoaderCircle } from 'lucide-vue-next'
import Button from '@/components/ui/button/Button.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { toast } from "vue-sonner"

const form = useForm({ code: '' })
const resendForm = useForm({})

const timer = ref(0)
let interval: ReturnType<typeof setInterval> | null = null

const submit = () => {
  form.post('/login/verify', {
    onError: (errors) => console.log(errors),
  })
}

const startTimer = (seconds: number) => {
  timer.value = seconds
  interval = setInterval(() => {
    timer.value--
    if (timer.value <= 0 && interval) {
      clearInterval(interval)
      interval = null
    }
  }, 1000)
}

const resend = () => {
  if (timer.value > 0) return

  resendForm.post('/login/resend', {
    preserveScroll: true,
    onSuccess: () => {
      toast.success(`A new code has been sent to your email.`)
      startTimer(60) // 60 seconds before allowing resend
    },
    onError: () => {
      toast.error(`Failed to resend code. Please try again.`)
    },
  })
}

const resendLabel = computed(() => {
  if (timer.value > 0) return `Resend Code (${timer.value}s)`
  return 'Resend Code'
})
</script>

<template>
  <AuthLayout
    title="Verify Login Code"
    description="Enter the code we sent to your email to continue."
  >
    <Head title="Verify Login Code" />

    <div
      class="max-w-md mx-auto mt-1 p-8 rounded-2xl shadow-lg 
             bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700"
    >
      <h1 class="text-2xl font-bold text-center mb-2 text-gray-900 dark:text-gray-100">
        Verify Code
      </h1>
      <p class="text-center text-gray-500 dark:text-gray-400 mb-6 text-sm">
        Please check your email and enter the 6-digit code below.
      </p>

      <form @submit.prevent="submit" class="flex flex-col gap-6">
        <input
          type="text"
          v-model="form.code"
          name="code"
          maxlength="6"
          placeholder="••••••"
          class="tracking-widest text-center text-2xl font-semibold
                 border-2 border-gray-300 dark:border-gray-700
                 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                 rounded-xl p-3 transition bg-gray-50 dark:bg-gray-800"
        />

        <div v-if="form.errors.code" class="text-center text-red-600 text-sm">
          {{ form.errors.code }}
        </div>

        <Button
          :disabled="form.processing"
          class="w-full flex justify-center items-center gap-2 py-3 
                 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold 
                 rounded-xl transition"
        >
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
          <span>Verify</span>
        </Button>

        <button
          type="button"
          @click="resend"
          :disabled="resendForm.processing || timer > 0"
          class="text-sm text-indigo-600 hover:text-indigo-800 font-medium underline text-center mt-3 disabled:opacity-50"
        >
          <span v-if="!resendForm.processing">{{ resendLabel }}</span>
          <span v-else class="flex justify-center items-center gap-2">
            <LoaderCircle class="h-4 w-4 animate-spin" /> Sending...
          </span>
        </button>
      </form>
    </div>
  </AuthLayout>
</template>
