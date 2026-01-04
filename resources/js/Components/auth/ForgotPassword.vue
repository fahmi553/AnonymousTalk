<template>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 400px; width: 100%;">
      <div class="card-body p-5">

        <div class="text-center mb-4">
          <i class="fas fa-lock fa-3x text-primary mb-3"></i>
          <h3 class="fw-bold">Forgot Password?</h3>
          <p class="text-muted small">Enter your email and we'll send you a link to reset your password.</p>
        </div>

        <form @submit.prevent="sendLink">
          <div class="mb-3">
            <label class="form-label fw-bold">Email Address</label>
            <input type="email" v-model="email" class="form-control form-control-lg bg-light" placeholder="name@example.com" required>
          </div>

          <div v-if="message" class="alert alert-success d-flex align-items-center" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ message }}
          </div>

          <div v-if="error" class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i> {{ error }}
          </div>

          <button type="submit" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Sending...' : 'Send Reset Link' }}
          </button>
        </form>

        <div class="text-center mt-4">
          <router-link to="/login" class="text-decoration-none fw-bold text-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Login
          </router-link>
        </div>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      email: '',
      loading: false,
      message: '',
      error: ''
    };
  },
  methods: {
    async sendLink() {
      this.loading = true;
      this.message = '';
      this.error = '';

      try {
        const response = await axios.post('/api/forgot-password', { email: this.email });
        this.message = response.data.message;
        this.email = '';
      } catch (err) {
        this.error = err.response?.data?.email || "Failed to send link. Please try again.";
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
