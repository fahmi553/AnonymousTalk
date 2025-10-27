<template>
  <header class="header d-flex justify-content-between align-items-center p-3 bg-dark text-white shadow-sm border-bottom border-secondary">
    <div class="d-flex align-items-center">
      <h1 class="me-4 mb-0 h4">Anonymous Talk</h1>
      <nav class="d-flex">
        <router-link to="/" class="nav-link text-white me-3">Home</router-link>
        <router-link v-if="authUser" to="/posts/create" class="nav-link text-white me-3">
          Create Post
        </router-link>
        <router-link v-if="authUser" to="/profile" class="nav-link text-white me-3">
          Profile
        </router-link>
      </nav>
    </div>

    <div class="d-flex align-items-center gap-2">
      <ThemeToggle />
      <template v-if="authUser">
        <span class="text-white me-3">Hi, {{ authUser.username }}</span>
        <button @click="logout" class="btn btn-light btn-sm">Logout</button>
      </template>

      <template v-else>
        <a href="/login" class="btn btn-light btn-sm me-2">Login</a>
        <a href="/register" class="btn btn-outline-light btn-sm">Register</a>
      </template>
    </div>
  </header>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAuth } from '../../store/auth'
import ThemeToggle from './ThemeToggle.vue'

const { authUser, fetchUser, logout } = useAuth()

onMounted(fetchUser)
</script>

<style scoped>
.nav-link {
  text-decoration: none;
}
</style>
