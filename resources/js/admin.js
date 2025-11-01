import './bootstrap';
import { createApp } from 'vue';

import AdminHeader from './Components/admin/AdminHeader.vue';
import AdminFooter from './Components/admin/AdminFooter.vue';

const adminHeaderApp = createApp(AdminHeader);
adminHeaderApp.mount('#admin-header');

const adminFooterApp = createApp(AdminFooter);
adminFooterApp.mount('#admin-footer');
