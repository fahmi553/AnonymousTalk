<template>
  <div class="d-flex mb-3">
    <div class="me-2 flex-shrink-0">
    <img
        :src="comment.user?.avatar ? `/images/avatars/${comment.user.avatar}` : '/images/avatars/default.jpg'"
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
        ></textarea>
        <button class="btn btn-sm btn-primary" @click="submitReply" type="button">
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
          @reply="propagateReply"
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

const props = defineProps({
  comment: { type: Object, required: true },
  authUserId: { type: [Number, String], default: null },
  timeAgo: { type: Function, required: true },
});

const emit = defineEmits(["reply", "deleted", "delete-request", "report-request"]);

const showReplyForm = ref(false);
const replyContent = ref("");

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
const stripLeadingMention = (txt) => txt?.replace(/^@\S+\s+/i, "") || "";
const propagateReport = (targetId, type) => {
  emit('report-request', targetId, type);
}

</script>
