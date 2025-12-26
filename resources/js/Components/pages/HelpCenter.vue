<template>
  <div class="container mt-4 pb-5">

    <div class="row align-items-center mb-5 pb-4 border-bottom border-secondary-subtle">
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold text-body-emphasis mb-2">Help Center</h1>
        <p class="lead text-muted mb-0">
          Troubleshoot issues and learn about the platform.
        </p>
      </div>
      <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
        <i class="fas fa-life-ring fa-4x text-body-tertiary"></i>
      </div>
    </div>

    <div class="row g-5">

      <div class="col-lg-3 d-none d-lg-block">
        <div class="sticky-top" style="top: 2rem; z-index: 10;">
          <h6 class="fw-bold text-uppercase text-muted small mb-3 ps-3">On this page</h6>
          <div class="list-group list-group-flush border-start border-secondary-subtle">

            <a href="#general"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'general' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('general')">
               General & Account
            </a>

            <a href="#trust-score"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'trust-score' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('trust-score')">
               Trust Score & Bans
            </a>

            <a href="#badges"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'badges' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('badges')">
               Badges
            </a>

            <a href="#safety"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'safety' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('safety')">
               Safety & Reporting
            </a>

            <a href="#contact"
               class="list-group-item list-group-item-action bg-transparent border-0 py-2 ps-3"
               :class="activeSection === 'contact' ? 'text-primary fw-bold border-start border-primary border-3' : 'text-muted'"
               @click.prevent="scrollToSection('contact')">
               Contact Support
            </a>

          </div>
        </div>
      </div>

      <div class="col-lg-9">

        <section id="general" class="mb-5 section-offset">
          <div class="d-flex align-items-center mb-4">
            <div class="p-2 bg-body-tertiary rounded-3 me-3">
              <i class="fas fa-user-cog fa-lg text-primary"></i>
            </div>
            <h3 class="fw-bold text-body-emphasis mb-0">General & Account</h3>
          </div>

          <div class="d-flex flex-column gap-3">
            <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
              <div class="card-header bg-body-tertiary border-0 p-0">
                <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                        @click="toggle('gen1')">
                  Can I change my username?
                  <i class="fas small" :class="openItem === 'gen1' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
              </div>
              <div v-if="openItem === 'gen1'" class="card-body bg-body text-secondary pt-0 px-3 pb-3">
                <hr class="text-secondary opacity-25 mt-0 mb-3">
                Yes, but you cannot pick a custom name. You can <router-link to="/profile/edit">regenerate a new random identity</router-link> in your profile settings.
              </div>
            </div>

            <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
              <div class="card-header bg-body-tertiary border-0 p-0">
                <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                        @click="toggle('gen2')">
                  Is my email address visible?
                  <i class="fas small" :class="openItem === 'gen2' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
              </div>
              <div v-if="openItem === 'gen2'" class="card-body bg-body text-secondary pt-0 px-3 pb-3">
                <hr class="text-secondary opacity-25 mt-0 mb-3">
                No. Your email is strictly for login and account recovery. It is never shown to other users.
              </div>
            </div>
          </div>
        </section>

        <section id="trust-score" class="mb-5 section-offset">
          <div class="d-flex align-items-center mb-4">
            <div class="p-2 bg-body-tertiary rounded-3 me-3">
              <i class="fas fa-chart-line fa-lg text-warning"></i>
            </div>
            <h3 class="fw-bold text-body-emphasis mb-0">Trust Score & Bans</h3>
          </div>

          <div class="d-flex flex-column gap-3">
            <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
              <div class="card-header bg-body-tertiary border-0 p-0">
                <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                        @click="toggle('trust1')">
                  Why can't I post or comment?
                  <i class="fas small" :class="openItem === 'trust1' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
              </div>
              <div v-if="openItem === 'trust1'" class="card-body bg-body text-secondary pt-0 px-3 pb-3">
                <hr class="text-secondary opacity-25 mt-0 mb-3">
                If your Trust Score drops below <strong>30%</strong>, posting is disabled. Below <strong>10%</strong>, commenting is disabled.
              </div>
            </div>

            <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
              <div class="card-header bg-body-tertiary border-0 p-0">
                <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                        @click="toggle('trust2')">
                  How do I increase my Trust Score?
                  <i class="fas small" :class="openItem === 'trust2' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                </button>
              </div>
              <div v-if="openItem === 'trust2'" class="card-body bg-body text-secondary pt-0 px-3 pb-3">
                <hr class="text-secondary opacity-25 mt-0 mb-3">
                Consistent positive activity increases your score. Getting "Likes" helps significantly. Getting reported lowers your score.
              </div>
            </div>
          </div>
        </section>

        <section id="badges" class="mb-5 section-offset">
           <div class="d-flex align-items-center mb-4">
            <div class="p-2 bg-body-tertiary rounded-3 me-3">
              <i class="fas fa-medal fa-lg text-success"></i>
            </div>
            <h3 class="fw-bold text-body-emphasis mb-0">Badges</h3>
          </div>

           <div class="row g-3">
             <div class="col-md-6">
               <div class="card bg-body-tertiary border-0 h-100">
                 <div class="card-body">
                   <h6 class="fw-bold text-body-emphasis">Trust Badges</h6>
                   <p class="small text-secondary mb-0">Awarded automatically for reaching Trust Score milestones (e.g., Verified Member).</p>
                 </div>
               </div>
             </div>
             <div class="col-md-6">
               <div class="card bg-body-tertiary border-0 h-100">
                 <div class="card-body">
                   <h6 class="fw-bold text-body-emphasis">Behavior Badges</h6>
                   <p class="small text-secondary mb-0">Awarded for specific actions, like "Helpful Commenter" or "Respectful Debater."</p>
                 </div>
               </div>
             </div>
           </div>
        </section>

        <section id="safety" class="mb-5 section-offset">
          <div class="d-flex align-items-center mb-4">
            <div class="p-2 bg-body-tertiary rounded-3 me-3">
              <i class="fas fa-shield-alt fa-lg text-danger"></i>
            </div>
            <h3 class="fw-bold text-body-emphasis mb-0">Safety & Reporting</h3>
          </div>

          <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
            <div class="card-header bg-body-tertiary border-0 p-0">
              <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                      @click="toggle('safe1')">
                How do I report a user?
                <i class="fas small" :class="openItem === 'safe1' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
              </button>
            </div>
            <div v-if="openItem === 'safe1'" class="card-body bg-body text-secondary pt-0 px-3 pb-3">
              <hr class="text-secondary opacity-25 mt-0 mb-3">
              Click the <span class="badge bg-danger"><i class="fas fa-flag"></i> Report</span> button on any user profile or post. Our team reviews these daily.
            </div>
          </div>
        </section>

        <section id="contact" class="mb-5 section-offset">
          <div class="card border-0 shadow-sm bg-body-tertiary rounded-4 overflow-hidden">
            <div class="card-body p-5 text-center">
              <h3 class="fw-bold text-body-emphasis">Still need help?</h3>
              <p class="text-secondary mb-4">
                Our support team is here to help with account issues.
              </p>
              <a href="mailto:support@anonymoustalk.com" class="btn btn-primary rounded-pill px-5 fw-bold">
                Email Support
              </a>
            </div>
          </div>
        </section>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const activeSection = ref('general');
const openItem = ref('gen1');

const toggle = (id) => {
  openItem.value = openItem.value === id ? null : id;
};

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
  const sections = ['general', 'trust-score', 'badges', 'safety', 'contact'];

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

.btn:focus {
  box-shadow: none;
}
</style>
