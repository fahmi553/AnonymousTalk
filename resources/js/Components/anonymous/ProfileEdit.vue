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
                      type="email"
                      v-model="form.email"
                      class="form-control"
                      :class="{ 'bg-secondary-subtle': isGoogleUser }"
                      :disabled="isGoogleUser"
                  >
                  <small v-if="isGoogleUser" class="text-muted">
                      <i class="fas fa-lock me-1"></i>
                      Linked to your Google account. You cannot change this email.
                  </small>
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

        <div class="mt-5 pt-5">
            <div class="p-4 rounded-3 border border-danger border-opacity-25 bg-danger bg-opacity-10">
                <h5 class="text-danger fw-bold mb-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                </h5>
                <p class="text-secondary small mb-3">
                    Once you delete your account, there is no going back. All your posts, comments, badges, and trust score will be permanently removed.
                </p>
                <button
                    type="button"
                    class="btn btn-outline-danger"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteAccountModal"
                >
                    Delete Account
                </button>
            </div>
        </div>
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

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-danger">Confirm Account Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="mb-3">
                        Are you absolutely sure? This action <strong>cannot</strong> be undone.
                    </p>

                    <div v-if="!isGoogleUser">
                        <p class="small text-muted">Please enter your password to confirm.</p>
                        <div class="form-floating mb-3">
                            <input
                                v-model="deletePassword"
                                type="password"
                                class="form-control"
                                id="deletePasswordInput"
                                placeholder="Password"
                                name="delete_password"
                                autocomplete="new-password"
                                :readonly="deletePasswordLocked"
                                @focus="deletePasswordLocked = false"
                            >
                            <label for="deletePasswordInput">Current Password</label>
                        </div>
                    </div>

                    <div v-else class="alert alert-info d-flex align-items-center">
                        <i class="fab fa-google me-2"></i>
                        <div>
                            You are logged in via Google. No password is required to delete your account.
                        </div>
                    </div>

                    <div v-if="deleteError" class="alert alert-danger py-2 small">
                        {{ deleteError }}
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button
                        @click="deleteAccount"
                        class="btn btn-danger"
                        :disabled="deleting || (!isGoogleUser && !deletePassword)"
                    >
                        <span v-if="deleting" class="spinner-border spinner-border-sm me-2"></span>
                        {{ deleting ? 'Deleting...' : 'Confirm Delete' }}
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
const deletePassword = ref("");
const deleting = ref(false);
const deleteError = ref("");
const isGoogleUser = ref(false);
const deletePasswordLocked = ref(true);

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
    isGoogleUser.value = !!user.google_id;

  } catch (err) {
    console.error("Failed to load data", err);
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
    window.dispatchEvent(new Event('notification-update-needed'));
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

const deleteAccount = async () => {
    if (!isGoogleUser.value && !deletePassword.value) return;

    deleting.value = true;
    deleteError.value = "";

    try {
        await axios.delete('/api/profile', {
            data: {
                password: deletePassword.value
            }
        });
        window.location.href = '/login';
    } catch (err) {
        console.error("Delete failed", err);
        if (err.response && err.response.data && err.response.data.message) {
             deleteError.value = err.response.data.message;
        } else {
             deleteError.value = "An error occurred. Please try again.";
        }
    } finally {
        deleting.value = false;
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
