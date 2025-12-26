<template>
  <div class="privacy-page pb-5">

    <div class="bg-body-tertiary border-bottom py-5 mb-5">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-8">
            <h1 class="display-5 fw-bold mb-2">Privacy Policy</h1>
            <p class="lead text-muted mb-0">
              We respect your anonymity, but we also value transparency. Here is how we handle your data.
            </p>
          </div>
          <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill">
              Last Updated: {{ lastUpdated }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="container">
      <div class="row g-5">

        <div class="col-lg-3 d-none d-lg-block">
          <div class="sticky-top" style="top: 2rem; z-index: 1;">
            <h6 class="fw-bold text-uppercase text-muted small mb-3 ls-1">Table of Contents</h6>
            <div class="list-group list-group-flush border-start">
              <a href="#introduction" class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3 text-secondary" @click.prevent="scrollTo('introduction')">1. Introduction</a>
              <a href="#information" class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3 text-secondary" @click.prevent="scrollTo('information')">2. Information We Collect</a>
              <a href="#anonymity" class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3 text-secondary" @click.prevent="scrollTo('anonymity')">3. How Anonymity Works</a>
              <a href="#usage" class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3 text-secondary" @click.prevent="scrollTo('usage')">4. How We Use Data</a>
              <a href="#security" class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3 text-secondary" @click.prevent="scrollTo('security')">5. Data Security</a>
              <a href="#contact" class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3 text-secondary" @click.prevent="scrollTo('contact')">6. Contact Us</a>
            </div>
          </div>
        </div>

        <div class="col-lg-9">

          <section id="introduction" class="mb-5">
            <h3 class="fw-bold mb-3">1. Introduction</h3>
            <p class="text-secondary">
              Welcome to Anonymous Talk. We operate a platform designed to allow free expression while protecting your public identity. This Privacy Policy explains what information we collect, how we use it, and the choices you have concerning your data. By using our service, you agree to the collection and use of information in accordance with this policy.
            </p>
          </section>

          <section id="information" class="mb-5">
            <h3 class="fw-bold mb-3">2. Information We Collect</h3>
            <div class="card bg-body-tertiary border-0 p-4 mb-3">
              <h5 class="fw-bold"><i class="fas fa-user-circle text-primary me-2"></i>Account Data</h5>
              <p class="text-secondary mb-0 small">
                When you register, we collect your <strong>email address</strong> and a <strong>password</strong>. This is strictly for account recovery and logging in. We do not display this information publicly.
              </p>
            </div>
            <div class="card bg-body-tertiary border-0 p-4">
              <h5 class="fw-bold"><i class="fas fa-server text-primary me-2"></i>Usage Data</h5>
              <p class="text-secondary mb-0 small">
                We may log information such as your computer's Internet Protocol ("IP") address, browser version, and the time and date of your visit. This is standard procedure for web security and spam prevention.
              </p>
            </div>
          </section>

          <section id="anonymity" class="mb-5">
            <h3 class="fw-bold mb-3">3. How Anonymity Works</h3>
            <p class="text-secondary">
              It is important to distinguish between "Public Anonymity" and "Server Anonymity."
            </p>
            <ul class="text-secondary">
              <li class="mb-2"><strong>Public Anonymity:</strong> Other users cannot see your email address or real name. They only see your generated Username (e.g., "BoldCat596") and your generic avatar.</li>
              <li class="mb-2"><strong>Server Visibility:</strong> Our administrators have access to backend logs. While we do not actively monitor private identities, we retain the ability to link a post to an IP address or email account in cases of severe legal violations (e.g., threats of violence, illegal content).</li>
            </ul>
          </section>

          <section id="usage" class="mb-5">
            <h3 class="fw-bold mb-3">4. How We Use Data</h3>
            <p class="text-secondary">We use the collected data for various purposes:</p>
            <ul class="text-secondary">
              <li>To maintain and monitor the usage of our Service.</li>
              <li>To manage your account and authentication.</li>
              <li>To calculate your <strong>Trust Score</strong> based on community interactions.</li>
              <li>To detect, prevent, and address technical issues or spam attacks.</li>
            </ul>
          </section>

          <section id="security" class="mb-5">
            <h3 class="fw-bold mb-3">5. Data Security</h3>
            <p class="text-secondary">
              The security of your data is important to us. Your passwords are hashed using industry-standard encryption (Bcrypt) before being stored in our database. However, remember that no method of transmission over the Internet is 100% secure. While we strive to use commercially acceptable means to protect your Personal Data, we cannot guarantee its absolute security.
            </p>
          </section>

          <section id="contact" class="mb-5">
            <h3 class="fw-bold mb-3">6. Contact Us</h3>
            <p class="text-secondary">
              If you have any questions about this Privacy Policy, please contact us:
            </p>
            <div class="alert alert-light border d-inline-block px-4 py-3">
              <i class="fas fa-envelope me-2 text-primary"></i>
              <strong>support@anonymoustalk.com</strong>
            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const lastUpdated = computed(() => {
  const date = new Date();
  return date.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
});

const scrollTo = (id) => {
  const element = document.getElementById(id);
  if (element) {
    element.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
};
</script>

<style scoped>
.ls-1 {
  letter-spacing: 1px;
}

.list-group-item {
  font-size: 0.95rem;
  transition: all 0.2s ease;
  border-left: 2px solid transparent !important;
}

.list-group-item:hover {
  color: var(--bs-primary) !important;
  background-color: var(--bs-secondary-bg-subtle) !important;
  border-left: 2px solid var(--bs-primary) !important;
  padding-left: 1.5rem !important;
}
</style>
