<template>
  <div class="container-xxl">
    <div class="row g-4">
      <div class="col-lg-8">

        <h3 class="mb-4 fw-bold text-primary">Latest Posts</h3>

        <div class="card bg-body-tertiary shadow-sm border-0 rounded-lg mb-4">
          <div class="card-body p-3 p-md-4">
            <div class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label for="filter-search" class="form-label small fw-medium">Search</label>
                    <div class="input-group">
                    <input
                        type="text"
                        id="filter-search"
                        class="form-control bg-body"
                        placeholder="Search posts..."
                        v-model="searchTerm"
                        @input="debouncedSearch"
                    >
                    <button class="btn btn-outline-secondary" type="button" @click="resetAndLoad">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
              </div>
              <div class="col-lg-4">
                <label for="filter-sort" class="form-label small fw-medium">Sort By</label>
                <select v-model="sortOrder" @change="resetAndLoad" class="form-select bg-body" id="filter-sort">
                  <option value="desc">Newest First</option>
                  <option value="asc">Oldest First</option>
                  <option value="most_commented">Most Commented</option>
                </select>
              </div>
              <div class="col-lg-3">
                <div class="form-check pb-1">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    v-model="withCommentsOnly"
                    @change="resetAndLoad"
                    id="commentsOnlyCheck"
                    :disabled="sortOrder === 'most_commented'"
                  >
                  <label
                    class="form-check-label small"
                    for="commentsOnlyCheck"
                    :class="{ 'text-body-secondary': sortOrder === 'most_commented' }"
                  >
                    With Comments Only
                  </label>
                </div>
              </div>

            </div>
          </div>
        </div>

        <div v-if="loading && posts.length === 0" class="text-center py-5">
          <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
          <p class="mt-3 text-muted">Loading posts...</p>
        </div>

        <div v-if="!loading && posts.length === 0" class="card bg-body border-0 shadow-sm rounded-lg">
          <div class="card-body text-center p-5 text-muted fst-italic">
            <span v-if="selectedCategory">
              No posts found for the category "{{ selectedCategoryName }}".
            </span>
            <span v-else>No posts available yet.</span>
          </div>
        </div>

        <div v-if="error" class="alert alert-danger">{{ error }}</div>

        <div
          v-for="post in posts"
          :key="post.post_id"
          class="card bg-body shadow-sm border border-secondary rounded-lg mb-3"
        >
          <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-start mb-3">
              <div class="d-flex align-items-center">

                <img
                    :src="post.user?.avatar ? `/images/avatars/${post.user.avatar}` : '/images/avatars/default.jpg'"
                    :alt="post.user?.username || 'User'"
                    class="rounded-circle me-3 flex-shrink-0 border bg-white"
                    style="width: 45px; height: 45px; object-fit: cover;"
                >

                <div>
                  <a v-if="post.user && post.user.user_id" :href="'/profile/' + post.user.user_id" class="fw-bold text-body-emphasis text-decoration-none">{{ post.user.username }}</a>
                  <span v-else class="fw-bold text-body-emphasis">{{ post.user?.username ?? 'Anonymous' }}</span>
                  <small class="d-block text-muted">
                    {{ timeAgo(post.created_at) }}
                  </small>
                </div>
              </div>
              <span
                v-if="post.category"
                class="badge rounded-pill shadow-sm"
                :style="{ backgroundColor: getCategoryColor(post.category), color: 'white' }"
              >
                {{ post.category }}
              </span>
            </div>

            <router-link :to="`/posts/${post.post_id}`" class="text-decoration-none">
              <h4 class="fw-bold text-body-emphasis mb-2">{{ post.title }}</h4>
            </router-link>
            <p class="text-body-secondary mb-3" style="white-space: pre-line;">
              {{ post.content }}
            </p>

            <div v-if="authUserRole === 'admin'" class="mt-2 mb-3 d-flex gap-2">
              <button class="btn btn-sm btn-success" @click="moderatePost(post.post_id, 'approve')">Approve</button>
              <button class="btn btn-sm btn-warning" @click="moderatePost(post.post_id, 'hide')">Hide</button>
              <button class="btn btn-sm btn-danger" @click="moderatePost(post.post_id, 'delete')">Delete</button>
            </div>

            <div class="d-flex justify-content-between align-items-center border-top pt-3">
              <button
                :disabled="!authUserId"
                class="btn d-flex align-items-center rounded-pill px-3 py-2"
                :class="{
                  'btn-secondary': !authUserId,
                  'btn-outline-danger': authUserId && !post.liked,
                  'btn-danger': authUserId && post.liked
                }"
                @click="authUserId && toggleLike(post)"
              >
                <i class="fas fa-heart me-2"></i>
                <span class="fw-bold">{{ post.likes_count }}</span>
              </button>

              <router-link
                :to="`/posts/${post.post_id}`"
                class="fw-medium text-body-secondary text-decoration-none"
              >
                <i class="fas fa-comments me-1 text-primary"></i>
                {{ post.comments_count ?? post.comments.length }} Comment{{ (post.comments_count ?? post.comments.length) === 1 ? '' : 's' }}
              </router-link>
            </div>

          </div>

          <ul
            v-if="post.comments && post.comments.length > 0"
            class="list-group list-group-flush"
          >
            <li
              v-for="comment in post.comments.slice(0, 2)"
              :key="comment.comment_id"
              class="list-group-item small bg-body text-body-secondary"
            >
              <strong class="text-body-emphasis">{{ comment.user?.username ?? 'Anonymous' }}</strong>:
              {{ comment.content }}
            </li>
          </ul>

          <div v-if="post.comments.length > 2" class="card-footer bg-body border-top-0 py-2">
            <router-link :to="`/posts/${post.post_id}`" class="small text-muted fw-medium text-decoration-none">
              View all {{ post.comments_count ?? post.comments.length }} comments
            </router-link>
          </div>
        </div>
        <div v-if="loading && posts.length > 0" class="text-center my-3">
          <div class="spinner-border spinner-border-sm text-muted" role="status"></div>
          <span class="ms-2 text-muted small">Loading more posts...</span>
        </div>

      </div>

      <div class="col-lg-4">

        <div class="card bg-body shadow-sm border border-secondary rounded-lg mb-3">
          <div class="card-header bg-body py-3">
            <h6 class="fw-bold mb-0 text-body-emphasis">Categories</h6>
          </div>
          <div class="list-group list-group-flush">
            <a
              href="#"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3"
              :class="{ 'active': !selectedCategory }"
              @click.prevent="filterByCategory('')"
            >
              All Categories
            </a>
            <a
              v-for="cat in categories"
              :key="cat.category_id"
              href="#"
              class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3"
              :class="{ 'active': selectedCategory === cat.category_id }"
              @click.prevent="filterByCategory(cat.category_id)"
            >
              {{ cat.name }}
              <span v-if="cat.posts_count" class="badge bg-primary rounded-pill">{{ cat.posts_count }}</span>
            </a>
          </div>
        </div>

        <div class="card bg-body shadow-sm border border-secondary rounded-lg mb-3">
          <div class="card-header bg-body py-3">
            <h6 class="fw-bold mb-0 text-body-emphasis">Trending Posts</h6>
          </div>

          <div v-if="trendingLoading" class="list-group-item list-group-item-action bg-body text-body-secondary text-center py-3 px-3">
            <div class="spinner-border spinner-border-sm" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>

          <div v-else-if="trendingPosts.length === 0" class="list-group-item list-group-item-action bg-body text-body-secondary py-3 px-3">
            No trending posts right now.
          </div>

          <div v-else class="list-group list-group-flush">
            <router-link
              v-for="post in trendingPosts"
              :key="post.post_id"
              :to="`/posts/${post.post_id}`"
              class="list-group-item list-group-item-action bg-body text-body-secondary px-3"
            >
              <span class="d-block fw-medium text-body-emphasis">{{ post.title }}</span>
              <small>{{ post.comments_count }} Comments · {{ post.likes_count }} Likes</small>
            </router-link>
          </div>
        </div>

        <div class="card bg-body-tertiary shadow-sm border-0 rounded-lg">
          <div class="card-body">
            <h6 class="fw-bold text-body-emphasis">About Anonymous Talk</h6>
            <p class="small text-body-secondary">
              Speak freely, discuss openly, and connect with others while keeping your identity private.
            </p>
            <router-link to="/posts/create" class="btn btn-primary btn-sm w-100">Create Post</router-link>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import debounce from 'lodash.debounce'
axios.defaults.withCredentials = true

const posts = ref([])
const categories = ref([])
const loading = ref(true)
const error = ref('')
const selectedCategory = ref('')
const sortOrder = ref('desc')
const withCommentsOnly = ref(false)
const searchTerm = ref('')
const currentPage = ref(1)
const lastPage = ref(1)
const perPage = 10
const authUserId = window.authUserId || null
const authUserRole = window.authUserRole || null
const trendingPosts = ref([])
const trendingLoading = ref(true)

const selectedCategoryName = computed(() => {
  if (!selectedCategory.value) return null
  const cat = categories.value.find(c => c.category_id === selectedCategory.value)
  return cat ? cat.name : null
})

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Failed to load categories', err)
  }
}

const fetchTrendingPosts = async () => {
  trendingLoading.value = true
  try {
    const res = await axios.get('/api/posts/trending')
    trendingPosts.value = res.data
  } catch (err)
 {
    console.error('Failed to load trending posts', err)
  } finally {
    trendingLoading.value = false
  }
}

const loadPosts = async (append = false) => {
  if (loading.value && append) return
  loading.value = true
  error.value = ''

  try {
    const res = await axios.get('/api/posts', {
      params: {
        category_id: selectedCategory.value || undefined,
        sort: sortOrder.value,
        with_comments_only: sortOrder.value !== 'most_commented' && withCommentsOnly.value ? 1 : 0,
        search: searchTerm.value || undefined,
        page: currentPage.value,
        per_page: perPage
      }
    })

    const { data, meta } = res.data
    if (append) {
      posts.value.push(...data)
    } else {
      posts.value = data
    }
    lastPage.value = meta.last_page
  } catch (err) {
    console.error('Failed to load posts:', err)
    error.value = 'Failed to load posts.'
  } finally {
    loading.value = false
  }
}

const resetAndLoad = () => {
  posts.value = []
  currentPage.value = 1
  lastPage.value = 1
  loadPosts()
}

const debouncedSearch = debounce(() => {
  resetAndLoad()
}, 400)

const filterByCategory = (categoryId) => {
  selectedCategory.value = categoryId
  resetAndLoad()
}

const toggleLike = async (post) => {
  try {
    await axios.get('/sanctum/csrf-cookie')
    const res = await axios.post(`/api/posts/${post.post_id}/toggle-like`)
    post.liked = res.data.liked
    post.likes_count = res.data.likes_count
  } catch (err) {
    console.error('Error toggling like:', err.response?.data || err)
  }
}

const moderatePost = async (postId, action) => {
  try {
    await axios.post(`/api/posts/${postId}/moderate`, { action })
    alert(`Post ${action}d successfully.`)
    resetAndLoad()
  } catch (err) {
    console.error('Moderation failed:', err.response?.data || err)
  }
}

const getCategoryColor = (categoryName) => {
  const cat = categories.value.find(c => c.name === categoryName)
  return cat ? cat.color_code : '#6c757d'
}

const timeAgo = (dateStr) => {
  if (!dateStr) return ''
  const date = new Date(dateStr)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  const intervals = { year: 31536000, month: 2592000, day: 86400, hour: 3600, minute: 60 }
  for (const unit in intervals) {
    const interval = Math.floor(seconds / intervals[unit])
    if (interval >= 1) return `${interval} ${unit}${interval > 1 ? "s" : ""} ago`
  }
  return "Just now"
}

const handleScroll = () => {
  const scrollBottom = window.innerHeight + window.scrollY >= document.body.offsetHeight - 300
  if (scrollBottom && !loading.value && currentPage.value < lastPage.value) {
    currentPage.value++
    loadPosts(true)
  }
}

onMounted(() => {
  fetchCategories()
  fetchTrendingPosts()
  resetAndLoad()
  window.addEventListener('scroll', handleScroll)
})
</script>

<style scoped>

.list-group-item-action:hover {
  background-color: var(--bs-body-tertiary) !important;
}

.btn-outline-danger {
  --bs-btn-color: var(--bs-danger);
  --bs-btn-border-color: var(--bs-danger);
  --bs-btn-hover-bg: var(--bs-danger);
  --bs-btn-hover-border-color: var(--bs-danger);
}

.btn-outline-danger:hover {
  color: var(--bs-white) !important;
}
</style>
