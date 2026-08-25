<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-4 max-w-md mx-auto w-full">
      <router-link to="/operador" class="text-sm text-gray-500 mb-4 inline-block">← Voltar</router-link>
      <h1 class="text-xl font-semibold mb-4">Pátio ({{ sessionStore.active.length }})</h1>
      <div class="space-y-2">
        <div
          v-for="session in sessionStore.active"
          :key="session.local_uuid"
          class="p-4 bg-white border rounded-xl flex justify-between items-center"
        >
          <div>
            <p class="font-bold text-lg">{{ session.plate }}</p>
            <p class="text-xs text-gray-500">{{ formatTime(session.entry_at) }}</p>
          </div>
          <router-link :to="'/operador/saida'" class="text-sm font-medium" style="color: var(--color-accent)">Saída</router-link>
        </div>
        <p v-if="!sessionStore.active.length" class="text-center text-gray-500 py-8">Nenhum veículo no pátio</p>
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import StatusBar from '../components/StatusBar.vue';
import { useSessionStore } from '../stores/sessions';

const sessionStore = useSessionStore();

onMounted(() => sessionStore.loadActive());

function formatTime(iso) {
  return new Date(iso).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' });
}
</script>
