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
      <div class="card bg-body shadow-sm border border-secondary-subtle rounded-4 mb-4">
        <div class="card-body p-4 p-md-5">

          <div class="d-flex align-items-center mb-4">
            <router-link
              v-if="post.user?.user_id"
              :to="`/profile/${post.user.user_id}`"
              class="me-3 flex-shrink-0"
            >
              <img
                :src="getAvatarUrl(post.user?.avatar)"
                @error="$event.target.src = '/images/avatars/default.jpg'"
                :alt="post.user?.username"
                class="rounded-circle border bg-white profile-avatar"
                style="width: 50px; height: 50px; object-fit: cover;"
              />
            </router-link>

            <img
                v-else
                :src="'/images/avatars/default.jpg'"
                alt="Anonymous"
                class="rounded-circle me-3 flex-shrink-0 border bg-white"
                style="width: 50px; height: 50px; object-fit: cover;"
            />

            <div class="flex-grow-1">
              <div class="fw-bold fs-5 text-body-emphasis">
                <router-link
                  v-if="post.user?.user_id"
                  :to="`/profile/${post.user.user_id}`"
                  class="text-decoration-none text-body-emphasis profile-link"
                >
                  {{ post.user.username }}
                </router-link>
                <span v-else>Anonymous</span>
              </div>
              <small class="text-secondary">{{ timeAgo(post.created_at) }}</small>
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
          <p class="fs-5 text-body" style="white-space: pre-line; line-height: 1.6;">{{ post.content }}</p>

          <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top border-secondary-subtle flex-wrap gap-2">

            <button
              :disabled="!authUser"
              class="btn d-flex align-items-center rounded-pill px-3 py-2 transition-btn"
              :class="{
                'btn-light text-secondary': !authUser || !post.liked,
                'btn-danger text-white': authUser && post.liked
              }"
              @click="authUser && toggleLike(post)"
            >
              <i class="fas fa-heart me-2" :class="{ 'animate-pulse': post.liked }"></i>
              <span class="fw-bold">{{ post.likes_count }}</span>
            </button>

            <div class="d-flex gap-2">
                <button
                v-if="authUser && post.user?.user_id === authUser.user_id"
                class="btn btn-outline-secondary d-flex align-items-center rounded-pill px-3 py-2"
                @click="openDeletePostModal"
                >
                <i class="fas fa-trash-alt me-2"></i>
                <span>Delete</span>
                </button>

                <button
                v-else-if="authUser"
                class="btn btn-outline-danger d-flex align-items-center rounded-pill px-3 py-2"
                @click="openReportModal(post.post_id, 'post')"
                >
                <i class="fas fa-flag me-2"></i>
                <span>Report</span>
                </button>

                <button
                    class="btn btn-light d-flex align-items-center rounded-pill px-3 py-2 transition-btn"
                    @click="sharePost"
                >
                    <i class="fas fa-share-alt me-2"></i>
                    <span class="fw-bold">Share</span>
                </button>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-5">
        <h4 class="fw-bold mb-4 text-body-emphasis">
          <i class="fas fa-comments me-2 text-primary"></i> Comments
        </h4>

        <div v-if="authUser">
          <CommentForm
            :post-id="post.post_id"
            :auth-user-id="authUser.user_id"
            :user-initial="authUser.username.charAt(0).toUpperCase()"
            :user-avatar="authUser.avatar"
            @success="handleCommentSuccess"
           />
        </div>

        <div v-else class="alert alert-secondary d-flex align-items-center">
          <i class="fas fa-lock me-3 text-muted fs-4"></i>
          <div>
            Please <a href="/login" class="fw-bold text-decoration-underline">login</a> to join the conversation.
          </div>
        </div>

        <div v-if="!loading && post.comments.length === 0" class="text-center py-5 text-muted">
           <i class="far fa-comment-dots fa-3x mb-3 opacity-50"></i>
           <p>No comments yet. Be the first to share your thoughts!</p>
        </div>

        <div class="d-flex flex-column gap-3">
            <CommentThread
            v-for="comment in post.comments"
            :key="comment.comment_id"
            :comment="comment"
            :auth-user-id="authUser?.user_id"
            :time-ago="timeAgo"
            @reply="handleReply"
            @success="handleCommentSuccess"  @deleted="handleDelete"
            @delete-request="openDeleteModal"
            @report-request="openReportModal"
            />
        </div>
      </div>

      <div class="modal fade" id="deletePostModal" tabindex="-1" aria-hidden="true" ref="deletePostModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-body rounded-4 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold">Delete Post?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-secondary">
              Are you sure? This will remove the post and all associated comments permanently.
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger rounded-pill px-4" @click="confirmDeletePost">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true" ref="deleteModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-body rounded-4 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold">Delete Comment?</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-secondary">Are you sure you want to remove this comment?</div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger rounded-pill px-4" @click="confirmDelete">Delete</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true" ref="reportModal">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-body rounded-4 shadow">
            <div class="modal-header border-0">
              <h5 class="modal-title fw-bold text-danger">
                <i class="fas fa-flag me-2"></i>Report Content
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-secondary">Reason</label>
                <select
                    v-model="reportReasonCategory"
                    class="form-select"
                    :class="{ 'is-invalid': showReportError && !reportReasonCategory }"
                    >
                    <option disabled value="">Select a reason...</option>
                    <option value="Harassment or Bullying">Harassment or Bullying</option>
                    <option value="Hate Speech or Discrimination">Hate Speech or Discrimination</option>
                    <option value="Threats or Intimidation">Threats or Intimidation</option>
                    <option value="Spam or Advertising">Spam or Advertising</option>
                    <option value="Trolling or Provocation">Trolling or Provocation</option>
                    <option value="Misinformation">Misinformation or False Claims</option>
                    <option value="Inappropriate Content">Inappropriate or Explicit Content</option>
                    <option value="Abuse of Anonymity">Abuse of Anonymity</option>
                    <option value="Other">Other</option>
                </select>
                <div v-if="showReportError && !reportReasonCategory" class="invalid-feedback">
                    Please select a reason for the report.
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-secondary">Details</label>
                <textarea
                    v-model="reportDetails"
                    class="form-control"
                    rows="3"
                    placeholder="Optional details..."
                ></textarea>
              </div>
            </div>
            <div class="modal-footer border-0">
              <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-danger rounded-pill px-4" @click="submitReport">Submit Report</button>
            </div>
          </div>
        </div>
      </div>

    </div>

    <Teleport to="body">
        <div
            v-if="showToastVisible"
            class="toast-container position-fixed bottom-0 end-0 p-3"
            style="z-index: 1055;"
        >
            <div class="toast align-items-center border-0 shadow-lg show" :class="toastClass" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body fs-6 text-white" :class="{'text-dark': toastClass.includes('bg-warning')}">
                        <i class="fas me-2" :class="toastClass.includes('bg-warning') ? 'fa-exclamation-triangle' : (toastClass.includes('bg-success') ? 'fa-check-circle' : 'fa-exclamation-circle')"></i>
                        {{ toastMessage }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" :class="toastClass.includes('bg-warning') ? '' : 'btn-close-white'" @click="showToastVisible = false"></button>
                </div>
            </div>
        </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue"
import { useRoute } from "vue-router"
import axios from "axios"
import { Modal, Toast } from "bootstrap"
import { useAuth } from '../../store/auth'
import CommentThread from "../CommentThread.vue"
import CommentForm from "./CommentForm.vue"

axios.defaults.withCredentials = true

const route = useRoute()
const { authUser } = useAuth()

const postId = route.params.id
const post = ref({ comments: [] })
const loading = ref(true)
const error = ref("")
const categories = ref([])
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
const showToastVisible = ref(false)

const getAvatarUrl = (filename) => {
  if (!filename) return '/images/avatars/default.jpg';
  if (filename.startsWith('http')) return filename;
  return `/images/avatars/${filename}`;
};

// Inside <script setup>

const sharePost = async () => {
  const shareData = {
    title: post.value.title,
    text: `Check out this post by ${post.value.user?.username || 'Anonymous'}`,
    url: window.location.href,
  };

  try {
    if (navigator.share) {
      await navigator.share(shareData);
    }
    else {
      await navigator.clipboard.writeText(window.location.href);
      showToast("Link copied to clipboard!", "success");
    }
  } catch (err) {
    if (err.name !== 'AbortError') {
      console.error('Share failed:', err);
      showToast("Failed to share.", "error");
    }
  }
};


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
    if (!silent) error.value = "Failed to load post. It may have been deleted."
  } finally {
    loading.value = false
  }
}


const handleCommentSuccess = () => {
    loadPost(true);
    showToast("Comment added successfully", "success");
    window.dispatchEvent(new Event('notification-update-needed'));
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

const showToast = (message, type = "success") => {
  toastMessage.value = message
  toastClass.value = type === "success" ? "bg-success" : (type === "warning" ? "bg-warning text-dark" : "bg-danger");

  showToastVisible.value = true

  setTimeout(() => {
    showToastVisible.value = false
  }, 3000)
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

const openDeletePostModal = () => new Modal(deletePostModal.value).show()
const openDeleteModal = (id) => { deleteTargetId.value = id; new Modal(deleteModal.value).show() }
const openReportModal = (id, type) => { reportTargetId.value = id; reportType.value = type; new Modal(reportModal.value).show() }

const confirmDeletePost = async () => {
  try {
    await axios.delete(`/api/posts/${postId}`)
    showToast("Post deleted.", "success")
    Modal.getInstance(deletePostModal.value).hide()
    setTimeout(() => window.location.href = "/feed", 1000)
    window.dispatchEvent(new Event('notification-update-needed'));
  } catch (e) {
    showToast("Failed to delete.", "error")
  }
}

const confirmDelete = async () => {
  if (!deleteTargetId.value) return
  try {
    await axios.delete(`/api/comments/${deleteTargetId.value}`)
    handleDelete(deleteTargetId.value)
    showToast("Comment deleted.", "success")
    window.dispatchEvent(new Event('notification-update-needed'));
  } catch (e) {
    showToast("Failed to delete.", "error")
  } finally {
    Modal.getInstance(deleteModal.value).hide()
  }
}

const handleDelete = (id) => {
  const removeRecursively = (comments) =>
    comments
      .filter(c => c.comment_id !== id)
      .map(c => ({ ...c, replies: removeRecursively(c.replies || []) }))
  if (post.value?.comments) post.value.comments = removeRecursively(post.value.comments)
}

const submitReport = async () => {
  if (!reportReasonCategory.value) { showReportError.value = true; return }
  try {
    await axios.post("/api/report", {
      target_id: reportTargetId.value,
      type: reportType.value,
      reason: reportReasonCategory.value,
      details: reportDetails.value,
    })
    showToast("Report submitted.")
    Modal.getInstance(reportModal.value).hide()
  } catch (err) { showToast("Failed to report.", "error") }
}

const handleReply = async ({ parent_id, content }) => {
  if (!content?.trim()) return

  try {
    const res = await axios.post("/api/comments", {
      post_id: postId,
      user_id: authUser.value.user_id,
      content: content.trim(),
      parent_id,
    })

    if (res.data.is_flagged) {
        showToast(res.data.message, "warning")
    } else {
        showToast("Replied!", "success")
        loadPost(true)
        window.dispatchEvent(new Event('notification-update-needed'));
    }

  } catch (e) {
    if (e.response && e.response.status === 422 && e.response.data.is_flagged) {
        showToast(e.response.data.message, "warning")
    }
    else if (e.response && e.response.status === 403 && e.response.data.banned) {
        showToast(e.response.data.message, "error")
        setTimeout(() => window.location.href = '/login', 2000)
    }
    else {
        console.error(e)
        showToast("Failed to reply.", "error")
    }
  }
}

onMounted(() => {
  loadPost()
  fetchCategories()
})
</script>

<style scoped>
.transition-btn {
  transition: all 0.2s ease;
}
.animate-pulse {
  animation: pulse 0.5s ease-in-out;
}
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.3); }
  100% { transform: scale(1); }
}

.profile-avatar {
  transition: transform 0.2s ease;
}
.profile-avatar:hover {
  transform: scale(1.05);
  cursor: pointer;
}

.profile-link:hover {
  text-decoration: underline !important;
  color: var(--bs-primary) !important;
}
</style>
