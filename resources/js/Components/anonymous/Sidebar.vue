<template>
  <div class="card border-0 rounded-lg shadow-sm">
    <div class="card-body p-3">
      <h6 class="text-uppercase text-muted mb-3 small fw-bold ls-1">Explore</h6>
      <ul class="list-unstyled mb-4">
        <li class="mb-2">
          <router-link to="/?category=advice" class="sidebar-link d-flex align-items-center text-decoration-none">
            <i class="fas fa-comment-medical me-2 text-primary width-20"></i> Advice
          </router-link>
        </li>
        <li class="mb-2">
          <router-link to="/?category=experiences" class="sidebar-link d-flex align-items-center text-decoration-none">
            <i class="fas fa-history me-2 text-success width-20"></i> Experiences
          </router-link>
        </li>
        <li>
          <router-link to="/?category=off-topic" class="sidebar-link d-flex align-items-center text-decoration-none">
            <i class="fas fa-coffee me-2 text-warning width-20"></i> Off-Topic
          </router-link>
        </li>
      </ul>
      <h6 class="text-uppercase text-muted mb-3 small fw-bold ls-1">Community</h6>
      <ul class="list-unstyled mb-4">
        <li class="mb-2">
          <router-link to="/posts/trending" class="sidebar-link d-flex align-items-center text-decoration-none">
            <i class="fas fa-fire me-2 text-danger width-20"></i> Top Posts
          </router-link>
        </li>
        <li class="mb-2">
          <a href="#" class="sidebar-link d-flex align-items-center text-decoration-none text-muted">
            <i class="fas fa-comments me-2 width-20"></i> Recent Comments
          </a>
        </li>
        <li>
          <a href="#" class="sidebar-link d-flex align-items-center text-decoration-none text-muted">
            <i class="fas fa-trophy me-2 text-warning width-20"></i> Leaderboard
          </a>
        </li>
      </ul>
      <h6 class="text-uppercase text-muted mb-3 small fw-bold ls-1">About</h6>
      <ul class="list-unstyled">
        <li>
          <router-link to="/about" class="sidebar-link d-flex align-items-center text-decoration-none">
            <i class="fas fa-info-circle me-2 text-info width-20"></i> About Anonymous Talk
          </router-link>
        </li>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.sidebar-link {
  color: var(--bs-body-color);
  padding: 8px 10px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.sidebar-link:hover {
  background-color: rgba(var(--bs-primary-rgb), 0.1);
  color: var(--bs-primary);
  transform: translateX(3px);
}

.ls-1 {
  letter-spacing: 1px;
}

.width-20 {
  width: 20px;
  text-align: center;
}
</style>