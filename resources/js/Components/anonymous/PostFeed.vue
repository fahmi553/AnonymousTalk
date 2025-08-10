<template>
  <div class="feed-container">
    <h3 class="mb-4 fw-bold text-primary">Latest Posts</h3>

    <div v-if="loading">Loading posts...</div>
    <div v-if="error" class="text-danger">{{ error }}</div>

    <div v-for="post in posts" :key="post.post_id" class="post-card mb-4 p-3 border rounded shadow-sm bg-white">
      <div class="post-header mb-1">
        <span class="username fw-bold">{{ post.user?.username ?? 'Anonymous' }}</span>
        <span class="time text-muted small">· {{ timeAgo(post.created_at) }}</span>
      </div>

      <!-- Post Title -->
      <h5 v-if="post.title" class="fw-semibold mb-2">{{ post.title }}</h5>

      <!-- Post Content -->
      <div class="post-content mb-2">{{ post.content }}</div>

      <a :href="`/posts/${post.post_id}`" class="text-decoration-none text-primary fw-semibold">
        View Post
      </a>

      <!-- Comments Preview -->
      <div v-if="post.comments && post.comments.length > 0" class="comment-section mt-3 border-top pt-2">
        <div
          v-for="(comment, index) in post.comments.slice(0, 2)"
          :key="comment.comment_id"
          class="comment small text-muted"
        >
          <strong>{{ comment.user?.username ?? 'Anonymous' }}</strong>:
          {{ comment.content }}
        </div>

        <a v-if="post.comments.length > 2"
           :href="`/posts/${post.post_id}`"
           class="small text-muted">
          View all {{ post.comments.length }} comments
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import axios from 'axios'

const posts = ref([])
const loading = ref(true)
const error = ref('')

const loadPosts = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/posts?with_comments=1')
    posts.value = response.data
  } catch (err) {
    error.value = 'Failed to load posts.'
  } finally {
    loading.value = false
  }
}

const addNewPost = (event) => {
  const newPost = event.detail
  posts.value.unshift(newPost)
}
const timeAgo = (dateStr) => {
  const date = new Date(dateStr)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)

  if (seconds < 60) return 'just now'
  if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes ago`
  if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`
  if (seconds < 172800) return 'yesterday'
  return `${Math.floor(seconds / 86400)} days ago`
}

onMounted(() => {
  loadPosts()
  window.addEventListener('post-created', addNewPost)
})

onBeforeUnmount(() => {
  window.removeEventListener('post-created', addNewPost)
})
</script>

<style scoped>
.post-card {
  transition: box-shadow 0.2s ease;
}
.post-card:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
