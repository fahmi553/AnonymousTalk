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
                    <li><a href="#" class="text-white-50 text-decoration-none">Manage Posts</a></li>
                    <li><a href="#" class="text-white-50 text-decoration-none">Manage Comments</a></li>
                </ul>
            </div>

            <div class="col-6 col-lg-2">
                <h3 class="h6 fw-semibold text-white">Management</h3>
                <ul class="list-unstyled mt-3 d-grid gap-2">
                    <li><a href="#" class="text-white-50 text-decoration-none">Manage Users</a></li>
                    <li><a href="#" class="text-white-50 text-decoration-none">Manage Categories</a></li>
                    <li><a href="/" class="text-white-50 text-decoration-none">View Main Site</a></li>
                </ul>
            </div>

            <div class="col-12 col-lg-2">
                <h3 class="h6 fw-semibold text-white">Quick Actions</h3>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="text-white-50" title="Site Settings">
                        <svg class="bi" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                           <path d="M9.405 1.05c-.413-1.4-2.397-1.4-2.81 0l-.1.34a1.464 1.464 0 0 1-2.105.872l-.31-.17c-1.283-.698-2.686.705-1.987 1.987l.169.311a1.464 1.464 0 0 1-.872 2.105l-.34.1c-1.4.413-1.4 2.397 0 2.81l.34.1a1.464 1.464 0 0 1 .872 2.105l-.17.31c-.698 1.283.705 2.686 1.987 1.987l.311-.169a1.464 1.464 0 0 1 2.105.872l.1.34c.413 1.4 2.397 1.4 2.81 0l.1-.34a1.464 1.464 0 0 1 2.105-.872l.31.17c1.283.698 2.686-.705 1.987-1.987l-.169-.311a1.464 1.464 0 0 1 .872-2.105l.34-.1c1.4-.413-1.4-2.397 0-2.81l-.34-.1a1.464 1.464 0 0 1-.872-2.105l.17-.31c.698-1.283-.705-2.686-1.987-1.987l-.311.169a1.464 1.464 0 0 1-2.105-.872l-.1-.34zM8 10.93a2.929 2.929 0 1 1 0-5.858 2.929 2.929 0 0 1 0 5.858z"/>
                        </svg>
                    </a>
                </div>
            </div>

        </div>

        <hr class="my-4 border-secondary">

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
            <p class="mb-3 mb-md-0">&copy; {{ new Date().getFullYear() }} AnonymousTalk (Admin Panel)</p>
            <div class="d-flex gap-3">
                <a href="#" class="text-white-50 text-decoration-none">Admin Policy</a>
            </div>
        </div>

    </div>
  </div>
</template>

<script setup>

</script>
