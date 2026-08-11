<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-4 max-w-md mx-auto w-full">
      <router-link to="/operador" class="text-sm text-gray-500 mb-4 inline-block">← Voltar</router-link>
      <h1 class="text-xl font-semibold mb-4">Saída</h1>
      <PlateKeyboard v-model="plate" />
      <div v-if="preview" class="mt-4 p-4 bg-white border rounded-2xl">
        <p class="text-sm text-gray-500">Tempo: {{ preview.duration_minutes }} min</p>
        <p class="text-2xl font-bold mt-1">R$ {{ preview.amount.toFixed(2).replace('.', ',') }}</p>
      </div>
      <p v-if="error" class="text-red-600 text-sm mt-3">{{ error }}</p>
      <p v-if="success" class="text-green-700 text-sm mt-3">{{ success }}</p>
      <div class="flex gap-3 mt-6">
        <button
          type="button"
          class="flex-1 py-3 rounded-2xl border bg-white font-medium"
          :disabled="plate.length < 4"
          @click="loadPreview"
        >
          Calcular
        </button>
        <button
          type="button"
          class="flex-1 py-3 rounded-2xl text-white font-semibold disabled:opacity-50"
          style="background: var(--color-accent)"
          :disabled="plate.length < 4 || loading"
          @click="confirm"
        >
          {{ loading ? '...' : 'Cobrar dinheiro' }}
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import PlateKeyboard from '../components/PlateKeyboard.vue';
import StatusBar from '../components/StatusBar.vue';
import { useSessionStore } from '../stores/sessions';

const sessionStore = useSessionStore();
const plate = ref('');
const preview = ref(null);
const error = ref('');
const success = ref('');
const loading = ref(false);

async function loadPreview() {
  error.value = '';
  try {
    preview.value = await sessionStore.preview(plate.value);
  } catch (e) {
    error.value = e.message;
    preview.value = null;
  }
}

async function confirm() {
  error.value = '';
  success.value = '';
  loading.value = true;
  try {
    const { amount } = await sessionStore.registerExit(plate.value);
    success.value = `Saída registrada. Total: R$ ${amount.toFixed(2).replace('.', ',')}`;
    plate.value = '';
    preview.value = null;
  } catch (e) {
    error.value = e.message || 'Erro ao registrar';
  } finally {
    loading.value = false;
  }
}
</script>
