<template>
  <div>
    <h2>Anonymous Posts</h2>
    <div v-if="posts.length === 0">
      <p>No posts yet.</p>
    </div>
    <div v-for="post in posts" :key="post.id" class="post">
      <p>{{ post.content }}</p>
      <small>{{ formatDate(post.created_at) }}</small>
      <hr />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const posts = ref([])

const loadPosts = async () => {
  try {
    const response = await axios.get('/api/posts')
    posts.value = response.data
  } catch (error) {
    console.error('Failed to load posts:', error)
  }
}

const formatDate = (timestamp) => {
  return new Date(timestamp).toLocaleString()
}

onMounted(() => {
  loadPosts()
  window.addEventListener('post-created', loadPosts)
})
</script>

<style scoped>
.post {
  margin-bottom: 20px;
}
</style>
