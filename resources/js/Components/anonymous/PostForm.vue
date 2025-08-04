<template>
  <form @submit.prevent="submitPost" class="post-form">
    <textarea
      v-model="content"
      placeholder="Write your anonymous message..."
      rows="4"
    ></textarea>
    <button type="submit">Post</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const content = ref('')

const submitPost = async () => {
  if (!content.value.trim()) return

  try {
    await axios.post('/api/posts', { content: content.value })
    content.value = ''
    window.dispatchEvent(new Event('post-created')) // tell feed to refresh
  } catch (error) {
    console.error('Post failed:', error)
  }
}
</script>

<style scoped>
.post-form {
  margin-bottom: 20px;
}
textarea {
  width: 100%;
  padding: 10px;
  font-size: 1rem;
  resize: vertical;
}
button {
  margin-top: 10px;
  padding: 8px 16px;
  font-weight: bold;
}
</style>
