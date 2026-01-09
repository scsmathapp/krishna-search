<template>
    <div id="app">
        <Navbar class="d-flex"></Navbar>
        <router-view class="view"></router-view>
    </div>
</template>
<script>
import Navbar from "./components/Navbar.vue";

export default {
    components: { Navbar },
    mounted() {
        this.$store.dispatch('loadBooks');
    }
}
</script>