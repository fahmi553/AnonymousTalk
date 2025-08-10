<template>
  <div v-if="loading">Loading post...</div>
  <div v-else-if="error" class="text-danger">{{ error }}</div>

  <div v-else-if="post">
    <div class="card shadow-sm border-0 rounded-3 mb-4">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2">
          <div class="fw-bold text-dark">{{ post.user?.username ?? 'Anonymous' }}</div>
          <small class="text-muted ms-2">{{ timeAgo(post.created_at) }}</small>
        </div>

        <h4 v-if="post.title" class="fw-bold mb-2">{{ post.title }}</h4>
        <p class="mb-3">{{ post.content }}</p>
      </div>
    </div>

    <h5 class="fw-bold mb-3">Comments</h5>

    <div v-if="post.comments.length === 0">No comments yet.</div>

    <div
      v-for="comment in post.comments"
      :key="comment.comment_id"
      class="border-bottom py-2"
    >
      <strong>{{ comment.user?.username ?? 'Anonymous' }}</strong>:
      {{ comment.content }}
    </div>

    <comment-form
      v-if="authUserId"
      :post-id="post.post_id"
      :user-id="authUserId"
      @comment-submitted="addComment"
    />
    <p v-else class="text-muted mt-3">
      Please <a href="/login">login</a> to comment.
    </p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import CommentForm from './CommentForm.vue'

const props = defineProps({
  postId: Number,
  authUserId: Number
})

const post = ref(null)
const loading = ref(true)
const error = ref('')

const loadPost = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/posts/${props.postId}`)
    res.data.comments.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
    post.value = res.data
  } catch (e) {
    console.error(e)
    error.value = 'Failed to load post.'
  } finally {
    loading.value = false
  }
}

const addComment = (comment) => {
  post.value.comments.push(comment)
}

const timeAgo = (dateStr) => {
  const date = new Date(dateStr)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)
  const intervals = {
    year: 31536000,
    month: 2592000,
    day: 86400,
    hour: 3600,
    minute: 60
  }
  for (const unit in intervals) {
    const interval = Math.floor(seconds / intervals[unit])
    if (interval >= 1) return `${interval} ${unit}${interval > 1 ? 's' : ''} ago`
  }
  return 'Just now'
}

onMounted(loadPost)
</script>
