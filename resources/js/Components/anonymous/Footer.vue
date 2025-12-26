<template>
  <footer class="bg-dark text-white pt-5 pb-4 border-top border-secondary mt-auto">
    <div class="container">
      <div class="row gy-4 justify-content-between">

        <div class="col-lg-4 col-md-6">
            <div class="mb-3 d-flex align-items-center gap-2">
                <img :src="myLogo" alt="Logo" class="footer-logo rounded-3">

                <h5 class="fw-bold mb-0 text-white">Anonymous Talk</h5>
            </div>
          <p class="text-white-50 small mb-4" style="line-height: 1.6;">
            A safe space to speak freely and connect openly. We prioritize your privacy and freedom of expression within a respectful community.
          </p>

          <div class="d-flex gap-3">
            <a href="#" class="social-link" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="#" class="social-link" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link" aria-label="GitHub"><i class="fab fa-github"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-md-6">
          <h6 class="fw-bold mb-3 text-white text-uppercase small spacing-wider">Community</h6>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li>
              <router-link to="/" class="footer-link">
                <i class="fas fa-comments me-2 opacity-50"></i>Discussion Feed
              </router-link>
            </li>
            <li>
              <router-link to="/community-guidelines" class="footer-link">
                <i class="fas fa-book-open me-2 opacity-50"></i>Guidelines & Safety
              </router-link>
            </li>
            <li>
              <router-link to="/help" class="footer-link">
                <i class="fas fa-life-ring me-2 opacity-50"></i>Help Center
              </router-link>
            </li>
          </ul>
        </div>

        <div class="col-lg-3 col-md-6">
          <h6 class="fw-bold mb-3 text-white text-uppercase small spacing-wider">Legal</h6>
          <ul class="list-unstyled d-flex flex-column gap-2 small">
            <li><router-link to="/terms" class="footer-link">Terms of Service</router-link></li>
            <li><router-link to="/privacy" class="footer-link">Privacy Policy</router-link></li>
            <li><router-link to="/cookies" class="footer-link">Cookie Policy</router-link></li>
          </ul>
        </div>

      </div>

      <hr class="border-secondary opacity-25 my-4">

      <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-white-50">
        <p class="mb-2 mb-md-0">
          &copy; {{ currentYear }} Anonymous Talk. All rights reserved.
        </p>
        <div>
          <span class="opacity-50">Designed for Privacy.</span>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue';
import myLogo from '../../../images/AnonymousTalkLogo3.png';

const currentYear = computed(() => new Date().getFullYear());
</script>

<style scoped>
.spacing-wider {
  letter-spacing: 1px;
}

.footer-link {
  color: rgba(255, 255, 255, 0.6);
  text-decoration: none;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
}

.footer-link:hover {
  color: #fff;
  transform: translateX(3px);
}

.social-link {
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  transition: all 0.3s ease;
}

.social-link:hover {
  background-color: var(--bs-primary);
  color: #fff;
  transform: translateY(-2px);
}

.footer-logo {
  height: 40px;
  width: auto;
}

.spacing-wider {
  letter-spacing: 1px;
}
</style>
