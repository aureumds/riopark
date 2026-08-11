<template>
  <div class="min-h-screen flex flex-col">
    <StatusBar />
    <main class="flex-1 p-4 max-w-md mx-auto w-full space-y-4">
      <router-link to="/operador" class="text-sm text-gray-500 inline-block">← Voltar</router-link>
      <h1 class="text-xl font-semibold">Turno</h1>

      <div class="p-4 bg-white border rounded-2xl">
        <p class="text-sm text-gray-500">Status</p>
        <p class="font-semibold text-lg">{{ shiftStore.current ? 'Aberto' : 'Fechado' }}</p>
        <p v-if="shiftStore.current?.opened_at" class="text-sm text-gray-500 mt-2">
          Desde {{ new Date(shiftStore.current.opened_at).toLocaleString('pt-BR') }}
        </p>
      </div>

      <div v-if="!shiftStore.current" class="space-y-3">
        <input
          v-model.number="openingBalance"
          type="number"
          step="0.01"
          placeholder="Saldo inicial (opcional)"
          class="w-full border rounded-xl px-4 py-3 bg-white"
        />
        <button
          type="button"
          class="w-full py-4 rounded-2xl text-white font-semibold"
          style="background: var(--color-primary)"
          @click="openShift"
        >
          Abrir turno
        </button>
      </div>

      <div v-else class="space-y-3">
        <input
          v-model.number="closingBalance"
          type="number"
          step="0.01"
          placeholder="Saldo final (opcional)"
          class="w-full border rounded-xl px-4 py-3 bg-white"
        />
        <button
          type="button"
          class="w-full py-4 rounded-2xl text-white font-semibold"
          style="background: var(--color-accent)"
          @click="closeShift"
        >
          Fechar turno
        </button>
      </div>

      <p v-if="message" class="text-sm text-green-700">{{ message }}</p>
    </main>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import StatusBar from '../components/StatusBar.vue';
import { useShiftStore } from '../stores/shift';

const shiftStore = useShiftStore();
const openingBalance = ref(0);
const closingBalance = ref(0);
const message = ref('');

async function openShift() {
  await shiftStore.open(openingBalance.value || 0);
  message.value = 'Turno aberto';
}

async function closeShift() {
  await shiftStore.close(closingBalance.value || 0);
  message.value = 'Turno fechado';
}
</script>
