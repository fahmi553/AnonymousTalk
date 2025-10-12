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
        <!-- username (link only if user id exists) -->
        <a
          v-if="comment.user && comment.user.user_id"
          :href="'/profile/' + comment.user.user_id"
          class="fw-bold me-2 text-decoration-none text-dark username-link"
        >
          {{ comment.user.username }}
        </a>
        <span v-else-if="comment.user" class="fw-bold me-2">{{ comment.user.username }}</span>
        <span v-else class="fw-bold me-2">Anonymous</span>

        <!-- Dummy badge icon -->
        <i class="fas fa-crown text-warning me-2" title="Gold Member"></i>

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
      </div>

      <p class="mb-1">
        <template v-if="comment.reply_to && comment.reply_to_user_id">
            <a
            :href="'/profile/' + comment.reply_to_user_id"
            class="text-primary fw-bold me-1 text-decoration-none"
            style="cursor: pointer; z-index: 5; position: relative;"
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

      <!-- Reply form -->
      <div v-if="showReplyForm" class="mt-2 ms-4">
        <div class="text-muted small mb-1">
          Replying to <span class="fw-bold">@{{ comment.user?.username || 'Anonymous' }}</span>
        </div>
        <textarea
          v-model="replyContent"
          class="form-control form-control-sm mb-1"
          rows="2"
          placeholder="Write a reply..."
        ></textarea>
        <button class="btn btn-sm btn-outline-primary" @click="submitReply" type="button">
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
import { ref, computed, onMounted } from "vue";

const props = defineProps({
  comment: { type: Object, required: true },
  authUserId: { type: [Number, String], default: null },
  timeAgo: { type: Function, required: true },
});

const emit = defineEmits(["reply", "deleted", "delete-request"]);

const showReplyForm = ref(false);
const replyContent = ref("");

// ✅ Add this computed property
const hasReplyTarget = computed(() => {
  return !!(props.comment.reply_to_user_id && props.comment.reply_to);
});

// ✅ Optional: debug what Vue actually receives
onMounted(() => {
  console.log("Comment data:", props.comment.reply_to, props.comment.reply_to_user_id);
});

const submitReply = () => {
  if (!replyContent.value.trim()) return;

  emit("reply", {
    parent_id: props.comment.comment_id,
    content: `@${props.comment.user?.username || ""} ${replyContent.value.trim()}`,
  });

  replyContent.value = "";
  showReplyForm.value = false;
};

const requestDelete = (id) => emit("delete-request", id);
const propagateReply = (payload) => emit("reply", payload);
const propagateDeleted = (id) => emit("deleted", id);

const stripLeadingMention = (txt) => {
  if (!txt) return txt;
  return txt.replace(/^@\S+\s+/i, "");
};

const isCurrentUser = (userId) => String(userId) === String(window.authUserId);
</script>
