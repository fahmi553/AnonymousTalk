<template>
  <div class="bg-body-tertiary min-vh-100 d-flex align-items-center justify-content-center p-4">
    <div class="card bg-body shadow-lg border-0 login-card-width">
      <div class="card-body p-5">

        <div class="text-center mb-5">
          <i class="fas fa-crown fa-3x text-primary mb-3"></i>
          <h2 class="fw-bolder text-body-emphasis">Welcome Back, Admin</h2>
          <p class="text-body-secondary">Sign in to manage the AnonymousTalk platform.</p>
        </div>

        <div v-if="error" class="alert alert-danger py-2" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i> {{ error }}
        </div>

        <form @submit.prevent="loginAdmin">
          <div class="mb-3">
            <label for="email" class="form-label fw-bold">Email Address</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-body"><i class="fas fa-envelope"></i></span>
              <input
                id="email"
                v-model="email"
                type="email"
                class="form-control bg-body"
                placeholder="Enter your email"
                required
                autocomplete="email"
                :disabled="isLoading"
              />
            </div>
          </div>

          <div class="mb-4">
            <label for="password" class="form-label fw-bold">Password</label>
            <div class="input-group input-group-lg">
              <span class="input-group-text bg-body"><i class="fas fa-lock"></i></span>
              <input
                id="password"
                v-model="password"
                type="password"
                class="form-control bg-body"
                placeholder="Enter your password"
                required
                autocomplete="current-password"
                :disabled="isLoading"
              />
            </div>
          </div>

          <button
            type="submit"
            class="btn btn-primary w-100 fw-bold py-3 text-uppercase shadow-sm"
            :disabled="isLoading"
          >
            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            {{ isLoading ? 'Authenticating...' : 'Secure Login' }}
          </button>
        </form>

        <div class="text-center mt-4">
            <a href="#" class="text-body-secondary text-decoration-none small">Trouble logging in? Contact Support</a>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const email = ref('')
const password = ref('')
const error = ref('')
const isLoading = ref(false)
const router = useRouter()

const loginAdmin = async () => {
  error.value = ''
  isLoading.value = true

  try {
    await axios.get('/sanctum/csrf-cookie');
    await axios.post('/admin/login', {
      email: email.value,
      password: password.value,
    })
    const userRes = await axios.get('/api/user');
    if (userRes.data && userRes.data.role === 'admin') {
      router.push({ name: 'AdminDashboard' })
    } else {
      error.value = 'You do not have permission to access this area.';
      await axios.post('/logout');
    }

  } catch (e) {
    console.error('Login error:', e)
    if (e.response && (e.response.status === 401 || e.response.status === 422)) {
      error.value = e.response.data.message || 'Invalid credentials provided. Please check your email and password.'
    } else {
      error.value = 'A connection error occurred. Check your network and try again.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<style scoped>

.login-card-width {
    max-width: 440px;
    border-radius: 12px;
}
.input-group-text {
    border-right: 0;
}
.form-control {
    border-left: 0;
}
</style>
