<template>
  <div class="container py-4" style="max-width: 650px;">
    <div class="card bg-body shadow-lg p-4 border-0 rounded-4">
      <h3 class="mb-4 fw-bold text-primary">
        <i class="fas fa-user-edit me-2"></i> Edit Profile
      </h3>

      <form @submit.prevent="updateProfile">
        <div class="mb-3">
          <label class="form-label fw-semibold">
            <i class="fas fa-user me-2"></i> Username
          </label>
          <div class="input-group">
            <input
              v-model="form.username"
              type="text"
              class="form-control bg-body"
              disabled
            />
            <button
              type="button"
              class="btn btn-secondary"
              @click="regenerateUsername"
            >
              <i class="fas fa-sync-alt me-1"></i> Regenerate
            </button>
          </div>
          <small class="text-muted">Your username is unique and can be refreshed here.</small>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">
            <i class="fas fa-envelope me-2"></i> Email
          </label>
          <input
            v-model="form.email"
            type="email"
            class="form-control bg-body"
            required
          />
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">
            <i class="fas fa-lock me-2"></i> New Password
          </label>
          <input
            v-model="form.password"
            type="password"
            class="form-control bg-body"
            placeholder="Leave blank to keep current"
          />
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">
            <i class="fas fa-check-circle me-2"></i> Confirm Password
          </label>
          <input
            v-model="form.password_confirmation"
            type="password"
            class="form-control bg-body"
          />
        </div>
        <div v-if="errorMessage" class="alert alert-danger" role="alert">
          <i class="fas fa-exclamation-triangle me-2"></i>
          {{ errorMessage }}
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
            <i v-else class="fas fa-save me-1"></i>
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
          <router-link to="/profile" class="btn btn-secondary flex-grow-1">
            <i class="fas fa-times me-1"></i> Cancel
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
import { useAuth } from "../../store/auth";

const router = useRouter();
const { fetchUser } = useAuth();

const form = ref({
  username: "",
  email: "",
  password: "",
  password_confirmation: ""
});

const loading = ref(false);
const errorMessage = ref("");

const fetchProfile = async () => {
  try {
    const res = await axios.get("/api/profile")
    const user = res.data.user || res.data
    form.value.username = user.username
    form.value.email = user.email
  } catch (err) {
    console.error("Failed to load profile", err)
    errorMessage.value = "Failed to load your profile data.";
  }
}

const updateProfile = async () => {
  loading.value = true;
  errorMessage.value = "";
  try {
    await axios.patch("/api/profile", form.value);
    router.push({ path: "/profile", query: { updated: "true" } });
  } catch (err) {
    console.error("Update failed", err);
    errorMessage.value = err.response?.data?.message || "Failed to update profile. Please try again.";

  } finally {
    loading.value = false;
  }
};

const regenerateUsername = async () => {
  errorMessage.value = "";
  try {
    const res = await axios.post("/api/profile/regenerate-username");
    form.value.username = res.data.username;
  } catch (err) {
    console.error("Error regenerating username", err);
    errorMessage.value = err.response?.data?.message || "Failed to regenerate username.";
  }
};

onMounted(fetchProfile);
</script>
