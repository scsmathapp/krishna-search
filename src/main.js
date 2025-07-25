import Vue from 'vue';
import App from './App.vue';
import router from './router';
import store from './store';
import VueResource from 'vue-resource';

import '@/assets/books/style/book-text.scss';
import '@/assets/books/style/font.scss';
import '@/assets/books/style/main.scss';

import './registerServiceWorker';
import '@/assets/bootstrap/js/bootstrap.bundle.js';
import '@/assets/bootstrap/css/bootstrap.min.css';
import '@/assets/fa/free.min.css';

Vue.use(VueResource);

Vue.config.productionTip = false;

new Vue({
	router,
	store,
	render: h => h(App)
}).$mount('#app');