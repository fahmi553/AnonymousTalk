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
import NotFound from './Components/anonymous/NotFound.vue'
import AdminLogin from './Components/admin/AdminLogin.vue'
import AdminDashboard from './Components/admin/AdminDashboard.vue'
import '@fortawesome/fontawesome-free/css/all.min.css'
import AppHeader from './Components/AppHeader.vue'
import AppFooter from './Components/AppFooter.vue'
import AdminReportDetail from './Components/admin/AdminReportDetail.vue'
import ReportUserDetails from './Components/admin/AdminUserReportDetail.vue'
import UserList from './Components/admin/UserList.vue';
import SystemLogs from './Components/admin/SystemLogs.vue';
import AppSidebar from './Components/AppSidebar.vue'
import TermsOfService from './Components/pages/TermsOfService.vue';
import PrivacyPolicy from './Components/pages/PrivacyPolicy.vue';
import CommunityGuidelines from './Components/pages/CommunityGuidelines.vue';
import HelpCenter from './Components/pages/HelpCenter.vue';
import DiscussionFeed from './Components/pages/DiscussionFeed.vue'
import CookiePolicy from './Components/pages/CookiePolicy.vue';
import AdminContent from './Components/admin/AdminContent.vue';
import ForgotPassword from './Components/auth/ForgotPassword.vue';
import ResetPassword from './Components/auth/ResetPassword.vue';
import VerificationAlert from './Components/VerificationAlert.vue';
import Login from './Components/auth/Login.vue'
import Register from './Components/auth/Register.vue'

const routes = [
  { path: '/', component: PostFeed },
  { path: '/posts/create', component: PostForm },
  { path: '/posts/:id', component: PostDetail, props: true },
  { path: '/profile', component: ProfileView, name: 'profile.view' },
  { path: '/profile/edit', component: ProfileEdit, name: 'profile.edit' },
  { path: '/profile/:id', component: ProfileView, name: 'profile.visit', props: true },
  { path: '/admin/login', component: AdminLogin, name: 'AdminLogin' },
  { path: '/admin/dashboard', component: AdminDashboard, name: 'AdminDashboard'},
  { path: '/admin/report/:id', component: AdminReportDetail, name: 'AdminReportDetail'},
  { path: '/admin/report/user/:id', component: ReportUserDetails, name: 'ReportUserDetails'},
  { path: '/admin/users', component: UserList, name: 'AdminUserList', meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/admin/logs', component: SystemLogs, name: 'SystemLogs', meta: { requiresAuth: true, requiresAdmin: true }},
  { path: '/terms', component: TermsOfService, name: 'Terms' },
  { path: '/privacy', component: PrivacyPolicy, name: 'Privacy' },
  { path: '/community-guidelines', component: CommunityGuidelines, name: 'CommunityGuidelines' },
  { path: '/help', component: HelpCenter, name: 'HelpCenter' },
  { path: '/cookies', component: CookiePolicy, name: 'CookiePolicy' },
  { path: '/feed', component: DiscussionFeed, name: 'DiscussionFeed' },
  { path: '/forgot-password', name: 'ForgotPassword', component: ForgotPassword, meta: { guest: true } },
  { path: '/reset-password', name: 'ResetPassword', component: ResetPassword, meta: { guest: true } },
  { path: '/admin/content', component: AdminContent, name: 'AdminContent', meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/login', component: Login, name: 'Login' },
  { path: '/register', component: Register, name: 'Register' },
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

axios.interceptors.response.use(
    response => response,
    error => {
        if (window.location.pathname === '/login' || window.location.pathname === '/admin/login') {
            return Promise.reject(error);
        }

        if (error.response) {
            const status = error.response.status;
            const originalRequest = error.config;

            if (status === 419) {
                window.location.href = '/login';
                return Promise.reject(error);
            }

            if (status === 401) {
                if (originalRequest.url.includes('/user') || originalRequest.url.includes('/api/user')) {
                    return Promise.reject(error);
                }

                window.location.href = '/login';
            }

            if (status === 403) {
            }
        }
        return Promise.reject(error);
    }
);

const app = createApp({})
app.use(router)
app.component('comment-form', CommentForm)
app.component('comment-list', CommentList)
app.component('like-button', LikeButton)
app.component('app-sidebar', AppSidebar)
app.component('verification-alert', VerificationAlert);

app.mount('#app')
const headerApp = createApp(AppHeader)
headerApp.use(router)
headerApp.mount('#app-header')
const footerApp = createApp(AppFooter)
footerApp.use(router)
footerApp.mount('#app-footer')
