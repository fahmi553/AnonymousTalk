<template>
  <div class="mb-4">
    <h4>Create a New Post</h4>
    <form @submit.prevent="submitPost" class="card p-3 shadow-sm">

      <div class="mb-3">
        <label class="form-label">Post Title</label>
        <input
          v-model="title"
          type="text"
          class="form-control"
          :class="{ 'is-invalid': showErrors && !title.trim() }"
        />
        <div v-if="showErrors && !title.trim()" class="invalid-feedback">
          Title is required.
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Post Content</label>
        <textarea
          v-model="content"
          class="form-control"
          rows="4"
          :class="{ 'is-invalid': showErrors && !content.trim() }"
        ></textarea>
        <div v-if="showErrors && !content.trim()" class="invalid-feedback">
          Content is required.
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Category</label>
        <select
          v-model="category"
          class="form-select"
          :class="{ 'is-invalid': showErrors && !category }"
        >
          <option disabled value="">-- Select Category --</option>
          <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
            {{ cat.name }}
          </option>
        </select>
        <div v-if="showErrors && !category" class="invalid-feedback">
          Category is required.
        </div>
      </div>

      <button type="submit" class="btn btn-primary">
        Post
      </button>
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

const showErrors = ref(false)

const fetchCategories = async () => {
  const res = await axios.get('/api/categories')
  categories.value = res.data
}

const submitPost = async () => {
  showErrors.value = true

  if (!title.value.trim() || !content.value.trim() || !category.value) {
    return
  }

  await axios.post('/api/posts', {
    title: title.value,
    content: content.value,
    category_id: category.value
  })

  title.value = ''
  content.value = ''
  category.value = ''
  showErrors.value = false

  window.location.href = '/'
}

onMounted(fetchCategories)
</script>
