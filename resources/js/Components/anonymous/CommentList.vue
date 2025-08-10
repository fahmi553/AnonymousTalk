<template>
  <div class="mt-3 ps-3">
    <h6>Comments</h6>
    <div v-if="comments.length === 0">No comments yet.</div>

    <div v-for="comment in comments" :key="comment.comment_id" class="border-top pt-2">
      <strong>{{ comment.user?.username ?? 'Anonymous' }}</strong>:
      <span>{{ comment.content }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
  postId: Number
})

const comments = ref([])

const loadComments = async () => {
  try {
    const response = await axios.get(`/api/posts/${props.postId}/comments`)
    console.log(`Comments for post ${props.postId}:`, response.data)
    comments.value = response.data
  } catch (error) {
    console.error('Failed to load comments', error)
  }
}

onMounted(() => {
  loadComments()
  window.addEventListener('comment-added', loadComments)
})
</script>

