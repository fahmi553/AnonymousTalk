<template>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg border-0 rounded-4" style="max-width: 400px; width: 100%;">
      <div class="card-body p-5">

        <div class="text-center mb-4">
          <i class="fas fa-key fa-3x text-primary mb-3"></i>
          <h3 class="fw-bold">Reset Password</h3>
          <p class="text-muted small">Enter a new secure password for your account.</p>
        </div>

        <form @submit.prevent="resetPassword">

          <div class="mb-3">
            <label class="form-label fw-bold text-muted">Email</label>
            <input type="email" :value="email" class="form-control bg-secondary bg-opacity-10" disabled>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">New Password</label>
            <input type="password" v-model="password" class="form-control form-control-lg bg-light" required>
          </div>

          <div class="mb-4">
            <label class="form-label fw-bold">Confirm Password</label>
            <input type="password" v-model="password_confirmation" class="form-control form-control-lg bg-light" required>
          </div>

          <div v-if="message" class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> {{ message }}
            <div class="mt-2">
                <router-link to="/login" class="btn btn-sm btn-success fw-bold">Login Now</router-link>
            </div>
          </div>

          <div v-if="error" class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i> {{ error }}
          </div>

          <button v-if="!message" type="submit" class="btn btn-primary w-100 btn-lg rounded-pill fw-bold" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Resetting...' : 'Reset Password' }}
          </button>
        </form>

      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      token: '',
      email: '',
      password: '',
      password_confirmation: '',
      loading: false,
      message: '',
      error: ''
    };
  },
  created() {
    this.token = this.$route.query.token;
    this.email = this.$route.query.email;
  },
  methods: {
    async resetPassword() {
      this.loading = true;
      this.message = '';
      this.error = '';

      try {
        const response = await axios.post('/api/reset-password', {
          token: this.token,
          email: this.email,
          password: this.password,
          password_confirmation: this.password_confirmation
        });

        this.message = response.data.message;
        setTimeout(() => this.$router.push('/login'), 3000);

      } catch (err) {
        this.error = err.response?.data?.message || err.response?.data?.email || "Failed to reset password.";
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>
