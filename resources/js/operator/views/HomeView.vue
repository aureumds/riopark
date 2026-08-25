<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-4 max-w-md mx-auto w-full space-y-4">
      <div class="card bg-white border rounded-2xl p-4">
        <p class="text-sm text-gray-500">Estacionamento</p>
        <p class="font-semibold">{{ auth.parkingLot?.name }}</p>
        <p class="text-sm mt-2 text-gray-500">Turno</p>
        <p class="font-medium">{{ shiftStore.current ? 'Aberto' : 'Fechado' }}</p>
      </div>

      <div class="grid grid-cols-1 gap-3">
        <router-link
          to="/operador/entrada"
          class="block py-5 text-center rounded-2xl text-white text-lg font-semibold"
          style="background: var(--color-primary)"
        >
          Entrada
        </router-link>
        <router-link
          to="/operador/saida"
          class="block py-5 text-center rounded-2xl text-white text-lg font-semibold"
          style="background: var(--color-accent)"
        >
          Saída
        </router-link>
        <router-link
          to="/operador/patio"
          class="block py-5 text-center rounded-2xl border bg-white text-lg font-semibold"
        >
          Veículos no pátio ({{ sessionStore.active.length }})
        </router-link>
        <router-link
          to="/operador/turno"
          class="block py-4 text-center rounded-2xl border bg-white text-sm font-medium text-gray-700"
        >
          Gerenciar turno
        </router-link>
        <router-link
          to="/operador/fechamento"
          class="block py-4 text-center rounded-2xl border bg-white text-sm font-medium text-gray-700"
        >
          Fechamento local
        </router-link>
      </div>

      <button type="button" class="w-full text-sm text-gray-500 py-2" @click="logout">Sair</button>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRouter } from 'vue-router';
import StatusBar from '../components/StatusBar.vue';
import { useAuthStore } from '../stores/auth';
import { useShiftStore } from '../stores/shift';
import { useSessionStore } from '../stores/sessions';

const auth = useAuthStore();
const shiftStore = useShiftStore();
const sessionStore = useSessionStore();
const router = useRouter();

onMounted(async () => {
  await auth.init();
  await shiftStore.init();
  await sessionStore.loadActive();
});

async function logout() {
  await auth.logout();
  router.push({ name: 'login' });
}
</script>
