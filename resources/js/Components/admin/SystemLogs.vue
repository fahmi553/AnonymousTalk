<template>
  <div class="container mt-4">
    <h2 class="fw-bold text-body-emphasis mb-4">System Activity Logs</h2>

    <div class="card bg-body shadow-sm border-0 rounded-lg">
      <div class="card-header bg-body py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> Audit Trail</h6>
        <button class="btn btn-sm btn-outline-secondary" @click="fetchLogs">
          <i class="fas fa-sync-alt"></i> Refresh
        </button>
      </div>

      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" class="ps-4">Time</th>
                <th scope="col">User</th>
                <th scope="col">Action Type</th>
                <th scope="col">Reason / Details</th>
                <th scope="col" class="text-end pe-4">Score Impact</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="logs.data.length === 0">
                <td colspan="5" class="text-center py-4 text-muted">No system logs found.</td>
              </tr>

              <tr v-for="log in logs.data" :key="log.log_id">
                <td class="ps-4 text-secondary small">
                  {{ new Date(log.created_at || log.timestamp).toLocaleString() }}
                </td>

                <td>
                  <span v-if="log.user" class="fw-semibold text-primary">
                    {{ log.user.username }}
                  </span>
                  <span v-else class="text-muted fst-italic">System/Unknown</span>
                </td>

                <td>
                  <span 
                    class="badge"
                    :class="{
                      'text-bg-success': log.action_type === 'reward' || log.action_type === 'post_approved',
                      'text-bg-danger': log.action_type === 'ban' || log.action_type === 'post_flagged',
                      'text-bg-warning': log.action_type === 'warn' || log.action_type === 'admin_adjustment',
                      'text-bg-secondary': !['reward', 'ban', 'warn', 'post_approved', 'post_flagged'].includes(log.action_type)
                    }"
                  >
                    {{ formatAction(log.action_type) }}
                  </span>
                </td>

                <td class="text-muted small">
                  {{ log.reason }}
                </td>

                <td class="text-end pe-4">
                  <span 
                    class="fw-bold"
                    :class="{
                      'text-success': log.score_change > 0,
                      'text-danger': log.score_change < 0,
                      'text-muted': log.score_change === 0
                    }"
                  >
                    {{ log.score_change > 0 ? '+' : '' }}{{ log.score_change }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="card-footer bg-body py-3 d-flex justify-content-end" v-if="logs.meta">
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: !logs.links.prev }">
              <button class="page-link" @click="changePage(logs.links.prev)">Previous</button>
            </li>
            <li class="page-item" :class="{ disabled: !logs.links.next }">
              <button class="page-link" @click="changePage(logs.links.next)">Next</button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AdminStats from './AdminStats.vue';

const logs = ref({ data: [], links: {}, meta: {} });
const loading = ref(false);

const fetchLogs = async (url = '/api/admin/logs') => {
  loading.value = true;
  try {
    const res = await axios.get(url);
    logs.value = res.data;
  } catch (err) {
    console.error("Failed to fetch logs", err);
  } finally {
    loading.value = false;
  }
};

const changePage = (url) => {
  if (url) fetchLogs(url);
};
const formatAction = (string) => {
  if (!string) return 'Unknown';
  return string.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};

onMounted(() => {
  fetchLogs();
});
</script>