<template>
  <form @submit.prevent="submitPost" class="post-form">
    <textarea
      v-model="content"
      placeholder="Write anonymously..."
      rows="4"
    ></textarea>
    <div v-if="error" style="color: red">{{ error }}</div>
    <button type="submit" :disabled="loading">Post</button>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import PostService from '../../services/PostService'

const content = ref('')
const loading = ref(false)
const error = ref(null)

const submitPost = async () => {
  error.value = null
  if (!content.value.trim()) {
    error.value = 'Post cannot be empty.'
    return
  }

  loading.value = true
  try {
    await PostService.createPost(content.value)
    content.value = ''
    window.dispatchEvent(new Event('post-created'))
  } catch (err) {
    error.value = 'Failed to post. Please try again.'
    console.error(err)
  } finally {
    loading.value = false
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
}
button {
  margin-top: 10px;
  padding: 8px 12px;
  font-weight: bold;
}
</style>
