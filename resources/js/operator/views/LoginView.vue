<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-6 flex flex-col justify-center max-w-md mx-auto w-full">
      <h1 class="text-xl font-semibold mb-6 text-center">Operador</h1>
      <form class="space-y-4" @submit.prevent="submit">
        <input
          v-model="email"
          type="email"
          placeholder="E-mail"
          class="w-full border rounded-xl px-4 py-3 bg-white"
          required
        />
        <input
          v-model="password"
          type="password"
          placeholder="Senha"
          class="w-full border rounded-xl px-4 py-3 bg-white"
          required
        />
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <button
          type="submit"
          class="w-full py-4 rounded-xl text-white font-semibold text-lg"
          style="background: var(--color-primary)"
          :disabled="loading"
        >
          {{ loading ? 'Entrando...' : 'Entrar' }}
        </button>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import StatusBar from '../components/StatusBar.vue';
import { useAuthStore } from '../stores/auth';
import { useShiftStore } from '../stores/shift';

const auth = useAuthStore();
const shiftStore = useShiftStore();
const router = useRouter();

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

async function submit() {
  error.value = '';
  loading.value = true;
  try {
    await auth.login(email.value, password.value);
    await shiftStore.init();
    router.push({ name: 'home' });
  } catch (e) {
    error.value = e.message || e.response?.data?.message || 'Falha no login';
  } finally {
    loading.value = false;
  }
}
</script>
