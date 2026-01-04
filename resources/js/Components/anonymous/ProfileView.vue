<template>
  <div class="container py-4" style="max-width: 800px;">

    <div
      v-if="showToast"
      class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">✅ {{ toastMessage }}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showToast = false"></button>
      </div>
    </div>

    <div v-if="successMessage" class="alert alert-success d-flex align-items-center shadow-sm mb-4">
        <i class="fas fa-check-circle fs-4 me-3"></i>
        <div>{{ successMessage }}</div>
        <button type="button" class="btn-close ms-auto" @click="successMessage = ''"></button>
    </div>

    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
      <p class="mt-3 text-muted fw-medium">Loading profile...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger shadow-sm mt-4">
      <i class="fas fa-exclamation-circle me-2"></i> {{ error }}
    </div>

    <div v-else-if="user" class="card border-0 shadow-lg rounded-4 overflow-hidden mb-4 bg-body">

      <div class="profile-banner" style="height: 200px;"></div>

      <div class="card-body px-4 px-md-5 pb-4 position-relative">

        <div class="row align-items-end mb-4" style="margin-top: -90px;">

          <div class="col-12 col-md-auto text-center mb-3 mb-md-0">
            <div class="position-relative d-inline-block">
              <img
                :src="avatarUrlWithCache"
                @error="$event.target.src = '/images/avatars/default.jpg'"
                alt="Avatar"
                class="rounded-circle border border-4 border-body shadow bg-body"
                width="150"
                height="150"
                style="object-fit: cover;"
              />
            </div>
          </div>

          <div class="col-12 col-md text-center text-md-start mb-2 mb-md-0">
            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                <h1 class="fw-bolder mb-0 text-body-emphasis display-5 text-shadow">{{ user.username || "Anonymous" }}</h1>
                <i v-if="user.is_verified" class="fas fa-check-circle text-info fs-4 filter-shadow" data-bs-toggle="tooltip" title="Verified Account"></i>
            </div>

            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 flex-wrap text-muted">
              <span v-if="isSelf" class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill">That's You</span>
              <span class="small"><i class="far fa-calendar-alt me-1"></i>Joined {{ formatDate(user.created_at) }}</span>
              <span class="badge bg-body-secondary text-body-emphasis border text-uppercase small">{{ user.role || "User" }}</span>
            </div>
          </div>

          <div class="col-12 col-md-auto text-center mt-3 mt-md-0 pb-md-2">
            <router-link v-if="isSelf" to="/profile/edit" class="btn btn-outline-primary rounded-pill px-4 fw-bold shadow-sm">
              <i class="fas fa-edit me-2"></i>Edit Profile
            </router-link>
            <button v-else-if="authUserId" class="btn btn-outline-danger rounded-pill px-4" @click="openReportModal(user.user_id, 'user')">
              <i class="fas fa-flag me-2"></i>Report
            </button>
          </div>

        </div>

        <div v-if="isSelf && !user.is_verified" class="p-3 mb-4 rounded-3 border border-warning border-opacity-50" style="background: rgba(255, 193, 7, 0.1);">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fs-4 me-3 text-warning"></i>
                    <div>
                        <h6 class="fw-bold mb-0 text-body-emphasis">Account Unverified</h6>
                        <small class="text-secondary">
                            Verify your email to unlock posting and comment privileges.
                        </small>
                    </div>
                </div>
                <button
                    @click="resendVerification"
                    class="btn btn-sm btn-outline-warning fw-bold"
                    :disabled="resending"
                >
                    <span v-if="resending" class="spinner-border spinner-border-sm me-1"></span>
                    {{ resending ? 'Sending...' : 'Resend Email' }}
                </button>
            </div>
            <div v-if="resendMessage" class="mt-2 small fw-bold text-warning pt-2">
                {{ resendMessage }}
            </div>
        </div>

        <div class="row text-center border-top border-bottom py-4 mb-4 g-0 bg-body-tertiary rounded-3 mx-0">
          <div class="col-4 border-end border-secondary-subtle">
            <div class="h3 fw-bolder mb-0 text-body-emphasis">{{ postsMeta.total ?? 0 }}</div>
            <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Posts</div>
          </div>
          <div class="col-4 border-end border-secondary-subtle">
            <div class="h3 fw-bolder mb-0 text-body-emphasis">{{ commentsMeta.total ?? 0 }}</div>
            <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Comments</div>
          </div>
          <div class="col-4">
            <div class="h3 fw-bolder mb-0 text-body-emphasis">{{ user.likes_count ?? 0 }}</div>
            <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Likes</div>
          </div>
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-end mb-2">
            <label class="fw-bold small text-uppercase text-muted">Trust Score</label>
            <span class="fw-bold fs-5 text-primary">{{ user.trust_score || 0 }}%</span>
          </div>
          <div class="progress bg-body-secondary" style="height: 12px; border-radius: 10px;">
            <div
              class="progress-bar bg-gradient"
              role="progressbar"
              :class="user.trust_score > 80 ? 'bg-success' : (user.trust_score > 50 ? 'bg-info' : 'bg-warning')"
              :style="{ width: (user.trust_score || 0) + '%' }"
            ></div>
          </div>
        </div>

        <div class="bg-body-tertiary rounded-4 p-4 mb-3 border border-secondary-subtle">
             <div class="d-flex align-items-center justify-content-between mb-3">
                 <h6 class="fw-bold mb-0 text-body-emphasis"><i class="fas fa-trophy text-warning me-2"></i>Achievements</h6>
                 <span class="badge bg-body text-body border shadow-sm">{{ unlockedBadges.length }} Unlocked</span>
              </div>
              <div v-if="unlockedBadges.length > 0" class="row row-cols-4 row-cols-md-5 g-3 justify-content-center">
                <div v-for="badge in unlockedBadges" :key="badge.badge_id" class="col">
                  <div class="badge-item d-flex flex-column align-items-center text-center p-2 rounded-3"
                    data-bs-toggle="tooltip" :title="badge.description">
                    <img v-if="badge.icon_url" :src="badge.icon_url" class="img-fluid mb-2 drop-shadow" width="48" />
                    <div class="small fw-bold lh-1 text-body-secondary" style="font-size: 0.7rem;">{{ badge.badge_name }}</div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center text-muted small py-2">No badges earned yet.</div>

              <div v-if="lockedBadges.length > 0" class="text-center mt-3 pt-3 border-top border-secondary-subtle">
                <button class="btn btn-sm btn-link text-decoration-none text-muted" @click="showLockedBadges = !showLockedBadges">
                    {{ showLockedBadges ? 'Hide' : 'Show' }} Locked Badges
                    <i class="fas ms-1" :class="showLockedBadges ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
              </div>

              <div v-if="showLockedBadges && lockedBadges.length > 0" class="row row-cols-4 row-cols-md-5 g-3 justify-content-center mt-1">
                <div v-for="badge in lockedBadges" :key="badge.badge_id" class="col">
                    <div class="badge-item d-flex flex-column align-items-center text-center p-2 rounded-3 opacity-50"
                    data-bs-toggle="tooltip" :title="'Locked: ' + badge.description">
                    <div class="position-relative mb-2">
                        <img v-if="badge.icon_url" :src="badge.icon_url" class="img-fluid grayscale" width="40" />
                        <i class="fas fa-lock position-absolute top-50 start-50 translate-middle text-body"></i>
                    </div>
                    <div class="small text-muted lh-1" style="font-size: 0.7rem;">{{ badge.badge_name }}</div>
                    </div>
                </div>
              </div>
        </div>

      </div>
    </div>

    <div v-if="user">
        <div v-if="isSelf" class="d-flex justify-content-end mb-3">
            <button class="btn btn-sm btn-outline-secondary" @click="toggleHideAll('posts')" :disabled="loadingPostsToggle">
              <i class="fas" :class="user.hide_all_posts ? 'fa-eye' : 'fa-eye-slash'"></i>
              {{ user.hide_all_posts ? "Show All Posts Publicly" : "Hide All Posts Publicly" }}
            </button>
        </div>

        <ul class="nav nav-pills nav-fill mb-4 bg-body shadow-sm rounded-pill p-1 border border-secondary-subtle">
            <li class="nav-item">
            <button class="nav-link rounded-pill fw-bold" :class="{ active: activeTab === 'posts' }" @click="activeTab = 'posts'">
                <i class="fas fa-pen-nib me-2"></i>Posts
            </button>
            </li>
            <li class="nav-item" v-if="isSelf">
            <button class="nav-link rounded-pill fw-bold" :class="{ active: activeTab === 'comments' }" @click="activeTab = 'comments'">
                <i class="fas fa-comment-dots me-2"></i>Comments
            </button>
            </li>
        </ul>

        <div v-if="activeTab === 'posts'" class="fade-in">
             <div v-if="isSelf" class="position-relative mb-4">
                <input type="text" class="form-control form-control-lg ps-5 rounded-pill bg-body text-body border-secondary-subtle"
                  placeholder="Search your posts..." v-model="postSearchTerm" @input="debouncedSearchPosts"/>
                <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </div>

             <div v-if="loadingPosts" class="text-center py-5 text-muted">Loading posts...</div>
             <div v-else-if="posts.length === 0" class="text-center py-5 card border-0 shadow-sm rounded-4 bg-body">
                <div class="card-body">
                    <i class="fas fa-folder-open fa-3x text-body-tertiary mb-3"></i>
                    <p class="mb-0 text-muted">No posts found.</p>
                </div>
             </div>

             <div v-else class="d-flex flex-column gap-3">
                 <div v-for="post in posts" :key="post.post_id" class="card border border-secondary-subtle shadow-sm rounded-4 overflow-hidden card-hover bg-body" :class="{ 'border-warning border-2': post.hidden_in_profile }">
                     <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start">
                             <router-link :to="`/posts/${post.post_id}`" class="h5 fw-bold text-decoration-none text-body-emphasis mb-2 d-block stretched-link">{{ post.title || "Untitled Post" }}</router-link>
                             <button v-if="isSelf" class="btn btn-sm rounded-pill position-relative z-2 ms-2" :class="post.hidden_in_profile ? 'btn-warning' : 'btn-light border'" @click.stop="togglePostVisibility(post.post_id)">
                                <i :class="post.hidden_in_profile ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                             </button>
                        </div>
                        <div class="d-flex gap-3 text-muted small mt-2">
                             <span><i class="far fa-comment me-1"></i>{{ post.comments_count ?? 0 }}</span>
                             <span><i class="far fa-heart me-1"></i>{{ post.likes_count ?? 0 }}</span>
                             <span><i class="far fa-calendar me-1"></i>{{ formatDate(post.created_at) }}</span>
                        </div>
                     </div>
                 </div>
                 <PaginationControls v-if="postsMeta.last_page > 1" :meta="postsMeta" @page-change="fetchPosts" class="mt-3" />
             </div>
        </div>

        <div v-else-if="activeTab === 'comments' && isSelf" class="fade-in">
             <div class="alert alert-info border-0 rounded-4 d-flex align-items-center mb-4">
                <i class="fas fa-info-circle fa-lg me-3"></i><div>Only you can see your comment history.</div>
            </div>
            <div v-if="loadingComments" class="text-center py-5">Loading...</div>
            <div v-else-if="comments.length === 0" class="text-center py-5 text-muted">No comments made yet.</div>
            <div v-else class="d-flex flex-column gap-2">
                <div v-for="c in comments" :key="c.comment_id" class="card border border-secondary-subtle shadow-sm rounded-3 bg-body">
                    <div class="card-body">
                        <p class="mb-2 text-body-emphasis">{{ c.content }}</p>
                        <div class="small text-muted bg-body-tertiary p-2 rounded-2 d-inline-block border border-secondary-subtle">
                             <i class="fas fa-reply me-1"></i> On:
                             <router-link v-if="c.post" :to="`/posts/${c.post_id}`" class="fw-bold text-decoration-none text-body-secondary">{{ c.post.title || "Post" }}</router-link>
                        </div>
                    </div>
                </div>
                <PaginationControls v-if="commentsMeta.last_page > 1" :meta="commentsMeta" @page-change="fetchComments" class="mt-3" />
            </div>
        </div>

    </div>

    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true" ref="reportModal">
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-body rounded-3 shadow">
          <div class="modal-header border-0">
            <h5 class="modal-title fw-bold">
              <i class="fas fa-flag text-danger me-2"></i>
              Report {{ reportType === 'user' ? 'User' : (reportType === 'post' ? 'Post' : 'Comment') }}
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label fw-semibold">Reason</label>
              <select
                v-model="reportReasonCategory"
                class="form-select bg-body"
                :class="{ 'is-invalid': showReportError && !reportReasonCategory }"
                >
                <option disabled value="">Select a reason...</option>
                <option value="Harassment or Bullying">Harassment or Bullying</option>
                <option value="Hate Speech or Discrimination">Hate Speech or Discrimination</option>
                <option value="Threats or Intimidation">Threats or Intimidation</option>
                <option value="Self-Harm or Suicide Content">Self-Harm or Suicide Content</option>
                <option value="Spam or Advertising">Spam or Advertising</option>
                <option value="Misinformation or False Claims">Misinformation or False Claims</option>
                <option value="Trolling or Provocation">Trolling or Provocation</option>
                <option value="Impersonation or Deception">Impersonation or Deception</option>
                <option value="Inappropriate or Explicit Content">Inappropriate or Explicit Content</option>
                <option value="Repeated Rule Violations">Repeated Rule Violations</option>
                <option value="Abuse of Anonymity">Abuse of Anonymity</option>
                <option value="Other">Other</option>
              </select>
              <div class="invalid-feedback">Please select a reason.</div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Additional Details (Optional)</label>
              <textarea v-model="reportDetails" class="form-control bg-body" rows="3" placeholder="Provide specific context..."></textarea>
            </div>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="submitReport" :disabled="reporting">
              <span v-if="reporting" class="spinner-border spinner-border-sm me-2"></span> Submit Report
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.profile-banner {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
}

.badge-item {
    transition: transform 0.2s ease;
    cursor: default;
}
.badge-item:hover {
    transform: translateY(-3px);
}
.drop-shadow {
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.2));
}
.grayscale {
    filter: grayscale(100%);
}

.card-hover {
    transition: transform 0.2s, box-shadow 0.2s;
}
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.1) !important;
}

.fade-in {
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.z-2 {
    z-index: 2;
}
.text-shadow {
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

.filter-shadow {
    filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
}
.profile-banner {
    background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
}
</style>

<script>
import { ref, defineComponent, watch } from 'vue';

const PaginationControls = defineComponent({
  name: 'PaginationControls',
  props: {
    meta: {
      type: Object,
      required: true,
      default: () => ({ current_page: 1, last_page: 1 })
    }
  },
  emits: ['page-change'],
  setup(props, { emit }) {
    const pageInput = ref(props.meta.current_page);

    const goToPage = () => {
      let page = parseInt(pageInput.value, 10);
      if (isNaN(page) || page < 1) {
        page = 1;
      } else if (page > props.meta.last_page) {
        page = props.meta.last_page;
      }
      pageInput.value = page;
      emit('page-change', page);
    };

    const changePage = (page) => {
      if (page < 1 || page > props.meta.last_page) return;
      pageInput.value = page;
      emit('page-change', page);
    };

    watch(() => props.meta.current_page, (newPage) => {
      pageInput.value = newPage;
    });

    return { pageInput, goToPage, changePage };
  },
  template: `
    <nav class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
      <div class="btn-group" role="group">
        <button
          type="button"
          class="btn btn-outline-secondary"
          :disabled="meta.current_page === 1"
          @click="changePage(meta.current_page - 1)"
        >
          <i class="fas fa-angle-left"></i> Prev
        </button>
        <button
          type="button"
          class="btn btn-outline-secondary"
          :disabled="meta.current_page === meta.last_page"
          @click="changePage(meta.current_page + 1)"
        >
          Next <i class="fas fa-angle-right"></i>
        </button>
      </div>

      <div class="text-muted small d-none d-sm-block">
        Page {{ meta.current_page }} of {{ meta.last_page }}
      </div>

      <div class="input-group" style="max-width: 150px;">
        <input
          type="number"
          class="form-control bg-body"
          v-model.number="pageInput"
          :min="1"
          :max="meta.last_page"
          @keyup.enter="goToPage"
          aria-label="Go to page"
        />
        <button class="btn btn-outline-secondary" type="button" @click="goToPage">
          Go
        </button>
      </div>
    </nav>
  `
});

export default {
  components: {
    PaginationControls
  }
}
</script>

<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import debounce from "lodash.debounce";
import { Modal } from "bootstrap";
import { useAuth } from '../../store/auth';

const route = useRoute();
const router = useRouter();
const { fetchUser: refreshGlobalUser } = useAuth();

const user = ref(null);
const loading = ref(true);
const error = ref("");
const isSelf = ref(false);
const activeTab = ref("posts");
const showToast = ref(false);
const toastMessage = ref("");
const posts = ref([]);
const comments = ref([]);
const postSearchTerm = ref("");
const commentSearchTerm = ref("");
const postsMeta = ref({ current_page: 1, last_page: 1, total: 0 });
const commentsMeta = ref({ current_page: 1, last_page: 1, total: 0 });
const loadingPosts = ref(true);
const loadingComments = ref(true);
const loadingPostsToggle = ref(false);
const authUserId = window.authUserId || null;
const reportModal = ref(null);
const reportType = ref('user');
const reportTargetId = ref(null);
const reportReasonCategory = ref("");
const reportDetails = ref("");
const showReportError = ref(false);
const reporting = ref(false);
const showLockedBadges = ref(false);
const resending = ref(false);
const resendMessage = ref("");
const successMessage = ref("");

const unlockedBadges = computed(() => {
  return user.value?.badges?.filter(b => !b.locked) || [];
});

const lockedBadges = computed(() => {
  return user.value?.badges?.filter(b => b.locked) || [];
});

const avatarUrlWithCache = computed(() => {
    if (!user.value || !user.value.avatar_url) return '/images/avatars/default.jpg';
    if (route.query.updated === 'true') {
        return `${user.value.avatar_url}?t=${new Date().getTime()}`;
    }
    return user.value.avatar_url;
});

const formatDate = (dateStr) => {
  if (!dateStr) return "Unknown";
  const d = new Date(dateStr);
  return isNaN(d.getTime()) ? "Unknown" : d.toLocaleDateString();
};

const fetchUser = async () => {
  loading.value = true;
  error.value = "";
  try {
    const id = route.params.id;
    const url = id ? `/api/profile/${id}` : `/api/profile`;
    const res = await axios.get(url, { params: { user_only: true } });

    user.value = res.data.user;
    isSelf.value = res.data.is_owner;

    if (isSelf.value) {
        refreshGlobalUser();
    }

    postsMeta.value.total = res.data.posts_meta?.total ?? 0;
    commentsMeta.value.total = res.data.comments_meta?.total ?? 0;

    if (route.query.updated === "true") {
      toastMessage.value = "Profile updated successfully!";
      showToast.value = true;
      setTimeout(() => (showToast.value = false), 3000);
      router.replace({ query: {} });
    }
  } catch (e) {
    console.error(e);
    error.value = "Failed to load profile";
  } finally {
    loading.value = false;
  }
};

const fetchPosts = async (page = 1) => {
  if (!user.value) return;
  loadingPosts.value = true;
  try {
    const id = route.params.id || user.value.user_id;
    const url = `/api/profile/${id}`;
    const res = await axios.get(url, {
      params: {
        page: page,
        per_page: 10,
        tab: "posts",
        search: postSearchTerm.value || undefined,
      },
    });

    posts.value = res.data.posts ?? [];
    postsMeta.value = res.data.posts_meta ?? postsMeta.value;
  } catch (err) {
    console.error("Error loading posts:", err);
  } finally {
    loadingPosts.value = false;
  }
};

const fetchComments = async (page = 1) => {
  if (!isSelf.value) return;
  loadingComments.value = true;
  try {
    const res = await axios.get(`/api/profile`, {
      params: {
        page: page,
        per_page: 10,
        tab: "comments",
        search: commentSearchTerm.value || undefined,
      },
    });
    comments.value = res.data.comments ?? [];
    commentsMeta.value = res.data.comments_meta ?? commentsMeta.value;
  } catch (err) {
    console.error("Error loading comments:", err);
  } finally {
    loadingComments.value = false;
  }
};

const debouncedSearchPosts = debounce(() => fetchPosts(1), 400);

const togglePostVisibility = async (id) => {
  try {
    const res = await axios.patch(`/api/posts/${id}/toggle-profile-visibility`);
    const post = posts.value.find((p) => p.post_id === id);
    if (post) post.hidden_in_profile = res.data.hidden_in_profile;
  } catch (err) {
    console.error("Error toggling post visibility:", err);
  }
};

const toggleHideAll = async () => {
  try {
    loadingPostsToggle.value = true;
    const res = await axios.post("/api/profile/toggle-hide-all-posts");

    user.value.hide_all_posts = res.data.hide_all_posts;
    posts.value.forEach((p) => (p.hidden_in_profile = user.value.hide_all_posts));

    toastMessage.value = res.data.message;
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
  } catch (err) {
    console.error("Error toggling posts visibility:", err);
  } finally {
    loadingPostsToggle.value = false;
  }
};

const resendVerification = async () => {
    resending.value = true;
    resendMessage.value = "";

    try {
        await axios.post('/api/email/verification-notification');
        resendMessage.value = "Link sent! Check your inbox (and spam).";
    } catch (err) {
        if (err.response && err.response.status === 429) {
            resendMessage.value = "Please wait a minute before retrying.";
        } else {
            resendMessage.value = "Failed to send. Try again later.";
        }
    } finally {
        resending.value = false;
    }
};

const openReportModal = (targetId, type) => {
  reportTargetId.value = targetId;
  reportType.value = type;
  reportReasonCategory.value = "";
  reportDetails.value = "";
  showReportError.value = false;
  const modal = Modal.getOrCreateInstance(reportModal.value);
  modal.show();
};

const submitReport = async () => {
  if (!reportReasonCategory.value) {
    showReportError.value = true;
    return;
  }

  try {
    reporting.value = true;
    await axios.get("/sanctum/csrf-cookie");
    await axios.post("/api/report", {
      target_id: reportTargetId.value,
      type: reportType.value,
      reason: reportReasonCategory.value,
      details: reportDetails.value.trim(),
    });

    const modal = Modal.getInstance(reportModal.value);
    modal.hide();

    toastMessage.value = "Report submitted successfully. Thank you!";
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
  } catch (err) {
    console.error("Error submitting report:", err);
    toastMessage.value = "Failed to submit report.";
    showToast.value = true;
    setTimeout(() => (showToast.value = false), 3000);
  } finally {
    reporting.value = false;
    reportReasonCategory.value = "";
    reportDetails.value = "";
  }
};

onMounted(async () => {
  await fetchUser();
  if (route.query.verified === 'true') {
      successMessage.value = "Your email has been successfully verified! You can now post and comment.";
      router.replace({ query: null });
  }

  setTimeout(() => {
    if (!window.bootstrap) return;

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(el => {
      new window.bootstrap.Tooltip(el);
    });
  }, 0);
});


watch(
  user,
  (newUser) => {
    if (!newUser) return;

    setTimeout(() => {
      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
      tooltipTriggerList.forEach(el => {
        new bootstrap.Tooltip(el);
      });
    }, 0);

    if (activeTab.value === "posts") fetchPosts(1);
    else if (activeTab.value === "comments" && isSelf.value) fetchComments(1);
  }
);

watch(
  user,
  (newUser, oldUser) => {
    if (newUser && !oldUser) {
      if (activeTab.value === "posts") fetchPosts(1);
      else if (activeTab.value === "comments" && isSelf.value) fetchComments(1);
    }
  },
  { immediate: false }
);

watch(activeTab, (newTab) => {
  if (user.value) {
    if (newTab === "posts") fetchPosts(1);
    else if (newTab === "comments" && isSelf.value) fetchComments(1);
  }
});
</script>
