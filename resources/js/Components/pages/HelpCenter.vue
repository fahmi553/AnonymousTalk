<template>
  <div class="container py-5" style="max-width: 800px;">
    <h1 class="fw-bold mb-4 text-center">How can we help?</h1>

    <div class="accordion shadow-sm" id="faqAccordion">
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
            Is Anonymous Talk really anonymous?
          </button>
        </h2>
        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Yes. We do not display your real name on your profile. However, we do store your email for account recovery purposes.
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
            How do I report a user?
          </button>
        </h2>
        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Go to the specific post or comment you want to report, click the <strong>Report</strong> button (flag icon), select a reason, and submit. Our team will review it shortly.
          </div>
        </div>
      </div>

      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
            Can I delete my account?
          </button>
        </h2>
        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
          <div class="accordion-body">
            Currently, you can delete individual posts and comments. To request full account deletion, please contact support.
          </div>
        </div>
      </div>
    </div>

    <div class="text-center mt-5">
      <p class="text-muted">Still need help?</p>
      <a href="mailto:support@anonymoustalk.com" class="btn btn-primary">Contact Support</a>
    </div>
  </div>
</template>
