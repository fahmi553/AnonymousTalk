<template>
  <div class="container mt-5">
    <h2 class="mb-4 text-center">Admin Dashboard</h2>

    <div class="row g-3">
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h5>Total Users</h5>
          <p class="fs-3">{{ stats.totalUsers }}</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h5>Total Posts</h5>
          <p class="fs-3">{{ stats.totalPosts }}</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card p-3 shadow-sm">
          <h5>Total Reports</h5>
          <p class="fs-3">{{ stats.totalReports }}</p>
        </div>
      </div>
    </div>

    <div class="mt-5">
      <h4>Recent Reports</h4>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Type</th>
            <th>Reported By</th>
            <th>Reason</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="report in reports" :key="report.id">
            <td>{{ report.type }}</td>
            <td>{{ report.reported_by }}</td>
            <td>{{ report.reason }}</td>
            <td>
              <button class="btn btn-danger btn-sm" @click="deleteReport(report.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const stats = ref({
  totalUsers: 0,
  totalPosts: 0,
  totalReports: 0
})

const reports = ref([])

onMounted(async () => {
  const res = await axios.get('/api/admin/dashboard')
  stats.value = res.data.stats
  reports.value = res.data.reports
})

const deleteReport = async (id) => {
  if (confirm('Are you sure you want to delete this report?')) {
    await axios.delete(`/api/admin/reports/${id}`)
    reports.value = reports.value.filter(r => r.id !== id)
  }
}
</script>

<style scoped>
.table th,
.table td {
  vertical-align: middle;
}
</style>
