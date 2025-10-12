<template>
  <div class="container py-5 text-center">
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 500px;">
      <div class="card-body">
        <div class="mb-4">
          <i class="fas fa-exclamation-circle text-warning" style="font-size: 4rem;"></i>
        </div>
        <h3 class="fw-bold mb-3">404 - Page Not Found</h3>
        <p class="text-muted mb-4">
          The page you’re trying to reach doesn’t exist or may have been moved.
        </p>
        <router-link to="/" class="btn btn-primary px-4">
          <i class="fas fa-home me-2"></i>Return to Home
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "NotFound",
};
</script>
