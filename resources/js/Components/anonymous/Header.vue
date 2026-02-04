<template>
  <header class="header d-flex justify-content-between align-items-center px-4 py-3 bg-dark text-white shadow-sm border-bottom border-secondary sticky-top" style="z-index: 1000;">

    <div class="d-flex align-items-center">
      <router-link to="/" class="text-decoration-none d-flex align-items-center">
        <img :src="myLogo" alt="Logo" class="header-logo me-3 rounded-3">
        <h1 class="mb-0 h5 fw-bold text-white tracking-tight">Anonymous Talk</h1>
      </router-link>
    </div>

    <div class="d-flex align-items-center gap-3">
      <ThemeToggle />

      <template v-if="authUser">
        <div class="dropdown">
          <button
            class="btn btn-link text-decoration-none position-relative p-0 me-2"
            type="button"
            data-bs-toggle="dropdown"
            @click="markAllAsRead"
          >
            <i class="fas fa-bell fa-lg text-secondary"></i>
            <span v-if="unreadCount > 0" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
              {{ unreadCount }}
            </span>
          </button>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-secondary mt-2" style="width: 320px; max-height: 400px; overflow-y: auto;">
            <li>
                <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-secondary">
                    <h6 class="dropdown-header text-uppercase small ls-1 p-0 mb-0">Notifications</h6>
                    <button v-if="notifications.length > 0" @click.stop="clearAll" class="btn btn-link btn-sm text-decoration-none p-0 text-muted" style="font-size: 0.75rem;">Clear All</button>
                </div>
            </li>

            <div v-if="notifications.length === 0" class="text-center p-4 text-muted small">
                <i class="far fa-bell-slash mb-2 d-block opacity-50"></i>
                No notifications.
            </div>

            <li v-for="notif in notifications" :key="notif.id" class="border-bottom border-secondary border-opacity-10 position-relative">
              <div
                 class="dropdown-item d-flex align-items-start gap-3 py-3"
                 style="white-space: normal; cursor: pointer;"
                 @click="handleNotificationClick(notif)"
              >
                 <i :class="[notif.data.icon, notif.data.color, 'mt-1 fs-5']"></i>

                 <div class="flex-grow-1">
                     <p class="mb-1 small text-white" style="line-height: 1.4;">{{ notif.data.message }}</p>
                     <small class="text-white-50" style="font-size: 0.7rem;">{{ timeAgo(notif.created_at) }}</small>
                 </div>

                 <button
                    class="btn btn-link text-secondary p-0 ms-1 align-self-start opacity-50 hover-opacity-100"
                    @click.stop="dismissSingle(notif.id)"
                    title="Dismiss"
                 >
                    <i class="fas fa-times"></i>
                 </button>
              </div>
            </li>
          </ul>
        </div>
      </template>

      <div class="vr bg-secondary mx-1 opacity-50" style="height: 24px;"></div>

      <template v-if="authUser">
        <div class="dropdown">
          <button class="btn btn-link text-decoration-none p-0 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
            <img :src="avatarSrc" @error="$event.target.src = '/images/avatars/default.jpg'" class="rounded-circle border border-secondary" style="width: 36px; height: 36px; object-fit: cover;">
            <span class="text-white fw-bold d-none d-md-block small">{{ authUser.username }}</span>
            <i class="fas fa-chevron-down text-secondary small"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark shadow-lg border-secondary mt-2">
            <li><h6 class="dropdown-header text-uppercase small ls-1">Account</h6></li>
            <li><router-link to="/profile" class="dropdown-item d-flex align-items-center gap-2"><i class="fas fa-user fa-fw text-secondary"></i> My Profile</router-link></li>
            <li><hr class="dropdown-divider border-secondary"></li>
            <li><button @click="handleLogout" class="dropdown-item d-flex align-items-center gap-2 text-danger"><i class="fas fa-sign-out-alt fa-fw"></i> Logout</button></li>
          </ul>
        </div>
      </template>

      <template v-else>
        <div class="d-flex gap-2">
          <a href="/login" class="btn btn-outline-light btn-sm fw-bold px-3 rounded-pill">Login</a>
          <a href="/register" class="btn btn-primary btn-sm fw-bold px-3 rounded-pill">Register</a>
        </div>
      </template>
    </div>
  </header>
</template>

<script setup>
import { onMounted, onUnmounted, ref, computed } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuth } from '../../store/auth'
import ThemeToggle from './ThemeToggle.vue'
import myLogo from '../../../images/AnonymousTalkLogo3.png'

const { authUser, fetchUser, logout } = useAuth()
const router = useRouter()
const notifications = ref([])

const unreadCount = computed(() => notifications.value.filter(n => !n.read_at).length)

const avatarSrc = computed(() => {
  if (!authUser.value) return '/images/avatars/default.jpg';
  if (authUser.value.avatar_url) return authUser.value.avatar_url;
  const filename = authUser.value.avatar || 'default.jpg';
  return `/images/avatars/${filename}`;
});

const handleLogout = async () => {
    await logout();
    window.location.href = '/login';
};

const fetchNotifications = async () => {
    if (!authUser.value) return;
    try {
        const res = await axios.get('/api/notifications');
        notifications.value = res.data;
    } catch (e) {
        console.error("Notification error:", e);
    }
}

const handleNotificationUpdate = () => {
    fetchNotifications();
};

const markAllAsRead = async () => {
    if (unreadCount.value === 0) return;
    try {
        await axios.post('/api/notifications/mark-read');
        notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date() }));
    } catch (e) {
        console.error(e);
    }
}

const handleNotificationClick = (notif) => {
    if (!notif.read_at) {
        notifications.value = notifications.value.map(n =>
            n.id === notif.id ? { ...n, read_at: new Date() } : n
        );
        axios.post('/api/notifications/mark-read');
    }
    if (notif.data.link) {
        router.push(notif.data.link);
    }
}

const dismissSingle = async (id) => {
    try {
        notifications.value = notifications.value.filter(n => n.id !== id);
        await axios.delete(`/api/notifications/${id}`);
    } catch (e) {
        console.error("Failed to dismiss:", e);
    }
}

const clearAll = async () => {
    try {
        notifications.value = [];
        await axios.delete('/api/notifications/clear-all');
    } catch (e) {
        console.error(e);
    }
}

const timeAgo = (dateStr) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

onMounted(() => {
    fetchUser().then(() => {
        if (authUser.value) {
            fetchNotifications();
            window.addEventListener('notification-update-needed', handleNotificationUpdate);
            setInterval(fetchNotifications, 30000);
        }
    });
})

onUnmounted(() => {
    window.removeEventListener('notification-update-needed', handleNotificationUpdate);
});
</script>

<style scoped>
.header-logo { height: 40px; width: auto; }
.tracking-tight { letter-spacing: -0.5px; }
.sticky-top { position: sticky; top: 0; }
.hover-opacity-100:hover { opacity: 1 !important; color: #dc3545 !important; }
</style>
