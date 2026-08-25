<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-4 max-w-md mx-auto w-full space-y-4">
      <router-link to="/operador" class="text-sm text-gray-500 inline-block">← Voltar</router-link>
      <h1 class="text-xl font-semibold">Fechamento local</h1>
      <p class="text-sm text-gray-500">Resumo desta máquina (hoje). O fechamento oficial da empresa continua no painel web após o sync.</p>

      <div class="p-4 bg-white border rounded-2xl space-y-2">
        <p class="text-sm text-gray-500">Saídas hoje</p>
        <p class="text-2xl font-bold">{{ summary.exits }}</p>
        <p class="text-sm text-gray-500 mt-3">Total recebido</p>
        <p class="text-2xl font-bold">R$ {{ summary.total.toFixed(2).replace('.', ',') }}</p>
        <p class="text-sm text-gray-500 mt-3">Ainda no pátio</p>
        <p class="text-lg font-semibold">{{ summary.inYard }}</p>
      </div>

      <button
        type="button"
        class="w-full py-4 rounded-2xl text-white font-semibold"
        style="background: var(--color-primary)"
        @click="print"
      >
        Imprimir resumo
      </button>
    </main>
  </div>
</template>

<script setup>
import { onMounted, reactive } from 'vue';
import StatusBar from '../components/StatusBar.vue';
import { useAuthStore } from '../stores/auth';
import { useSessionStore } from '../stores/sessions';

const auth = useAuthStore();
const sessionStore = useSessionStore();
const summary = reactive({ exits: 0, total: 0, inYard: 0 });

onMounted(async () => {
  Object.assign(summary, await sessionStore.closingSummary());
});

function print() {
  const text = [
    auth.company?.name || 'Rio Park',
    auth.parkingLot?.name || '',
    'FECHAMENTO LOCAL',
    `Data: ${new Date().toLocaleDateString('pt-BR')}`,
    `Saidas: ${summary.exits}`,
    `Total: R$ ${summary.total.toFixed(2)}`,
    `Patio: ${summary.inYard}`,
  ].join('\n');
  if (window.RioParkBridge?.printTicket) {
    window.RioParkBridge.printTicket(text);
  }
}
</script>
