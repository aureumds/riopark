<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-4 max-w-md mx-auto w-full">
      <router-link to="/operador" class="text-sm text-gray-500 mb-4 inline-block">← Voltar</router-link>
      <h1 class="text-xl font-semibold mb-4">Entrada</h1>
      <PlateKeyboard v-model="plate" />
      <p v-if="error" class="text-red-600 text-sm mt-3">{{ error }}</p>
      <p v-if="success" class="text-green-700 text-sm mt-3">{{ success }}</p>
      <button
        type="button"
        class="w-full mt-6 py-4 rounded-2xl text-white font-semibold text-lg disabled:opacity-50"
        style="background: var(--color-primary)"
        :disabled="plate.length < 4 || loading"
        @click="confirm"
      >
        {{ loading ? 'Registrando...' : 'Confirmar entrada' }}
      </button>
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
const error = ref('');
const success = ref('');
const loading = ref(false);

async function confirm() {
  error.value = '';
  success.value = '';
  loading.value = true;
  try {
    const session = await sessionStore.registerEntry(plate.value);
    success.value = `Entrada registrada: ${session.plate}`;
    plate.value = '';
  } catch (e) {
    error.value = e.message || 'Erro ao registrar';
  } finally {
    loading.value = false;
  }
}
</script>
