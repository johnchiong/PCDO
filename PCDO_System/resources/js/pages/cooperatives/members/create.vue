<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { BreadcrumbItem } from '@/types'
import SelectSearch from '@/components/SelectSearch.vue';
import { toast } from "vue-sonner"

const props = defineProps<{
    breadcrumbs?: BreadcrumbItem[]
    cooperative: { id: string }
}>()

const form = useForm({
    coop_id: props.cooperative.id,
    position: '',
    active_year: new Date().getFullYear(),
    first_name: '',
    middle_initial: '',
    last_name: '',
    suffix: '',
    is_representative: false,
    files: [] as File[],
})

const positions = [
    { id: 'Treasurer', name: 'Treasurer' },
    { id: 'Chairman', name: 'Chairman' },
    { id: 'Manager', name: 'Manager' },
    { id: 'Member', name: 'Member' },
]

const searchPosition = ref('')
const dropDownPositionOpen = ref(false)

const file = ref<File[]>([])

const allowedFileTypes = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'image/jpeg',
    'image/png',
    'image/jpg',
];

function setMiddleInitial(initial: string) {
    if (initial.length > 1) {
        initial = initial.charAt(0);
    }
    return initial;
}

function onDrop(e: DragEvent) {
    e.preventDefault();
    const dt = e.dataTransfer;
    if (dt && dt.files.length > 0) {
        const validFiles = validateFiles(Array.from(dt.files));
        file.value = [...file.value, ...validFiles];
        form.files = file.value;
    }
}

function validateFiles(selectedFiles: File[]) {
    return selectedFiles.filter(f => {
        if (!allowedFileTypes.includes(f.type)) {
            toast.error(`File type not allowed: ${f.name}`);
            return false;
        }
        return true;
    });
}

function onFileChange(e: Event) {
    const target = e.target as HTMLInputElement
    if (target.files && target.files.length > 0) {
        const validFiles = validateFiles(Array.from(target.files));
        file.value = [...file.value, ...validFiles];
        form.files = file.value;
    }
}

function clearFile(index: number) {
    file.value.splice(index, 1)
    const input = document.getElementById('fileInput') as HTMLInputElement
    if (input && file.value.length === 0) {
        input.value = ''
    }
}

function openFileModal() {
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.click();
    }
}

function handleSubmit() {
    form.files = file.value.length > 0 ? file.value : [];
    form.post(`/cooperatives/${props.cooperative.id}/members`, {
        forceFormData: true,
        onError: (errors) => {
            const messages = Object.values(errors);
            if (messages.length) {
                toast.error(messages.join('\n'));
            }
        },
        onSuccess: () => {
            toast.success(`${form.first_name} ${form.last_name} has been added successfully!`)
        }
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="bg-gray-100/90 dark:bg-gray-900 min-h-screen">
            <div class="max-w-7x7 p-6">
                <div
                    class="bg-gray-100/90 dark:bg-gray-800/80 border ring-1 ring-gray-300 dark:ring-gray-700 border-gray-300 dark:border-gray-700 rounded-xl shadow-m px-6 py-5 mb-6">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-3 pb-6">
                        <Plus class="w-10 h-10 text-blue-600 dark:text-blue-400 flex-shrink-0" /> Add Cooperative Member
                    </h1>

                    <form @submit.prevent="handleSubmit" class="space-y-6">
                        <div>
                            <label for="position" class="block mb-2">Position</label>
                            <SelectSearch id="position" :items="positions" itemKeyProp="id" itemLabelKey="name"
                                v-model:search="searchPosition" :modelValue="form.position" @select="(val: { id: string; name: string }) => {
                                    form.position = val.id
                                    if (val.id !== 'Chairman' && val.id !== 'Treasurer' && val.id !== 'Manager') {
                                        form.first_name = ''
                                        form.middle_initial = ''
                                        form.last_name = ''
                                        form.suffix = ''
                                    }
                                }" :open="dropDownPositionOpen" @update:open="val => dropDownPositionOpen = val"
                                placeholder="Select Position" />
                        </div>
                        <div>
                            <label for="active_year" class="block mb-2">Active Year</label>
                            <input v-model="form.active_year" id="active_year" type="number" min="2000"
                                :max="new Date().getFullYear() + 1"
                                class="w-full pl-9 rounded-xl border bg-white dark:bg-gray-700 border-gray-500 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>
                        <div v-if="['Chairman', 'Treasurer', 'Manager'].includes(form.position)">
                            <label for="first_name" class="block mb-2">First Name</label>
                            <input v-model="form.first_name" id="first_name" type="text"
                                class="w-full pl-9 rounded-xl border bg-white dark:bg-gray-700 border-gray-500 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div v-if="['Chairman', 'Treasurer', 'Manager'].includes(form.position)">
                            <label for="middle_initial" class="block mb-2">Middle Initial</label>
                            <input v-model="form.middle_initial" id="middle_initial" type="text" maxlength="1"
                                @input="form.middle_initial = setMiddleInitial(form.middle_initial)"
                                class="w-full pl-9 rounded-xl border bg-white dark:bg-gray-700 border-gray-500 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                            <div v-if="form.middle_initial.length > 1" class="text-red-500 text-sm mt-1">
                                Middle initial must be a single character.
                            </div>
                        </div>

                        <div v-if="['Chairman', 'Treasurer', 'Manager'].includes(form.position)">
                            <label for="last_name" class="block mb-2">Last Name</label>
                            <input v-model="form.last_name" id="last_name" type="text"
                                class="w-full pl-9 rounded-xl border bg-white dark:bg-gray-700 border-gray-500 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div v-if="['Chairman', 'Treasurer', 'Manager'].includes(form.position)">
                            <label for="suffix" class="block mb-2">
                                Suffix
                                <span class="text-sm text-gray-600 dark:text-gray-400">(optional)</span>
                            </label>
                            <input v-model="form.suffix" id="suffix" type="text"
                                class="w-full pl-9 rounded-xl border bg-white dark:bg-gray-700 border-gray-500 dark:border-gray-700 p-3 focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
                        </div>

                        <div v-if="['Chairman', 'Treasurer', 'Manager'].includes(form.position)"
                            class="flex items-center gap-2">
                            <input type="checkbox" id="is_representative" v-model="form.is_representative" />
                            <label for="is_representative">
                                Is Representative?
                            </label>
                        </div>

                        <div v-if="['Chairman', 'Treasurer', 'Manager', 'Member'].includes(form.position)">
                            <label class="block mb-2">Upload File</label>
                            <div class="border-2 border-dashed bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 rounded-lg p-6 text-center text-gray-500 dark:text-gray-400 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-500 transition"
                                @dragover.prevent @drop="onDrop" @click="openFileModal">
                                <input id="fileInput" type="file" multiple @change="onFileChange" class="hidden"
                                    accept=".pdf,.doc,.docx,.jpg, .jpeg,.png" />
                                <div v-if="file.length" class="mb-2 space-y-2">
                                    <div v-for="(f, index) in file" :key="index"
                                        class="flex items-center justify-between bg-gray-100 p-2 rounded">
                                        <p class="text-gray-700 text-sm truncate">{{ f.name }}</p>
                                        <button type="button" @click.stop="clearFile(index)"
                                            class="text-red-500 underline text-xs">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="mb-2">
                                    <p class="text-gray-500">Drag & drop files here, or click to select</p>
                                </div>
                                <div class="text-xs text-gray-400">Accepted formats: PDF, DOC, DOCX, JPG, PNG</div>
                            </div>
                        </div>
                        <div class="pt-6 md:col-span-2 flex justify-center md:justify-end">
                            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
