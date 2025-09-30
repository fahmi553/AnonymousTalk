<template>
  <div class="mb-3 border rounded p-2 bg-light">
    <div>
      <strong>{{ comment.user.username }}</strong>
      <small class="text-muted"> • {{ formatDate(comment.created_at) }}</small>
    </div>
    <div class="mb-2">
      {{ comment.content }}
    </div>

    <button
      class="btn btn-sm btn-link p-0"
      @click="showReplyForm = !showReplyForm"
    >
      Reply
    </button>

    <div v-if="showReplyForm" class="mt-2">
      <textarea v-model="replyContent" class="form-control mb-2" rows="2" placeholder="Write a reply..."></textarea>
      <button class="btn btn-primary btn-sm" @click="submitReply">Post Reply</button>
    </div>

    <div v-if="comment.replies && comment.replies.length" class="ms-4 mt-2">
      <CommentItem
        v-for="reply in comment.replies"
        :key="reply.comment_id"
        :comment="reply"
        :post-id="postId"
      />
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: "CommentItem",
  props: {
    comment: Object,
    postId: Number,
  },
  data() {
    return {
      showReplyForm: false,
      replyContent: "",
    };
  },
  methods: {
    formatDate(date) {
      return new Date(date).toLocaleString();
    },
    async submitReply() {
      if (!this.replyContent.trim()) return;

      try {
        const res = await axios.post("/comments", {
          post_id: this.postId,
          user_id: window.authUserId,
          content: this.replyContent,
          parent_id: this.comment.comment_id,
        });

        this.comment.replies.push(res.data);

        this.replyContent = "";
        this.showReplyForm = false;
      } catch (error) {
        console.error(error);
      }
    },
  },
};
</script>
