<template>
  <AdminFooter v-if="isAdminRoute" />
  <UserFooter v-else-if="!isAuthRoute" />
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminFooter from './admin/AdminFooter.vue';
import UserFooter from './anonymous/Footer.vue';

const route = useRoute();
const authRouteNames = ['login', 'register', 'password.request', 'password.reset', 'AdminLogin'];

const isAdminRoute = computed(() => {
  return route.path.startsWith('/admin') && route.name !== 'AdminLogin';
});

const isAuthRoute = computed(() => {
  return authRouteNames.includes(route.name);
});
</script>
