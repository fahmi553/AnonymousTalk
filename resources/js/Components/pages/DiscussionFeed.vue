<template>
  <div class="container py-4">
    <div class="row g-4">

      <div class="col-lg-3">
        <div class="sticky-top" style="top: 2rem; z-index: 10;">

          <button class="btn btn-primary w-100 rounded-pill fw-bold mb-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#createPostModal">
            <i class="fas fa-plus me-2"></i>Create Post
          </button>

          <div class="card bg-body border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="list-group list-group-flush">
              <button
                class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center gap-3"
                :class="{ 'active-filter': filter === 'latest' }"
                @click="setFilter('latest')"
              >
                <i class="fas fa-clock fa-fw" :class="filter === 'latest' ? 'text-primary' : 'text-secondary'"></i>
                <span class="fw-bold text-body-emphasis">Latest</span>
              </button>

              <button
                class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center gap-3"
                :class="{ 'active-filter': filter === 'trending' }"
                @click="setFilter('trending')"
              >
                <i class="fas fa-fire fa-fw" :class="filter === 'trending' ? 'text-danger' : 'text-secondary'"></i>
                <span class="fw-bold text-body-emphasis">Trending</span>
              </button>

              <button
                class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center gap-3"
                :class="{ 'active-filter': filter === 'following' }"
                @click="setFilter('following')"
              >
                <i class="fas fa-user-friends fa-fw" :class="filter === 'following' ? 'text-success' : 'text-secondary'"></i>
                <span class="fw-bold text-body-emphasis">Following</span>
              </button>
            </div>
          </div>

          <div class="card bg-body-tertiary border-0 rounded-4 p-3">
            <h6 class="fw-bold text-body-emphasis mb-2 px-2 small text-uppercase">Posting Rules</h6>
            <ul class="list-unstyled mb-0 px-2">
              <li class="mb-2 small text-secondary"><i class="fas fa-check-circle text-success me-2"></i>Be respectful</li>
              <li class="mb-2 small text-secondary"><i class="fas fa-check-circle text-success me-2"></i>No hate speech</li>
              <li class="small text-secondary"><i class="fas fa-check-circle text-success me-2"></i>Protect anonymity</li>
            </ul>
          </div>

        </div>
      </div>

      <div class="col-lg-9">

        <div class="card bg-body border-0 shadow-sm rounded-pill mb-4 px-2">
          <div class="card-body p-2 d-flex align-items-center">
            <i class="fas fa-search text-muted ms-3 me-3"></i>
            <input
              type="text"
              class="form-control border-0 shadow-none bg-transparent text-body-emphasis"
              placeholder="Search discussions..."
              v-model="search"
              @keyup.enter="fetchPosts"
            >
          </div>
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-2 small">Loading discussions...</p>
        </div>

        <div v-else-if="posts.length === 0" class="text-center py-5">
          <i class="fas fa-comments fa-3x text-body-tertiary mb-3"></i>
          <h5 class="fw-bold text-body-emphasis">No posts found</h5>
          <p class="text-muted">Be the first to start a conversation!</p>
        </div>

        <div v-else class="d-flex flex-column gap-3">
          <div
            v-for="post in posts"
            :key="post.post_id"
            class="card bg-body border border-secondary-subtle shadow-sm rounded-4 overflow-hidden hover-card"
          >
            <div class="card-body p-4">

              <div class="d-flex align-items-center mb-3">
                <img
                  :src="getAvatarUrl(post.user?.avatar)"
                  @error="$event.target.src = '/images/avatars/default.jpg'"
                  class="rounded-circle border border-2 border-body shadow-sm me-3"
                  width="40"
                  height="40"
                  alt="Avatar"
                  style="object-fit: cover;"
                >
                <div>
                  <h6 class="fw-bold text-body-emphasis mb-0">{{ post.user?.username || 'Anonymous' }}</h6>
                  <small class="text-secondary" style="font-size: 0.75rem;">
                    {{ formatDate(post.created_at) }} &bull; <i class="fas fa-globe-americas ms-1"></i>
                  </small>
                </div>

                <div class="dropdown ms-auto">
                  <button class="btn btn-link text-muted p-0" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-h"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                    <li><button class="dropdown-item text-danger" @click="reportPost(post.post_id)"><i class="fas fa-flag me-2"></i>Report</button></li>
                  </ul>
                </div>
              </div>

              <router-link :to="`/posts/${post.post_id}`" class="text-decoration-none">
                <h5 class="fw-bold text-body-emphasis mb-2">{{ post.title }}</h5>
                <p class="text-secondary mb-3" style="line-height: 1.6;">
                  {{ truncate(post.content, 200) }}
                </p>
              </router-link>

              <div class="d-flex gap-3 border-top border-secondary-subtle pt-3 mt-3">
                <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-secondary action-btn">
                  <i class="far fa-heart me-2"></i>{{ post.likes_count || 0 }}
                </button>
                <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-secondary action-btn">
                  <i class="far fa-comment-alt me-2"></i>{{ post.comments_count || 0 }}
                </button>
                <button class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-secondary ms-auto action-btn">
                  <i class="fas fa-share me-2"></i>Share
                </button>
              </div>

            </div>
          </div>
        </div>

        <div class="mt-4 d-flex justify-content-center" v-if="meta.last_page > 1">
          <nav>
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                <button class="page-link border-0 rounded-start-pill bg-body text-body-emphasis" @click="changePage(meta.current_page - 1)">Prev</button>
              </li>
              <li class="page-item disabled">
                <span class="page-link border-0 bg-body text-muted">Page {{ meta.current_page }}</span>
              </li>
              <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                <button class="page-link border-0 rounded-end-pill bg-body text-body-emphasis" @click="changePage(meta.current_page + 1)">Next</button>
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>

    <div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-body rounded-4 border-0 shadow-lg">
          <div class="modal-header border-bottom border-secondary-subtle">
            <h5 class="modal-title fw-bold text-body-emphasis">Start a Discussion</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold text-secondary small text-uppercase">Title</label>
              <input type="text" class="form-control form-control-lg bg-body-tertiary border-0" placeholder="What's on your mind?">
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold text-secondary small text-uppercase">Content</label>
              <textarea class="form-control bg-body-tertiary border-0" rows="6" placeholder="Share your thoughts..."></textarea>
            </div>
            <div class="alert alert-info border-0 d-flex align-items-center small">
              <i class="fas fa-info-circle me-2"></i>
              <span>Remember, your identity is hidden but your content is moderated.</span>
            </div>
          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold">Post</button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const posts = ref([]);
const loading = ref(true);
const filter = ref('latest');
const search = ref('');
const meta = ref({ current_page: 1, last_page: 1 });
const getAvatarUrl = (filename) => {
  if (!filename) return '/images/avatars/default.jpg';

  if (filename.startsWith('http')) {
    return filename;
  }

  return `/images/avatars/${filename}`;
};

const formatDate = (dateString) => {
  if (!dateString) return 'Just now';
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const truncate = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get('/api/posts', {
      params: {
        page: page,
        filter: filter.value,
        search: search.value
      }
    });
    posts.value = res.data.data || [];
    meta.value = res.data.meta || { current_page: 1, last_page: 1 };
  } catch (err) {
    console.error("Failed to load posts", err);
  } finally {
    loading.value = false;
  }
};

const setFilter = (newFilter) => {
  filter.value = newFilter;
  fetchPosts(1);
};

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchPosts(page);
};

const reportPost = (id) => {
  alert(`Report logic for post ${id} goes here`);
};

onMounted(() => {
  fetchPosts();
});
</script>

<style scoped>
.active-filter {
  background-color: var(--bs-primary-bg-subtle);
  color: var(--bs-primary) !important;
  border-right: 4px solid var(--bs-primary) !important;
}

.hover-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
}

.action-btn:hover {
  background-color: var(--bs-secondary-bg);
  color: var(--bs-primary) !important;
}

.page-link:focus {
  box-shadow: none;
}
</style>
