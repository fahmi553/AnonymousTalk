<template>
  <div v-if="loading">Loading post...</div>
  <div v-else-if="error" class="text-danger">{{ error }}</div>

  <div v-else-if="post">
    <div class="card shadow-sm border-0 rounded-3 mb-4">
      <div class="card-body">
        <div class="d-flex align-items-center mb-3">
          <div class="fw-bold text-dark me-2">{{ post.user?.username ?? 'Anonymous' }}</div>
          <small class="text-muted">· {{ timeAgo(post.created_at) }}</small>
        </div>

        <div v-if="post.category" class="mb-2">
          <span class="badge" :style="{ backgroundColor: '#6c757d', color: '#fff' }">
            {{ post.category }}
          </span>
        </div>

        <h2 class="fw-bold text-dark mb-3">{{ post.title }}</h2>

        <p class="fs-5 text-secondary mb-4" style="white-space: pre-line;">
          {{ post.content }}
        </p>

        <button
          :disabled="!authUserId"
          class="btn btn-sm"
          :class="{
            'btn-secondary': !authUserId,
            'btn-outline-primary': authUserId && !post.liked,
            'btn-primary': post.liked
          }"
          @click="authUserId && toggleLike(post)"
        >
          ❤️ {{ post.likes_count }}
        </button>
      </div>
    </div>

    <h5 class="fw-bold mb-3">Comments</h5>

<div class="mt-3">
  <div
    v-if="post.comments.length === 0"
    class="fst-italic text-muted mb-2"
  >
    No comments yet. Be the first to comment!
  </div>

  <div
    v-for="comment in post.comments"
    :key="comment.comment_id"
    class="d-flex mb-3"
  >
    <!-- Avatar -->
    <div class="me-2">
      <div
        class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
        style="width: 40px; height: 40px; font-weight: bold;"
      >
        {{ comment.user.username.charAt(0).toUpperCase() }}
      </div>
    </div>

    <!-- Comment content -->
    <div class="flex-grow-1">
      <!-- Username + timestamp -->
      <div class="d-flex align-items-center mb-1">
        <span class="fw-bold me-2">{{ comment.user.username }}</span>
        <small class="text-muted">{{ timeAgo(comment.created_at) }}</small>
        <button
          v-if="authUserId"
          class="btn btn-link btn-sm p-0 ms-2"
          @click="startReply(comment.comment_id)"
        >
          Reply
        </button>
      </div>

      <!-- Comment text -->
      <p class="mb-1">{{ comment.content }}</p>

      <!-- Reply input -->
      <div v-if="replyingTo === comment.comment_id" class="mt-2 ms-4">
        <textarea
          v-model="replyContent"
          class="form-control form-control-sm mb-1"
          rows="2"
          placeholder="Write a reply..."
        ></textarea>
        <button
          class="btn btn-sm btn-outline-primary"
          @click="submitReply"
        >
          Submit Reply
        </button>
      </div>

      <!-- Replies -->
      <div
        v-for="reply in comment.replies"
        :key="reply.comment_id"
        class="d-flex mt-2 ms-4"
      >
        <!-- Reply avatar -->
        <div class="me-2">
          <div
            class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
            style="width: 32px; height: 32px; font-weight: bold; font-size: 0.9rem;"
          >
            {{ reply.user.username.charAt(0).toUpperCase() }}
          </div>
        </div>

        <!-- Reply content -->
        <div class="flex-grow-1">
          <div class="d-flex align-items-center mb-1">
            <span class="fw-bold me-2">{{ reply.user.username }}</span>
            <small class="text-muted">{{ timeAgo(reply.created_at) }}</small>
          </div>
          <p class="mb-0">{{ reply.content }}</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- New comment input -->
<div v-if="authUserId" class="d-flex mt-3">
  <div class="me-2">
    <div
      class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
      style="width: 40px; height: 40px; font-weight: bold;"
    >
      {{ authUserIdInitial }}
    </div>
  </div>
  <div class="flex-grow-1">
    <textarea
      v-model="newComment"
      class="form-control mb-1"
      rows="3"
      placeholder="Write a comment..."
    ></textarea>
    <button class="btn btn-sm btn-primary" @click="submitComment">
      Comment
    </button>
  </div>
</div>
<p v-else class="text-muted mt-3">
  Please <a href="/login">login</a> to comment.
</p>

  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
axios.defaults.withCredentials = true

const route = useRoute()
const postId = route.params.id
const authUserId = window.authUserId || null

const post = ref(null)
const loading = ref(true)
const error = ref("")

const newComment = ref("")
const replyContent = ref("")
const replyingTo = ref(null)

const loadPost = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/posts/${postId}`)
    post.value = res.data
  } catch (e) {
    console.error(e)
    error.value = "Failed to load post."
  } finally {
    loading.value = false
  }
}

const toggleLike = async (post) => {
  try {
    await axios.get("/sanctum/csrf-cookie")
    const res = await axios.post(`/api/posts/${post.post_id}/toggle-like`)
    post.liked = res.data.liked
    post.likes_count = res.data.likes_count
  } catch (err) {
    console.error("Error toggling like:", err.response?.data || err)
  }
}

const authUserIdInitial = window.authUserName
  ? window.authUserName.charAt(0).toUpperCase()
  : '?';


const submitComment = async () => {
  if (!newComment.value.trim()) return
  try {
    await axios.post("/api/comments", {
      post_id: postId,
      user_id: authUserId,
      content: newComment.value,
    })
    newComment.value = ""
    loadPost()
  } catch (e) {
    console.error(e)
  }
}

const startReply = (commentId) => {
  replyingTo.value = commentId
  replyContent.value = ""
}

const submitReply = async () => {
  if (!replyContent.value.trim()) return
  try {
    await axios.post("/api/comments", {
      post_id: postId,
      user_id: authUserId,
      content: replyContent.value,
      parent_id: replyingTo.value,
    })
    replyContent.value = ""
    replyingTo.value = null
    loadPost()
  } catch (e) {
    console.error(e)
  }
}

const timeAgo = (dateStr) => {
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

onMounted(loadPost)
</script>
