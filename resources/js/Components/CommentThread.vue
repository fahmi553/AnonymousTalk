<template>
  <div class="d-flex mb-3 position-relative">
    <Teleport to="body">
        <div v-if="showToast" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast align-items-center border-0 shadow-lg show" :class="toastClass" role="alert">
                <div class="d-flex">
                    <div class="toast-body fs-6" :class="toastClass.includes('text-dark') ? 'text-dark' : 'text-white'">
                        <i class="fas me-2" :class="toastClass.includes('bg-warning') ? 'fa-exclamation-triangle' : 'fa-exclamation-circle'"></i>
                        {{ toastMessage }}
                    </div>
                    <button type="button" class="btn-close me-2 m-auto" :class="toastClass.includes('text-dark') ? '' : 'btn-close-white'" @click="showToast = false"></button>
                </div>
            </div>
        </div>
    </Teleport>

    <div class="me-2 flex-shrink-0">
      <img
        :src="comment.user?.avatar ? (comment.user.avatar.startsWith('http') ? comment.user.avatar : `/images/avatars/${comment.user.avatar}`) : '/images/avatars/default.jpg'"
        alt="User Avatar"
        class="rounded-circle border bg-white"
        style="width: 40px; height: 40px; object-fit: cover;"
      />
    </div>

    <div class="flex-grow-1">
      <div class="d-flex align-items-center mb-1">
        <a
            v-if="comment.user && comment.user.user_id"
            :href="'/profile/' + comment.user.user_id"
            class="fw-bold me-2 text-decoration-none text-body-emphasis username-link"
        >
            {{ comment.user.username }}
        </a>
        <span v-else-if="comment.user" class="fw-bold me-2">{{ comment.user.username }}</span>
        <span v-else class="fw-bold me-2">Anonymous</span>

        <template v-if="comment.user && comment.user.badges && comment.user.badges.length">
            <span
            v-for="badge in comment.user.badges"
            :key="badge.badge_id"
            class="badge bg-secondary text-white me-1"
            :title="badge.description"
            style="font-size: 0.65rem;"
            >
            {{ badge.badge_name }}
            </span>
        </template>
        <small class="text-muted">{{ timeAgo(comment.created_at) }}</small>

        <button
            v-if="authUserId"
            class="btn btn-link btn-sm p-0 ms-2"
            @click="showReplyForm = !showReplyForm"
            type="button"
        >
            Reply
        </button>

        <button
            v-if="authUserId == comment.user?.user_id"
            class="btn btn-link btn-sm text-danger p-0 ms-2"
            @click="requestDelete(comment.comment_id)"
            type="button"
        >
            Delete
        </button>

        <button
            v-if="authUserId && comment.user?.user_id != authUserId"
            class="btn btn-link btn-sm text-danger p-0 ms-2"
            type="button"
            @click.prevent="$emit('report-request', comment.comment_id, 'comment')"
        >
            Report
        </button>
       </div>

      <p class="mb-1">
        <template v-if="comment.reply_to && comment.reply_to_user_id">
          <a
            :href="'/profile/' + comment.reply_to_user_id"
            class="text-primary fw-bold me-1 text-decoration-none"
          >
            @{{ comment.reply_to }}
          </a>
          {{ stripLeadingMention(comment.content) }}
        </template>
        <template v-else-if="comment.reply_to">
          <span class="text-primary fw-bold me-1">@{{ comment.reply_to }}</span>
          {{ stripLeadingMention(comment.content) }}
        </template>
        <template v-else>
          {{ comment.content }}
        </template>
      </p>

      <div v-if="showReplyForm" class="mt-2 ms-4">
        <div class="text-muted small mb-1">
          Replying to <span class="fw-bold">@{{ comment.user?.username || 'Anonymous' }}</span>
        </div>
        <textarea
          v-model="replyContent"
          class="form-control form-control-sm bg-body mb-1"
          rows="2"
          placeholder="Write a reply..."
          :disabled="isSubmitting"
        ></textarea>

        <button
            class="btn btn-sm btn-primary"
            @click="submitReply"
            type="button"
            :disabled="isSubmitting || !replyContent.trim()"
        >
          <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
          Submit Reply
        </button>
      </div>

      <div v-if="comment.replies && comment.replies.length" class="ms-4 mt-2">
        <CommentThread
          v-for="reply in comment.replies"
          :key="reply.comment_id"
          :comment="reply"
          :auth-user-id="authUserId"
          :time-ago="timeAgo"
          @success="$emit('success')"
          @deleted="propagateDeleted"
          @delete-request="requestDelete"
          @report-request="propagateReport"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from "axios";
import { useRoute } from "vue-router";

const props = defineProps({
  comment: { type: Object, required: true },
  authUserId: { type: [Number, String], default: null },
  timeAgo: { type: Function, required: true },
});

const emit = defineEmits(["success", "deleted", "delete-request", "report-request"]);
const route = useRoute();

const showReplyForm = ref(false);
const replyContent = ref("");
const isSubmitting = ref(false);
const showToast = ref(false);
const toastMessage = ref("");
const toastClass = ref("");

const submitReply = async () => {
  if (!replyContent.value.trim()) return;

  isSubmitting.value = true;
  showToast.value = false;

  const postId = props.comment.post_id || route.params.id;

  try {
    const res = await axios.post("/api/comments", {
      post_id: postId,
      user_id: props.authUserId,
      content: `@${props.comment.user?.username || "Anonymous"} ${replyContent.value.trim()}`,
      parent_id: props.comment.comment_id,
    });

    if (res.data.status === 'warning' || res.data.is_flagged) {
        triggerToast(res.data.message, 'bg-warning text-dark');
        if (res.data.is_flagged) replyContent.value = "";
        return;
    }

    emit("success");
    replyContent.value = "";
    showReplyForm.value = false;

  } catch (e) {
    if (e.response) {
        const status = e.response.status;
        const data = e.response.data;

        if (status === 403 && (data.message === 'Your email address is not verified.' || data.message.includes('verify'))) {
            triggerToast("Please verify your email address to reply.", "bg-warning text-dark");
        }
        else if (status === 403 && data.banned) {
            triggerToast(data.message, "bg-danger text-white");
            setTimeout(() => window.location.href = '/login', 2000);
        }
        else if (data.is_flagged) {
            triggerToast(data.message, 'bg-warning text-dark');
            replyContent.value = "";
        }
        else {
            triggerToast(data.message || "Failed to reply.", "bg-danger text-white");
        }
    } else {
        triggerToast("Network error. Please try again.", "bg-danger text-white");
    }
  } finally {
    isSubmitting.value = false;
  }
};

const triggerToast = (msg, cssClass) => {
  toastMessage.value = msg;
  toastClass.value = cssClass;
  showToast.value = true;
  setTimeout(() => showToast.value = false, 4000);
};

const requestDelete = (id) => emit("delete-request", id);
const propagateDeleted = (id) => emit("deleted", id);
const stripLeadingMention = (txt) => txt?.replace(/^@\S+\s+/i, "") || "";
const propagateReport = (targetId, type) => {
  emit('report-request', targetId, type);
}
</script>
