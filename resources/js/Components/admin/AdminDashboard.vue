<template>
  <div class="container mt-4">
    <h2 class="mb-4 fw-bold text-body-emphasis">Admin Dashboard</h2>
    <div class="row g-3">
      <div class="col-md-4">
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
      <div class="col-md-4">
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
      <div class="col-md-4">
        <div class="card bg-body p-3 shadow-sm border-0 rounded-lg d-flex flex-row align-items-center">
          <div class="fs-3 text-danger bg-danger-subtle p-3 rounded-3 me-3">
            <i class="fas fa-flag"></i>
          </div>
          <div>
            <h6 class="text-body-secondary mb-0">Pending Reports</h6>
            <p class="fs-3 fw-bold mb-0 text-body-emphasis">{{ stats.totalReports }}</p>
          </div>
        </div>
      </div>
    </div>
    <div class="row g-4 mt-4">
      <div class="col-lg-3">
        <div class="card bg-body shadow-sm border-0 rounded-lg">
          <div class="card-header bg-body py-3">
            <h6 class="fw-bold mb-0 text-body-emphasis">Management</h6>
          </div>
          <div class="list-group list-group-flush">
            <a
              href="#"
              class="list-group-item list-group-item-action"
              :class="{ 'active': filterType === 'All' }"
              @click.prevent="filterType = 'All'"
            >
              <i class="fas fa-inbox me-2"></i> All Reports
            </a>
            <a
              href="#"
              class="list-group-item list-group-item-action"
              :class="{ 'active': filterType === 'Post' }"
              @click.prevent="filterType = 'Post'"
            >
              <i class="fas fa-file-alt me-2"></i> Post Reports
            </a>
            <a
              href="#"
              class="list-group-item list-group-item-action"
              :class="{ 'active': filterType === 'Comment' }"
              @click.prevent="filterType = 'Comment'"
            >
              <i class="fas fa-comment me-2"></i> Comment Reports
            </a>
            <a
              href="#"
              class="list-group-item list-group-item-action"
              :class="{ 'active': filterType === 'User' }"
              @click.prevent="filterType = 'User'"
            >
              <i class="fas fa-user me-2"></i> User Reports
            </a>
          </div>
        </div>
      </div>
      <div class="col-lg-9">
        <div class="card bg-body shadow-sm border-0 rounded-lg">
          <div class="card-header bg-body py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="fw-bold mb-0 text-body-emphasis">{{ filterType }} Reports</h6>
            <div class="input-group" style="max-width: 250px;">
              <span class="input-group-text bg-body-tertiary"><i class="fas fa-search"></i></span>
              <input
                type="text"
                class="form-control bg-body"
                placeholder="Search reasons..."
                v-model="searchTerm"
              />
            </div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover bg-body mb-0">
                <thead class="table-light">
                  <tr>
                    <th scope="col" class="ps-3">Type</th>
                    <th scope="col">Reported By</th>
                    <th scope="col">Reason</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="filteredReports.length === 0">
                    <td colspan="4" class="text-center text-muted p-4">
                      <span v-if="!searchTerm">No {{ filterType }} reports found.</span>
                      <span v-else>No reports found matching your search.</span>
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
                    <td>{{ report.reported_by }}</td>
                    <td>{{ report.reason }}</td>
                    <td>
                      <router-link
                        :to="{ name: 'AdminReportDetail', params: { id: report.reportable_id } }"
                        class="btn btn-primary btn-sm me-2"
                        v-if="report.type === 'Post'"
                      >
                        View
                      </router-link>
                      <router-link
                        :to="{ name: 'AdminReportDetail', params: { id: report.post_id_for_comment } }"
                        class="btn btn-info btn-sm me-2"
                        v-else-if="report.type === 'Comment' && report.post_id_for_comment"
                      >
                        View
                      </router-link>
                      <a
                        :href="`/profile/${report.reportable_id}`"
                        target="_blank"
                        class="btn btn-warning btn-sm me-2"
                        v-else-if="report.type === 'User'"
                      >
                        View
                      </a>

                      <button class="btn btn-danger btn-sm" @click="deleteReport(report.id)">
                        Delete
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const stats = ref({
  totalUsers: 0,
  totalPosts: 0,
  totalReports: 0
})

const allReports = ref([])
const filterType = ref('All')
const searchTerm = ref('')

const filteredReports = computed(() => {
  return allReports.value.filter(report => {
    const typeMatch = filterType.value === 'All' || report.type === filterType.value;
    const searchMatch = !searchTerm.value ||
      report.reason.toLowerCase().includes(searchTerm.value.toLowerCase()) ||
      report.reported_by.toLowerCase().includes(searchTerm.value.toLowerCase());
    return typeMatch && searchMatch;
  });
});

const fetchData = async () => {
  try {
    const res = await axios.get('/api/admin/dashboard');
    stats.value = res.data.stats;
    allReports.value = res.data.reports;
  } catch (error) {
    console.error("Failed to load dashboard data:", error);
  }
};

onMounted(fetchData);

const deleteReport = async (id) => {
  if (confirm('Are you sure you want to delete this report?')) {
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
.list-group-item.active {
  border-left-width: 4px;
  border-left-color: var(--bs-primary);
  font-weight: 600;
}
</style>
