<template>
  <div class="container mt-4">

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1060">
      <div id="liveToast" class="toast align-items-center text-white border-0" :class="toastClass" role="alert" aria-live="assertive" aria-atomic="true" ref="toastEl">
        <div class="d-flex">
          <div class="toast-body">
            <i class="fas me-2" :class="toastIcon"></i> {{ toastMessage }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Loading user details...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger shadow-sm">
      <i class="fas fa-exclamation-triangle me-2"></i> {{ error }}
    </div>

    <div v-else-if="reportData">
      <router-link to="/admin/reports" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
      </router-link>

      <div class="card bg-body shadow-sm border-0 rounded-lg">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
             <h5 class="fw-bold mb-0 text-body-emphasis">Reported User Profile</h5>
             <span v-if="reportData.user.banned_at" class="badge bg-danger">
                <i class="fas fa-ban me-1"></i> BANNED
             </span>
          </div>

          <span
            class="badge rounded-pill px-3 py-2"
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
                :src="getAvatarUrl(reportData.user.avatar)"
                @error="$event.target.src = '/images/avatars/default.jpg'"
                class="rounded-circle border me-4 shadow-sm"
                width="80"
                height="80"
                style="object-fit: cover;"
                alt="User Avatar"
            >
            <div>
              <h4 class="fw-bold text-body-emphasis mb-1">{{ reportData.user.username }}</h4>
              <p class="text-body-secondary mb-0">{{ reportData.user.email }}</p>
              <small class="text-muted d-block">Joined: {{ formatDate(reportData.user.created_at) }}</small>

              <div class="mt-2 d-flex flex-wrap gap-2">
                <span
                  class="badge"
                  :class="reportData.user.banned_at ? 'text-bg-danger' : 'text-bg-success'"
                >
                  <i class="fas me-1" :class="reportData.user.banned_at ? 'fa-ban' : 'fa-check-circle'"></i>
                  {{ reportData.user.banned_at ? 'Banned' : 'Active' }}
                </span>
                <span class="badge text-bg-secondary">
                  <i class="fas fa-user-shield me-1"></i>
                  {{ reportData.user.role ? reportData.user.role.toUpperCase() : 'USER' }}
                </span>
                <span class="badge text-bg-info">
                  <i class="fas fa-flag me-1"></i>
                  Reports: {{ reportData.reports.length }}
                </span>
              </div>

              <div class="mt-3">
                <h6 class="fw-bold small text-body-emphasis mb-2">Earned Badges</h6>
                <div
                  v-if="reportData.user.badges && reportData.user.badges.length > 0"
                  class="d-flex flex-wrap gap-2"
                >
                  <div
                    v-for="badge in reportData.user.badges"
                    :key="badge.badge_id || badge.id"
                    class="badge rounded-pill border border-secondary-subtle bg-body-tertiary text-body-emphasis d-inline-flex align-items-center p-1 pe-3"
                    :title="badge.description"
                  >
                    <img
                      v-if="badge.icon_url"
                      :src="badge.icon_url"
                      :alt="badge.badge_name"
                      width="20"
                      height="20"
                      class="me-2"
                    />
                    <span class="fw-medium">{{ badge.badge_name }}</span>
                  </div>
                </div>
                <span v-else class="text-muted small">No badges earned yet.</span>
              </div>

              <div v-if="reportData.user.banned_at" class="mt-2 p-2 bg-danger-subtle text-danger rounded border border-danger-subtle small">
                 <strong><i class="fas fa-info-circle me-1"></i> Ban Reason:</strong> {{ reportData.user.ban_reason }}
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-primary" @click="showAdjustmentForm = !showAdjustmentForm">
              <i class="fas fa-sliders-h me-2"></i> Adjust Score
            </button>

            <button class="btn btn-success" @click="openActionModal('dismiss')">
              <i class="fas fa-check me-2"></i> Dismiss Reports
            </button>

            <button class="btn btn-warning" @click="openActionModal('warn')">
              <i class="fas fa-exclamation-triangle me-2"></i> Warn User
            </button>

            <button v-if="!reportData.user.banned_at" class="btn btn-danger" @click="openActionModal('ban')">
              <i class="fas fa-ban me-2"></i> Ban User
            </button>
            <button v-else class="btn btn-secondary" @click="openActionModal('unban')">
              <i class="fas fa-unlock me-2"></i> Unban User
            </button>
          </div>

          <div v-if="showAdjustmentForm" class="card mt-3 bg-body-tertiary border-primary slide-in">
            <div class="card-body">
              <h6 class="fw-bold mb-3 text-body-emphasis">Manual Trust Score Adjustment</h6>
              <div class="row g-3">
                <div class="col-md-3">
                  <label class="form-label small fw-bold text-body-secondary">Score Change (+/-)</label>
                  <input
                    type="number"
                    v-model="adjustment.score"
                    class="form-control bg-body"
                    placeholder="-10"
                  >
                </div>
                <div class="col-md-7">
                  <label class="form-label small fw-bold text-body-secondary">Reason for Adjustment</label>
                  <input
                    type="text"
                    v-model="adjustment.reason"
                    class="form-control bg-body"
                    placeholder="e.g. Correcting false report"
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
            <h6 class="text-danger mb-1"><i class="fas fa-flag me-2"></i>Reason: {{ report.reason }}</h6>
            <p class="mb-2 text-body-secondary fst-italic">
              "{{ report.details || 'No additional details provided.' }}"
            </p>
            <small class="text-muted border-top pt-2 d-block">
              Reported by: <strong>{{ report.reporter?.username || 'Anonymous' }}</strong>
              <span class="mx-1">•</span>
              {{ formatDate(report.created_at) }}
            </small>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true" ref="confirmationModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body shadow-lg border-0 rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold" :class="modalTitleClass">
                <i class="fas me-2" :class="modalIcon"></i>{{ modalTitle }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-4">
            <p class="mb-3 fs-6 text-body-secondary">{{ modalMessage }}</p>

            <div v-if="['warn', 'ban'].includes(selectedAction)">
                <label class="form-label fw-bold small text-muted">Reason (Required)</label>
                <textarea
                    v-model="actionReason"
                    class="form-control bg-body-tertiary border-secondary-subtle"
                    rows="3"
                    :placeholder="selectedAction === 'ban' ? 'E.g. Repeated violation of hate speech rules...' : 'E.g. Please be respectful in comments...'"
                ></textarea>
                <div v-if="actionError" class="text-danger small mt-1">
                    <i class="fas fa-exclamation-circle me-1"></i> Please provide a reason.
                </div>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button
                type="button"
                class="btn rounded-pill px-4 fw-bold text-white"
                :class="modalBtnClass"
                @click="executeAction"
                :disabled="processing"
            >
                <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                Confirm
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { Modal, Toast } from 'bootstrap';

const route = useRoute();
const router = useRouter();
const userId = route.params.id;
const reportData = ref(null);
const loading = ref(true);
const error = ref('');
const showAdjustmentForm = ref(false);
const processing = ref(false);
const adjustment = ref({ score: 0, reason: '' });
const confirmationModal = ref(null);
let modalInstance = null;
const selectedAction = ref('');
const actionReason = ref('');
const actionError = ref(false);
const toastEl = ref(null);
let toastInstance = null;
const toastMessage = ref('');
const toastClass = ref('bg-success');
const toastIcon = ref('fa-check-circle');
const modalTitle = computed(() => {
    switch(selectedAction.value) {
        case 'dismiss': return 'Dismiss Reports';
        case 'warn': return 'Warn User';
        case 'ban': return 'Ban User';
        case 'unban': return 'Unban User';
        default: return 'Confirm Action';
    }
});

const modalMessage = computed(() => {
    switch(selectedAction.value) {
        case 'dismiss': return 'Are you sure you want to dismiss all pending reports? This will clear them from the queue.';
        case 'warn': return 'This will send a warning to the user and deduct 10 Trust Score.';
        case 'ban': return 'This will suspend the user account. They will be logged out immediately.';
        case 'unban': return 'Are you sure you want to restore access for this user?';
        default: return 'Are you sure?';
    }
});

const modalTitleClass = computed(() => {
    if (selectedAction.value === 'dismiss') return 'text-success';
    if (selectedAction.value === 'warn') return 'text-warning';
    if (['ban', 'unban'].includes(selectedAction.value)) return 'text-danger';
    return 'text-primary';
});

const modalIcon = computed(() => {
    if (selectedAction.value === 'dismiss') return 'fa-check-circle';
    if (selectedAction.value === 'warn') return 'fa-exclamation-triangle';
    if (selectedAction.value === 'ban') return 'fa-ban';
    if (selectedAction.value === 'unban') return 'fa-unlock';
    return 'fa-info-circle';
});

const modalBtnClass = computed(() => {
    if (selectedAction.value === 'dismiss') return 'btn-success';
    if (selectedAction.value === 'warn') return 'btn-warning';
    if (selectedAction.value === 'ban') return 'btn-danger';
    if (selectedAction.value === 'unban') return 'btn-secondary';
    return 'btn-primary';
});


const getAvatarUrl = (filename) => {
  if (!filename) return '/images/avatars/default.jpg';
  if (filename.startsWith('http')) return filename;
  return `/images/avatars/${filename}`;
};

const formatDate = (dateStr) => {
    if (!dateStr) return 'Unknown';
    return new Date(dateStr).toLocaleDateString();
};


const showToast = (message, type = 'success') => {
    toastMessage.value = message;
    if (type === 'success') {
        toastClass.value = 'bg-success';
        toastIcon.value = 'fa-check-circle';
    } else {
        toastClass.value = 'bg-danger';
        toastIcon.value = 'fa-times-circle';
    }

    if (!toastInstance && toastEl.value) {
        toastInstance = new Toast(toastEl.value);
    }
    if (toastInstance) toastInstance.show();
};


const fetchDetails = async () => {
    try {
        const res = await axios.get(`/api/admin/user-report-details/${userId}`);
        reportData.value = res.data;
    } catch (err) {
        console.error("Failed to load details:", err);
        error.value = "Failed to load details. The user may have been deleted.";
    } finally {
        loading.value = false;
    }
};

const submitAdjustment = async () => {
  if (adjustment.value.score === 0 || !adjustment.value.reason) {
    showToast("Please provide a score change and reason.", "error");
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

    showToast("Trust score updated successfully.");
    showAdjustmentForm.value = false;
    adjustment.value = { score: 0, reason: '' };
  } catch (err) {
    console.error("Failed to adjust score:", err);
    showToast("Error updating score.", "error");
  } finally {
    processing.value = false;
  }
};

const openActionModal = (action) => {
    selectedAction.value = action;
    actionReason.value = '';
    actionError.value = false;

    if (!modalInstance && confirmationModal.value) {
        modalInstance = new Modal(confirmationModal.value);
    }
    if (modalInstance) modalInstance.show();
};

const executeAction = async () => {
  if (['warn', 'ban'].includes(selectedAction.value) && !actionReason.value.trim()) {
      actionError.value = true;
      return;
  }

  processing.value = true;
  actionError.value = false;

  try {
    const res = await axios.post(`/api/moderate/user/${userId}/${selectedAction.value}`, {
        reason: actionReason.value
    });

    if (modalInstance) modalInstance.hide();
    showToast(res.data.message);

    if (res.data.user_status) {
        reportData.value.user.trust_score = res.data.user_status.trust_score;
        reportData.value.user.banned_at = res.data.user_status.banned_at;
        if (selectedAction.value === 'ban') {
            reportData.value.user.ban_reason = actionReason.value;
        }
    }

    if (selectedAction.value === 'dismiss') await fetchDetails();

  } catch (err) {
    console.error(`Failed to ${selectedAction.value} user:`, err);
    if (modalInstance) modalInstance.hide();
    showToast(`Failed to ${selectedAction.value} user.`, "error");
  } finally {
    processing.value = false;
  }
};

onMounted(() => {
  fetchDetails();
});
</script>

<style scoped>
.slide-in {
    animation: slideIn 0.3s ease-out;
}
@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
