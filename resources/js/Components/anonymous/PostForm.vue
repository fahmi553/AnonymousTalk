<template>
  <div class="mb-4">
    <h4>Create a New Post</h4>
    <form @submit.prevent="submitPost" class="card p-3 shadow-sm">
      <div class="mb-3">
        <input v-model="title" type="text" class="form-control" placeholder="Post Title" />
      </div>

      <div class="mb-3">
        <textarea v-model="content" class="form-control" rows="4" placeholder="What's on your mind?"></textarea>
      </div>

      <div class="mb-3">
        <select v-model="category" class="form-select">
          <option disabled value="">Select Category</option>
          <option v-for="cat in categories" :key="cat.category_id" :value="cat.name">
            {{ cat.name }}
          </option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary">Post</button>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const title = ref('')
const content = ref('')
const category = ref('')
const categories = ref([])

const fetchCategories = async () => {
  const res = await axios.get('/api/categories')
  categories.value = res.data

  if (!category.value && categories.value.length) {
    category.value = categories.value[0].name
  }
}


const submitPost = async () => {
  if (!content.value.trim()) return

  await axios.post('/api/posts', {
    title: title.value,
    content: content.value,
    category: category.value
  })

  title.value = ''
  content.value = ''
  category.value = ''

  window.location.href = '/'
}

onMounted(fetchCategories)
</script>
