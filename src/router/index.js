import Vue from 'vue'
import VueRouter from 'vue-router'

Vue.use(VueRouter)

const routes = [{
    path: '/',
    name: 'Home',
    component: () => import('@/views/Home.vue')
}, {
    path: '/book/:code',
    name: 'Book',
    component: () => import('@/views/Book.vue')
}, {
    path: '/search',
    name: 'Search',
    component: () => import('@/views/Search.vue')
}, {
    path: '/mission',
    name: 'Mission',
    component: () => import('@/views/Mission.vue')
}, {
    path: '/versions',
    name: 'Versions',
    component: () => import('@/views/Versions.vue')
}, {
    path: '/calendar',
    name: 'Calendar',
    component: () => import('@/views/Calendar.vue')
}, {
    path: '/kirtan',
    name: 'KirtanList',
    component: () => import('@/views/KirtanList.vue'),
    redirect: '/kirtan/vande-ham-shri-guroh-shri-yuta-pada-kamalam',
    children: [{
        path: ':kirtanCode',
        name: 'Kirtan',
        component: () => import('@/views/Kirtan.vue')
    }]
}, {
    path: '/chat',
    name: 'Chat',
    component: () => import('@/views/Chat.vue')
}, {
    path: '/favourites',
    name: 'Favourites',
    component: () => import('@/views/Favourites.vue')
}, {
    path: '/test',
    name: 'Test',
    component: () => import('@/views/Test.vue')
}];

const router = new VueRouter({
    routes
});

export default router;