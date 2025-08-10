<template>
  <div class="mb-4">
    <h4>Create a New Post</h4>
    <form @submit.prevent="submitPost" class="card p-3 shadow-sm">
      <div class="mb-3">
        <input
          v-model="title"
          type="text"
          class="form-control"
          placeholder="Post Title (optional)"
        />
      </div>

      <div class="mb-3">
        <textarea
          v-model="content"
          class="form-control"
          rows="4"
          placeholder="What's on your mind?"
        ></textarea>
      </div>

      <div class="mb-3">
        <select v-model="category" class="form-select">
          <option disabled value="">Select Category</option>
          <option>General</option>
          <option>Help</option>
          <option>News</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Post</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const title = ref('')
const content = ref('')
const category = ref('')

const submitPost = async () => {
  if (!content.value.trim()) return

  await axios.post('/api/posts', {
    title: title.value,
    content: content.value,
    category: category.value || 'General'
  })

  title.value = ''
  content.value = ''
  category.value = ''

  window.dispatchEvent(new Event('post-created'))
}
</script>
