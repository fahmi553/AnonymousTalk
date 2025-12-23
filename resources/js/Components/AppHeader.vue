<template>
  <div
    v-if="flashMessage"
    class="alert alert-success alert-dismissible fade show text-center m-0 rounded-0"
    role="alert"
    style="z-index: 9999; position: relative;"
  >
    <i class="fas fa-check-circle me-2"></i> {{ flashMessage }}
    <button type="button" class="btn-close" @click="flashMessage = ''"></button>
  </div>

  <AdminHeader v-if="isAdminRoute" />
  <UserHeader v-else />
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import AdminHeader from './admin/AdminHeader.vue';
import UserHeader from './anonymous/Header.vue';

const route = useRoute();
const flashMessage = ref('');

const isAdminRoute = computed(() => {
  return route.path.startsWith('/admin') && route.name !== 'AdminLogin';
});

const setAutoClose = () => {
    if (flashMessage.value) {
        setTimeout(() => {
            flashMessage.value = '';
        }, 3000);
    }
};

watch(() => route.path, () => {
    flashMessage.value = '';
});

onMounted(() => {
    if (window.flashMessage) {
        flashMessage.value = window.flashMessage;
        window.flashMessage = null;
        setAutoClose();
    }
});
</script>
