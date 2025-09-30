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
import ProfileView from './Components/anonymous/ProfileView.vue'
import ProfileEdit from './Components/anonymous/ProfileEdit.vue'
import Header from './Components/anonymous/Header.vue'

const routes = [
  { path: '/', component: PostFeed },
  { path: '/posts/create', component: PostForm },
  { path: '/posts/:id', component: PostDetail, props: true },
  { path: '/profile', component: ProfileView, name: 'profile.view' },
  { path: '/profile/edit', component: ProfileEdit, name: 'profile.edit' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

const app = createApp({})
app.use(router)

app.component('comment-form', CommentForm)
app.component('comment-list', CommentList)
app.component('like-button', LikeButton)

app.mount('#app')
const headerApp = createApp(Header)
headerApp.use(router)
headerApp.mount('#vue-header')
