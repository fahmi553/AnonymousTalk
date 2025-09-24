import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import '../css/app.css'
import axios from 'axios'

import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'

import PostForm from './Components/anonymous/PostForm.vue'
import PostFeed from './Components/anonymous/PostFeed.vue'
import CommentForm from './Components/anonymous/CommentForm.vue'
import CommentList from './Components/anonymous/CommentList.vue'
import PostDetail from './Components/anonymous/PostDetail.vue'
import LikeButton from './Components/anonymous/LikeButton.vue'

const routes = [
  { path: '/', component: PostFeed },
  { path: '/posts/create', component: PostForm },
  { path: '/posts/:id', component: PostDetail, props: true },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

const app = createApp({})

app.component('comment-form', CommentForm)
app.component('comment-list', CommentList)
app.component('like-button', LikeButton)

axios.defaults.withCredentials = true

app.use(router)
app.mount('#app')
