<template>
  <footer class="bg-dark text-white py-5 border-top border-secondary mt-auto">
    <div class="container">
      <div class="row gy-4">

        <div class="col-lg-4 col-md-6">
          <h5 class="fw-bold mb-3 text-white">Anonymous Talk</h5>
          <p class="text-white-50 small">
            Speak freely, discuss openly, and connect with others while keeping your identity private.
          </p>
          <div class="d-flex gap-3">
            <a href="#" class="text-white-50 hover-white"><i class="fab fa-facebook fa-lg"></i></a>
            <a href="#" class="text-white-50 hover-white"><i class="fab fa-github fa-lg"></i></a>
            <a href="#" class="text-white-50 hover-white"><i class="fab fa-twitter fa-lg"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-6">
          <h6 class="fw-bold mb-3 text-white">Community</h6>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><router-link to="/community-guidelines" class="text-white-50 text-decoration-none hover-white">Community Guidelines</router-link></li>
            <li><router-link to="/community-guidelines" class="text-white-50 text-decoration-none hover-white">Safety Center</router-link></li>
            <li><router-link to="/community-guidelines" class="text-white-50 text-decoration-none hover-white">Moderation Policies</router-link></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-6">
          <h6 class="fw-bold mb-3 text-white">Support</h6>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><router-link to="/help" class="text-white-50 text-decoration-none hover-white">Report a User</router-link></li>
            <li><router-link to="/help" class="text-white-50 text-decoration-none hover-white">Help & FAQs</router-link></li>
            <li><router-link to="/privacy" class="text-white-50 text-decoration-none hover-white">Privacy Policy</router-link></li>
            <li><router-link to="/terms" class="text-white-50 text-decoration-none hover-white">Terms of Service</router-link></li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-6">
          <h6 class="fw-bold mb-3 text-white">Stay Connected</h6>
          <p class="text-white-50 small">Join our newsletter to stay updated on new features.</p>
          <div class="input-group input-group-sm">
            <input type="email" class="form-control bg-dark text-white border-secondary" placeholder="Enter your email">
            <button class="btn btn-primary" type="button">Subscribe</button>
          </div>
        </div>

      </div>

      <div class="border-top border-secondary mt-5 pt-4 d-flex flex-column flex-md-row justify-content-between align-items-center text-white-50 small">
        <p class="mb-0">&copy; 2025 Anonymous Talk. All rights reserved.</p>
        <div class="d-flex gap-3 mt-2 mt-md-0">
          <router-link to="/terms" class="text-white-50 text-decoration-none hover-white">Terms</router-link>
          <router-link to="/privacy" class="text-white-50 text-decoration-none hover-white">Privacy</router-link>
          <router-link to="/help" class="text-white-50 text-decoration-none hover-white">Help</router-link>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.hover-white:hover {
  color: #fff !important;
  text-decoration: underline !important;
}
</style>
