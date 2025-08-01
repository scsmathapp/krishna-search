<template>
    <div id="app">
        <nav class="navbar navbar-expand-lg bg-body-secondary fixed-top">
            <div class="d-flex flex-fill align-items-center px-5">
                <a class="d-flex align-items-center justify-content-center w-20" href="/#/">
                    <img src="@/assets/KrishnaSearchLogo.png" height="150" >
                </a>
                <form @submit="doSearch" class="d-flex flex-fill justify-content-center">
                    <div class="input-group search ks-border">
                        <input type="text" class="form-control border-0" v-model="searchVal">
                        <div class="d-flex align-items-center border-0 p-2"
                             v-if="searchVal !== ''"
                             @click="searchVal = ''">
                            <i class="fa fa-close"></i>
                        </div>
                        <div class="d-flex align-items-center border-0 p-2"
                             @click="doSearch">
                            <i class="fa fa-search"></i>
                        </div>
                    </div>
                </form>
                <div class="w-20 d-flex justify-content-center nav-list">
                    <a class="me-2" href="/#/mission">Our mission</a>
                    <a href="/v1.php">Old version</a>
                </div>
            </div>
        </nav>
        <router-view class="view"></router-view>
    </div>
</template>
<script>
import allBooks from '@/assets/books/allBooks.js';

export default {
    data() {
        return {
            searchVal: ''
        }
    },
    created() {
        const vm = this;

        vm.$store.dispatch('loadBooks', allBooks);
        
        setTimeout(() => {
            vm.searchVal = vm.$route.query && vm.$route.query.q ? vm.$route.query.q : '';
        }, 500);
    },
    methods: {
        doSearch(e) {
            const vm = this;

            e.preventDefault();

            if (vm.searchVal && vm.$route.query.q !== vm.searchVal) {
                vm.$router.push('/search?q=' + vm.searchVal.replaceAll('+', '%2B'));
            }
        }
    }
}
</script>
<style lang="scss">
@import '@/assets/css/style.scss';

@each $breakpoint in $breakpoints {
    $point: nth($breakpoint, 1);
    $min: nth($breakpoint, 2);
    $max: nth($breakpoint, 3);

    @media screen and (min-width: $min) and (max-width: $max) {
        @for $i from 1 through 20 {
            $width: percentage($i*5/100);
    
            .w#{$point}-#{$i*5} {
                width: $width !important;
            }
        }
    }
}

.paragraph {
    padding: 10px;
}

.paragraph-title-list {
    min-width: 300px;
    overflow-y: auto;
    height: calc(100vh - 100px);
    box-shadow: grey 0 0 10px;
}

.paragraph-title-list .position-sticky {
    background-color: white;
    box-shadow: grey 0 0 10px;
}

.paragraph-list {
    overflow-y: auto;
    height: calc(100vh - 110px);
}

.book-card {
    max-width: 150px;
    min-width: 150px;
    text-decoration: none !important;
    cursor: pointer;
    color: $theme-color-primary;
}

.book-card .card-body {
    padding: 10px !important;
}

.book-card .card-title {
    margin: 0;
}

.book-card .img-bg {
    min-height: 200px;
    max-height: 200px;
    min-width: 148px;
    max-width: 148px;
}

.book-card:hover .img-bg {
    box-shadow: grey 0 0 20px;
}

.img-bg {
    background-repeat: no-repeat;
    background-size: cover;
    background-position-x: center;
}

.list-group-item {
    cursor: pointer;
}

.paragraph-section {
    box-shadow: grey 0 0 10px;
    margin: 20px 50px;
    padding: 50px;
    max-width: 800px;
}

body *:not(.book *):not(.fa) {
    font-family: 'Atlassian Sans', sans-serif;
}

.view {
    margin-top: 100px;
}

.search {
    min-width: 25vw;
    max-width: 25vw;
    height: 40px;
    background-color: white;
    overflow: hidden;
}

.ks-border {
    border-radius: 20px;
    border: 2px solid $theme-color-primary;
}

.search > div {
    height: 37.6px;
    cursor: pointer;
    background-color: white;
}

.search input:focus {
    outline: none;
    box-shadow: none;
}

.navbar {
    height: 100px;
}

button, .nav-list a {
    color: white;
    background-color: $theme-color-primary;
    overflow: hidden;
    border-width: 0;
    border-radius: 20px !important;
    height: 40px;
    width: 150px;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.navbar-brand {
    left: 3rem;
}

.vaishnava-book {
    color: $theme-color-primary;
}

.vaishnava-book ::-webkit-scrollbar {
    width: 15px;
    height: 15px;
    background: $theme-color-secondary;
    border-radius: 7.5px;
}

.vaishnava-book ::-webkit-scrollbar-thumb {
    background: $theme-color-primary;
    border-radius: 7.5px;
}

.list-group-item.active {
    background-color: $theme-color-primary !important;
}

.vaishnava-book ::-webkit-scrollbar-thumb:active {
    background: grey;
}

.color-default {
    color: $theme-color-primary;
}

.welcome {
    max-width: 1200px;
}

.welcome>div:last-of-type {
    text-align: center;
}

.welcome>div>p:first-of-type {
    font-style: italic;
    margin-bottom: 5px;
    margin-top: -10px;
}

.welcome>div>p:last-child {
    font-size: 22px;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 5px solid $theme-color-secondary;
    border-top-color: $theme-color-primary; /* Blue top */
    border-radius: 50%;
    animation: spin 0.5s linear infinite;
    margin: 50px auto; /* Center horizontally */
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.cursor-pointer {
    cursor: pointer;
}
</style>