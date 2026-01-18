import Vue from 'vue'
import VueRouter from 'vue-router'
import Home from '../views/Home.vue'
import Book from '../views/Book.vue'
import Search from '../views/Search.vue'
import Mission from '../views/Mission.vue'
import Versions from '../views/Versions.vue'
import Calendar from '../views/Calendar.vue'
import KirtanList from '../views/KirtanList.vue'
import Kirtan from '../views/Kirtan.vue'
import Test from '../views/Test.vue'

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
}, {
    path: '/versions',
    name: 'Versions',
    component: Versions
}, {
    path: '/calendar',
    name: 'Calendar',
    component: Calendar
}, {
    path: '/kirtan',
    name: 'KirtanList',
    component: KirtanList,
    children: [{
        path: ':kirtanCode',
        name: 'Kirtan',
        component: Kirtan
    }]
}, {
    path: '/test',
    name: 'Test',
    component: Test
}];

const router = new VueRouter({
    routes
});

export default router;