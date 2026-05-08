<template>
    <div id="app">
        <Navbar></Navbar>
        <div class="view">
            <router-view></router-view>
        </div>
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
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.view {
    overflow-y: auto;
    
    @include lg {
        height: calc(100vh - 100px);
    }
    
    @include sm-md {
        height: 100dvh;
    }
}
</style>