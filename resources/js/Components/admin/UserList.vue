<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-body-emphasis">View User Trust Scores</h2>
    </div>

    <div class="card bg-body shadow-sm border-0 rounded-lg">
      <div class="card-header bg-body py-3">
        <div class="row align-items-center">
          <div class="col-md-6">
            <h6 class="mb-0 fw-bold">All Users System List</h6>
          </div>
          <div class="col-md-6">
            <div class="input-group">
              <input 
                type="text" 
                class="form-control" 
                placeholder="Search by username..." 
                v-model="searchQuery" 
                @keyup.enter="fetchUsers"
              >
              <button class="btn btn-primary" type="button" @click="fetchUsers">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
        </div>

        <div v-else class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th scope="col" class="ps-4">User ID</th>
                <th scope="col">Username</th>
                <th scope="col" class="text-center">Earned Badge(s)</th>
                <th scope="col" class="text-center">Trust Score</th>
                <th scope="col" class="text-end pe-4">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="users.data.length === 0">
                <td colspan="5" class="text-center py-4 text-muted">No users found.</td>
              </tr>
              
              <tr v-for="user in users.data" :key="user.user_id">
                <td class="ps-4 text-muted">#{{ user.user_id }}</td>
                
                <td>
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                      <span class="fw-bold text-secondary">{{ user.username.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                      <p class="mb-0 fw-semibold">{{ user.username }}</p>
                      <small class="text-muted">{{ user.email }}</small>
                    </div>
                  </div>
                </td>

                <td class="text-center">
                  <div v-if="user.badges && user.badges.length > 0">
                    <span 
                      v-for="badge in user.badges" 
                      :key="badge.id" 
                      class="badge rounded-pill text-bg-info me-1"
                      :title="badge.description"
                    >
                      <i class="fas fa-shield-alt me-1"></i> {{ badge.badge_name }}
                    </span>
                  </div>
                  <span v-else class="text-muted small">-</span>
                </td>

                <td class="text-center">
                  <span 
                    class="badge rounded-pill"
                    :class="{
                      'text-bg-success': user.trust_score >= 80,
                      'text-bg-primary': user.trust_score >= 50 && user.trust_score < 80,
                      'text-bg-warning': user.trust_score >= 0 && user.trust_score < 50,
                      'text-bg-danger': user.trust_score < 0
                    }"
                    style="font-size: 0.9rem; min-width: 60px;"
                  >
                    {{ user.trust_score }}
                  </span>
                </td>

                <td class="text-end pe-4">
                  <router-link 
                    :to="{ name: 'ReportUserDetails', params: { id: user.user_id } }" 
                    class="btn btn-primary btn-sm px-3"
                  >
                    View
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      
      <div class="card-footer bg-body py-3 d-flex justify-content-end" v-if="users.meta">
        <nav>
          <ul class="pagination pagination-sm mb-0">
            <li class="page-item" :class="{ disabled: !users.links.prev }">
              <button class="page-link" @click="changePage(users.links.prev)">Previous</button>
            </li>
            <li class="page-item" :class="{ disabled: !users.links.next }">
              <button class="page-link" @click="changePage(users.links.next)">Next</button>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { debounce } from 'lodash';

const users = ref({ data: [], links: {}, meta: {} });
const loading = ref(false);
const searchQuery = ref('');

const fetchUsers = async (url = '/api/admin/users') => {
  loading.value = true;
  try {
    const params = { search: searchQuery.value };
    const res = await axios.get(url, { params });
    users.value = res.data;
  } catch (err) {
    console.error("Failed to fetch users", err);
  } finally {
    loading.value = false;
  }
};

const changePage = (url) => {
  if (url) fetchUsers(url);
};
let timeout = null;
watch(searchQuery, (newVal) => {
  clearTimeout(timeout);
  timeout = setTimeout(() => {
    fetchUsers();
  }, 500);
});

onMounted(() => {
  fetchUsers();
});
</script>