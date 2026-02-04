<template>
  <button
    class="btn btn-outline-light btn-sm d-flex align-items-center gap-2"
    @click="toggleTheme"
  >
    <i :class="theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon'"></i>
    <span>{{ theme === 'dark' ? 'Light' : 'Dark' }}</span>
  </button>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

const theme = ref(localStorage.getItem('theme') || 'light')

const applyTheme = (mode) => {
  document.documentElement.setAttribute('data-bs-theme', mode);
  document.documentElement.classList.toggle('dark', mode === 'dark');
  localStorage.setItem('theme', mode);
}

const toggleTheme = () => {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

watch(theme, (newVal) => applyTheme(newVal))
onMounted(() => applyTheme(theme.value))
</script>
