<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminSidebar from './admin/AdminSidebar.vue';
import UserSidebar from './anonymous/Sidebar.vue'; 

const route = useRoute();

const isAdminRoute = computed(() => {
  return route.path.startsWith('/admin') && route.name !== 'AdminLogin';
});

const hiddenRoutes = ['/login', '/register', '/admin/login'];

const shouldShowSidebar = computed(() => {
  if (route.path === '/admin/login') return false;
  if (hiddenRoutes.includes(route.path)) return false;
  return true;
});
</script>

<template>
  <div v-if="shouldShowSidebar" class="col-lg-3 mb-4 d-none d-lg-block">
    <AdminSidebar v-if="isAdminRoute" />
    <UserSidebar v-else />
  </div>
</template>