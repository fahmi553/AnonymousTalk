<template>
  <form @submit.prevent="submitComment">
    <input v-model="content" placeholder="Write a comment..." />
    <button type="submit">Post Comment</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  postId: Number,
  userId: Number
})

const content = ref('')

const submitComment = async () => {
  if (!content.value.trim()) return

  await axios.post('/api/comments', {
    post_id: props.postId,
    user_id: props.userId,
    content: content.value
  })

  content.value = ''
  window.dispatchEvent(new Event('comment-created'))
}
</script>
