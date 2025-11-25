<template>
  <div class="container py-4" style="max-width: 650px;">
    
    <div
      v-if="showSuccessToast"
      class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas fa-check-circle me-2"></i> {{ successMessage }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showSuccessToast = false"></button>
      </div>
    </div>

    <div
      v-if="showWarningToast"
      class="toast align-items-center text-bg-warning border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body fw-bold text-dark">
          <i class="fas fa-exclamation-circle me-2"></i> {{ warningMessage }}
        </div>
        <button type="button" class="btn-close btn-close-black me-2 m-auto" @click="showWarningToast = false"></button>
      </div>
    </div>

    <div
      v-if="showErrorToast"
      class="toast align-items-center text-bg-danger border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">
          <i class="fas fa-exclamation-triangle me-2"></i> {{ errorMessage }}
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showErrorToast = false"></button>
      </div>
    </div>

    <div class="card bg-body shadow-lg p-4 border-0 rounded-4">
      <h3 class="mb-4 fw-bold text-primary">
        <i class="fas fa-pen-nib me-2"></i> Create a New Post
      </h3>

      <form @submit.prevent="submitPost">
        <div class="mb-3">
          <label class="form-label fw-semibold"><i class="fas fa-heading me-2"></i> Post Title</label>
          <input v-model="title" type="text" class="form-control bg-body" :class="{ 'is-invalid': showErrors && !title.trim() }" />
          <div v-if="showErrors && !title.trim()" class="invalid-feedback">Title is required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold"><i class="fas fa-align-left me-2"></i> Post Content</label>
          <textarea v-model="content" class="form-control bg-body" rows="4" :class="{ 'is-invalid': showErrors && !content.trim() }"></textarea>
          <div v-if="showErrors && !content.trim()" class="invalid-feedback">Content is required.</div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold"><i class="fas fa-tags me-2"></i> Category</label>
          <select v-model="category" class="form-select bg-body" :class="{ 'is-invalid': showErrors && !category }">
            <option disabled value="">-- Select Category --</option>
            <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">{{ cat.name }}</option>
          </select>
          <div v-if="showErrors && !category" class="invalid-feedback">Category is required.</div>
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-success flex-grow-1" :disabled="isSubmitting">
            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
            <span v-else><i class="fas fa-paper-plane me-1"></i> Post</span>
          </button>
          <router-link to="/" class="btn btn-secondary flex-grow-1"><i class="fas fa-times me-1"></i> Cancel</router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

const title = ref("");
const content = ref("");
const category = ref("");
const categories = ref([]);
const showErrors = ref(false);
const isSubmitting = ref(false);
const showSuccessToast = ref(false);
const showWarningToast = ref(false);
const showErrorToast = ref(false);
const successMessage = ref("Post created successfully!");
const warningMessage = ref("");
const errorMessage = ref("Failed to create post.");

const fetchCategories = async () => {
  try {
    const res = await axios.get("/api/categories");
    categories.value = res.data;
  } catch (err) {
    console.error("Failed to load categories", err);
  }
};

const submitPost = async () => {
  showErrors.value = true;
  if (!title.value.trim() || !content.value.trim() || !category.value) return;

  isSubmitting.value = true;

  try {
    const res = await axios.post("/api/posts", {
      title: title.value,
      content: content.value,
      category_id: category.value,
    });

    title.value = "";
    content.value = "";
    category.value = "";
    showErrors.value = false;

    if (res.data.status === 'moderated') {
      warningMessage.value = res.data.message; 
      showWarningToast.value = true;
      setTimeout(() => {
        showWarningToast.value = false;
        router.push("/");
      }, 4000); 
    } else {
      successMessage.value = res.data.message;
      showSuccessToast.value = true;
      setTimeout(() => {
        showSuccessToast.value = false;
        router.push("/");
      }, 2000);
    }

  } catch (err) {
    console.error("Post failed", err);
    errorMessage.value = err.response?.data?.message || "Failed to create post.";
    showErrorToast.value = true;
    setTimeout(() => (showErrorToast.value = false), 3000);
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(fetchCategories);
</script>