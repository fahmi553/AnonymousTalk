<template>
  <div class="guidelines-page pb-5">

    <div class="bg-dark text-white py-5 mb-5 border-bottom" style="border-color: var(--bs-primary) !important;">
      <div class="container text-center" style="max-width: 700px;">
        <i class="fas fa-balance-scale fa-3x text-primary mb-3"></i>
        <h1 class="display-5 fw-bold">Community Guidelines</h1>
        <p class="lead text-white-50">
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

      <div class="accordion shadow-sm rounded-3 overflow-hidden" id="rulesAccordion">

        <div class="accordion-item border-0 border-bottom">
          <h2 class="accordion-header">
            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#rule1" aria-expanded="true">
              1. No Hate Speech or Harassment
            </button>
          </h2>
          <div id="rule1" class="accordion-collapse collapse show" data-bs-parent="#rulesAccordion">
            <div class="accordion-body text-body">
              We have a zero-tolerance policy for hate speech. Content that attacks, threatens, or insults individuals or groups based on race, religion, gender, sexual orientation, or disability will be removed immediately.
              <br><br>
              <strong class="text-primary">Consequence:</strong> Immediate ban or massive Trust Score penalty.
            </div>
          </div>
        </div>

        <div class="accordion-item border-0 border-bottom">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#rule2">
              2. No Doxxing (Revealing Private Info)
            </button>
          </h2>
          <div id="rule2" class="accordion-collapse collapse" data-bs-parent="#rulesAccordion">
            <div class="accordion-body text-body">
              Do not post personal information about yourself or others. This includes real names, phone numbers, addresses, or links to personal social media profiles.
              <br><br>
              <strong class="text-primary">Consequence:</strong> Permanent account suspension.
            </div>
          </div>
        </div>

        <div class="accordion-item border-0 border-bottom">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#rule3">
              3. No Spam or Self-Promotion
            </button>
          </h2>
          <div id="rule3" class="accordion-collapse collapse" data-bs-parent="#rulesAccordion">
            <div class="accordion-body text-body">
              Anonymous Talk is for conversation, not advertising. Do not post repetitive content, affiliate links, or promote products/services.
            </div>
          </div>
        </div>

        <div class="accordion-item border-0">
          <h2 class="accordion-header">
            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#rule4">
              4. Trust Score System
            </button>
          </h2>
          <div id="rule4" class="accordion-collapse collapse" data-bs-parent="#rulesAccordion">
            <div class="accordion-body text-body">
              <p>Your actions on the platform affect your <strong class="text-primary">Trust Score</strong>.</p>
              <ul class="mb-0">
                <li><strong>Gaining Trust:</strong> Consistent posting, receiving likes, and helpful comments.</li>
                <li><strong>Losing Trust:</strong> Having posts reported, spamming, or violating these guidelines.</li>
              </ul>
              <p class="mt-2 mb-0 fst-italic text-secondary small">Users with low trust scores may be restricted from posting or commenting.</p>
            </div>
          </div>
        </div>

      </div>

      <div class="alert alert-light border shadow-sm mt-5 d-flex flex-column flex-md-row align-items-center justify-content-between p-4 rounded-4 bg-body-tertiary">
        <div class="mb-3 mb-md-0">
          <h5 class="fw-bold mb-1">See something wrong?</h5>
          <p class="mb-0 text-secondary small">If you see content that violates these rules, please report it.</p>
        </div>
        <button @click="$router.push('/help')" class="btn btn-outline-primary rounded-pill px-4">
          <i class="fas fa-flag me-2"></i>How to Report
        </button>
      </div>

    </div>
  </div>
</template>

<style scoped>
.icon-box {
  width: 60px;
  height: 60px;
}

/* --- FORCE ACCORDION TO MATCH DARK/LIGHT MODE --- */

/* 1. Force the Item background to be the body background (Dark in dark mode) */
.accordion-item {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
}

/* 2. Force the collapsed button to match the body background */
.accordion-button.collapsed {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
}

/* 3. Force the Expanded Button to be Purple-Tinted */
.accordion-button:not(.collapsed) {
  background-color: var(--bs-primary-bg-subtle);
  color: var(--bs-primary);
  box-shadow: none; /* Remove blue glow */
}

/* 4. Focus Ring - Purple */
.accordion-button:focus {
  border-color: var(--bs-primary);
  box-shadow: 0 0 0 0.25rem rgba(var(--bs-primary-rgb), 0.25);
}

/* 5. Ensure the Body Text inside is visible */
.accordion-body {
    background-color: var(--bs-body-bg);
    color: var(--bs-body-color);
}
</style>
