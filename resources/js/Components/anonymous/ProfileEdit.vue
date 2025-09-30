<template>
  <div class="container" style="max-width: 600px;">
    <h2 class="mb-4">Edit Profile</h2>

    <form @submit.prevent="updateProfile" class="card p-3 shadow-sm">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <div class="input-group">
          <input
            v-model="form.username"
            type="text"
            class="form-control"
            required
          />
          <button
            type="button"
            class="btn btn-outline-secondary"
            @click="regenerateUsername"
          >
            Regenerate
          </button>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="form-control"
          required
        />
      </div>

      <div class="mb-3">
        <label class="form-label">New Password (leave blank to keep current)</label>
        <input
          v-model="form.password"
          type="password"
          class="form-control"
        />
      </div>

      <div class="mb-3">
        <label class="form-label">Confirm New Password</label>
        <input
          v-model="form.password_confirmation"
          type="password"
          class="form-control"
        />
      </div>

      <button type="submit" class="btn btn-success">
        Save Changes
      </button>
    </form>
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
    const res = await axios.get("/api/profile");
    form.value.username = res.data.username;
    form.value.email = res.data.email;
  } catch (err) {
    console.error("Failed to load profile", err);
  }
};

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
