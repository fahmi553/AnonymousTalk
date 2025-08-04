import { ref, onMounted } from 'vue'
import PostService from '../services/PostService'

export function usePosts() {
  const posts = ref([])
  const loading = ref(false)
  const error = ref(null)

  const loadPosts = async () => {
    loading.value = true
    error.value = null
    try {
      const response = await PostService.fetchPosts()
      posts.value = response.data
    } catch (err) {
      error.value = 'Failed to load posts.'
      console.error(err)
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    loadPosts()
    window.addEventListener('post-created', loadPosts)
  })

  return { posts, loading, error, loadPosts }
}
