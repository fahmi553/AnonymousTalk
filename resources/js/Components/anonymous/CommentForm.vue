<template>
  <div class="d-flex mt-3 mb-4 position-relative">
    <div class="me-3 flex-shrink-0">
      <img
        :src="userAvatar ? (userAvatar.startsWith('http') ? userAvatar : `/images/avatars/${userAvatar}`) : '/images/avatars/default.jpg'"
        alt="My Avatar"
        class="rounded-circle border bg-white"
        style="width: 40px; height: 40px; object-fit: cover;"
      />
    </div>

    <div class="flex-grow-1">
      <textarea
        v-model="content"
        class="form-control bg-body mb-2"
        rows="3"
        placeholder="Write a comment..."
        :disabled="isSubmitting"
      ></textarea>

      <button
        class="btn btn-sm btn-primary"
        @click="submitComment"
        :disabled="isSubmitting || !content.trim()"
      >
        <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-1"></span>
        <i v-else class="fas fa-paper-plane me-1"></i>
        Submit Comment
      </button>
    </div>

    <div
      v-if="showToast"
      class="toast align-items-center border-0 position-absolute top-0 end-0 m-3 show"
      :class="toastClass"
      role="alert"
      style="z-index: 100;"
    >
      <div class="d-flex">
        <div class="toast-body" :class="toastClass.includes('text-dark') ? 'text-dark' : 'text-white'">
          <i class="fas me-2" :class="toastClass.includes('bg-warning') ? 'fa-exclamation-triangle' : 'fa-exclamation-circle'"></i>
          {{ toastMessage }}
        </div>
        <button
            type="button"
            class="btn-close me-2 m-auto"
            :class="toastClass.includes('text-dark') ? '' : 'btn-close-white'"
            @click="showToast = false"
        ></button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  postId: { type: Number, required: true },
  authUserId: { type: Number, required: true },
  userInitial: { type: String, default: '?' },
  userAvatar: { type: String, default: null }
})

const emit = defineEmits(['success'])
const content = ref('')
const isSubmitting = ref(false)
const showToast = ref(false)
const toastMessage = ref('')
const toastClass = ref('')

const submitComment = async () => {
  if (!content.value.trim()) return

  isSubmitting.value = true
  showToast.value = false

  try {
    await axios.get("/sanctum/csrf-cookie")
    const res = await axios.post("/api/comments", {
      post_id: props.postId,
      user_id: props.authUserId,
      content: content.value.trim()
    })

    if (res.data.is_flagged) {
        triggerToast(res.data.message, 'bg-warning text-dark');
        content.value = "";
        return;
    }
    emit('success', res.data.data)
    content.value = ""

  } catch (e) {
    if (e.response && e.response.data && e.response.data.is_flagged) {
        triggerToast(e.response.data.message, 'bg-warning text-dark');
        content.value = "";
    }
    else if (e.response && e.response.status === 403 && e.response.data.banned) {
        triggerToast(e.response.data.message, 'bg-danger text-white');
        setTimeout(() => window.location.href = '/login', 2000);
    }
    else {
        console.error(e)
        triggerToast("Failed to submit comment", "bg-danger text-white")
    }
  } finally {
    isSubmitting.value = false
  }
}

const triggerToast = (msg, cssClass) => {
  toastMessage.value = msg
  toastClass.value = cssClass
  showToast.value = true
  setTimeout(() => showToast.value = false, 4000)
}
</script>
