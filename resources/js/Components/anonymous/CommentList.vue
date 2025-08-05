<template>
  <div>
    <h4>Comments</h4>
    <div v-for="comment in comments" :key="comment.comment_id">
      <strong>{{ comment.user.username }}:</strong> {{ comment.content }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  postId: Number
})

const comments = ref([])

const loadComments = async () => {
  const res = await axios.get(`/api/posts/${props.postId}/comments`)
  comments.value = res.data
}

onMounted(() => {
  loadComments()
  window.addEventListener('comment-created', loadComments)
})

watch(() => props.postId, loadComments)
</script>
