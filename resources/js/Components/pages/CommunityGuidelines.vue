<template>
  <div class="guidelines-page pb-5">

    <div class="bg-body-tertiary text-body py-5 mb-5 border-bottom border-secondary-subtle">
      <div class="container text-center" style="max-width: 700px;">
        <i class="fas fa-balance-scale fa-3x text-primary mb-3"></i>
        <h1 class="display-5 fw-bold text-body-emphasis">Community Guidelines</h1>
        <p class="lead text-body-secondary">
          We believe in free speech, but we also believe in safety.
          Help us keep Anonymous Talk a place for open, respectful discussion.
        </p>
      </div>
    </div>

    <div class="container" style="max-width: 900px;">

      <div class="row g-4 mb-5">
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
            <div class="card-body text-center p-4">
              <div class="icon-box bg-success-subtle text-success mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-shield-alt fa-lg"></i>
              </div>
              <h5 class="fw-bold">Stay Safe</h5>
              <p class="small text-secondary mb-0">Never share your real name, address, or financial details. Anonymity is your shield.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
            <div class="card-body text-center p-4">
              <div class="icon-box bg-primary-subtle text-primary mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-heart fa-lg"></i>
              </div>
              <h5 class="fw-bold">Be Civil</h5>
              <p class="small text-secondary mb-0">Disagreement is fine; disrespect is not. Attack ideas, not people.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-sm bg-body-tertiary">
            <div class="card-body text-center p-4">
              <div class="icon-box bg-warning-subtle text-warning mb-3 mx-auto rounded-circle d-flex align-items-center justify-content-center">
                <i class="fas fa-chart-line fa-lg"></i>
              </div>
              <h5 class="fw-bold">Build Trust</h5>
              <p class="small text-secondary mb-0">Good behavior earns Trust Score points. Bad behavior restricts your account.</p>
            </div>
          </div>
        </div>
      </div>

      <h3 class="fw-bold mb-4 border-bottom pb-2">The Code of Conduct</h3>

      <div class="d-flex flex-column gap-3">

        <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
          <div class="card-header bg-body-tertiary border-0 p-0">
            <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                    @click="toggle('rule1')">
              1. No Hate Speech or Harassment
              <i class="fas small" :class="openItem === 'rule1' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
          </div>
          <div v-if="openItem === 'rule1'" class="card-body bg-body text-body-secondary pt-0 px-3 pb-3">
            <hr class="text-secondary opacity-25 mt-0 mb-3">
            We have a zero-tolerance policy for hate speech. Content that attacks, threatens, or insults individuals or groups based on race, religion, gender, sexual orientation, or disability will be removed immediately.
            <br><br>
            <strong class="text-body-emphasis">Consequence:</strong> Immediate ban or massive Trust Score penalty.
          </div>
        </div>

        <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
          <div class="card-header bg-body-tertiary border-0 p-0">
            <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                    @click="toggle('rule2')">
              2. No Doxxing (Revealing Private Info)
              <i class="fas small" :class="openItem === 'rule2' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
          </div>
          <div v-if="openItem === 'rule2'" class="card-body bg-body text-body-secondary pt-0 px-3 pb-3">
            <hr class="text-secondary opacity-25 mt-0 mb-3">
            Do not post personal information about yourself or others. This includes real names, phone numbers, addresses, or links to personal social media profiles.
            <br><br>
            <strong class="text-body-emphasis">Consequence:</strong> Permanent account suspension.
          </div>
        </div>

        <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
          <div class="card-header bg-body-tertiary border-0 p-0">
            <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                    @click="toggle('rule3')">
              3. No Spam or Self-Promotion
              <i class="fas small" :class="openItem === 'rule3' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
          </div>
          <div v-if="openItem === 'rule3'" class="card-body bg-body text-body-secondary pt-0 px-3 pb-3">
            <hr class="text-secondary opacity-25 mt-0 mb-3">
            Anonymous Talk is for conversation, not advertising. Do not post repetitive content, affiliate links, or promote products/services.
          </div>
        </div>

        <div class="card bg-body border border-secondary-subtle shadow-sm rounded-3">
          <div class="card-header bg-body-tertiary border-0 p-0">
            <button class="btn w-100 text-start d-flex justify-content-between align-items-center p-3 text-body-emphasis fw-bold"
                    @click="toggle('rule4')">
              4. Trust Score System
              <i class="fas small" :class="openItem === 'rule4' ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
            </button>
          </div>
          <div v-if="openItem === 'rule4'" class="card-body bg-body text-body-secondary pt-0 px-3 pb-3">
            <hr class="text-secondary opacity-25 mt-0 mb-3">
            <p>Your actions on the platform affect your <strong class="text-primary">Trust Score</strong>.</p>
            <ul class="mb-0">
              <li><strong>Gaining Trust:</strong> Consistent posting, receiving likes, and helpful comments.</li>
              <li><strong>Losing Trust:</strong> Having posts reported, spamming, or violating these guidelines.</li>
            </ul>
            <p class="mt-2 mb-0 fst-italic text-secondary small">Users with low trust scores may be restricted from posting or commenting.</p>
          </div>
        </div>

      </div>

      <div class="alert alert-light border shadow-sm mt-5 d-flex flex-column flex-md-row align-items-center justify-content-between p-4 rounded-4 bg-body-tertiary">
        <div class="mb-3 mb-md-0">
          <h5 class="fw-bold mb-1">See something wrong?</h5>
          <p class="mb-0 text-secondary small">If you see content that violates these rules, please report it.</p>
        </div>
        <router-link to="/help" class="btn btn-outline-primary rounded-pill px-4">
          <i class="fas fa-flag me-2"></i>How to Report
        </router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const openItem = ref('rule1');

const toggle = (id) => {
  openItem.value = openItem.value === id ? null : id;
};
</script>

<style scoped>
.icon-box {
  width: 60px;
  height: 60px;
}

.btn:focus {
  box-shadow: none;
}
</style>
