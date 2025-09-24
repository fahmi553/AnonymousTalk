<template>
  <div class="feed-container">
    <h3 class="mb-4 fw-bold text-primary">Latest Posts</h3>

    <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
      <<select v-model="selectedCategory" @change="loadPosts" class="form-select w-auto">
        <option value="">All Categories</option>
        <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
          {{ cat.name }}
        </option>
      </select>

      <select v-model="sortOrder" @change="loadPosts" class="form-select w-auto">
        <option value="desc">Newest First</option>
        <option value="asc">Oldest First</option>
        <option value="most_commented">Most Commented</option>
      </select>

      <div class="form-check ms-2">
        <input class="form-check-input" type="checkbox" v-model="withCommentsOnly" @change="loadPosts"
               id="commentsOnlyCheck" :disabled="sortOrder === 'most_commented'">
        <label class="form-check-label" for="commentsOnlyCheck"
               :style="{ color: sortOrder === 'most_commented' ? '#999' : '#000' }">
          With Comments Only
        </label>
      </div>
    </div>

    <div v-if="!loading && posts.length === 0" class="text-muted fst-italic mt-3">
      <span v-if="selectedCategory">
        No posts found for the category "{{ selectedCategoryName }}".
      </span>
      <span v-else>
        No posts available yet.
      </span>
    </div>

    <div v-if="loading">Loading posts...</div>
    <div v-if="error" class="text-danger">{{ error }}</div>

    <div v-for="post in posts" :key="post.post_id" class="post-card">
      <div class="post-header">
        <span class="username">{{ post.user?.username ?? 'Anonymous' }}</span>
        <span class="time">· {{ timeAgo(post.created_at) }}</span>
      </div>

      <small v-if="post.category" class="badge mb-1"
             :style="{ backgroundColor: getCategoryColor(post.category), color: 'white' }">
        {{ post.category }}
      </small>

      <div class="post-content">{{ post.content }}</div>

      <button
        :disabled="!authUserId"
        class="btn btn-sm me-2"
        :class="{ 'btn-secondary': !authUserId, 'btn-outline-primary': authUserId && !post.liked, 'btn-primary': post.liked }"
        @click="authUserId && toggleLike(post)"
      >
        ❤️ {{ post.likes_count }}
      </button>

      <router-link
        :to="`/posts/${post.post_id}`"
        class="text-decoration-none text-primary fw-semibold">
        View Post
      </router-link>

      <div v-if="post.comments && post.comments.length > 0" class="comment-section mt-2">
        <div v-for="comment in post.comments.slice(0, 2)" :key="comment.comment_id" class="comment">
          <strong>{{ comment.user?.username ?? 'Anonymous' }}</strong>: {{ comment.content }}
        </div>
        <router-link
            :to="`/posts/${post.post_id}`"
            class="small text-muted">
            View all {{ post.comments.length }} comments
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
axios.defaults.withCredentials = true;

const posts = ref([])
const categories = ref([])
const loading = ref(true)
const error = ref('')
const selectedCategory = ref('')
const sortOrder = ref('desc')
const withCommentsOnly = ref(false)
const authUserId = window.authUserId || null

watch(sortOrder, (newVal) => {
  if (newVal === 'most_commented') {
    withCommentsOnly.value = false
  }
  loadPosts()
})

import { computed } from 'vue'

const selectedCategoryName = computed(() => {
  if (!selectedCategory.value) return null
  const cat = categories.value.find(c => c.category_id === selectedCategory.value)
  return cat ? cat.name : null
})


const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories')
    categories.value = res.data
  } catch (err) {
    console.error('Failed to load categories', err)
  }
}

const getCategoryColor = (categoryName) => {
  const cat = categories.value.find(c => c.name === categoryName)
  return cat ? cat.color_code : '#6c757d'
}

const loadPosts = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/posts', {
      params: {
        category_id: selectedCategory.value || undefined,
        sort: sortOrder.value,
        with_comments_only: sortOrder.value !== 'most_commented' && withCommentsOnly.value ? 1 : 0
      }
    })
    posts.value = res.data
  } catch (err) {
    console.error(err)
    error.value = 'Failed to load posts.'
  } finally {
    loading.value = false
  }
}

const toggleLike = async (post) => {
  try {
    await axios.get('/sanctum/csrf-cookie');
    const res = await axios.post(`/api/posts/${post.post_id}/toggle-like`);
    post.liked = res.data.liked;
    post.likes_count = res.data.likes_count;
  } catch (err) {
    console.error('Error toggling like:', err.response?.data || err);
  }
}

const timeAgo = (dateStr) => new Date(dateStr).toLocaleString()

onMounted(() => {
  fetchCategories()
  loadPosts()
})
</script>
