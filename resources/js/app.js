import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';
import '../css/app.css';

import { createApp } from 'vue';
import PostForm from './Components/anonymous/PostForm.vue';
import PostFeed from './Components/anonymous/PostFeed.vue';
import CommentForm from './Components/anonymous/CommentForm.vue';
import CommentList from './Components/anonymous/CommentList.vue';

const app = createApp({});
app.component('post-form', PostForm);
app.component('post-feed', PostFeed);
app.component('comment-form', CommentForm);
app.component('comment-list', CommentList);

app.mount('#app');
