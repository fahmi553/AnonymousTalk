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
          <h5 class="fw-bold mb-0 text-body-emphasis">View Reported {{ reportData.type }}</h5>
          <span class="badge text-bg-primary">{{ reportData.content.category }}</span>
        </div>
        
        <div class="card-body p-4">
          <h6 class="text-body-secondary">Context / Title</h6>
          <p class="fs-4 fw-bold text-body-emphasis">{{ reportData.content.title }}</p>

          <h6 class="text-body-secondary mt-3">Content</h6>
          <div class="fs-5 bg-body-tertiary p-3 rounded-3" style="white-space: pre-line;">
            {{ reportData.content.body }}
          </div>
          
          <div v-if="reportData.content.author" class="mt-2 text-muted small">
             Author: <strong>{{ reportData.content.author }}</strong>
          </div>

          <hr class="my-4">

          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-success" @click="takeAction('approve')">
              <i class="fas fa-check me-2"></i> Approve (Safe)
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
            Reports ({{ reportData.reports.length }})
          </h6>
        </div>
        <div class="list-group list-group-flush">
          <div v-if="reportData.reports.length === 0" class="p-4 text-center text-muted">
             No reports found (Automated Flag only).
          </div>
          <div v-for="report in reportData.reports" :key="report.id" class="list-group-item bg-body px-4 py-3">
            <h6 class="text-danger">Reason: {{ report.reason }}</h6>
            <p class="mb-1">
              "{{ report.details || 'No details provided.' }}"
            </p>
            <small class="text-body-secondary">
              Reported by: <strong>{{ report.reporter?.username || 'Automated System' }}</strong>
              <span class="mx-1">•</span>
              {{ new Date(report.created_at).toLocaleDateString() }}
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
const id = route.params.id;
onMounted(async () => {
  try {
    const res = await axios.get(`/api/admin/report-details/${id}`);
    reportData.value = res.data;
  } catch (err) {
    console.error("Failed to load report details:", err);
    error.value = "Failed to load content. It may have been deleted.";
  } finally {
    loading.value = false;
  }
});

const takeAction = async (action) => {
  const type = reportData.value.type;
  
  if (!confirm(`Are you sure you want to ${action} this ${type}?`)) return;

  try {
    const endpoint = `/api/moderate/${type.toLowerCase()}/${id}/${action}`;
    
    await axios.post(endpoint);
    
    alert(`${type} successfully ${action}d.`);
    router.push('/admin/dashboard');

  } catch (err) {
    console.error(`Failed to ${action} ${type}:`, err);
    alert(`Failed to ${action} the item. Please try again.`);
  }
};
</script>