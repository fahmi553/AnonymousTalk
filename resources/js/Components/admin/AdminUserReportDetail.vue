<template>
  <div class="container mt-4">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Loading user details...</p>
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
          <h5 class="fw-bold mb-0 text-body-emphasis">Reported User Profile</h5>
          <span 
            class="badge"
            :class="{
              'text-bg-success': reportData.user.trust_score >= 80,
              'text-bg-warning': reportData.user.trust_score < 80 && reportData.user.trust_score >= 50,
              'text-bg-danger': reportData.user.trust_score < 50
            }"
          >
            Trust Score: {{ reportData.user.trust_score }}
          </span>
        </div>
        <div class="card-body p-4">
          <div class="d-flex align-items-center mb-4">
            <img 
                src="https://i.pravatar.cc/150" 
                class="rounded-circle border me-4" 
                width="80" 
                height="80" 
                alt="User Avatar"
            >
            <div>
              <h4 class="fw-bold text-body-emphasis mb-1">{{ reportData.user.username }}</h4>
              <p class="text-body-secondary mb-0">{{ reportData.user.email }}</p>
              <small class="text-muted">Joined: {{ new Date(reportData.user.created_at).toLocaleDateString() }}</small>
            </div>
          </div>
          
          <hr class="my-4">

          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary" @click="showAdjustmentForm = !showAdjustmentForm">
              <i class="fas fa-sliders-h me-2"></i> Adjust Score
            </button>

            <button class="btn btn-success" @click="takeAction('dismiss')">
              <i class="fas fa-check me-2"></i> Dismiss Reports
            </button>
            <button class="btn btn-warning" @click="takeAction('warn')">
              <i class="fas fa-exclamation-triangle me-2"></i> Warn User
            </button>
            <button class="btn btn-danger" @click="takeAction('ban')">
              <i class="fas fa-ban me-2"></i> Ban User
            </button>
          </div>

          <div v-if="showAdjustmentForm" class="card mt-3 bg-light border-primary">
            <div class="card-body">
              <h6 class="fw-bold mb-3">Manual Trust Score Adjustment</h6>
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label small fw-bold">Score Change (+/-)</label>
                  <input 
                    type="number" 
                    v-model="adjustment.score" 
                    class="form-control" 
                    placeholder="e.g. -10 or 5"
                  >
                </div>
                <div class="col-md-7">
                  <label class="form-label small fw-bold">Reason for Adjustment</label>
                  <input 
                    type="text" 
                    v-model="adjustment.reason" 
                    class="form-control" 
                    placeholder="e.g. False accusation correction, good behavior..."
                  >
                </div>
                <div class="col-md-2 d-flex align-items-end">
                  <button 
                    class="btn btn-primary w-100" 
                    @click="submitAdjustment" 
                    :disabled="processing"
                  >
                    <span v-if="processing" class="spinner-border spinner-border-sm me-1"></span>
                    {{ processing ? 'Saving...' : 'Save' }}
                  </button>
                </div>
              </div>
              <div class="mt-2 text-end">
                 <button class="btn btn-link btn-sm text-muted text-decoration-none" @click="showAdjustmentForm = false">Cancel</button>
              </div>
            </div>
          </div>
          </div>
      </div>

      <div class="card bg-body shadow-sm border-0 rounded-lg mt-4">
        <div class="card-header bg-body py-3">
          <h6 class="fw-bold mb-0 text-body-emphasis">
            Reports against this User ({{ reportData.reports.length }})
          </h6>
        </div>
        <div class="list-group list-group-flush">
          <div v-if="reportData.reports.length === 0" class="p-4 text-center text-muted">
              No pending reports found for this user.
          </div>
          <div v-for="report in reportData.reports" :key="report.id" class="list-group-item bg-body px-4 py-3">
            <h6 class="text-danger">Reason: {{ report.reason }}</h6>
            <p class="mb-1 text-body-secondary">
              "{{ report.details || 'No additional details provided.' }}"
            </p>
            <small class="text-muted">
              Reported by: <strong>{{ report.reporter?.username || 'Unknown' }}</strong>
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
const userId = route.params.id;
const showAdjustmentForm = ref(false);
const processing = ref(false);
const adjustment = ref({
  score: 0,
  reason: ''
});
onMounted(async () => {
  try {
    const res = await axios.get(`/api/admin/user-report-details/${userId}`);
    reportData.value = res.data;
  } catch (err) {
    console.error("Failed to load user report details:", err);
    error.value = "Failed to load details. The user may have been deleted.";
  } finally {
    loading.value = false;
  }
});
const submitAdjustment = async () => {
  if (adjustment.value.score === 0 || !adjustment.value.reason) {
    alert("Please provide a valid score change (not 0) and a reason.");
    return;
  }

  processing.value = true;
  try {
    const res = await axios.post(`/api/admin/user/${userId}/adjust-score`, {
      score_change: adjustment.value.score,
      reason: adjustment.value.reason
    });
    if (reportData.value && reportData.value.user) {
        reportData.value.user.trust_score = res.data.new_score;
    }
    
    alert("Trust score updated successfully.");
    showAdjustmentForm.value = false;
    adjustment.value = { score: 0, reason: '' };

  } catch (err) {
    console.error("Failed to adjust score:", err);
    alert("Error updating score. Please check console.");
  } finally {
    processing.value = false;
  }
};
const takeAction = async (action) => {
  if (!confirm(`Are you sure you want to ${action} this user?`)) return;

  try {
    await axios.post(`/api/moderate/user/${userId}/${action}`);
    
    alert(`User successfully ${action}ed.`);
    router.push('/admin/dashboard');

  } catch (err) {
    console.error(`Failed to ${action} user:`, err);
     alert(`Failed to ${action} user. Please check console.`);
  }
};
</script>