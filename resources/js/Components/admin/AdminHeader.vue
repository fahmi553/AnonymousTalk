<template>
  <header class="header d-flex justify-content-between align-items-center p-3 bg-dark text-white shadow-sm border-bottom border-secondary">
    <div class="d-flex align-items-center">
      <h1 class="me-4 mb-0 h4">AnonymousTalk Admin</h1>
      <nav class="d-flex">
        <a href="/admin/dashboard" class="nav-link text-white me-3">Dashboard</a>
      </nav>
    </div>

    <div class="d-flex align-items-center gap-2">
      <ThemeToggle />
      <template v-if="adminUser">
        <span class="text-white me-3">Hi, {{ displayName }}</span>
        <button @click="logout" class="btn btn-light btn-sm" :disabled="isLoggingOut">
          <span v-if="isLoggingOut" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span v-else>Logout</span>
        </button>
      </template>
      <template v-else>
        <a href="/admin/login" class="btn btn-light btn-sm me-2">Admin Login</a>
      </template>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import ThemeToggle from '../anonymous/ThemeToggle.vue';

const adminUser = ref(null);
const isLoggingOut = ref(false);
const displayName = computed(() => adminUser.value?.username || adminUser.value?.name || 'Admin');

const fetchAdminUser = async () => {
  try {
    const res = await axios.get('/api/user');
    if (res.data && res.data.role === 'admin') {
      adminUser.value = res.data;
    }
  } catch (error) {
    console.warn('No authenticated admin user found.');
  }
};

const logout = async () => {
  isLoggingOut.value = true;
  try {
    await axios.post('/admin/logout');
    window.location.href = '/admin/login';

  } catch (error) {
    console.error('Admin logout failed:', error);
    alert('Logout failed. Please try again.');
  } finally {
    isLoggingOut.value = false;
  }
};

onMounted(fetchAdminUser);
</script>

<style scoped>
.nav-link {
  text-decoration: none;
}
</style>
