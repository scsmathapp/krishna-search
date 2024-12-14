import router from './router.js';

// Create Vue app
const app = Vue.createApp({});

// Use the router
app.use(router);

// Mount the app
app.mount('#app');
