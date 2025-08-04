import './bootstrap';
import { createApp } from 'vue';
import PostForm from './Components/anonymous/PostForm.vue';
import PostFeed from './Components/anonymous/PostFeed.vue';

const app = createApp({});
app.component('post-form', PostForm);
app.component('post-feed', PostFeed);
app.mount('#app');
