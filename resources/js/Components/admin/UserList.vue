<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-body-emphasis">View User Trust Scores</h2>
    </div>

    <AdminStats />

    <div class="card bg-body shadow-sm border-0 rounded-lg">
      <div class="card-header bg-body py-3">
        <div class="row align-items-center g-2">
          <div class="col-md-6 d-flex align-items-center gap-3">
            <h6 class="mb-0 fw-bold text-nowrap">All Users</h6>
            <select
              class="form-select form-select-sm"
              style="width: auto;"
              v-model="perPage"
              @change="fetchUsers()"
            >
              <option :value="10">10 per page</option>
              <option :value="25">25 per page</option>
              <option :value="50">50 per page</option>
              <option :value="100">100 per page</option>
            </select>
          </div>

          <div class="col-md-6">
            <div class="input-group input-group-sm">
              <input
                type="text"
                class="form-control bg-body"
                placeholder="Search by username..."
                v-model="searchQuery"
                @keyup.enter="fetchUsers()"
              >
              <button class="btn btn-primary" type="button" @click="fetchUsers()">
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

              <tr v-for="user in users.data" :key="user.user_id" :class="{ 'bg-danger-subtle': user.banned_at }">
                <td class="ps-4 text-muted">#{{ user.user_id }}</td>
                <td>
                  <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px;">
                      <span class="fw-bold text-secondary">{{ user.username.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div>
                      <div class="d-flex align-items-center gap-2">
                        <p class="mb-0 fw-semibold">{{ user.username }}</p>
                        <span v-if="user.banned_at" class="badge bg-danger" style="font-size: 0.65rem;">
                          <i class="fas fa-ban me-1"></i> BANNED
                        </span>
                      </div>
                      <small class="text-muted">{{ user.email }}</small>
                    </div>
                  </div>
                </td>
                <td class="text-center" style="max-width: 300px;">
                  <div v-if="user.badges && user.badges.length > 0" class="d-flex justify-content-center flex-wrap gap-2">
                    <div
                      v-for="badge in user.badges"
                      :key="badge.badge_id || badge.id"
                      class="badge rounded-pill border border-secondary-subtle bg-body-tertiary text-body-emphasis d-inline-flex align-items-center p-1 pe-3"
                      data-bs-toggle="tooltip"
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

      <div class="card-footer bg-body py-3" v-if="users.meta">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">

          <div class="text-muted small">
            Showing {{ users.meta.from || 0 }} to {{ users.meta.to || 0 }} of {{ users.meta.total }} entries
            <span class="fw-bold mx-1">|</span>
            Page {{ users.meta.current_page }} of {{ users.meta.last_page }}
          </div>

          <div class="d-flex align-items-center gap-2">

            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="!users.links.prev"
                @click="changePage(users.links.prev)"
            >
                Previous
            </button>

            <button
                class="btn btn-sm btn-outline-secondary"
                :disabled="!users.links.next"
                @click="changePage(users.links.next)"
            >
                Next
            </button>

            <div class="ms-2">
                <input
                    type="number"
                    class="form-control form-control-sm text-center"
                    style="width: 60px;"
                    v-model="jumpPage"
                    min="1"
                    :max="users.meta.last_page"
                    @keyup.enter="goToSpecificPage"
                >
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import axios from 'axios';
import AdminStats from './AdminStats.vue';

const users = ref({ data: [], links: {}, meta: {} });
const loading = ref(false);
const searchQuery = ref('');
const perPage = ref(10);
const jumpPage = ref(1);

const initTooltips = () => {
  nextTick(() => {
    if (window.bootstrap) {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltipTriggerList.forEach(el => {
        const oldTooltip = window.bootstrap.Tooltip.getInstance(el);
        if (oldTooltip) oldTooltip.dispose();
        new window.bootstrap.Tooltip(el);
      });
    }
  });
};

const fetchUsers = async (url = '/api/admin/users') => {
  loading.value = true;
  try {
    const params = {
        search: searchQuery.value,
        per_page: perPage.value
    };

    const config = { params };

    if (url !== '/api/admin/users') {
        if (!url.includes('per_page')) {
             url += `&per_page=${perPage.value}`;
        }
    }

    const res = await axios.get(url, url === '/api/admin/users' ? config : {});

    users.value = {
        data: res.data.data,
        links: {
            next: res.data.next_page_url,
            prev: res.data.prev_page_url
        },
        meta: {
            current_page: res.data.current_page,
            last_page: res.data.last_page,
            from: res.data.from,
            to: res.data.to,
            total: res.data.total
        }
    };

    jumpPage.value = users.value.meta.current_page;

    initTooltips();
  } catch (err) {
    console.error("Failed to fetch users", err);
  } finally {
    loading.value = false;
  }
};

const changePage = (url) => {
  if (url) fetchUsers(url);
};

const goToSpecificPage = () => {
    if (!jumpPage.value || jumpPage.value < 1 || jumpPage.value > users.value.meta.last_page) {
        return;
    }
    const url = `/api/admin/users?page=${jumpPage.value}&per_page=${perPage.value}&search=${searchQuery.value}`;
    fetchUsers(url);
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
