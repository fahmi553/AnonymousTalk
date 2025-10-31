<template>
  <div class="container" style="max-width: 700px;">
    <!-- Main Toast -->
    <div
      v-if="showToast"
      class="toast align-items-center text-bg-success border-0 position-fixed top-0 end-0 m-3 show"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
      style="z-index: 1055; min-width: 250px;"
    >
      <div class="d-flex">
        <div class="toast-body">✅ {{ toastMessage }}</div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" @click="showToast = false"></button>
      </div>
    </div>

    <div v-if="loading" class="text-center mt-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">Loading profile...</p>
    </div>
    <div v-else-if="error" class="text-danger mt-5">{{ error }}</div>

    <div v-else-if="user" class="card bg-body shadow-lg border-0 overflow-hidden">
      <div class="p-4 text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="d-flex align-items-center">
          <img
            src="https://i.pravatar.cc/100"
            alt="Avatar"
            class="rounded-circle border border-3 border-white me-3"
            width="80"
            height="80"
          />
          <div>
            <h3 class="mb-0">{{ user.username || "Anonymous" }}</h3>
            <small class="text-light">{{ isSelf ? "This is you" : "Anonymous User" }}</small>
          </div>
        </div>
      </div>

      <div class="card-body">
        <div class="d-flex justify-content-around text-center mb-4">
          <div>
            <h5 class="mb-0">{{ postsMeta.total ?? 0 }}</h5>
            <small class="text-muted">Posts</small>
          </div>
          <div>
            <h5 class="mb-0">{{ commentsMeta.total ?? 0 }}</h5>
            <small class="text-muted">Comments</small>
          </div>
          <div>
            <h5 class="mb-0">{{ user.likes_count ?? 0 }}</h5>
            <small class="text-muted">Likes</small>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label"><strong>Trust Score:</strong></label>
          <div class="progress" style="height: 20px;">
            <div
              class="progress-bar"
              role="progressbar"
              :style="{ width: (user.trust_score || 0) + '%' }"
              :aria-valuenow="user.trust_score || 0"
              aria-valuemin="0"
              aria-valuemax="100"
            >
              {{ user.trust_score || 0 }}%
            </div>
          </div>
        </div>
        <p><i class="fas fa-user-shield me-2"></i><strong>Role:</strong> {{ user.role || "User" }}</p>
        <p>
          <i class="fas fa-calendar-alt me-2"></i>
          <strong>Joined:</strong>
          {{ formatDate(user.created_at) }}
        </p>
        <div v-if="isSelf" class="mt-3 d-flex gap-2">
          <button
            class="btn btn-secondary flex-grow-1"
            @click="toggleHideAll('posts')"
            :disabled="loadingPostsToggle"
          >
            <i class="fas" :class="user.hide_all_posts ? 'fa-eye' : 'fa-eye-slash'"></i>
            {{ user.hide_all_posts ? "Show All Posts" : "Hide All Posts" }}
          </button>
        </div>
        <ul v-if="isSelf" class="nav nav-pills mt-4" id="profileTabs">
          <li class="nav-item">
            <button class="nav-link" :class="{ active: activeTab === 'posts' }" @click="activeTab = 'posts'">Posts</button>
          </li>
          <li class="nav-item">
            <button class="nav-link" :class="{ active: activeTab === 'comments' }" @click="activeTab = 'comments'">Comments</button>
          </li>
        </ul>
        <div class="mt-3" v-if="activeTab === 'posts'">
          <h4 v-if="!isSelf" class="fw-bold mb-3">Posts</h4>
          <div v-if="isSelf" class="input-group mb-3">
            <input type="text" class="form-control bg-body" placeholder="Search your posts..." v-model="postSearchTerm" @input="debouncedSearchPosts" />
            <button class="btn btn-outline-secondary" type="button" @click="fetchPosts(1)">
              <i class="fas fa-search"></i>
            </button>
          </div>
          <div v-if="loadingPosts" class="text-muted small text-center py-3">Loading posts...</div>
          <div v-else-if="posts.length === 0 && postSearchTerm" class="text-muted small text-center py-3">
            No posts found matching your search.
          </div>
          <div v-else-if="posts.length === 0" class="text-muted small text-center py-3">No posts yet.</div>
          <div v-else>
            <div
              v-for="post in posts"
              :key="post.post_id"
              class="border-bottom py-2 d-flex justify-content-between align-items-start"
              :class="{ 'opacity-50': post.hidden_in_profile }"
            >
              <div>
                <router-link :to="`/posts/${post.post_id}`" class="fw-semibold text-decoration-none text-body-emphasis">
                  {{ post.title || "Untitled Post" }}
                </router-link>
                <p class="text-muted small mb-0">{{ post.comments_count ?? 0 }} comments · {{ post.likes_count ?? 0 }} likes</p>
              </div>
              <button
                v-if="isSelf"
                class="btn btn-sm"
                :class="post.hidden_in_profile ? 'btn-success' : 'btn-secondary'"
                @click="togglePostVisibility(post.post_id)"
              >
                <i :class="post.hidden_in_profile ? 'fas fa-eye' : 'fas fa-eye-slash'" class="me-1"></i>
                {{ post.hidden_in_profile ? "Unhide" : "Hide" }}
              </button>
            </div>
            <PaginationControls v-if="postsMeta.last_page > 1" :meta="postsMeta" @page-change="fetchPosts" class="mt-3" />
          </div>
        </div>
        <div class="mt-3" v-else-if="activeTab === 'comments' && isSelf">
          <div class="alert alert-info small text-center rounded-3 mb-3">
            <i class="fas fa-info-circle me-2"></i>
            Only you can see your comment history.
          </div>
          <div class="input-group mb-3">
            <input type="text" class="form-control bg-body" placeholder="Search your comments..." v-model="commentSearchTerm" @input="debouncedSearchComments" />
            <button class="btn btn-outline-secondary" type="button" @click="fetchComments(1)">
              <i class="fas fa-search"></i>
            </button>
          </div>
          <div v-if="loadingComments" class="text-muted small text-center py-3">Loading comments...</div>
          <div v-else-if="comments.length === 0 && commentSearchTerm" class="text-muted small text-center py-3">No comments found matching your search.</div>
          <div v-else-if="comments.length === 0" class="text-muted small text-center py-3">You haven’t made any comments yet.</div>
          <div v-else>
            <div v-for="c in comments" :key="c.comment_id" class="border-bottom py-2">
              <p class="mb-1">{{ c.content }}</p>
              <router-link v-if="c.post" :to="`/posts/${c.post_id}`" class="small text-muted">
                On: {{ c.post.title || "Post" }}
              </router-link>
            </div>
            <PaginationControls v-if="commentsMeta.last_page > 1" :meta="commentsMeta" @page-change="fetchComments" class="mt-3" />
          </div>
        </div>
        <div class="d-flex gap-2 mt-4">
          <router-link v-if="isSelf" to="/profile/edit" class="btn btn-primary flex-grow-1">
            <i class="fas fa-edit me-1"></i> Edit Profile
          </router-link>
          <button v-else-if="authUserId" class="btn btn-danger flex-grow-1" @click="openReportModal(user.user_id, 'user')">
            🚩 Report User
          </button>

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
            <label class="form-label">Reason for reporting:</label>
            <textarea
              v-model="reportReason"
              class="form-control bg-body"
              rows="3"
              placeholder="Describe the issue..."
            ></textarea>
          </div>
          <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" @click="submitReport" :disabled="reporting">
              <span v-if="reporting" class="spinner-border spinner-border-sm me-2"></span>
              Submit Report
            </button>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import { ref, defineComponent, watch } from 'vue';
import { Modal } from "bootstrap";

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
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import debounce from "lodash.debounce";
import { Modal } from "bootstrap";

const route = useRoute();
const router = useRouter();
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
const reportReason = ref("");
const reporting = ref(false);

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
const debouncedSearchComments = debounce(() => fetchComments(1), 400);

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

const openReportModal = (targetId, type) => {
  reportTargetId.value = targetId;
  reportType.value = type;
  reportReason.value = "";
  reporting.value = false;
  const modal = Modal.getOrCreateInstance(reportModal.value);
  modal.show();
};

const submitReport = async () => {
  if (!reportReason.value.trim()) {
    return;
  }

  try {
    reporting.value = true;
    await axios.get("/sanctum/csrf-cookie");
    await axios.post("/api/report", {
      target_id: reportTargetId.value,
      type: reportType.value,
      reason: reportReason.value.trim(),
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
    reportReason.value = "";
  }
};

onMounted(fetchUser);

watch(
  () => route.params.id,
  (newId, oldId) => {
    if (newId !== oldId) {
      activeTab.value = "posts";
      postSearchTerm.value = "";
      commentSearchTerm.value = "";
      fetchUser();
    }
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

<style scoped>
.card {
  border-radius: 15px;
}
.opacity-50 {
  opacity: 0.5;
}
.input-group .form-control {
  text-align: center;
}
</style>
