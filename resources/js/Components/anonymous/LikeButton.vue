<template>
  <button
    @click="toggleLike"
    :class="['btn', liked ? 'btn-primary' : 'btn-outline-primary', 'btn-sm']"
    :disabled="loading"
    title="Like"
  >
    👍
    <span class="ms-1" v-if="count !== null">{{ count }}</span>
  </button>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  post: { type: Object, required: true }
})

const count = ref(props.post.likes_count ?? 0)
const liked = ref(!!props.post.liked)
const loading = ref(false)

watch(() => props.post.likes_count, (v) => {
  count.value = v ?? count.value
})
watch(() => props.post.liked, (v) => {
  liked.value = !!v
})

const toggleLike = async () => {
  if (loading.value) return
  loading.value = true
  try {
    const res = await axios.post(`/posts/${props.post.post_id}/like`)
    liked.value = !!res.data.liked
    count.value = res.data.likes_count
    props.post.likes_count = count.value
    props.post.liked = liked.value
    window.dispatchEvent(new Event('notification-update-needed'));
  } catch (err) {
    if (err.response && (err.response.status === 401 || err.response.status === 419)) {
      window.location.href = '/login'
      return
    }
    console.error('Like error', err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.btn { display: inline-flex; align-items:center; gap:6px; }
</style>
