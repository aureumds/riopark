<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-6 flex flex-col justify-center max-w-md mx-auto w-full">
      <h1 class="text-xl font-semibold mb-2 text-center">Licença de uso</h1>
      <p class="text-sm text-gray-500 text-center mb-6">
        {{ auth.online ? 'Conecte e confirme a senha para baixar o novo token.' : 'Sem internet. Ligue a máquina na web para renovar.' }}
      </p>
      <p class="text-sm text-center mb-4">
        Válida até:
        <strong>{{ expiresLabel }}</strong>
      </p>
      <form class="space-y-4" @submit.prevent="renew">
        <input
          v-model="password"
          type="password"
          placeholder="Senha do operador"
          class="w-full border rounded-xl px-4 py-3 bg-white"
          :disabled="!auth.online"
        />
        <p v-if="error" class="text-red-600 text-sm">{{ error }}</p>
        <button
          type="submit"
          class="w-full py-4 rounded-xl text-white font-semibold text-lg disabled:opacity-50"
          style="background: var(--color-primary)"
          :disabled="loading || !auth.online"
        >
          {{ loading ? 'Renovando...' : 'Renovar licença' }}
        </button>
      </form>
      <button type="button" class="w-full text-sm text-gray-500 py-4" @click="logout">Sair</button>
    </main>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import StatusBar from '../components/StatusBar.vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const password = ref('');
const error = ref('');
const loading = ref(false);

const expiresLabel = computed(() => {
  if (!auth.license?.expires_at) return 'sem token';
  return new Date(auth.license.expires_at).toLocaleString('pt-BR');
});

async function renew() {
  error.value = '';
  loading.value = true;
  try {
    await auth.renewLicenseWithPassword(password.value);
    if (auth.licenseValid) {
      router.push({ name: 'home' });
    }
  } catch (e) {
    error.value = e.response?.data?.errors?.email?.[0] || e.message || 'Não foi possível renovar';
  } finally {
    loading.value = false;
  }
}

async function logout() {
  await auth.logout();
  router.push({ name: 'login' });
}
</script>
