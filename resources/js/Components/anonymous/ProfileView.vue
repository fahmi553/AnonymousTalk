<template>
  <div class="container" style="max-width: 600px;">
    <h2 class="mb-4">My Profile</h2>

    <div
      v-if="showToast"
      class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">
          ✅ Profile updated successfully!
        </div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          @click="showToast = false"
        ></button>
      </div>
    </div>

    <div v-if="user" class="card p-3 shadow-sm">
      <p><strong>Username:</strong> {{ user.username }}</p>
      <p><strong>Email:</strong> {{ user.email }}</p>
      <p><strong>Trust Score:</strong> {{ user.trust_score }}</p>
      <p><strong>Role:</strong> {{ user.role }}</p>
      <p><strong>Joined At:</strong> {{ user.created_at }}</p>

      <router-link to="/profile/edit" class="btn btn-primary mt-3">
        Edit Profile
      </router-link>
    </div>

    <div v-else>
      <p>Loading profile...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

const user = ref(null)
const showToast = ref(false)

const route = useRoute()
const router = useRouter()

const fetchUser = async () => {
  try {
    const res = await axios.get('/api/profile')
    user.value = res.data
  } catch (err) {
    console.error('Failed to load profile', err)
  }
}

onMounted(async () => {
  await fetchUser()

  if (route.query.updated) {
    showToast.value = true
    setTimeout(() => {
      showToast.value = false
    }, 3000)

    router.replace({ path: '/profile' })
  }
})

watch(
  () => route.fullPath,
  () => {
    fetchUser()
  }
)
</script>
