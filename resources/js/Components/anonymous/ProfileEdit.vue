<template>
  <div class="container py-4" style="max-width: 650px;">
    <div class="card shadow-lg p-4 border-0 rounded-4">
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
              class="form-control"
              disabled
            />
            <button
              type="button"
              class="btn btn-outline-secondary"
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
            class="form-control"
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
            class="form-control"
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
            class="form-control"
          />
        </div>

        <div class="d-flex gap-2">
          <button type="submit" class="btn btn-primary flex-grow-1">
            <i class="fas fa-save me-1"></i> Save Changes
          </button>
          <router-link to="/profile" class="btn btn-outline-secondary flex-grow-1">
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

const fetchProfile = async () => {
  try {
    const res = await axios.get("/api/profile")
    const user = res.data.user || res.data
    form.value.username = user.username
    form.value.email = user.email
  } catch (err) {
    console.error("Failed to load profile", err)
  }
}

const updateProfile = async () => {
  try {
    await axios.patch("/api/profile", form.value);

    await fetchUser();

    router.push({ path: "/profile", query: { updated: "true" } });
  } catch (err) {
    console.error("Update failed", err);
    alert("Failed to update profile. Please try again.");
  }
};

const regenerateUsername = async () => {
  try {
    const res = await axios.post("/api/profile/regenerate-username");
    form.value.username = res.data.username;
  } catch (err) {
    console.error("Error regenerating username", err);
  }
};

onMounted(fetchProfile);
</script>
