<template>
  <div class="container" style="max-width: 700px;">
    <div
      v-if="showToast"
      class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">✅ Profile updated successfully!</div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          @click="showToast = false"
        ></button>
      </div>
    </div>

    <div v-if="loading" class="text-center mt-5">Loading profile...</div>
    <div v-else-if="error" class="text-danger mt-5">{{ error }}</div>

    <div v-else-if="user" class="card shadow-lg border-0 overflow-hidden">
      <div
        class="p-4 text-white"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"
      >
        <div class="d-flex align-items-center">
          <img
            src="https://i.pravatar.cc/100"
            alt="Avatar"
            class="rounded-circle border border-3 border-white me-3"
            width="80"
            height="80"
          />
          <div>
            <h3 class="mb-0">{{ user.username }}</h3>
            <small class="text-light">{{ isSelf ? "This is you" : "Anonymous User" }}</small>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="d-flex justify-content-around text-center mb-4">
          <div>
            <h5 class="mb-0">{{ user.posts_count ?? 12 }}</h5>
            <small class="text-muted">Posts</small>
          </div>
          <div>
            <h5 class="mb-0">{{ user.comments_count ?? 34 }}</h5>
            <small class="text-muted">Comments</small>
          </div>
          <div>
            <h5 class="mb-0">{{ user.likes_count ?? 56 }}</h5>
            <small class="text-muted">Likes</small>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label"><strong>Trust Score:</strong></label>
          <div class="progress" style="height: 20px;">
            <div
              class="progress-bar"
              role="progressbar"
              :style="{ width: user.trust_score + '%' }"
              :aria-valuenow="user.trust_score"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              {{ user.trust_score }}%
            </div>
          </div>
        </div>

        <p><i class="fas fa-user-shield me-2"></i><strong>Role:</strong> {{ user.role }}</p>
        <p>
          <i class="fas fa-calendar-alt me-2"></i>
          <strong>Joined:</strong> {{ new Date(user.created_at).toLocaleDateString() }}
        </p>

        <div class="mb-3">
          <strong>Badges:</strong>
          <div class="d-flex gap-2 mt-2">
            <span class="badge bg-primary">🏆 Top Contributor</span>
            <span class="badge bg-warning">⭐ Helpful</span>
            <span class="badge bg-success">💬 Active</span>
          </div>
        </div>

        <div class="d-flex gap-2">
          <router-link
            v-if="isSelf"
            to="/profile/edit"
            class="btn btn-outline-primary flex-grow-1"
          >
            <i class="fas fa-edit me-1"></i> Edit Profile
          </router-link>
          <button
            v-else
            class="btn btn-outline-danger flex-grow-1"
          >
            🚩 Report User
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useRoute, useRouter } from "vue-router"
import axios from "axios"

const route = useRoute()
const router = useRouter()

const user = ref(null)
const loading = ref(true)
const error = ref("")
const showToast = ref(false)
const isSelf = ref(false)

const fetchUser = async () => {
  loading.value = true
  error.value = ""

  try {
    if (route.params.id) {
      const res = await axios.get(`/api/profile/${route.params.id}`)
      user.value = res.data
      isSelf.value = false
    } else {
      const res = await axios.get(`/api/profile`)
      user.value = res.data
      isSelf.value = true
    }
  } catch (e) {
    error.value = "Failed to load profile"
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await fetchUser()

  if (route.query.updated) {
    showToast.value = true
    setTimeout(() => (showToast.value = false), 3000)
    router.replace({
      path: route.params.id ? `/profile/${route.params.id}` : "/profile",
    })
  }
})

watch(
  () => route.fullPath,
  () => fetchUser()
)
</script>

<style scoped>
.card {
  border-radius: 15px;
}
</style>
