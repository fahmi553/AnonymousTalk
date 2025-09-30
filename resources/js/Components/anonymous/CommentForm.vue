<template>
  <form @submit.prevent="submitComment" class="mt-3">
    <div class="input-group">
      <input
        v-model="content"
        type="text"
        class="form-control"
        placeholder="Write a comment..."
      />
      <button type="submit" class="btn btn-outline-secondary">Comment</button>
    </div>
  </form>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  postId: Number,
  userId: Number
})
const emit = defineEmits(['comment-submitted'])
const content = ref('')

const submitComment = async () => {
  if (!content.value.trim()) return

  try {
    const res = await axios.post('/api/comments', {
      post_id: props.postId,
      user_id: props.userId,
      content: content.value
    })
    emit('comment-submitted', res.data) // send new comment to parent
    content.value = ''
  } catch (err) {
    console.error('Error submitting comment', err)
  }
}
</script>
