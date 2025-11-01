<template>
  <header class="header d-flex justify-content-between align-items-center p-3 bg-dark text-white shadow-sm border-bottom border-secondary">
    <div class="d-flex align-items-center">
      <h1 class="me-4 mb-0 h4">AnonymousTalk Admin</h1>
      <nav class="d-flex">
        <a href="/admin/dashboard" class="nav-link text-white me-3">Dashboard</a>
      </nav>
    </div>

    <div class="d-flex align-items-center gap-2">
      <!--
        FIX: The import path for ThemeToggle is now correct.
      -->
      <ThemeToggle />
      <template v-if="adminUser">
        <span class="text-white me-3">Hi, {{ adminUser.name || adminUser.email }}</span>
        <button @click="logout" class="btn btn-light btn-sm">Logout</button>
      </template>
      <template v-else>
        <a href="/admin/login" class="btn btn-light btn-sm me-2">Admin Login</a>
      </template>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
//
// FIX: Path updated to go up to 'components' and down into 'anonymous'
//
import ThemeToggle from '../anonymous/ThemeToggle.vue';

const adminUser = ref(null);

const fetchAdminUser = async () => {
  try {
    const res = await axios.get('/api/user'); // Assumes /api/user returns the logged-in admin
    if (res.data && res.data.role === 'admin') {
      adminUser.value = res.data;
    }
  } catch (error) {
    console.warn('No authenticated admin user found.');
  }
};

const logout = async () => {
  try {
    await axios.get('/sanctum/csrf-cookie');
    await axios.post('/admin/logout');
    window.location.href = '/admin/login';
  } catch (error) {
    console.error('Admin logout failed:', error);
    alert('Logout failed. Please try again.');
  }
};

onMounted(fetchAdminUser);
</script>

<style scoped>
.nav-link {
  text-decoration: none;
}
</style>
