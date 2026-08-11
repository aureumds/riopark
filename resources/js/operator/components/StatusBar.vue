<template>
  <header class="px-4 py-3 bg-white border-b border-gray-200 flex items-center justify-between gap-2">
    <div class="min-w-0">
      <p class="text-sm font-semibold truncate" style="color: var(--color-primary)">Rio Park</p>
      <p class="text-xs text-gray-500 truncate">{{ auth.user?.name }}</p>
    </div>
    <div class="flex items-center gap-2 text-xs shrink-0">
      <span
        class="px-2 py-1 rounded-full"
        :class="auth.online ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800'"
      >
        {{ auth.online ? 'Online' : 'Offline' }}
      </span>
      <span v-if="auth.pendingSync > 0" class="px-2 py-1 rounded-full bg-amber-100 text-amber-800">
        {{ auth.pendingSync }} sync
      </span>
    </div>
  </header>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
</script>
