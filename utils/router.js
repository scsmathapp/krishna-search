import Home from '../pages/Home.js';
import About from '../pages/About.js';

// Define routes
const routes = [
    {path: '/', component: Home},
    {path: '/about', component: About},
];

// Create Vue Router instance
const router = VueRouter.createRouter({
    history: VueRouter.createWebHashHistory(),
    routes,
});

export default router
