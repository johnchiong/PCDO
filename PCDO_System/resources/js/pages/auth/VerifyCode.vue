<script setup lang="ts">
import { useForm, Head } from '@inertiajs/vue3'
import { LoaderCircle } from 'lucide-vue-next';
import Button from '@/components/ui/button/Button.vue';
import { Form } from '@inertiajs/vue3';
import AuthLayout from '@/layouts/AuthLayout.vue';

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
  <AuthLayout title="Verify Login Code" description="Please enter the login code sent to your email to proceed.">
    <Head title="Verify Login Code" />

    <div class="max-w-md mx-auto mt-12 p-6 border rounded">
      <h1 class="text-xl font-semibold mb-4">Enter Your Login Code</h1>

      <Form @submit.prevent="submit" class="flex flex-col gap-4">
        <input
          type="text"
          v-model="form.code"
          name="code"
          placeholder="Enter code"
          class="border p-2 rounded"
        />
        <div v-if="form.errors.code" class="text-red-600">{{ form.errors.code }}</div>

        <Button :disabled="form.processing">
          <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
          Verify
        </Button>
      </Form>
    </div>
  </AuthLayout>
</template>