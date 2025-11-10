<template>
  <AdminHeader v-if="isAdminRoute" />
  <UserHeader v-else />
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import AdminHeader from './admin/AdminHeader.vue';
import UserHeader from './anonymous/Header.vue';

const route = useRoute();

const isAdminRoute = computed(() => {
  return route.path.startsWith('/admin') && route.name !== 'AdminLogin';
});
</script>
