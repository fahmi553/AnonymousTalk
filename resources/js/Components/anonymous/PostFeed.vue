<template>
  <div>
    <h2>Anonymous Posts</h2>

    <div v-if="loading">Loading posts...</div>
    <div v-if="error">{{ error }}</div>

    <div v-if="posts.length === 0 && !loading">
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
import { usePosts } from '../../composables/usePosts.js'

const { posts, loading, error } = usePosts()

function formatDate(timestamp) {
  return new Date(timestamp).toLocaleString()
}
</script>

<style scoped>
.post {
  margin-bottom: 20px;
}
</style>
