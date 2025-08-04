import axios from 'axios'

export default {
  async fetchPosts() {
    return axios.get('/api/posts')
  },

  async createPost(content) {
    return axios.post('/api/posts', { content })
  }
}
