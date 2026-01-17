<template>
  <div class="container py-4">
    <div class="row g-4">

      <div class="col-lg-3 d-none d-lg-block">
         <div class="sticky-top" style="top: 6rem; z-index: 1;">

            <button v-if="authUser" class="btn btn-primary w-100 rounded-pill fw-bold mb-4 py-2 shadow-sm" @click="$router.push('/posts/create')">
              <i class="fas fa-plus me-2"></i>Create Post
            </button>
            <button v-else class="btn btn-primary w-100 rounded-pill fw-bold mb-4 py-2 shadow-sm" @click="$router.push('/login')">
              <i class="fas fa-sign-in-alt me-2"></i>Login to Post
            </button>

            <div class="card bg-body border border-secondary-subtle shadow-sm rounded-4 overflow-hidden mb-4">
              <div class="card-header bg-body border-bottom border-secondary-subtle fw-bold text-body-emphasis small text-uppercase py-3">
                Feed Filters
              </div>
              <div class="list-group list-group-flush">
                <button
                  class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                  :class="{ 'active-filter': filter === 'latest' }"
                  @click="setFilter('latest')"
                >
                  <span><i class="fas fa-clock me-3 text-secondary"></i>Latest</span>
                  <i v-if="filter === 'latest'" class="fas fa-check text-primary"></i>
                </button>

                <button
                  class="list-group-item list-group-item-action border-0 py-3 px-4 d-flex align-items-center justify-content-between"
                  :class="{ 'active-filter': filter === 'trending' }"
                  @click="setFilter('trending')"
                >
                  <span><i class="fas fa-fire me-3 text-danger"></i>Trending</span>
                  <i v-if="filter === 'trending'" class="fas fa-check text-primary"></i>
                </button>
              </div>
            </div>

          <div class="card bg-body border border-secondary-subtle shadow-sm rounded-4 overflow-hidden">
               <div class="card-header bg-body border-bottom border-secondary-subtle fw-bold text-body-emphasis small text-uppercase py-3">
                 Categories
               </div>
               <div class="list-group list-group-flush">
                 <button
                    class="list-group-item list-group-item-action py-2 px-4 small"
                    :class="{'text-primary fw-bold': !selectedCategory}"
                    @click="setCategory('')"
                 >
                   All Categories
                 </button>
                 <button
                    v-for="cat in categories"
                    :key="cat.category_id"
                    class="list-group-item list-group-item-action py-2 px-4 small d-flex justify-content-between align-items-center"
                    :class="{'text-primary fw-bold': selectedCategory === cat.category_id}"
                    @click="setCategory(cat.category_id)"
                 >
                   {{ cat.name }}
                 </button>
               </div>
            </div>

         </div>
      </div>

      <div class="col-lg-9">

        <div class="card bg-body border border-secondary-subtle shadow-sm rounded-4 mb-4 p-2">
            <div class="d-flex flex-column flex-md-row gap-2 align-items-center">

            <div class="input-group input-group-lg border-0 flex-grow-1">
              <span class="input-group-text bg-transparent border-0 ps-3">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input
                type="text"
                class="form-control border-0 shadow-none bg-transparent text-body-emphasis fs-6"
                placeholder="Search discussions..."
                v-model="search"
                @keyup.enter="fetchPosts(1)"
              >
            </div>

            <div class="vr bg-secondary opacity-25 d-none d-md-block" style="height: 24px;"></div>

            <div class="dropdown px-2">
               <button class="btn btn-link text-decoration-none text-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                 <i class="fas fa-sort-amount-down me-1"></i>
                 {{ sortLabel }}
               </button>
               <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                 <li><button class="dropdown-item" @click="setSort('desc')">Newest First</button></li>
                 <li><button class="dropdown-item" @click="setSort('asc')">Oldest First</button></li>
                 <li><button class="dropdown-item" @click="setSort('most_commented')">Most Commented</button></li>
               </ul>
            </div>

          </div>
        </div>

        <div v-if="selectedCategory || search" class="mb-3 d-flex gap-2">
           <span v-if="selectedCategory" class="badge bg-primary rounded-pill d-flex align-items-center px-3 py-2">
             Category: {{ getCategoryName(selectedCategory) }}
             <i class="fas fa-times ms-2 cursor-pointer" @click="setCategory('')"></i>
           </span>
           <span v-if="search" class="badge bg-secondary rounded-pill d-flex align-items-center px-3 py-2">
             Search: "{{ search }}"
             <i class="fas fa-times ms-2 cursor-pointer" @click="search = ''; fetchPosts(1)"></i>
           </span>
        </div>

        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="text-muted mt-2 small">Loading community posts...</p>
        </div>

        <div v-else-if="posts.length === 0" class="text-center py-5">
          <div class="mb-3">
            <i class="fas fa-wind fa-3x text-secondary opacity-25"></i>
          </div>
          <h5 class="fw-bold text-body-emphasis">No posts found</h5>
          <p class="text-muted">Try adjusting your filters or search terms.</p>
          <button class="btn btn-outline-primary rounded-pill btn-sm" @click="clearAllFilters">
            Clear Filters
          </button>
        </div>

        <div v-else class="d-flex flex-column gap-3">
          <div
            v-for="post in posts"
            :key="post.post_id"
            class="card bg-body border border-secondary-subtle shadow-sm rounded-4 overflow-hidden hover-card"
          >
            <div class="card-body p-4">

              <div class="d-flex align-items-center mb-3">
                <img
                  :src="getAvatarUrl(post.user?.avatar)"
                  @error="$event.target.src = '/images/avatars/default.jpg'"
                  class="rounded-circle border border-2 border-body shadow-sm me-3"
                  width="40"
                  height="40"
                  alt="Avatar"
                  style="object-fit: cover;"
                >
                <div>
                  <h6 class="fw-bold text-body-emphasis mb-0">
                    {{ post.user?.username || 'Anonymous' }}
                    <span v-if="authUser && post.user?.user_id === authUser.user_id" class="badge bg-primary-subtle text-primary ms-1" style="font-size: 0.6rem;">YOU</span>
                  </h6>
                  <small class="text-secondary" style="font-size: 0.75rem;">
                    {{ formatDate(post.created_at) }} &bull; {{ post.category }}
                  </small>
                </div>
              </div>

              <router-link :to="`/posts/${post.post_id}`" class="text-decoration-none">
                <h5 class="fw-bold text-body-emphasis mb-2">{{ post.title }}</h5>
                <p class="text-secondary mb-3" style="line-height: 1.6;">
                  {{ truncate(post.content, 200) }}
                </p>
              </router-link>

              <div class="d-flex gap-2 border-top border-secondary-subtle pt-3 mt-3">

                <button
                  class="btn btn-sm rounded-pill px-3 fw-bold transition-btn"
                  :class="post.liked ? 'btn-danger text-white' : 'btn-light text-secondary'"
                  @click.stop="toggleLike(post)"
                  :disabled="!authUser"
                >
                  <i class="fas fa-heart me-2" :class="{'animate-pulse': post.liked}"></i>
                  {{ post.likes_count || 0 }}
                </button>

                <router-link
                  :to="`/posts/${post.post_id}`"
                  class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-secondary action-btn"
                >
                  <i class="far fa-comment-alt me-2"></i>{{ post.comments_count || 0 }}
                </router-link>

                <button
                  class="btn btn-sm btn-light rounded-pill px-3 fw-bold text-secondary ms-auto action-btn"
                  @click.stop="sharePost(post)"
                  >
                  <i class="fas fa-share me-2"></i>Share
                </button>
              </div>

            </div>
          </div>
        </div>

        <div class="mt-5 d-flex justify-content-center" v-if="meta.last_page > 1">
          <nav>
            <ul class="pagination">
              <li class="page-item" :class="{ disabled: meta.current_page === 1 }">
                <button class="page-link border-0 rounded-start-pill bg-body text-body-emphasis shadow-sm" @click="changePage(meta.current_page - 1)">
                  <i class="fas fa-chevron-left me-1"></i> Prev
                </button>
              </li>
              <li class="page-item disabled">
                <span class="page-link border-0 bg-body text-muted fw-bold px-4">Page {{ meta.current_page }}</span>
              </li>
              <li class="page-item" :class="{ disabled: meta.current_page === meta.last_page }">
                <button class="page-link border-0 rounded-end-pill bg-body text-body-emphasis shadow-sm" @click="changePage(meta.current_page + 1)">
                  Next <i class="fas fa-chevron-right ms-1"></i>
                </button>
              </li>
            </ul>
          </nav>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuth } from '../../store/auth';
const { authUser } = useAuth();
const route = useRoute();
const router = useRouter();
const posts = ref([]);
const categories = ref([]);
const loading = ref(true);
const filter = ref('latest');
const sortOrder = ref('desc');
const search = ref('');
const selectedCategory = ref('');
const meta = ref({ current_page: 1, last_page: 1 });
const sortLabel = computed(() => {
    if (sortOrder.value === 'asc') return 'Oldest First';
    if (sortOrder.value === 'most_commented') return 'Most Commented';
    return 'Newest First';
});

const sharePost = async (post) => {
  const postUrl = `${window.location.origin}/posts/${post.post_id}`;

  const shareData = {
    title: post.title,
    text: `Check out this post by ${post.user?.username || 'Anonymous'}`,
    url: postUrl,
  };

  try {
    if (navigator.share) {
      await navigator.share(shareData);
    }
    else {
      await navigator.clipboard.writeText(postUrl);
      alert("Link copied to clipboard!");
    }
  } catch (err) {
    if (err.name !== 'AbortError') {
      console.error('Share failed:', err);
    }
  }
};


const fetchCategories = async () => {
  try {
    const res = await axios.get('/api/categories');
    categories.value = res.data;
  } catch (err) {
    console.error('Categories error:', err);
  }
};

const fetchPosts = async (page = 1) => {
  loading.value = true;
  try {
    const res = await axios.get('/api/posts', {
      params: {
        page: page,
        filter: filter.value,
        sort: sortOrder.value,
        category_id: selectedCategory.value,
        search: search.value,
      }
    });
    posts.value = res.data.data || [];
    meta.value = res.data.meta || { current_page: 1, last_page: 1 };
  } catch (err) {
    console.error("Failed to load posts", err);
  } finally {
    loading.value = false;
  }
};

const toggleLike = async (post) => {
  if (!authUser.value) return;
  const originalLiked = post.liked;
  const originalCount = post.likes_count;

  post.liked = !post.liked;
  post.likes_count += post.liked ? 1 : -1;

  try {
    await axios.post(`/api/posts/${post.post_id}/toggle-like`);
  } catch (err) {
    post.liked = originalLiked;
    post.likes_count = originalCount;
    console.error('Like failed', err);
  }
};


const setFilter = (newFilter) => {
  filter.value = newFilter;
  fetchPosts(1);
};

const setSort = (order) => {
    sortOrder.value = order;
    fetchPosts(1);
}

const setCategory = (id) => {
    selectedCategory.value = id;
    fetchPosts(1);
}

const clearAllFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    filter.value = 'latest';
    sortOrder.value = 'desc';
    fetchPosts(1);
}

const changePage = (page) => {
  if (page < 1 || page > meta.value.last_page) return;
  fetchPosts(page);
};

const getAvatarUrl = (filename) => {
  if (!filename) return '/images/avatars/default.jpg';
  if (filename.startsWith('http')) return filename;
  return `/images/avatars/${filename}`;
};

const getCategoryName = (id) => {
    const cat = categories.value.find(c => c.category_id === id);
    return cat ? cat.name : 'Unknown';
}

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};

const truncate = (text, length) => {
  if (!text) return '';
  return text.length > length ? text.substring(0, length) + '...' : text;
};


onMounted(() => {
  if (route.query.category) selectedCategory.value = Number(route.query.category);
  if (route.query.search) search.value = route.query.search;

  fetchCategories();
  fetchPosts();
});

watch(() => route.query, (newQuery) => {
    if (newQuery.category) {
        selectedCategory.value = Number(newQuery.category);
        fetchPosts(1);
    }
});
</script>

<style scoped>
.active-filter {
    color: var(--bs-primary);
    background-color: var(--bs-primary-bg-subtle);
    font-weight: bold;
    border-left: 3px solid var(--bs-primary) !important;
}

.hover-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important;
}

.action-btn:hover {
  background-color: var(--bs-secondary-bg);
  color: var(--bs-primary) !important;
}

.animate-pulse {
  animation: pulse 0.4s ease-in-out;
}
@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.4); }
  100% { transform: scale(1); }
}

.cursor-pointer {
    cursor: pointer;
}
</style>
