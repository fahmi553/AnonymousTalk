<template>
  <form @submit.prevent="submitComment" class="mt-3">
    <div class="input-group">
      <input
        v-model="content"
        type="text"
        class="form-control"
        placeholder="Write a comment..."
      />
      <button type="submit" class="btn btn-outline-secondary">Comment</button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { defineProps } from 'vue'

const props = defineProps(['postId', 'userId'])

const content = ref('')

const submitComment = async () => {
  if (!content.value.trim()) return

  await axios.post('/api/comments', {
  post_id: props.postId,
  user_id: props.userId,
  content: content.value
})

content.value = ''
window.dispatchEvent(new Event('comment-added'))
}
</script>
