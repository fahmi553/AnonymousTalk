<template>
  <div class="container mt-4 pb-5">

    <div class="row align-items-center mb-5 pb-4 border-bottom border-secondary-subtle">
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold text-body-emphasis mb-2">Cookie Policy</h1>
        <p class="lead text-muted mb-0">
          How we use cookies and similar technologies to improve your experience.
        </p>
      </div>
      <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
        <i class="fas fa-cookie-bite fa-4x text-body-tertiary"></i>
      </div>
    </div>

    <div class="row g-5">

      <div class="col-lg-3 d-none d-lg-block">
        <div class="sticky-top" style="top: 2rem; z-index: 10;">
          <h6 class="fw-bold text-uppercase text-muted small mb-3 ps-3">On this page</h6>
          <div class="list-group list-group-flush border-start border-secondary-subtle">

            <a href="#what-are-cookies"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'what-are-cookies' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('what-are-cookies')">
               1. What are Cookies?
            </a>

            <a href="#how-we-use"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'how-we-use' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('how-we-use')">
               2. How We Use Them
            </a>

            <a href="#types"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'types' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('types')">
               3. Types of Cookies
            </a>

            <a href="#managing"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'managing' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('managing')">
               4. Managing Cookies
            </a>

          </div>
        </div>
      </div>

      <div class="col-lg-9">

        <section id="what-are-cookies" class="mb-5 section-offset">
          <h3 class="fw-bold text-body-emphasis mb-3">1. What are Cookies?</h3>
          <p class="text-secondary mb-3">
            Cookies are small text files that are placed on your computer or mobile device by websites that you visit. They are widely used in order to make websites work, or work more efficiently, as well as to provide information to the owners of the site.
          </p>
          <div class="alert alert-light border border-secondary-subtle d-flex align-items-center text-body-emphasis">
            <i class="fas fa-info-circle text-primary me-3 fa-lg"></i>
            <div>
              <strong>Note:</strong> We do not use cookies to track your personal identity across other websites. Your anonymity is our priority.
            </div>
          </div>
        </section>

        <section id="how-we-use" class="mb-5 section-offset">
          <h3 class="fw-bold text-body-emphasis mb-3">2. How We Use Cookies</h3>
          <p class="text-secondary mb-3">
            We use cookies primarily for <strong>authentication</strong> and <strong>security</strong>. Since our platform relies on trust scores and preventing ban evasion, cookies help us identify unique sessions without requiring real-world identity verification.
          </p>
          <ul class="text-secondary">
            <li class="mb-2">To keep you signed in securely.</li>
            <li class="mb-2">To remember your preferences (like Dark Mode settings).</li>
            <li class="mb-2">To prevent CSRF (Cross-Site Request Forgery) attacks.</li>
          </ul>
        </section>

        <section id="types" class="mb-5 section-offset">
          <h3 class="fw-bold text-body-emphasis mb-3">3. Types of Cookies We Use</h3>

          <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3 mb-3">
            <div class="card-body">
              <h6 class="fw-bold text-body-emphasis"><i class="fas fa-shield-alt text-success me-2"></i>Essential Cookies</h6>
              <p class="small text-secondary mb-0">
                These are necessary for the website to function. They include the Laravel Session cookie (<code>laravel_session</code>) and the CSRF token (<code>XSRF-TOKEN</code>). You cannot opt out of these if you wish to use the site.
              </p>
            </div>
          </div>

          <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3 mb-3">
            <div class="card-body">
              <h6 class="fw-bold text-body-emphasis"><i class="fas fa-chart-bar text-info me-2"></i>Analytical Cookies</h6>
              <p class="small text-secondary mb-0">
                We use minimal, anonymous logging to monitor server performance and load times. This data is aggregated and does not identify individual users.
              </p>
            </div>
          </div>
        </section>

        <section id="managing" class="mb-5 section-offset">
          <h3 class="fw-bold text-body-emphasis mb-3">4. Managing Cookies</h3>
          <p class="text-secondary">
            Most web browsers allow some control of most cookies through the browser settings. To find out more about cookies, including how to see what cookies have been set and how to manage and delete them, visit <a href="https://www.aboutcookies.org" target="_blank" class="text-primary text-decoration-none">www.aboutcookies.org</a>.
          </p>
          <p class="text-secondary">
            Please note that if you disable "Essential Cookies," you will be unable to log in or post content on Anonymous Talk.
          </p>
        </section>

        <div class="border-top border-secondary-subtle pt-4 mt-5">
          <p class="small text-muted">
            Last Updated: <span class="fw-bold text-body-emphasis">{{ lastUpdated }}</span>
          </p>
          <p class="small text-muted">
            Questions? Email us at <a href="mailto:privacy@anonymoustalk.com" class="text-decoration-none">privacy@anonymoustalk.com</a>.
          </p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';

const activeSection = ref('what-are-cookies');

const lastUpdated = computed(() => {
  return new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
});

const scrollToSection = (id) => {
  activeSection.value = id;
  const element = document.getElementById(id);
  if (element) {
    const headerOffset = 80;
    const elementPosition = element.getBoundingClientRect().top;
    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

    window.scrollTo({
      top: offsetPosition,
      behavior: "smooth"
    });
  }
};

const handleScroll = () => {
  const sections = ['what-are-cookies', 'how-we-use', 'types', 'managing'];

  for (const section of sections) {
    const element = document.getElementById(section);
    if (element) {
      const rect = element.getBoundingClientRect();
      if (rect.top >= 0 && rect.top <= 300) {
        activeSection.value = section;
        break;
      }
    }
  }
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
.section-offset {
  scroll-margin-top: 100px;
}
</style>
