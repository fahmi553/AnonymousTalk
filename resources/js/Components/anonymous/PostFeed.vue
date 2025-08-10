<template>
  <div>
    <h2>Anonymous Posts</h2>

    <div v-if="loading">Loading posts...</div>
    <div v-if="error">{{ error }}</div>

    <div v-if="posts.length === 0 && !loading">
      <p>No posts yet.</p>
    </div>

    <div v-for="post in posts" :key="post.post_id" class="post">
      <p>{{ post.content }}</p>
      <small>{{ formatDate(post.created_at) }}</small>
      <button @click="moderate(post.post_id)">Moderate</button>

      <hr />
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { usePosts } from '../../composables/usePosts.js'

const { posts, loading, error, loadPosts } = usePosts()

function formatDate(timestamp) {
  return new Date(timestamp).toLocaleString()
}

const moderate = async (postId) => {
  try {
    await axios.patch(`/api/posts/${postId}/status`, { status: 'moderated' })
    await loadPosts()
  } catch (err) {
    console.error('Moderation failed:', err)
  }
}
</script>

<style scoped>
.post {
  margin-bottom: 20px;
}
</style>
