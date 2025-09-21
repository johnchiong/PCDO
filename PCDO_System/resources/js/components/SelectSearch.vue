<!-- src/components/SelectSearch.vue -->
<template>
  <div class="relative w-full" v-click-outside="onOutside" ref="root">

    <input
      :id="id"
      v-model="searchValue"
      :placeholder="placeholder"
      @focus="openLocal()"
      @input="onInput"
      :disabled="disabled"
        class="pl-9 pr-3 w-full p-3 rounded-xl border border-gray-300 dark:border-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
    />

    <div v-if="open && filtered.length > 0" class="absolute w-full mt-1 border border-gray-300 bg-white shadow-lg max-h-60 overflow-y-auto rounded-md z-10">
      <ul>
        <li v-for="item in filtered" :key="itemKey(item)" @click="selectItem(item)" class="px-4 py-2 cursor-pointer hover:bg-gray-200">
          {{ itemLabel(item) }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed, defineEmits, defineProps } from 'vue'

const props = defineProps({
  id: { type: String, default: '' },
  items: { type: Array as () => any[], default: () => [] }, // array of objects or strings
  placeholder: { type: String, default: 'Search' },
  disabled: { type: Boolean, default: false },
  modelValue: { type: [String, Number], default: '' }, // selected display value
  open: { type: Boolean, default: false },
  itemLabelKey: { type: String, default: 'name' },
  itemKeyProp: { type: String, default: 'id' },
})

const emits = defineEmits(['update:modelValue', 'select', 'update:open', 'update:search'])
const searchValue = ref(props.modelValue ?? '')
const open = ref(props.open)

watch(() => props.open, v => open.value = v)
watch(() => props.modelValue, v => searchValue.value = v ?? '')

const filtered = computed(() => {
  if (!searchValue.value) return props.items
  const q = searchValue.value.toString().toLowerCase()
  return props.items.filter(it => {
    const text = itemLabel(it).toString().toLowerCase()
    return text.includes(q)
  })
})

function itemLabel(it: any) {
  return typeof it === 'string' ? it : it[props.itemLabelKey]
}
function itemKey(it: any) {
  return typeof it === 'string' ? it : it[props.itemKeyProp] ?? itemLabel(it)
}

function selectItem(it: any) {
  const label = itemLabel(it)
  const id = itemKey(it)
  emits('update:modelValue', label)
  emits('select', { name: label, id })
  open.value = false
  emits('update:open', false)
  emits('update:search', label)
}

function onInput() {
  emits('update:search', searchValue.value)
}
function openLocal() {
  if (!props.disabled) {
    open.value = true
    emits('update:open', true)
  }
}
function onOutside() {
  open.value = false
  emits('update:open', false)
}
</script>

