import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'
import '../css/app.css'
import '../css/style.css'
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
import VerificationAlert from './Components/VerificationAlert.vue';
import { useAuth } from './store/auth';

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
  { path: '/admin/content', component: AdminContent, name: 'AdminContent', meta: { requiresAuth: true, requiresAdmin: true } },
  { path: '/:pathMatch(.*)*', name: 'NotFound', component: NotFound },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.baseURL = '/';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

axios.interceptors.response.use(
    response => response,
    error => {
        const currentPath = window.location.pathname;
        if (currentPath === '/login' || currentPath === '/admin/login') {
            return Promise.reject(error);
        }

        if (error.response) {
            const status = error.response.status;
            if ((status === 419 || status === 401)) {
                localStorage.removeItem('isLoggedIn');
                window.location.href = '/login';
                return Promise.reject(error);
            }
            if (status === 403 && error.response.data.banned) {
                alert(error.response.data.message);
                localStorage.removeItem('isLoggedIn');
                window.location.href = '/login';
                return Promise.reject(error);
            }
        }
        return Promise.reject(error);
    }
);
const app = createApp({})
const { fetchUser } = useAuth();

app.use(router)
app.component('comment-form', CommentForm)
app.component('comment-list', CommentList)
app.component('like-button', LikeButton)
app.component('app-sidebar', AppSidebar)
app.component('verification-alert', VerificationAlert);

function mountApps() {
    if (!app._container) {
        app.mount('#app');
        createApp(AppHeader).use(router).mount('#app-header');
        createApp(AppFooter).use(router).mount('#app-footer');
    }
}

axios.get('/sanctum/csrf-cookie').then(() => {
    fetchUser()
        .then(() => mountApps())
        .catch(() => mountApps());
}).catch((err) => {
    console.error("CSRF Handshake failed. Check CORS settings.", err);
    mountApps();
});
