import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Book from '../views/Book.vue'
import Search from '../views/Search.vue'
import Mission from '../views/Mission.vue'

Vue.use(VueRouter)

const routes = [{
    path: '/',
    name: 'Home',
    component: Home
}, {
    path: '/book/:code',
    name: 'Book',
    component: Book
}, {
    path: '/search',
    name: 'Search',
    component: Search
}, {
    path: '/mission',
    name: 'Mission',
    component: Mission
}];

const router = new VueRouter({
    routes
});

export default router;