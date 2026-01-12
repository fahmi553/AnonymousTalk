<template>
    <div v-if="userId && !isVerified" class="alert alert-warning d-flex align-items-center justify-content-between mx-4 mt-3 mb-0 shadow-sm border">
        <div>
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Action Required:</strong> Please verify your email to post or comment.
        </div>

        <div>
            <router-link
                :to="{ name: 'profile.view' }"
                class="btn btn-sm btn-outline-dark fw-bold action-btn text-decoration-none"
            >
                Verify in Profile <i class="fas fa-arrow-right ms-1"></i>
            </router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: 'VerificationAlert',
    data() {
        return {
            userId: window.authUserId,
            isVerified: window.isEmailVerified
        }
    }
}
</script>

<style scoped>
[data-bs-theme="dark"] .alert-warning {
    background-color: #332701;
    color: #ffda6a;
    border-color: #664d03;
}

[data-bs-theme="dark"] .action-btn {
    color: #ffda6a;
    border-color: #ffda6a;
}

[data-bs-theme="dark"] .action-btn:hover {
    background-color: #ffda6a;
    color: #000;
}
</style>
