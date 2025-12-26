<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="fw-bold text-body-emphasis">Content Manager Report</h2>
      <p class="text-muted">
        Administrative report showing flagged posts and comments, moderation status,
        and user-generated reports for review and action.
      </p>

    </div>

    <div class="card bg-body shadow-sm border-0 rounded-lg mb-4">
      <div class="card-body p-3 d-flex gap-3 flex-wrap">

        <div class="btn-group">
          <button
            class="btn btn-outline-primary"
            :class="{ active: activeType === 'posts' }"
            @click="activeType = 'posts'"
          >Posts</button>
          <button
            class="btn btn-outline-primary"
            :class="{ active: activeType === 'comments' }"
            @click="activeType = 'comments'"
          >Comments</button>
        </div>

        <select
            v-model="filterStatus"
            class="form-select bg-body text-body border-secondary"
            style="max-width: 200px;"
        >
            <option value="all">All Statuses</option>
            <option value="published">Visible (Published)</option>
            <option value="moderated">Hidden (Moderated)</option>
            <option value="deleted">Deleted</option>
        </select>

        <div class="input-group" style="max-width: 300px;">
           <span class="input-group-text bg-body border-secondary text-muted">
             <i class="fas fa-search"></i>
           </span>
           <input
             type="text"
             v-model="search"
             class="form-control bg-body text-body border-secondary"
             placeholder="Search title, content, user..."
             @keyup.enter="fetchContent(1)"
           >
        </div>

        <button class="btn btn-primary" @click="fetchContent(1)">Filter</button>
      </div>
    </div>

    <div class="card bg-body shadow-sm border-0 rounded-lg">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th scope="col" class="ps-4">ID</th>
              <th scope="col">Status</th>
              <th scope="col" style="width: 40%;">Content</th>
              <th scope="col">Author</th>
              <th scope="col">Reports</th>
              <th scope="col" class="text-end pe-4">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading">
                <td colspan="6" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </td>
            </tr>
            <tr v-else-if="items.data.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">No content found.</td>
            </tr>
            <tr v-else v-for="item in items.data" :key="item.id">
              <td class="ps-4 text-muted">#{{ getId(item) }}</td>

              <td>
                <span
                    class="badge"
                    :class="{
                        'text-bg-success': item.status === 'published',
                        'text-bg-warning': item.status === 'moderated',
                        'text-bg-danger': item.status === 'deleted'
                    }"
                >
                    {{ item.status }}
                </span>
              </td>

              <td>
                <div class="fw-bold text-truncate" style="max-width: 400px;">
                    {{ item.title || 'Comment' }}
                </div>
                <div class="small text-muted text-truncate" style="max-width: 400px;">
                    {{ item.content }}
                </div>
              </td>

              <td>
                 <div class="d-flex align-items-center">
                    <div class="fw-bold">{{ item.user?.username || 'Unknown' }}</div>
                 </div>
              </td>

              <td>
                 <span v-if="item.reports_count > 0" class="badge bg-danger rounded-pill">
                    {{ item.reports_count }} Flags
                 </span>
                 <span v-else class="text-muted small">-</span>
              </td>

              <td class="text-end pe-4">
                 <button
                    v-if="item.status === 'moderated' || item.status === 'deleted'"
                    class="btn btn-sm btn-success me-2"
                    @click="updateStatus(item, 'approve')"
                    title="Unhide / Publish"
                 >
                    <i class="fas fa-check"></i> Unhide
                 </button>

                 <button
                    v-if="item.status === 'published'"
                    class="btn btn-sm btn-warning text-dark me-2"
                    @click="updateStatus(item, 'hide')"
                    title="Hide from public"
                 >
                    <i class="fas fa-eye-slash"></i> Hide
                 </button>

                 <button class="btn btn-sm btn-outline-secondary" @click="viewDetails(item)">
                    <i class="fas fa-external-link-alt"></i>
                 </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-body border-0 py-3 d-flex justify-content-center">
         <nav v-if="items.last_page > 1">
            <ul class="pagination mb-0">
                <li class="page-item" :class="{ disabled: items.current_page === 1 }">
                    <button class="page-link" @click="fetchContent(items.current_page - 1)">Prev</button>
                </li>
                <li class="page-item disabled">
                    <span class="page-link">Page {{ items.current_page }} of {{ items.last_page }}</span>
                </li>
                <li class="page-item" :class="{ disabled: items.current_page === items.last_page }">
                    <button class="page-link" @click="fetchContent(items.current_page + 1)">Next</button>
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
import { useRouter } from 'vue-router';

const router = useRouter();
const loading = ref(false);
const items = ref({ data: [], current_page: 1, last_page: 1 });

const activeType = ref('posts');
const filterStatus = ref('all');
const search = ref('');

const getId = (item) => item.post_id || item.comment_id;

const fetchContent = async (page = 1) => {
    loading.value = true;
    try {
        const res = await axios.get('/api/admin/content', {
            params: {
                type: activeType.value,
                status: filterStatus.value,
                search: search.value,
                page: page
            }
        });
        items.value = res.data;
    } catch (e) {
        console.error(e);
    } finally {
        loading.value = false;
    }
};

const updateStatus = async (item, action) => {
    if (!confirm(`Are you sure you want to ${action} this item?`)) return;

    try {
        const typeSingular = activeType.value === 'posts' ? 'post' : 'comment';
        const id = getId(item);

        await axios.post(`/api/moderate/${typeSingular}/${id}/${action}`);

        fetchContent(items.value.current_page);
    } catch (e) {
        alert("Failed to update status.");
    }
};

const viewDetails = (item) => {
    const id = getId(item);
    if (activeType.value === 'posts') {
        router.push(`/posts/${id}`);
    } else {
        router.push(`/posts/${item.post_id}`);
    }
};

watch([activeType, filterStatus], () => {
    fetchContent(1);
});

onMounted(() => fetchContent(1));
</script>
