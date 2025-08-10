<template>
  <div class="mt-3 ps-3 comments-section" ref="commentsSection">
    <!-- <h6>Comments</h6> -->
    <div v-if="comments.length === 0 && !loading">No comments yet.</div>

    <div v-for="comment in comments" :key="comment.comment_id" class="border-top pt-2">
      <strong>{{ comment.user?.username ?? 'Anonymous' }}</strong>:
      <span>{{ comment.content }}</span>
    </div>

    <div v-if="loading" class="text-muted small mt-2">Loading...</div>

    <div v-if="hasMore && !loading" class="mt-2">
      <button class="btn btn-sm btn-outline-primary" @click="loadComments">Load More</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'

const props = defineProps({ postId: Number })

const comments = ref([])
const currentPage = ref(1)
const hasMore = ref(true)
const loading = ref(false)
const commentsSection = ref(null)

const loadComments = async () => {
  if (!hasMore.value) return
  loading.value = true
  try {
    const response = await axios.get(`/api/posts/${props.postId}/comments?page=${currentPage.value}`)
    comments.value = [...response.data.data, ...comments.value]
    hasMore.value = response.data.current_page < response.data.last_page
    currentPage.value++
  } catch (error) {
    console.error('Failed to load comments', error)
  } finally {
    loading.value = false
  }
}

const addComment = async (newComment) => {
  comments.value.push(newComment)
  await nextTick()
  if (commentsSection.value) {
    commentsSection.value.scrollTop = commentsSection.value.scrollHeight
  }
}

onMounted(() => {
  loadComments()
})

defineExpose({ addComment })
</script>

<style scoped>
.comments-section {
  max-height: 300px;
  overflow-y: auto;
}
</style>
