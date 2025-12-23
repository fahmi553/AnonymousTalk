<template>
  <div class="container mt-4" style="max-width: 900px;">

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

      <div class="card bg-body shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
          <h5 class="fw-bold mb-0 text-body-emphasis">
            Reviewing {{ reportData.type }} #{{ reportData.content.id }}
          </h5>

          <div class="d-flex gap-2 align-items-center">
             <span
                class="badge"
                :class="{
                    'text-bg-success': ['active', 'published', 'approved'].includes(reportData.content.status),
                    'text-bg-warning': ['hidden', 'moderated'].includes(reportData.content.status),
                    'text-bg-danger': reportData.content.status === 'flagged',
                    'text-bg-secondary': !reportData.content.status
                }"
             >
                {{ reportData.content.status || 'Unknown' }}
             </span>
             <span class="badge text-bg-primary" v-if="reportData.content.category">
                {{ reportData.content.category }}
             </span>
          </div>
        </div>

        <div class="card-body p-4">
          <div class="mb-4">
              <label class="small text-muted fw-bold text-uppercase">Title / Context</label>
              <h5 class="fw-bold text-body-emphasis">{{ reportData.content.title || 'No Title (Comment)' }}</h5>
          </div>

          <div class="mb-4">
              <label class="small text-muted fw-bold text-uppercase">Body Content</label>
              <div class="p-3 rounded-3 border bg-body-tertiary">
                  <p class="mb-0" style="white-space: pre-wrap;">{{ reportData.content.body }}</p>
              </div>
          </div>

          <div class="d-flex justify-content-between align-items-center text-muted small">
             <div>
                Author: <strong class="text-body-emphasis">{{ reportData.content.author }}</strong>
             </div>
             <div>
                Created: {{ new Date(reportData.content.created_at).toLocaleString() }}
             </div>
          </div>

          <hr class="my-4">

          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <button class="btn btn-success px-4" @click="openActionModal('approve')">
              <i class="fas fa-check-circle me-2"></i> Keep / Approve
            </button>
            <button class="btn btn-warning px-4 text-dark" @click="openActionModal('hide')">
              <i class="fas fa-eye-slash me-2"></i> Hide Content
            </button>
            <button class="btn btn-danger px-4" @click="openActionModal('delete')">
              <i class="fas fa-trash-alt me-2"></i> Delete
            </button>
          </div>
        </div>
      </div>

      <div class="card bg-body shadow-sm border-0 rounded-3">
        <div class="card-header bg-body py-3">
          <h6 class="fw-bold mb-0 text-body-emphasis">
            <i class="fas fa-users-slash me-2 text-danger"></i>
            User Reports ({{ reportData.reports.length }})
          </h6>
        </div>
        <div class="list-group list-group-flush">
          <div v-if="reportData.reports.length === 0" class="p-4 text-center text-muted fst-italic">
              Automated system flag only. No user reports.
          </div>
          <div v-for="report in reportData.reports" :key="report.id" class="list-group-item bg-body px-4 py-3">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                    {{ report.reason }}
                </span>
                <small class="text-muted">{{ new Date(report.created_at).toLocaleDateString() }}</small>
            </div>
            <p class="mb-1 text-body-emphasis">
              {{ report.details || 'No additional details provided.' }}
            </p>
            <small class="text-body-secondary">
              Reported by: <strong>{{ report.reporter?.username || 'System' }}</strong>
            </small>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="actionModal" tabindex="-1" aria-hidden="true" ref="modalElement">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header text-white" :class="modalHeaderClass">
            <h5 class="modal-title fw-bold">
                <i :class="modalIcon" class="me-2"></i> {{ modalTitle }}
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-4">
            <p class="fs-5 text-center mb-0">{{ modalBody }}</p>
            <p class="text-center text-muted small mt-2">This action cannot be undone.</p>
          </div>
          <div class="modal-footer border-0 justify-content-center pb-4">
            <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
            <button
                type="button"
                class="btn px-4"
                :class="modalButtonClass"
                @click="confirmAction"
                :disabled="processing"
            >
                <span v-if="processing" class="spinner-border spinner-border-sm me-2"></span>
                Confirm {{ selectedAction }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
      <div id="liveToast" class="toast align-items-center text-white border-0 shadow" :class="toastClass" role="alert" aria-live="assertive" aria-atomic="true" ref="toastElement">
        <div class="d-flex">
          <div class="toast-body fs-6">
            <i :class="toastIcon" class="me-2"></i> {{ toastMessage }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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
const id = route.params.id;
const reportData = ref(null);
const loading = ref(true);
const error = ref('');
const processing = ref(false);
const modalElement = ref(null);
const selectedAction = ref('');
let bsModal = null;
const toastElement = ref(null);
const toastMessage = ref('');
const toastClass = ref('bg-success');
const toastIcon = ref('fas fa-check-circle');
let bsToast = null;

const modalHeaderClass = computed(() => {
    switch(selectedAction.value) {
        case 'delete': return 'bg-danger';
        case 'hide': return 'bg-warning text-dark';
        case 'approve': return 'bg-success';
        default: return 'bg-primary';
    }
});

const modalButtonClass = computed(() => {
    switch(selectedAction.value) {
        case 'delete': return 'btn-danger';
        case 'hide': return 'btn-warning';
        case 'approve': return 'btn-success';
        default: return 'btn-primary';
    }
});

const modalTitle = computed(() => {
    switch(selectedAction.value) {
        case 'delete': return 'Confirm Deletion';
        case 'hide': return 'Confirm Hide';
        case 'approve': return 'Confirm Approval';
        default: return 'Confirm';
    }
});

const modalIcon = computed(() => {
    switch(selectedAction.value) {
        case 'delete': return 'fas fa-trash-alt';
        case 'hide': return 'fas fa-eye-slash';
        case 'approve': return 'fas fa-check-circle';
        default: return 'fas fa-info-circle';
    }
});

const modalBody = computed(() => {
    const type = reportData.value?.type || 'item';
    switch(selectedAction.value) {
        case 'delete': return `Are you sure you want to permanently delete this ${type}?`;
        case 'hide': return `Are you sure you want to hide this ${type} from the public?`;
        case 'approve': return `Are you sure you want to mark this ${type} as safe?`;
        default: return 'Proceed with action?';
    }
});

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

const openActionModal = (action) => {
    selectedAction.value = action;
    if (!bsModal) {
        bsModal = new Modal(modalElement.value);
    }
    bsModal.show();
};

const confirmAction = async () => {
    processing.value = true;
    const type = reportData.value.type.toLowerCase();

    try {
        await axios.post(`/api/moderate/${type}/${id}/${selectedAction.value}`);

        bsModal.hide();
        showToast(`Successfully ${selectedAction.value}d the ${type}.`, 'success');

        setTimeout(() => {
            router.push('/admin/dashboard');
        }, 1500);

    } catch (err) {
        console.error("Action failed:", err);
        bsModal.hide();
        showToast(`Failed to ${selectedAction.value}. check console.`, 'error');
    } finally {
        processing.value = false;
    }
};

const showToast = (message, type) => {
    toastMessage.value = message;
    if (type === 'success') {
        toastClass.value = 'bg-success';
        toastIcon.value = 'fas fa-check-circle';
    } else {
        toastClass.value = 'bg-danger';
        toastIcon.value = 'fas fa-exclamation-circle';
    }

    if (!bsToast) {
        bsToast = new Toast(toastElement.value);
    }
    bsToast.show();
};
</script>
