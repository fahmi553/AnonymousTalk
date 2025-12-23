<template>
  <div class="container py-4" style="max-width: 720px;">

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

      <div class="profile-banner bg-primary bg-gradient" style="height: 140px;"></div>

      <div class="card-body px-4 px-md-5 pb-5">

        <div class="position-relative mb-4" style="margin-top: -70px;">
          <div class="d-flex flex-column align-items-center">

            <div class="position-relative group-avatar">
              <img
                :src="currentAvatarUrl"
                class="rounded-circle border border-4 border-white shadow"
                width="130"
                height="130"
                alt="Current Avatar"
                style="object-fit: cover; background-color: #fff;"
              >
              <button
                type="button"
                class="btn btn-dark rounded-circle position-absolute bottom-0 end-0 border border-2 border-white shadow-sm d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px;"
                data-bs-toggle="modal"
                data-bs-target="#avatarModal"
                title="Change Avatar"
              >
                <i class="fas fa-camera text-white" style="font-size: 0.9rem;"></i>
              </button>
            </div>

            <h4 class="mt-3 fw-bold">{{ form.username || 'User' }}</h4>
            <span class="badge bg-light text-dark border">User Settings</span>
          </div>
        </div>

        <form @submit.prevent="updateProfile">
        <div class="row g-4">
            <div class="col-12">
            <label class="form-label fw-bold text-secondary small text-uppercase">Public Profile</label>
            <div class="input-group">
                <span class="input-group-text bg-body-secondary"><i class="fas fa-at"></i></span>
                <input
                v-model="form.username"
                type="text"
                class="form-control form-control-lg bg-body"
                disabled
                />
                <button
                type="button"
                class="btn btn-outline-primary"
                @click="regenerateUsername"
                >
                <i class="fas fa-sync-alt"></i>
                </button>
            </div>
            <div class="form-text">Usernames are auto-generated to protect your identity.</div>
            </div>

            <div class="col-12">
            <label class="form-label fw-bold text-secondary small text-uppercase">Account Security</label>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input
                v-model="form.email"
                type="email"
                class="form-control bg-body"
                required
                />
            </div>
            </div>

            <div class="col-md-6">
            <label class="form-label">New Password</label>
            <input
                v-model="form.password"
                type="password"
                class="form-control bg-body"
                placeholder="••••••••"
            />
            </div>
            <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input
                v-model="form.password_confirmation"
                type="password"
                class="form-control bg-body"
                placeholder="••••••••"
            />
            </div>
        </div>

        <div v-if="errorMessage" class="alert alert-danger mt-4 d-flex align-items-center" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ errorMessage }}</div>
        </div>

        <div class="d-flex gap-3 mt-5 pt-3 border-top">
            <router-link to="/profile" class="btn btn-outline-secondary btn-lg px-4 bg-body">
            Cancel
            </router-link>
            <button type="submit" class="btn btn-primary btn-lg px-5 ms-auto" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
        </div>
        </form>
      </div>
    </div>

    <div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true" ref="avatarModalRef">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header border-0 pb-0">
            <h5 class="modal-title fw-bold">Choose an Avatar</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pt-4">
            <p class="text-muted small mb-3">Select one of our preset avatars to represent you.</p>

            <div class="row row-cols-4 g-3 justify-content-center">
              <div
                v-for="avatar in availableAvatars"
                :key="avatar"
                class="col"
              >
                <div
                  class="avatar-option ratio ratio-1x1 position-relative cursor-pointer"
                  :class="{ 'selected': form.avatar === avatar }"
                  @click="selectAvatar(avatar)"
                >
                  <img
                    :src="`/images/avatars/${avatar}`"
                    class="rounded-circle w-100 h-100 border"
                    style="object-fit: cover;"
                    alt="Avatar Option"
                  >
                  <div v-if="form.avatar === avatar" class="selected-overlay rounded-circle d-flex align-items-center justify-content-center">
                    <i class="fas fa-check text-white fa-lg"></i>
                  </div>
                </div>
              </div>
            </div>

          </div>
          <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-primary w-100" data-bs-dismiss="modal">
              Done
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";

const router = useRouter();

const form = ref({
  username: "",
  email: "",
  password: "",
  password_confirmation: "",
  avatar: "default.jpg"
});

const availableAvatars = ref([]);
const loading = ref(false);
const errorMessage = ref("");
const currentAvatarUrl = computed(() => {
    return form.value.avatar ? `/images/avatars/${form.value.avatar}` : '/images/avatars/default.jpg';
});

const fetchProfileAndAvatars = async () => {
  try {
    const avatarRes = await axios.get("/api/profile/avatars");
    availableAvatars.value = avatarRes.data.avatars;

    const res = await axios.get("/api/profile", { params: { user_only: true } });
    const user = res.data.user || res.data;

    form.value.username = user.username;
    form.value.email = user.email;
    form.value.avatar = user.avatar || "default.jpg";

  } catch (err) {
    console.error("Failed to load data", err);
    errorMessage.value = "Failed to load profile data.";
  }
}

const selectAvatar = (filename) => {
    form.value.avatar = filename;
};

const updateProfile = async () => {
  loading.value = true;
  errorMessage.value = "";
  try {
    await axios.patch("/api/profile", form.value);
    router.push({ path: "/profile", query: { updated: "true" } });
  } catch (err) {
    console.error("Update failed", err);
    errorMessage.value = err.response?.data?.message || "Failed to update profile.";
  } finally {
    loading.value = false;
  }
};

const regenerateUsername = async () => {
  try {
    const res = await axios.post("/api/profile/regenerate-username");
    form.value.username = res.data.username;
  } catch (err) {
    errorMessage.value = "Failed to regenerate username.";
  }
};

onMounted(fetchProfileAndAvatars);
</script>

<style scoped>
.profile-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.cursor-pointer {
    cursor: pointer;
}

.avatar-option img {
    transition: all 0.2s ease;
}

.avatar-option:hover img {
    transform: scale(1.05);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15);
}

.avatar-option.selected img {
    border-color: var(--bs-primary) !important;
    border-width: 3px !important;
}

.selected-overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(var(--bs-primary-rgb), 0.6);
    backdrop-filter: blur(1px);
    transition: opacity 0.2s;
}
</style>
