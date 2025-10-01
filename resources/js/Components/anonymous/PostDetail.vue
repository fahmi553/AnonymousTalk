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
          <span class="badge bg-secondary text-white">{{ post.category }}</span>
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
      <div v-if="!loading && post.comments.length === 0" class="fst-italic text-muted mb-2">
        No comments yet. Be the first to comment!
      </div>

      <CommentThread
        v-for="comment in post.comments"
        :key="comment.comment_id"
        :comment="comment"
        :auth-user-id="authUserId"
        :time-ago="timeAgo"
        @reply="handleReply"
        @deleted="handleDelete"
        @delete-request="openDeleteModal"
      />
    </div>

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

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" ref="deleteModal">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-3 shadow">
          <div class="modal-header border-0">
            <h5 class="modal-title">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this comment?
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
      <div
        id="liveToast"
        class="toast align-items-center text-white border-0"
        :class="toastClass"
        role="alert"
        aria-live="assertive"
        aria-atomic="true"
        ref="toastEl"
      >
        <div class="d-flex">
          <div class="toast-body">{{ toastMessage }}</div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            data-bs-dismiss="toast"
            aria-label="Close"
          ></button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
import { Modal, Toast } from "bootstrap"

axios.defaults.withCredentials = true

const route = useRoute()
const postId = route.params.id
const authUserId = window.authUserId || null
const authUserIdInitial = window.authUserName
  ? window.authUserName.charAt(0).toUpperCase()
  : "?"

const post = ref({ comments: [] })
const loading = ref(true)
const error = ref("")
const newComment = ref("")
const deleteTargetId = ref(null)
const deleteModal = ref(null)

// Toast state
const toastMessage = ref("")
const toastClass = ref("bg-success")
const toastEl = ref(null)
let toastInstance = null

const showToast = (message, type = "success") => {
  toastMessage.value = message
  toastClass.value = type === "success" ? "bg-success" : "bg-danger"
  if (!toastInstance) {
    toastInstance = new Toast(toastEl.value)
  }
  toastInstance.show()
}

const handleDelete = (id) => {
  const removeRecursively = (comments) => {
    return comments
      .filter(c => c.comment_id !== id)
      .map(c => ({
        ...c,
        replies: removeRecursively(c.replies || [])
      }))
  }
  if (post.value && post.value.comments) {
    post.value.comments = removeRecursively(post.value.comments)
  }
}

const openDeleteModal = (id) => {
  deleteTargetId.value = id
  const modal = new Modal(deleteModal.value)
  modal.show()
}

const confirmDelete = async () => {
  if (!deleteTargetId.value) return
  try {
    await axios.get("/sanctum/csrf-cookie")
    await axios.delete(`/api/comments/${deleteTargetId.value}`)
    handleDelete(deleteTargetId.value)
    showToast("Comment deleted successfully", "success")
  } catch (e) {
    console.error("Failed to delete comment:", e.response?.data || e)
    showToast("Failed to delete comment", "error")
  } finally {
    const modal = Modal.getInstance(deleteModal.value)
    modal.hide()
    deleteTargetId.value = null
  }
}

const loadPost = async () => {
  loading.value = true
  try {
    const res = await axios.get(`/api/posts/${postId}`)
    post.value = res.data
    post.value.comments = post.value.comments || []
    post.value.comments.forEach(c => {
      c.replies = c.replies || []
    })
  } catch (e) {
    console.error(e)
    error.value = "Failed to load post."
  } finally {
    loading.value = false
  }
}

const toggleLike = async (postItem) => {
  try {
    await axios.get("/sanctum/csrf-cookie")
    const res = await axios.post(`/api/posts/${postItem.post_id}/toggle-like`)
    postItem.liked = res.data.liked
    postItem.likes_count = res.data.likes_count
  } catch (err) {
    console.error("Error toggling like:", err.response?.data || err)
  }
}

const submitComment = async () => {
  if (!newComment.value.trim()) return
  try {
    await axios.get("/sanctum/csrf-cookie")
    await axios.post("/api/comments", {
      post_id: postId,
      user_id: authUserId,
      content: newComment.value.trim()
    })
    newComment.value = ""
    await loadPost()
    showToast("Comment added successfully", "success")
  } catch (e) {
    console.error(e)
    showToast("Failed to submit comment", "error")
  }
}

const handleReply = async ({ parentId, content }) => {
  if (!content || !content.trim()) return
  try {
    await axios.get("/sanctum/csrf-cookie")
    await axios.post("/api/comments", {
      post_id: postId,
      user_id: authUserId,
      content: content.trim(),
      parent_id: parentId
    })
    await loadPost()
    showToast("Reply added successfully", "success")
  } catch (e) {
    console.error("Failed to post reply", e)
    showToast("Failed to post reply", "error")
  }
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

onMounted(loadPost)
</script>

<script>
import { defineComponent, ref } from "vue"

export default {
  components: {
    CommentThread: defineComponent({
      name: "CommentThread",
      props: {
        comment: { type: Object, required: true },
        authUserId: { type: [Number, String], default: null },
        timeAgo: { type: Function, required: true }
      },
      emits: ["reply", "deleted", "delete-request"],
      setup(props, { emit }) {
        const showReplyForm = ref(false)
        const replyContent = ref("")

        const submitReply = () => {
          if (!replyContent.value.trim()) return
          emit("reply", {
            parentId: props.comment.comment_id,
            content: replyContent.value
          })
          replyContent.value = ""
          showReplyForm.value = false
        }

        const requestDelete = (id) => {
          emit("delete-request", id)
        }

        const stripLeadingMention = (txt) => {
          if (!txt) return txt
          return txt.replace(/^@\S+\s+/i, "")
        }

        const propagateReply = (payload) => emit("reply", payload)
        const propagateDeleted = (id) => emit("deleted", id)

        return {
          showReplyForm,
          replyContent,
          submitReply,
          requestDelete,
          stripLeadingMention,
          propagateReply,
          propagateDeleted
        }
      },
      template: `
        <div class="d-flex mb-3">
          <div class="me-2">
            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center"
              style="width: 40px; height: 40px; font-weight: bold;">
              {{ (comment.user?.username || '?').charAt(0).toUpperCase() }}
            </div>
          </div>

          <div class="flex-grow-1">
            <div class="d-flex align-items-center mb-1">
              <span class="fw-bold me-2">{{ comment.user?.username }}</span>
              <small class="text-muted">{{ timeAgo(comment.created_at) }}</small>

              <button v-if="authUserId"
                class="btn btn-link btn-sm p-0 ms-2"
                @click="showReplyForm = !showReplyForm">
                Reply
              </button>

              <button v-if="authUserId == comment.user?.user_id"
                class="btn btn-link btn-sm text-danger p-0 ms-2"
                @click="requestDelete(comment.comment_id)">
                Delete
              </button>
            </div>

            <p class="mb-1">
              <template v-if="comment.reply_to">
                <a :href="'/user/' + comment.reply_to" class="text-primary fw-bold me-1">@{{ comment.reply_to }}</a>
                {{ stripLeadingMention(comment.content) }}
              </template>
              <template v-else>
                {{ comment.content }}
              </template>
            </p>

            <div v-if="showReplyForm" class="mt-2 ms-4">
              <div class="text-muted small mb-1">
                Replying to <span class="fw-bold">@{{ comment.user?.username }}</span>
              </div>
              <textarea v-model="replyContent" class="form-control form-control-sm mb-1" rows="2" placeholder="Write a reply..."></textarea>
              <button class="btn btn-sm btn-outline-primary" @click="submitReply">Submit Reply</button>
            </div>

            <div v-if="comment.replies && comment.replies.length" class="ms-4 mt-2">
              <CommentThread
                v-for="reply in comment.replies"
                :key="reply.comment_id"
                :comment="reply"
                :auth-user-id="authUserId"
                :time-ago="timeAgo"
                @reply="propagateReply"
                @deleted="propagateDeleted"
                @delete-request="requestDelete"
              />
            </div>
          </div>
        </div>
      `
    })
  }
}
</script>
