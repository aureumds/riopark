<template>
  <div class="min-h-screen bg-gray-50 text-gray-900" :style="themeVars">
    <router-view />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from './stores/auth';

const auth = useAuthStore();

const themeVars = computed(() => ({
  '--color-primary': auth.settings?.primary_color || '#1e40af',
  '--color-accent': auth.settings?.accent_color || '#f59e0b',
}));
</script>
