<template>
  <div class="container my-4 my-md-5">
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-3 text-muted">Loading post...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger shadow-sm">
      <i class="fas fa-exclamation-triangle me-2"></i> {{ error }}
    </div>

    <div v-else-if="post">
      <div class="card bg-body shadow-sm border border-secondary rounded-lg mb-4">
        <div class="card-body p-4 p-md-5">
          <div class="d-flex align-items-center mb-4">
            <div
              class="rounded-circle bg-primary bg-gradient text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0"
              style="width: 50px; height: 50px; font-size: 1.25rem; font-weight: 500;"
            >
              {{ (post.user?.username || '?').charAt(0).toUpperCase() }}
            </div>
            <div class="flex-grow-1">
              <div class="fw-bold fs-5 text-body-emphasis">
                {{ post.user?.username ?? 'Anonymous' }}
              </div>
              <small class="text-muted">{{ timeAgo(post.created_at) }}</small>
            </div>
          </div>

          <div v-if="post.category" class="mb-3">
            <span
              class="badge rounded-pill px-3 py-2 fs-6 shadow-sm"
              :style="{ backgroundColor: getCategoryColor(post.category) }"
            >
              {{ post.category }}
            </span>
          </div>

          <h1 class="fw-bold text-body-emphasis mb-3">{{ post.title }}</h1>
          <p class="fs-5" style="white-space: pre-line;">{{ post.content }}</p>

          <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top flex-wrap gap-2">
            <button
              :disabled="!authUserId"
              class="btn d-flex align-items-center rounded-pill px-3 py-2"
              :class="{
                'btn-secondary': !authUserId || !post.liked,
                'btn-danger': authUserId && post.liked
              }"
              @click="authUserId && toggleLike(post)"
            >
              <i class="fas fa-heart me-2"></i>
              <span class="fw-bold">{{ post.likes_count }}</span>
            </button>

            <button
              v-if="authUserId && post.user?.user_id == authUserId"
              class="btn btn-secondary d-flex align-items-center rounded-pill px-3 py-2"
              @click="openDeletePostModal"
            >
              <i class="fas fa-trash-alt me-2"></i>
              <span>Delete Post</span>
            </button>

            <button
              v-else-if="authUserId"
              class="btn btn-outline-danger d-flex align-items-center rounded-pill px-3 py-2"
              @click="openReportModal(post.post_id, 'post')"
            >
              <i class="fas fa-flag me-2"></i>
              <span>Report Post</span>
            </button>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <h4 class="fw-bold mb-3">
          <i class="fas fa-comments me-2 text-primary"></i> Comments
        </h4>

        <div v-if="authUserId">
          <CommentForm
            :post-id="post.post_id"
            :auth-user-id="authUserId"
            :user-initial="authUserIdInitial"
            @success="handleCommentSuccess"
          />
        </div>

        <p v-else class="text-muted mt-3 alert alert-secondary">
          Please <a href="/login" class="fw-bold">login</a> to comment on this post.
        </p>

        <div v-if="!loading && post.comments.length === 0" class="fst-italic text-muted my-4">
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
          @report-request="openReportModal"
        />
      </div>

      <div class="modal fade" id="deletePostModal" tabindex="-1" aria-hidden="true" ref="deletePostModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-body rounded-3 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold">
                <i class="fas fa-exclamation-triangle text-danger me-2"></i> Confirm Deletion
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              Are you sure you want to delete this post? All comments will be deleted as well. This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="confirmDeletePost">Delete Post</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" ref="deleteModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-body rounded-3 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold">
                <i class="fas fa-exclamation-triangle text-danger me-2"></i> Confirm Delete
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Are you sure you want to delete this comment?</div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="confirmDelete">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true" ref="reportModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-body rounded-3 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold">
                <i class="fas fa-flag text-danger me-2"></i>
                Report {{ reportType === 'post' ? 'Post' : 'Comment' }}
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-semibold">Reason</label>
                <select
                  v-model="reportReasonCategory"
                  class="form-select bg-body"
                  :class="{'is-invalid': showReportError && !reportReasonCategory}"
                >
                  <option disabled value="">Select a reason...</option>
                  <option value="Spam">Spam</option>
                  <option value="Nudity/Sexual">Nudity/Sexual</option>
                  <option value="Hate Speech">Hate Speech</option>
                  <option value="Self-Harm">Self-Harm</option>
                  <option value="Harassment">Harassment</option>
                  <option value="Misinformation">Misinformation</option>
                  <option value="Trolling">Trolling</option>
                  <option value="Other">Other</option>
                </select>
                <div class="invalid-feedback">Please select a reason.</div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-semibold">Additional Details (Optional)</label>
                <textarea
                  v-model="reportDetails"
                  class="form-control bg-body"
                  rows="3"
                  placeholder="Provide specific context..."
                ></textarea>
              </div>
            </div>

            <div class="modal-footer border-0">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger" @click="submitReport">Submit Report</button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-white border-0 shadow-lg" :class="toastClass" role="alert" aria-live="assertive" aria-atomic="true" ref="toastEl">
          <div class="d-flex">
            <div class="toast-body">{{ toastMessage }}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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
import CommentThread from "../CommentThread.vue"
import CommentForm from "./CommentForm.vue"

axios.defaults.withCredentials = true

const route = useRoute()
const postId = route.params.id
const authUserId = window.authUserId || null
const authUserIdInitial = window.authUserName ? window.authUserName.charAt(0).toUpperCase() : "?"
const post = ref({ comments: [] })
const loading = ref(true)
const error = ref("")
const deleteTargetId = ref(null)
const deleteModal = ref(null)
const deletePostModal = ref(null)
const reportModal = ref(null)
const reportTargetId = ref(null)
const reportType = ref('post')
const reportReasonCategory = ref("")
const reportDetails = ref("")
const showReportError = ref(false)
const toastMessage = ref("")
const toastClass = ref("bg-success")
const toastEl = ref(null)
let toastInstance = null

const categories = ref([])

const openReportModal = (targetId, type = 'comment') => {
  reportTargetId.value = targetId
  reportType.value = type
  reportReasonCategory.value = ""
  reportDetails.value = ""
  showReportError.value = false
  const modal = new Modal(reportModal.value)
  modal.show()
}

const submitReport = async () => {
  if (!reportReasonCategory.value) {
    showReportError.value = true
    return
  }

  try {
    await axios.get("/sanctum/csrf-cookie")
    await axios.post("/api/report", {
      target_id: reportTargetId.value,
      type: reportType.value,
      reason: reportReasonCategory.value,
      details: reportDetails.value.trim(),
    })
    showToast("Report submitted successfully")
    Modal.getInstance(reportModal.value).hide()
  } catch (err) {
    console.error("Failed to submit report:", err)
    showToast("Failed to submit report", "error")
  }
}

const openDeletePostModal = () => {
  const modal = new Modal(deletePostModal.value)
  modal.show()
}

const showToast = (message, type = "success") => {
  toastMessage.value = message
  if (type === "warning") {
      toastClass.value = "bg-warning text-dark";
  } else {
      toastClass.value = type === "success" ? "bg-success" : "bg-danger";
  }
  if (!toastInstance) toastInstance = new Toast(toastEl.value)
  toastInstance.show()
}

const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Failed to load categories', err)
  }
}

const loadPost = async (silent = false) => {
  if (!silent) loading.value = true

  try {
    const res = await axios.get(`/api/posts/${postId}`)
    post.value = res.data
    post.value.comments = post.value.comments || []
    post.value.comments.forEach(c => c.replies = c.replies || [])
  } catch (e) {
    console.error(e)
    if (!silent) error.value = "Failed to load post."
  } finally {
    loading.value = false
  }
}

const handleCommentSuccess = () => {
    loadPost(true);
    showToast("Comment added successfully", "success");
}

const confirmDeletePost = async () => {
  try {
    await axios.get("/sanctum/csrf-cookie")
    await axios.delete(`/api/posts/${postId}`)
    showToast("Post deleted successfully", "success")
    Modal.getInstance(deletePostModal.value).hide()
    setTimeout(() => window.location.href = "/", 1000)
  } catch (e) {
    console.error("Failed to delete post:", e)
    showToast("Failed to delete post", "error")
    Modal.getInstance(deletePostModal.value).hide()
  }
}

const openDeleteModal = (id) => {
  deleteTargetId.value = id
  new Modal(deleteModal.value).show()
}

const confirmDelete = async () => {
  if (!deleteTargetId.value) return
  try {
    await axios.get("/sanctum/csrf-cookie")
    await axios.delete(`/api/comments/${deleteTargetId.value}`)
    handleDelete(deleteTargetId.value)
    showToast("Comment deleted successfully", "success")
  } catch (e) {
    console.error("Failed to delete comment:", e)
    showToast("Failed to delete comment", "error")
  } finally {
    Modal.getInstance(deleteModal.value).hide()
    deleteTargetId.value = null
  }
}

const handleDelete = (id) => {
  const removeRecursively = (comments) =>
    comments
      .filter(c => c.comment_id !== id)
      .map(c => ({ ...c, replies: removeRecursively(c.replies || []) }))
  if (post.value?.comments) post.value.comments = removeRecursively(post.value.comments)
}

const getCategoryColor = (categoryName) => {
  const cat = categories.value.find(c => c.name === categoryName)
  return cat ? cat.color_code : '#6c757d'
}

const toggleLike = async (post) => {
  try {
    await axios.get('/sanctum/csrf-cookie')
    const res = await axios.post(`/api/posts/${post.post_id}/toggle-like`)
    post.liked = res.data.liked
    post.likes_count = res.data.likes_count
  } catch (err) {
    console.error('Error toggling like:', err)
  }
}

const handleReply = async ({ parent_id, content }) => {
  if (!content?.trim()) return
  try {
    await axios.get("/sanctum/csrf-cookie")
    const res = await axios.post("/api/comments", {
      post_id: postId,
      user_id: authUserId,
      content: content.trim(),
      parent_id,
    })

    if (res.data.is_flagged) {
        showToast(res.data.message, "warning");
    } else {
        showToast("Reply added successfully", "success");
        await loadPost()
    }

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

onMounted(() => {
  loadPost()
  fetchCategories()
})
</script>
