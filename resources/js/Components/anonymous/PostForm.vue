<template>
  <div class="container py-4" style="max-width: 700px;">

    <Teleport to="body">
        <div v-if="showSuccessToast" class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show" style="z-index: 1055;">
            <div class="d-flex">
                <div class="toast-body"><i class="fas fa-check-circle me-2"></i> {{ successMessage }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showSuccessToast = false"></button>
            </div>
        </div>
        <div v-if="showWarningToast" class="toast align-items-center text-bg-warning border-0 position-fixed top-0 end-0 m-3 show" style="z-index: 1055;">
            <div class="d-flex">
                <div class="toast-body fw-bold text-dark"><i class="fas fa-exclamation-circle me-2"></i> {{ warningMessage }}</div>
                <button type="button" class="btn-close btn-close-black me-2 m-auto" @click="showWarningToast = false"></button>
            </div>
        </div>
        <div v-if="showErrorToast" class="toast align-items-center text-bg-danger border-0 position-fixed top-0 end-0 m-3 show" style="z-index: 1055;">
            <div class="d-flex">
                <div class="toast-body"><i class="fas fa-exclamation-triangle me-2"></i> {{ errorMessage }}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showErrorToast = false"></button>
            </div>
        </div>
    </Teleport>

    <div class="card bg-body shadow-sm border border-secondary-subtle rounded-4">
      <div class="card-body p-4 p-md-5">

        <h3 class="mb-4 fw-bold text-body-emphasis">Create Post</h3>

        <form @submit.prevent="submitPost">

          <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Title</label>
            <input
                v-model="title"
                type="text"
                class="form-control form-control-lg"
                :class="{ 'is-invalid': showErrors && !title.trim() }"
                placeholder="Give your post a catchy title..."
            />
            <div v-if="showErrors && !title.trim()" class="invalid-feedback">Title is required.</div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Category</label>
            <select
                v-model="category"
                class="form-select py-2"
                :class="{ 'is-invalid': showErrors && !category }"
            >
              <option disabled value="">Select a topic...</option>
              <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">{{ cat.name }}</option>
            </select>
            <div v-if="showErrors && !category" class="invalid-feedback">Category is required.</div>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Content</label>
            <textarea
                v-model="content"
                class="form-control"
                rows="6"
                :class="{ 'is-invalid': showErrors && !content.trim() }"
                placeholder="Share your thoughts with the community..."
                style="resize: none;"
            ></textarea>
            <div v-if="showErrors && !content.trim()" class="invalid-feedback">Content is required.</div>
          </div>

          <hr class="my-4 border-secondary-subtle">

          <div class="d-flex justify-content-end gap-2">
            <router-link to="/" class="btn btn-link text-decoration-none text-secondary px-4 fw-bold">
                Cancel
            </router-link>

            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" :disabled="isSubmitting">
              <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
              <span v-else>Post</span>
            </button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

// (Scripts remain unchanged)
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

    if (res.data.status === 'warning') {
      warningMessage.value = res.data.message;
      showWarningToast.value = true;
      window.dispatchEvent(new Event('notification-update-needed'));
      setTimeout(() => {
        showWarningToast.value = false;
        router.push("/");
      }, 4000);
    } else {
      successMessage.value = res.data.message;
      showSuccessToast.value = true;
      window.dispatchEvent(new Event('notification-update-needed'));
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

<style scoped>
.ls-1 {
    letter-spacing: 0.5px;
}

.form-control:focus, .form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}
</style>
