<template>
  <div class="container mt-4">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Loading report details...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger shadow-sm">
      <i class="fas fa-exclamation-triangle me-2"></i> {{ error }}
    </div>

    <div v-else-if="reportData">
      <router-link to="/admin/dashboard" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
      </router-link>
      <div class="card bg-body shadow-sm border-0 rounded-lg">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
          <h5 class="fw-bold mb-0 text-body-emphasis">View Reported Post</h5>
          <span class="badge text-bg-primary">{{ reportData.post.category }}</span>
        </div>
        <div class="card-body p-4">
          <h6 class="text-body-secondary">Post Title</h6>
          <p class="fs-4 fw-bold text-body-emphasis">{{ reportData.post.title }}</p>

          <h6 class="text-body-secondary mt-3">Post Content</h6>
          <p class="fs-5 bg-body-tertiary p-3 rounded-3" style="white-space: pre-line;">
            {{ reportData.post.content }}
          </p>

          <hr class="my-4">
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-success" @click="takeAction('approve')">
              <i class="fas fa-check me-2"></i> Approve
            </button>
            <button class="btn btn-warning" @click="takeAction('hide')">
              <i class="fas fa-eye-slash me-2"></i> Hide
            </button>
            <button class="btn btn-danger" @click="takeAction('delete')">
              <i class="fas fa-trash me-2"></i> Delete
            </button>
          </div>
        </div>
      </div>
      <div class="card bg-body shadow-sm border-0 rounded-lg mt-4">
        <div class="card-header bg-body py-3">
          <h6 class="fw-bold mb-0 text-body-emphasis">
            Reports for this Post ({{ reportData.reports.length }})
          </h6>
        </div>
        <div class="list-group list-group-flush">
          <div v-for="report in reportData.reports" :key="report.id" class="list-group-item bg-body px-4 py-3">
            <h6 class="text-danger">Reason: {{ report.reason }}</h6>
            <p class="mb-1">
              "{{ report.details || 'No details provided.' }}"
            </p>
            <small class="text-body-secondary">
              Reported by: <strong>{{ report.reporter.username || 'Unknown' }}</strong>
            </small>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const reportData = ref(null);
const loading = ref(true);
const error = ref('');
const postId = route.params.id;
onMounted(async () => {
  try {
    const res = await axios.get(`/api/admin/report-details/${postId}`);
    reportData.value = res.data;
  } catch (err) {
    console.error("Failed to load report details:", err);
    error.value = "Failed to load report details. The post may have been deleted.";
  } finally {
    loading.value = false;
  }
});
const takeAction = async (action) => {
  if (!confirm(`Are you sure you want to ${action} this post?`)) return;

  try {
    await axios.post(`/api/moderate/post/${postId}/${action}`);
    alert(`Post successfully ${action}d.`);
    router.push('/admin/dashboard');

  } catch (err) {
    console.error(`Failed to ${action} post:`, err);
    alert(`Failed to ${action} the post. Please try again.`);
  }
};
</script>
