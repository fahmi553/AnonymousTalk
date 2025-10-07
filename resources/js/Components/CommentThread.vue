<template>
  <div class="d-flex mb-3">
    <div class="me-2">
      <div
        class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center"
        style="width: 40px; height: 40px; font-weight: bold;"
      >
        {{ (comment.user?.username || '?').charAt(0).toUpperCase() }}
      </div>
    </div>

    <div class="flex-grow-1">
      <div class="d-flex align-items-center mb-1">
        <router-link
          v-if="comment.user"
          :to="usernameLink(comment.user.user_id)"
          class="fw-bold me-2 text-decoration-none username-link"
        >
          {{ comment.user.username }}
        </router-link>
        <span v-else class="fw-bold me-2">Anonymous</span>

        <!-- dummy badge icon -->
        <i class="fas fa-crown text-warning me-2" title="Badge"></i>

        <small class="text-muted">{{ timeAgo(comment.created_at) }}</small>

        <button
          v-if="authUserId"
          class="btn btn-link btn-sm p-0 ms-2"
          @click="showReplyForm = !showReplyForm"
        >
          Reply
        </button>

        <button
          v-if="String(authUserId) === String(comment.user?.user_id)"
          class="btn btn-link btn-sm text-danger p-0 ms-2"
          @click="requestDelete(comment.comment_id)"
        >
          Delete
        </button>
      </div>

      <p class="mb-1">
        <template v-if="comment.reply_to">
          <router-link
            :to="replyToLink()"
            class="text-primary fw-bold me-1 text-decoration-none"
          >
            @{{ comment.reply_to.username }}
          </router-link>
          {{ stripLeadingMention(comment.content) }}
        </template>
        <template v-else>
          {{ comment.content }}
        </template>
      </p>

      <!-- Reply form -->
      <div v-if="showReplyForm" class="mt-2 ms-4">
        <div class="text-muted small mb-1">
          Replying to <span class="fw-bold">@{{ comment.user?.username }}</span>
        </div>
        <textarea
          v-model="replyContent"
          class="form-control form-control-sm mb-1"
          rows="2"
          placeholder="Write a reply..."
        ></textarea>
        <button class="btn btn-sm btn-outline-primary" @click="submitReply">
          Submit Reply
        </button>
      </div>

      <!-- nested replies -->
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
</template>

<script setup>
import { ref, computed } from "vue"

const props = defineProps({
  comment: { type: Object, required: true },
  authUserId: { type: [Number, String], default: null },
  timeAgo: { type: Function, required: true }
})

const emit = defineEmits(["reply", "deleted", "delete-request"])

const showReplyForm = ref(false)
const replyContent = ref("")

const submitReply = () => {
  const content = replyContent.value.trim()
  if (!content) return
  emit("reply", {
    parentId: props.comment.comment_id,
    content
  })
  replyContent.value = ""
  showReplyForm.value = false
}

const requestDelete = (id) => emit("delete-request", id)

const stripLeadingMention = (txt) => {
  if (!txt) return txt
  return txt.replace(/^@\S+\s+/i, "")
}

const propagateReply = (payload) => emit("reply", payload)
const propagateDeleted = (id) => emit("deleted", id)

const isCurrentUser = (userId) => {
  return String(userId) === String(window.authUserId)
}

// build user profile route path for a given user id
const usernameLink = (userId) => {
  return isCurrentUser(userId) ? '/profile' : `/profile/${userId}`
}

// build link for reply_to (supports new object format or legacy string)
const replyToLink = () => {
  const rt = props.comment.reply_to
  if (!rt) return '/profile'
  // if reply_to is object with user_id
  if (typeof rt === 'object' && rt.user_id) {
    return isCurrentUser(rt.user_id) ? '/profile' : `/profile/${rt.user_id}`
  }
  // legacy: reply_to might be a username string — fallback to profile root
  return '/profile'
}
</script>

<style scoped>
.username-link {
  color: #000;               /* black by default */
}
.username-link:hover {
  color: var(--bs-primary);  /* bootstrap primary on hover */
  text-decoration: underline;
}
</style>
