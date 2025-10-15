<script setup lang="ts">
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import { DropdownMenuRoot, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from 'reka-ui';
import AppLayout from '@/layouts/AppLayout.vue';
import cooperative from '@/routes/cooperatives';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const breadcrumbs: BreadcrumbItem [] = [
    {title: 'Cooperative', href: cooperative.index().url},
    {title: 'Create Cooperative', href: cooperative.create().url},
]

defineProps({
    cooperatives: Array,
    details: Array
})

const form = useForm({
    coop_id: '',
    name: '',
});

const handleSubmit = () => {
    form.post('cooperative/store');
};

</script>

<template>

    <Head title="Create Cooperative" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex justify-center mt-8">
            <!-- Card Container -->
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 w-full md:w-8/12">
                <h2 class="text-lg font-semibold mb-4">Create New Cooperative</h2>

                <form @submit.prevent="" class="space-y-4">
                    <!-- Program Dropdown -->
                    <div class="space-y-2">
                        <Label for="program_id">Choose your Availed Program:</Label>
                        <div class="relative w-full">
                            <DropdownMenuRoot>
                                <DropdownMenuTrigger as="button"
                                    class="form-control w-full text-left py-2 px-3 border rounded 
                                bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 flex items-center justify-between focus:outline-none">
                                    <span class="text-gray-500" v-if="!selectedProgram">
                                        -- Select Program --
                                    </span>
                                    <span v-else>{{ selectedProgram }}</span>
                                    <svg class="w-4 h-4 ml-2 text-gray-400 absolute right-3 top-1/2 transform -translate-y-1/2 pointer-events-none"
                                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent
                                    class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow mt-1 w-190"
                                    :style="{ minWidth: '100%' }">
                                    <DropdownMenuItem v-for="option in programOptions" :key="option.value"
                                        @click="selectProgram(option.value)" class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer text-gray-900 dark:text-gray-100">
                                        {{ option.label }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenuRoot>
                            <input type="hidden" name="program_id" :value="form.program_id" />
                        </div>
                        <div class="text-sm text-red-600" v-if="form.errors.program_id">{{ form.errors.program_id }}
                        </div>
                    </div>

                    <!-- Cooperative Name -->
                    <div class="space-y-2">
                        <Label for="name">Cooperative Name</Label>
                        <Input type="text" placeholder="Cooperative Name" v-model="form.name" />
                        <div class="text-sm text-red-600" v-if="form.errors.name">{{ form.errors.name }}</div>
                    </div>

                    <!-- Submit Button -->
                    <Button type="submit">
                        Upload Cooperative Details
                    </Button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
