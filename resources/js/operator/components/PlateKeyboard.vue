<template>
  <div class="space-y-3">
    <div class="text-center text-2xl font-bold tracking-widest py-3 border rounded-xl bg-white">
      {{ display }}
    </div>
    <div class="grid grid-cols-3 gap-2">
      <button
        v-for="key in keys"
        :key="key"
        type="button"
        class="py-4 rounded-xl bg-white border text-lg font-semibold active:bg-gray-100"
        @click="press(key)"
      >
        {{ key }}
      </button>
      <button type="button" class="py-4 rounded-xl bg-gray-100 border text-sm" @click="backspace">⌫</button>
      <button type="button" class="py-4 rounded-xl bg-gray-100 border text-sm" @click="clear">Limpar</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const keys = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

const display = computed(() => props.modelValue || '———');

function press(key) {
  if (props.modelValue.length >= 7) return;
  emit('update:modelValue', props.modelValue + key);
}

function backspace() {
  emit('update:modelValue', props.modelValue.slice(0, -1));
}

function clear() {
  emit('update:modelValue', '');
}
</script>
