<template>
    <div id="app">
        <Navbar class="d-flex"></Navbar>
        <router-view class="view"></router-view>
    </div>
</template>
<script>
import allBooks from '@/assets/books/allBooks.js';
import Navbar from "./components/Navbar.vue";

export default {
    components: { Navbar },
    created() {
        const vm = this;

        vm.$store.dispatch('loadBooks', allBooks);
    }
}
</script>