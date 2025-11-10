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
// import Header from './Components/anonymous/Header.vue'
import NotFound from './Components/anonymous/NotFound.vue'
// import ThemeToggle from "./Components/anonymous/ThemeToggle.vue"
import AdminLogin from './Components/admin/AdminLogin.vue'
import AdminDashboard from './Components/admin/AdminDashboard.vue'
import '@fortawesome/fontawesome-free/css/all.min.css'
import AppHeader from './Components/AppHeader.vue'
import AppFooter from './Components/AppFooter.vue'


const routes = [
  { path: '/', component: PostFeed },
  { path: '/posts/create', component: PostForm },
  { path: '/posts/:id', component: PostDetail, props: true },
  { path: '/profile', component: ProfileView, name: 'profile.view' },
  { path: '/profile/edit', component: ProfileEdit, name: 'profile.edit' },
  { path: '/profile/:id', component: ProfileView, name: 'profile.visit', props: true },
  { path: '/admin/login', component: AdminLogin, name: 'AdminLogin' },
  {
    path: '/admin/dashboard',
    component: AdminDashboard,
    name: 'AdminDashboard'
  },

  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
if (token) {
  axios.defaults.headers.common['X-CSRF-TOKEN'] = token
}

const app = createApp({})
app.use(router)
app.component('comment-form', CommentForm)
app.component('comment-list', CommentList)
app.component('like-button', LikeButton)
app.mount('#app') // This mounts all your pages

const headerApp = createApp(AppHeader)
headerApp.use(router)
headerApp.mount('#app-header')

const footerApp = createApp(AppFooter)
footerApp.use(router)
footerApp.mount('#app-footer')
