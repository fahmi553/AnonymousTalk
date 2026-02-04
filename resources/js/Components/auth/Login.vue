<template>
  <div class="container-fluid">
    <div class="row min-vh-100">
      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center bg-body-tertiary p-5 d-none d-md-flex">
        <i class="fas fa-user-shield fa-5x text-primary mb-4"></i>
        <h1 class="fw-bold text-body-emphasis">Anonymous Talk</h1>
        <p class="text-body-secondary fs-5" style="max-width: 400px; text-align: center;">
          Speak freely, discuss openly, and connect with others.
        </p>
      </div>

      <div class="col-md-6 d-flex flex-column justify-content-center align-items-center p-4 p-md-5 bg-body">
        <div class="w-100" style="max-width: 420px;">
          <div class="mb-4">
            <h3 class="fw-bold text-body-emphasis">Login to your Account</h3>
            <p class="text-body-secondary">Welcome back! Please enter your details.</p>
          </div>

          <div v-if="status" class="alert alert-success py-2" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ status }}
          </div>
          <div v-if="error" class="alert alert-danger py-2" role="alert">
            <div class="d-flex align-items-center">
              <i class="fas fa-exclamation-triangle me-2"></i>
              <div>{{ error }}</div>
            </div>
          </div>

          <form @submit.prevent="handleLogin">
            <div class="mb-3">
                <label for="email" class="form-label fw-medium">Email Address</label>
                <div class="input-group">
                <span class="input-group-text bg-body-tertiary"><i class="fas fa-envelope"></i></span>
                <input v-model="form.email" type="email" class="form-control bg-body" required autofocus placeholder="you@example.com" :disabled="!isBackendReady || loading">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label fw-medium">Password</label>
                <div class="input-group">
                <span class="input-group-text bg-body-tertiary"><i class="fas fa-lock"></i></span>
                <input v-model="form.password" :type="showPassword ? 'text' : 'password'" class="form-control bg-body" required placeholder="Enter your password" :disabled="!isBackendReady || loading">
                <button class="btn btn-outline-secondary" type="button" @click="showPassword = !showPassword" :disabled="!isBackendReady">
                    <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                <input v-model="form.remember" class="form-check-input" type="checkbox" id="remember" :disabled="!isBackendReady">
                <label class="form-check-label small" for="remember">Remember Me</label>
                </div>
                <router-link to="/forgot-password" class="text-body-secondary text-decoration-underline small">
                Forgot password?
                </router-link>
            </div>

            <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mb-3" :disabled="loading || !isBackendReady">
                <template v-if="!isBackendReady">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Waking up Server...
                </template>
                <template v-else-if="loading">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                Logging in...
                </template>
                <template v-else>
                Log In
                </template>
            </button>
            </form>
          <div class="d-flex align-items-center mb-3">
            <hr class="flex-grow-1 text-secondary">
            <span class="px-3 text-secondary small">OR</span>
            <hr class="flex-grow-1 text-secondary">
          </div>

          <a href="https://anonymoustalk-6bcv.onrender.com/auth/google" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center py-2">
            <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" style="width: 20px; height: 20px;" class="me-2">
            <span class="fw-medium">Continue with Google</span>
          </a>

          <div class="mt-4 text-center">
            <router-link to="/register" class="text-body-secondary text-decoration-none small">
              Don't have an account? <span class="text-decoration-underline">Register here</span>
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const route = useRoute();

const email = ref('');
const password = ref('');
const error = ref(null);
const status = ref(null);
const isLoading = ref(false);
const isBackendReady = ref(false);

const checkHealth = async () => {
  try {
    await axios.get('/');
    isBackendReady.value = true;
  } catch (err) {
    console.log("Backend is warming up...");
    setTimeout(checkHealth, 3000);
  }
};

onMounted(() => {
  checkHealth();

  if (route.query.error) {
    error.value = route.query.error;
  }
  if (route.query.verified === '1') {
    status.value = 'Email verified successfully! You can now log in.';
  }
});

const handleLogin = async () => {
  if (!isBackendReady.value) return;

  isLoading.value = true;
  error.value = null;

  try {
    await axios.get('/sanctum/csrf-cookie');
    await axios.post('/api/login', {
      email: email.value,
      password: password.value
    });
    localStorage.setItem('isLoggedIn', 'true');
    router.push('/');
  } catch (err) {
    if (err.response) {
      error.value = err.response.data.message || 'Invalid credentials.';
    } else {
      error.value = 'Server is still waking up. Please wait a moment.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>
