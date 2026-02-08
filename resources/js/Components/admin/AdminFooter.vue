<template>
  <div class="bg-dark text-light py-5 border-top border-secondary">
    <div class="container">
        <div class="row g-4 gy-5">
            <div class="col-12 col-lg-4">
                <h2 class="h4 fw-bold text-white">Anonymous Talk (Admin)</h2>
                <p class="mt-3 text-white-50" style="max-width: 24rem;">
                    Administration and moderation dashboard.
                </p>
            </div>

            <div class="col-lg-2 d-none d-lg-block"></div>

            <div class="col-6 col-lg-2">
                <h3 class="h6 fw-semibold text-white">Moderation</h3>
                <ul class="list-unstyled mt-3 d-grid gap-2">
                    <li><a href="/admin/reports" class="text-white-50 text-decoration-none">Pending Reports</a></li>
                    <li><a href="/admin/reports?tab=sentiment" class="text-white-50 text-decoration-none">Flagged Content</a></li>
                    <li><a href="/admin/dashboard" class="text-white-50 text-decoration-none">Content Manager</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h3 class="h6 fw-semibold text-white">Management</h3>
                <ul class="list-unstyled mt-3 d-grid gap-2">
                    <li><a href="/admin/users" class="text-white-50 text-decoration-none">User Trust Scores</a></li>
                    <li><a href="/admin/logs" class="text-white-50 text-decoration-none">System Logs</a></li>
                    <li><a href="/" class="text-white-50 text-decoration-none">View Main Site</a></li>
                </ul>
            </div>
        </div>

        <hr class="my-4 border-secondary">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
            <p class="mb-3 mb-md-0">&copy; {{ new Date().getFullYear() }} AnonymousTalk (Admin Panel)</p>
            <div class="d-flex gap-3">
                <a href="/terms" class="text-white-50 text-decoration-none">Terms of Service</a>
            </div>
        </div>

    </div>
  </div>
</template>

<script setup>

</script>

