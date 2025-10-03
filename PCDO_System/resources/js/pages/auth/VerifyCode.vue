<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3'
import { LoaderCircle } from 'lucide-vue-next'
import Button from '@/components/ui/button/Button.vue'
import { Form } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'

const form = useForm({
  code: '',
})

const submit = () => {
  form.post('/login/verify', {
    onError: (errors) => console.log(errors),
  })
}
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
      <!-- Header -->
      <h1 class="text-2xl font-bold text-center mb-2 text-gray-900 dark:text-gray-100">
        Verify Code
      </h1>
      <p class="text-center text-gray-500 dark:text-gray-400 mb-6 text-sm">
        Please check your email and enter the 6-digit code below.
      </p>

      <!-- Form -->
      <Form @submit.prevent="submit" class="flex flex-col gap-6">
        <!-- Input styled like OTP -->
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

        <!-- Error message -->
        <div v-if="form.errors.code" class="text-center text-red-600 text-sm">
          {{ form.errors.code }}
        </div>

        <!-- Button -->
        <Button
          :disabled="form.processing"
          class="w-full flex justify-center items-center gap-2 py-3 
                 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold 
                 rounded-xl transition"
        >
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
          <span>Verify</span>
        </Button>
      </Form>
    </div>
  </AuthLayout>
</template>
