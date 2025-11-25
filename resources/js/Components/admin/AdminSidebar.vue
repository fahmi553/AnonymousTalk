<template>
  <div class="card bg-body shadow-sm border-0 rounded-lg">
    <div class="card-header bg-body py-3">
      <h6 class="fw-bold mb-0 text-body-emphasis">Management</h6>
    </div>
    <div class="list-group list-group-flush">
      
      <router-link 
        to="/admin/dashboard?tab=user" 
        class="list-group-item list-group-item-action"
        :class="{ 'active': isDashboard && currentTab === 'user' }"
      >
        <i class="fas fa-inbox me-2"></i> Pending User Reports
      </router-link>

      <router-link 
        to="/admin/dashboard?tab=sentiment" 
        class="list-group-item list-group-item-action"
        :class="{ 'active': isDashboard && currentTab === 'sentiment' }"
      >
        <i class="fas fa-robot me-2"></i> Flagged Content
      </router-link>

      <router-link 
        to="/admin/users" 
        class="list-group-item list-group-item-action"
        active-class="active"
      >
        <i class="fas fa-shield-alt me-2"></i> User Trust Scores
      </router-link>
      
      <router-link 
        to="/admin/logs" 
        class="list-group-item list-group-item-action"
        active-class="active"
      >
        <i class="fas fa-file-signature me-2"></i> System Logs
      </router-link>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();
const isDashboard = computed(() => route.path === '/admin/dashboard');
const currentTab = computed(() => route.query.tab || 'user');
</script>