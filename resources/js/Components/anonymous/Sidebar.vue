<template>
  <div class="card bg-body border-0 rounded-4 shadow-sm sticky-top" style="top: 6rem;">
    <div class="card-body p-4">

      <h6 class="text-uppercase text-secondary mb-3 small fw-bold ls-1">Menu</h6>
      <ul class="list-unstyled mb-4 gap-2 d-flex flex-column">
        <li>
          <router-link to="/" class="sidebar-link">
            <i class="fas fa-stream me-3 width-20"></i> Discussion Feed
          </router-link>
        </li>
        <li>
          <router-link to="/posts/create" class="sidebar-link">
            <i class="fas fa-plus-circle me-3 width-20"></i> Create Post
          </router-link>
        </li>
        <li>
          <router-link to="/profile" class="sidebar-link">
            <i class="fas fa-user me-3 width-20"></i> My Profile
          </router-link>
        </li>
      </ul>

      <h6 class="text-uppercase text-secondary mb-3 small fw-bold ls-1">Community</h6>
      <ul class="list-unstyled mb-4 gap-2 d-flex flex-column">
        <li>
          <router-link to="/community-guidelines" class="sidebar-link">
            <i class="fas fa-book-open me-3 width-20"></i> Guidelines
          </router-link>
        </li>
        <li>
          <router-link to="/help" class="sidebar-link">
            <i class="fas fa-life-ring me-3 width-20"></i> Help Center
          </router-link>
        </li>
      </ul>

      <h6 class="text-uppercase text-secondary mb-3 small fw-bold ls-1">Legal</h6>
      <ul class="list-unstyled gap-2 d-flex flex-column">
        <li>
          <router-link to="/privacy" class="sidebar-link">
            <i class="fas fa-shield-alt me-3 width-20"></i> Privacy Policy
          </router-link>
        </li>
        <li>
          <router-link to="/terms" class="sidebar-link">
            <i class="fas fa-file-contract me-3 width-20"></i> Terms of Service
          </router-link>
        </li>
      </ul>

    </div>
  </div>
</template>

<style scoped>
.sidebar-link {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  color: var(--bs-body-color);
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s ease-in-out;
  font-weight: 500;
}

.sidebar-link i {
  color: var(--bs-secondary);
  transition: color 0.2s ease-in-out;
}

.sidebar-link:hover {
  background-color: var(--bs-tertiary-bg);
  color: var(--bs-primary);
  transform: translateX(4px);
}

.sidebar-link:hover i {
  color: var(--bs-primary);
}

.router-link-active {
  background-color: rgba(var(--bs-primary-rgb), 0.1);
  color: var(--bs-primary) !important;
  font-weight: 700;
}

.router-link-active i {
  color: var(--bs-primary) !important;
}

.ls-1 {
  letter-spacing: 1px;
  font-size: 0.75rem;
}

.width-20 {
  width: 24px;
  text-align: center;
}
</style>
