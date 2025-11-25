<template>
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card bg-body p-3 shadow-sm border-0 rounded-lg d-flex flex-row align-items-center">
        <div class="fs-3 text-primary bg-primary-subtle p-3 rounded-3 me-3">
          <i class="fas fa-users"></i>
        </div>
        <div>
          <h6 class="text-body-secondary mb-0">Total Users</h6>
          <p class="fs-3 fw-bold mb-0 text-body-emphasis">{{ stats.totalUsers }}</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card bg-body p-3 shadow-sm border-0 rounded-lg d-flex flex-row align-items-center">
        <div class="fs-3 text-success bg-success-subtle p-3 rounded-3 me-3">
          <i class="fas fa-file-alt"></i>
        </div>
        <div>
          <h6 class="text-body-secondary mb-0">Total Posts</h6>
          <p class="fs-3 fw-bold mb-0 text-body-emphasis">{{ stats.totalPosts }}</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card bg-body p-3 shadow-sm border-0 rounded-lg d-flex flex-row align-items-center">
        <div class="fs-3 text-danger bg-danger-subtle p-3 rounded-3 me-3">
          <i class="fas fa-user-shield"></i>
        </div>
        <div>
          <h6 class="text-body-secondary mb-0">User Reports</h6>
          <p class="fs-3 fw-bold mb-0 text-body-emphasis">{{ stats.pendingUserReports }}</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card bg-body p-3 shadow-sm border-0 rounded-lg d-flex flex-row align-items-center">
        <div class="fs-3 text-warning bg-warning-subtle p-3 rounded-3 me-3">
          <i class="fas fa-robot"></i>
        </div>
        <div>
          <h6 class="text-body-secondary mb-0">Flagged Content</h6>
          <p class="fs-3 fw-bold mb-0 text-body-emphasis">{{ stats.pendingSentimentReports }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const stats = ref({
  totalUsers: 0,
  totalPosts: 0,
  pendingUserReports: 0,
  pendingSentimentReports: 0
});

const fetchStats = async () => {
  try {
    const res = await axios.get('/api/admin/dashboard'); 
    stats.value = res.data.stats;
  } catch (error) {
    console.error("Failed to load stats:", error);
  }
};

onMounted(fetchStats);
</script>