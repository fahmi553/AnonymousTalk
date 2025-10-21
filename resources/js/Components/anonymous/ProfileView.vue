<template>
  <div class="container" style="max-width: 700px;">
    <!-- ✅ Toast -->
    <div
      v-if="showToast"
      class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">✅ {{ toastMessage }}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showToast = false"></button>
      </div>
    </div>

    <!-- ✅ Loading/Error -->
    <div v-if="loading" class="text-center mt-5">Loading profile...</div>
    <div v-else-if="error" class="text-danger mt-5">{{ error }}</div>

    <!-- ✅ Profile -->
    <div v-else-if="user" class="card shadow-lg border-0 overflow-hidden">
      <div class="p-4 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex align-items-center">
          <img
            src="https://i.pravatar.cc/100"
            alt="Avatar"
            class="rounded-circle border border-3 border-white me-3"
            width="80"
            height="80"
          />
          <div>
            <h3 class="mb-0">{{ user.username || 'Anonymous' }}</h3>
            <small class="text-light">{{ isSelf ? "This is you" : "Anonymous User" }}</small>
          </div>
        </div>
      </div>

      <div class="card-body">
        <!-- ✅ Stats -->
        <div class="d-flex justify-content-around text-center mb-4">
          <div>
            <h5 class="mb-0">{{ posts.length }}</h5>
            <small class="text-muted">Posts</small>
          </div>
          <div>
            <h5 class="mb-0">{{ comments.length }}</h5>
            <small class="text-muted">Comments</small>
          </div>
          <div>
            <h5 class="mb-0">{{ user.likes_count ?? 0 }}</h5>
            <small class="text-muted">Likes</small>
          </div>
        </div>

        <!-- ✅ Trust Score -->
        <div class="mb-3">
          <label class="form-label"><strong>Trust Score:</strong></label>
          <div class="progress" style="height: 20px;">
            <div
              class="progress-bar"
              role="progressbar"
              :style="{ width: (user.trust_score || 0) + '%' }"
              :aria-valuenow="user.trust_score || 0"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              {{ user.trust_score || 0 }}%
            </div>
          </div>
        </div>

        <!-- ✅ Basic Info -->
        <p><i class="fas fa-user-shield me-2"></i><strong>Role:</strong> {{ user.role || 'User' }}</p>
        <p>
          <i class="fas fa-calendar-alt me-2"></i>
          <strong>Joined:</strong>
          {{ formatDate(user.created_at) }}
        </p>

        <!-- ✅ Hide All Toggles -->
        <div v-if="isSelf" class="mt-3 d-flex gap-2">
          <button
            class="btn btn-outline-secondary flex-grow-1"
            @click="toggleHideAll('posts')"
            :disabled="loadingPostsToggle"
          >
            <i class="fas" :class="user.hide_all_posts ? 'fa-eye' : 'fa-eye-slash'"></i>
            {{ user.hide_all_posts ? 'Show All Posts' : 'Hide All Posts' }}
          </button>

          <button
            class="btn btn-outline-secondary flex-grow-1"
            @click="toggleHideAll('comments')"
            :disabled="loadingCommentsToggle"
          >
            <i class="fas" :class="user.hide_all_comments ? 'fa-eye' : 'fa-eye-slash'"></i>
            {{ user.hide_all_comments ? 'Show All Comments' : 'Hide All Comments' }}
          </button>
        </div>

        <!-- ✅ Tabs -->
        <ul class="nav nav-tabs mt-4" id="profileTabs">
          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'posts' }"
              type="button"
              @click="activeTab = 'posts'"
            >
              Posts
            </button>
          </li>
          <li class="nav-item">
            <button
              class="nav-link"
              :class="{ active: activeTab === 'comments' }"
              type="button"
              @click="activeTab = 'comments'"
            >
              Comments
            </button>
          </li>
        </ul>

        <!-- ✅ Posts -->
        <div class="mt-3" v-if="activeTab === 'posts'">
          <div v-if="loadingPosts" class="text-muted small">Loading posts...</div>
          <div v-else-if="posts.length === 0" class="text-muted small">No posts yet.</div>

          <div v-else>
            <div
              v-for="post in posts"
              :key="post.post_id"
              class="border-bottom py-2 d-flex justify-content-between align-items-start"
              :class="{ 'opacity-50': post.hidden_in_profile }"
            >
              <div>
                <router-link
                  :to="`/posts/${post.post_id}`"
                  class="fw-semibold text-decoration-none"
                >
                  {{ post.title || 'Untitled Post' }}
                </router-link>
                <p class="text-muted small mb-0">
                  {{ post.comments_count ?? 0 }} comments · {{ post.likes_count ?? 0 }} likes
                </p>
              </div>

              <button
                v-if="isSelf"
                class="btn btn-sm"
                :class="post.hidden_in_profile ? 'btn-outline-success' : 'btn-outline-secondary'"
                @click="togglePostVisibility(post.post_id)"
              >
                <i :class="post.hidden_in_profile ? 'fas fa-eye' : 'fas fa-eye-slash'" class="me-1"></i>
                {{ post.hidden_in_profile ? 'Unhide' : 'Hide' }}
              </button>
            </div>
          </div>
        </div>

        <!-- ✅ Comments -->
        <div class="mt-3" v-else-if="activeTab === 'comments'">
          <div v-if="loadingComments" class="text-muted small">Loading comments...</div>
          <div v-else-if="comments.length === 0" class="text-muted small">No comments yet.</div>

          <div v-else>
            <div
              v-for="c in comments"
              :key="c.comment_id"
              class="border-bottom py-2 d-flex justify-content-between align-items-start"
              :class="{ 'opacity-50': c.hidden_in_profile }"
            >
              <div>
                <p class="mb-1">{{ c.content }}</p>
                <router-link
                  v-if="c.post"
                  :to="`/posts/${c.post.post_id}`"
                  class="small text-muted"
                >
                  On: {{ c.post.title || 'Post' }}
                </router-link>
              </div>

              <button
                v-if="isSelf"
                class="btn btn-sm"
                :class="c.hidden_in_profile ? 'btn-outline-success' : 'btn-outline-secondary'"
                @click="toggleCommentVisibility(c.comment_id)"
              >
                <i :class="c.hidden_in_profile ? 'fas fa-eye' : 'fas fa-eye-slash'" class="me-1"></i>
                {{ c.hidden_in_profile ? 'Unhide' : 'Hide' }}
              </button>
            </div>
          </div>
        </div>

        <!-- ✅ Footer -->
        <div class="d-flex gap-2 mt-4">
          <router-link
            v-if="isSelf"
            to="/profile/edit"
            class="btn btn-outline-primary flex-grow-1"
          >
            <i class="fas fa-edit me-1"></i> Edit Profile
          </router-link>
          <button v-else class="btn btn-outline-danger flex-grow-1">🚩 Report User</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"

const route = useRoute()

const user = ref(null)
const loading = ref(true)
const error = ref("")
const showToast = ref(false)
const toastMessage = ref("")
const isSelf = ref(false)
const activeTab = ref("posts")

const posts = ref([])
const comments = ref([])
const loadingPosts = ref(false)
const loadingComments = ref(false)
const loadingPostsToggle = ref(false)
const loadingCommentsToggle = ref(false)

const formatDate = (dateStr) => {
  if (!dateStr) return "Unknown"
  const d = new Date(dateStr)
  return isNaN(d.getTime()) ? "Unknown" : d.toLocaleDateString()
}

const fetchUser = async () => {
  loading.value = true
  error.value = ""
  try {
    const id = route.params.id
    const url = id ? `/api/profile/${id}` : `/api/profile`
    const res = await axios.get(url)
    user.value = res.data
    const uid = id || user.value.user_id
    isSelf.value = !id
    await Promise.all([fetchPosts(uid), fetchComments(uid)])
  } catch (e) {
    console.error(e)
    error.value = "Failed to load profile"
  } finally {
    loading.value = false
  }
}

const fetchPosts = async (id) => {
  loadingPosts.value = true
  try {
    const res = await axios.get(`/api/profile/${id}/posts`)
    posts.value = Array.isArray(res.data) ? res.data : []
  } finally {
    loadingPosts.value = false
  }
}

const fetchComments = async (id) => {
  loadingComments.value = true
  try {
    const res = await axios.get(`/api/profile/${id}/comments`)
    comments.value = Array.isArray(res.data) ? res.data : []
  } finally {
    loadingComments.value = false
  }
}

const togglePostVisibility = async (id) => {
  const res = await axios.patch(`/api/posts/${id}/toggle-profile-visibility`)
  const post = posts.value.find(p => p.post_id === id)
  if (post) post.hidden_in_profile = res.data.hidden_in_profile
}

const toggleCommentVisibility = async (id) => {
  const res = await axios.patch(`/api/comments/${id}/toggle-profile-visibility`)
  const c = comments.value.find(c => c.comment_id === id)
  if (c) c.hidden_in_profile = res.data.hidden_in_profile
}

const toggleHideAll = async (type) => {
  try {
    if (type === "posts") {
      loadingPostsToggle.value = true
      const res = await axios.post("/api/profile/toggle-hide-all-posts")
      user.value.hide_all_posts = res.data.hide_all_posts
      toastMessage.value = res.data.message
    } else {
      loadingCommentsToggle.value = true
      const res = await axios.post("/api/profile/toggle-hide-all-comments")
      user.value.hide_all_comments = res.data.hide_all_comments
      toastMessage.value = res.data.message
    }
    showToast.value = true
    setTimeout(() => (showToast.value = false), 3000)
  } catch (err) {
    console.error(`Error toggling ${type}:`, err)
  } finally {
    loadingPostsToggle.value = false
    loadingCommentsToggle.value = false
  }
}

onMounted(fetchUser)
watch(() => route.fullPath, fetchUser)
</script>

<style scoped>
.card {
  border-radius: 15px;
}
.nav-tabs .nav-link.active {
  font-weight: 600;
}
.opacity-50 {
  opacity: 0.5;
}
</style>
