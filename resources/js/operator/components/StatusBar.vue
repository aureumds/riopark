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
      <span
        v-if="auth.license"
        class="px-2 py-1 rounded-full"
        :class="licenseBadgeClass"
      >
        {{ licenseLabel }}
      </span>
      <span v-if="auth.pendingSync > 0" class="px-2 py-1 rounded-full bg-amber-100 text-amber-800">
        {{ auth.pendingSync }} sync
      </span>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const licenseLabel = computed(() => {
  const check = auth.licenseCheck;
  if (!check.valid) return 'Licença vencida';
  if (check.grace) return 'Período de graça';
  return `${check.daysLeft}d licença`;
});

const licenseBadgeClass = computed(() => {
  const check = auth.licenseCheck;
  if (!check.valid) return 'bg-red-100 text-red-800';
  if (check.grace || check.daysLeft <= 5) return 'bg-amber-100 text-amber-800';
  return 'bg-green-100 text-green-800';
});
</script>
