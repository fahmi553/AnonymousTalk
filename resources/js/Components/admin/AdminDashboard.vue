<template>
  <div class="container mt-4">
    <h2 class="mb-4 fw-bold text-body-emphasis">Admin Dashboard</h2>

    <AdminStats />

    <div class="card bg-body shadow-sm border-0 rounded-lg">
      <div class="card-header bg-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="fw-bold mb-0 text-body-emphasis me-auto">
          {{ activeTab === 'user' ? 'Pending User Reports' : 'Flagged Content (AI)' }}
        </h6>

        <div class="d-flex gap-2">
          <select v-model="filterSeverity" class="form-select form-select-sm bg-body" style="width: auto;">
            <option value="All">All Severities</option>
            <option value="High">High Priority</option>
            <option value="Medium">Medium Priority</option>
            <option value="Low">Low Priority</option>
          </select>

          <select v-model="filterType" class="form-select form-select-sm bg-body" style="width: auto;">
            <option value="All">All Types</option>
            <option value="Post">Posts Only</option>
            <option value="Comment">Comments Only</option>
            <option value="User">Users Only</option>
          </select>

          <div class="input-group input-group-sm" style="max-width: 250px;">
            <span class="input-group-text bg-body-tertiary"><i class="fas fa-search"></i></span>
            <input
              type="text"
              class="form-control bg-body"
              placeholder="Search reasons..."
              v-model="searchTerm"
            />
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle bg-body mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" class="ps-3">Type</th>
                <th scope="col">Severity</th>

                <th scope="col" :style="{ width: activeTab === 'user' ? '25%' : '45%' }">
                  Reported Content (Snippet)
                </th>

                <th scope="col">Reason</th>

                <th scope="col" v-if="activeTab === 'user'">Reported By</th>

                <th scope="col">Time</th>
                <th scope="col" class="text-end pe-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredReports.length === 0">
                <td :colspan="activeTab === 'user' ? 7 : 6" class="text-center text-muted p-4">
                  <span v-if="!searchTerm && filterType === 'All' && filterSeverity === 'All'">
                    No pending reports found. Good job!
                  </span>
                  <span v-else>No reports found matching your criteria.</span>
                </td>
              </tr>

              <tr v-for="report in filteredReports" :key="report.id">
                <td class="ps-3">
                  <span
                    class="badge"
                    :class="{
                      'text-bg-primary': report.type === 'Post',
                      'text-bg-info': report.type === 'Comment',
                      'text-bg-warning': report.type === 'User'
                    }"
                  >{{ report.type }}</span>
                </td>

                <td>
                  <span
                    class="badge rounded-pill"
                    :class="getSeverityBadge(report.reason)"
                  >
                    {{ getSeverityLabel(report.reason) }}
                  </span>
                </td>

                <td :style="{ maxWidth: activeTab === 'user' ? '250px' : '450px' }">
                  <div v-if="getContent(report)" class="text-truncate" :title="getContent(report)">
                    <span class="text-muted fst-italic small">
                      "{{ truncate(getContent(report), activeTab === 'user' ? 40 : 80) }}"
                    </span>
                  </div>
                  <div v-else class="text-muted small">
                    <i class="fas fa-ban me-1"></i> Content unavailable
                  </div>
                </td>

                <td>{{ report.reason }}</td>

                <td v-if="activeTab === 'user'">
                  <div>
                    {{ report.reported_by }}
                  </div>
                </td>

                <td class="small text-muted">
                  {{ timeAgo(report.created_at) }}
                </td>

                <td class="text-end pe-3">

                  <router-link
                    v-if="report.type === 'Post' || report.type === 'Comment'"
                    :to="{ name: 'AdminReportDetail', params: { id: report.reportable_id } }"
                    class="btn btn-sm me-2 text-white"
                    :class="{
                      'btn-primary': report.type === 'Post',
                      'btn-info': report.type === 'Comment'
                    }"
                  >
                    View
                  </router-link>

                  <router-link
                    v-else-if="report.type === 'User'"
                    :to="{ name: 'ReportUserDetails', params: { id: report.reportable_id } }"
                    class="btn btn-warning btn-sm me-2"
                  >
                    View
                  </router-link>

                  <button class="btn btn-outline-danger btn-sm" @click="deleteReport(report.id)">
                    Dismiss
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import AdminStats from './AdminStats.vue'

const route = useRoute()

const stats = ref({
  totalUsers: 0,
  totalPosts: 0,
  pendingUserReports: 0,
  pendingSentimentReports: 0
})

const userReports = ref([])
const sentimentReports = ref([])
const searchTerm = ref('')
const filterType = ref('All')
const filterSeverity = ref('All')
const loading = ref(true)
const activeTab = computed(() => route.query.tab || 'user')

const filteredReports = computed(() => {
  const activeList = activeTab.value === 'user' ? userReports.value : sentimentReports.value;

  const result = activeList.filter(report => {
    const severityLabel = getSeverityLabel(report.reason);

    const typeMatch = filterType.value === 'All' || report.type === filterType.value;
    const severityMatch = filterSeverity.value === 'All' || severityLabel === filterSeverity.value;

    const searchMatch = !searchTerm.value ||
           report.reason.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
           report.reported_by.toLowerCase().includes(searchTerm.value.toLowerCase());

    return typeMatch && severityMatch && searchMatch;
  });

  return result.sort((a, b) => {
      const severityWeight = { 'High': 3, 'Medium': 2, 'Low': 1 };

      const severityA = severityWeight[getSeverityLabel(a.reason)] || 0;
      const severityB = severityWeight[getSeverityLabel(b.reason)] || 0;

      return severityB - severityA;
  });
});


const getContent = (report) => {
    if (report.content) return report.content;
    if (report.details) return report.details;
    if (report.reportable) {
        return report.reportable.content || report.reportable.body || report.reportable.username || '';
    }
    return '';
};

const truncate = (text, length) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};

const getSeverityLabel = (reason) => {
    const r = (reason || '').toLowerCase();
    if (r.includes('hate') || r.includes('threat') || r.includes('suicide') || r.includes('harm') || r.includes('sexual')) return 'High';
    if (r.includes('spam') || r.includes('ad') || r.includes('bot')) return 'Low';
    return 'Medium';
};

const getSeverityBadge = (reason) => {
    const label = getSeverityLabel(reason);
    if (label === 'High') return 'bg-danger';
    if (label === 'Low') return 'bg-secondary';
    return 'bg-warning text-dark';
};

const timeAgo = (dateParam) => {
    if (!dateParam) return '';
    const date = new Date(dateParam);
    const now = new Date();
    const seconds = Math.round((now - date) / 1000);
    const minutes = Math.round(seconds / 60);
    const hours = Math.round(minutes / 60);
    const days = Math.round(hours / 24);

    if (seconds < 60) return 'Just now';
    if (minutes < 60) return `${minutes} min ago`;
    if (hours < 24) return `${hours} hrs ago`;
    if (days === 1) return 'Yesterday';
    return `${days} days ago`;
};

const fetchData = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/admin/dashboard');
    stats.value = res.data.stats;
    userReports.value = res.data.userReports;
    sentimentReports.value = res.data.sentimentReports;
  } catch (error) {
    console.error("Failed to load dashboard data:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchData);

const deleteReport = async (id) => {
  if (confirm('Are you sure you want to dismiss this report?')) {
    try {
      await axios.delete(`/api/admin/reports/${id}`);
      fetchData();
    } catch (error) {
      console.error("Failed to delete report:", error);
      alert("Could not delete the report.");
    }
  }
}
</script>

<style scoped>
.table th,
.table td {
  vertical-align: middle;
  white-space: nowrap;
}
</style>
