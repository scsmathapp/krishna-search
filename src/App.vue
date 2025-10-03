<template>
    <div id="app">
        <nav class="d-none d-md-flex d-lg-flex d-xl-flex shadow position-sticky top-0">
            <div class="d-flex flex-fill align-items-center justify-content-between">
                <a class="d-flex align-items-center justify-content-center w-30" href="/#/">
                    <img src="@/assets/KrishnaSearchLogo.png" height="150">
                </a>
                <div class="w-30 d-flex justify-content-center nav-list">
                    <a class="me-2 ks-button ks-font-secondary" href="/#/mission">Our mission</a>
                    <a class="ks-button ks-font-secondary" href="/v1.php">Old version</a>
                </div>
            </div>
        </nav>
        <nav class="d-flex d-md-none d-lg-none d-xl-none position-fixed bottom-0">
            <div class="d-flex flex-fill align-items-center justify-content-between">
                <a class="d-flex align-items-center justify-content-center w-30 ks-button" href="/#/">
                    <i class="fa fa-home"></i>
                </a>
                <button class="ks-button" @click="showNavMenuClick">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
        </nav>
        <form @submit="doSearch" class="search-container d-flex justify-content-center align-items-center"
            :class="isHome ? 'home-search ' + (!isFixed ? 'search-center' : 'search-down') : ''">
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
        <div v-if="isHome" :style="'background-image: url(' + require('@/assets/KSWelcome.jpg') + ');'"
             class="welcome position-absolute top-0 left-0 d-flex flex-column justify-content-center align-items-center img-bg">
            <h1 class="mb-5 mt-4 ks-font-italic">Welcome to Krishna Search</h1>
            <p class="mt-5 ks-font-italic">Digital Library of Śrī Chaitanya Sāraswat Maṭh</p>
            <p class="ks-font">A dedicated tool for reading and exploring the teachings of devotion<br class="d-none d-md-flex d-lg-flex d-xl-flex" />
                in the tradition of Śrīla Bhakti Rakṣak Śrīdhar Dev-Goswāmī Mahārāj<br class="d-none d-md-flex d-lg-flex d-xl-flex" />
                and Śrīla Bhakti Sundar Govinda Dev-Goswāmī Mahārāj.</p>
        </div>
        <!-- Nav menu for mob -->
        <div class="position-fixed nav-menu d-flex align-items-center"
             :class="showNavMenu ? 'show-nav-menu' : ''">
            <ul class="list-group list-group-flush flex-fill">
                <a href="/#/mission" class="list-group-item list-group-item-action cursor-pointer">
                    Mission
                </a>
                <a href="/v1.php" class="list-group-item list-group-item-action cursor-pointer">
                    Old version
                </a>
            </ul>
        </div>
        <router-view class="view"></router-view>
    </div>
</template>
<script>
import allBooks from '@/assets/books/allBooks.js';

export default {
    data() {
        return {
            searchInNavbar: false,
            searchVal: '',
            isFixed: false,
            showNavMenu: false
        }
    },
    computed: {
        isHome() {
            return this.$route.name === "Home"; // adjust to your route name
        },
    },
    mounted() {
        window.addEventListener('scroll', this.handleScroll);
    },
    beforeDestroy() {
        window.removeEventListener('scroll', this.handleScroll);
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
        },
        showNavMenuClick() {
            const vm = this;

            function hideNavMenu() {
                vm.showNavMenu = false;
                document.body.removeEventListener("click", hideNavMenu);
            }

            // Delay attaching the listener so the current click doesn’t trigger it
            setTimeout(() => {
                vm.showNavMenu = true;
                document.body.addEventListener("click", hideNavMenu);
            }, 0);
        },
        handleScroll() {
            const vm = this;

            if (window.scrollY > 0 && !vm.isFixed) {
                vm.isFixed = true;
            } else if (window.scrollY <= 0 && vm.isFixed) {
                vm.isFixed = false;
            }
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/css/style.scss';

nav {
    z-index: 5;
    height: 100px;
    
    @include md-lg {
        background-color: $theme-color-secondary;
    }
    
    @include sm {
        width: 100vw;
        
        .ks-button {
            margin: 0 25px;
            max-width: 40px;
        }
    }
}


.nav-menu {
    margin: 50px 45px;
    min-width: 0;
    max-width: 0;
    min-height: 0;
    max-height: 0;
    overflow: hidden;
    padding: 0;
    border: none;
    right: 0;
    bottom: 0;
    z-index: 7;
    border-radius: 20px;
    transition: all 0.2s ease;
    
    &.show-nav-menu {
        margin: 30px 25px;
        min-width: 200px;
        max-width: 200px;
        min-height: 85px;
        max-height: 85px;
        background-color: white;
        box-shadow: grey 0 0 10px;
        right: 0;
        bottom: 0;
        z-index: 7;
    }
}

.search-container {
    @include sm {
        height: 100px;
        position: fixed;
        margin-left: 50vw;
        z-index: 6;
        transition: all 0.5s ease;
        bottom: 0;

        &.search-center {
            transform: translate(0, calc(-100dvh + 320px));
        }

        &.search-down {
            transform: translate(0, 0);
        }
    }
    
    @include md-lg {
        height: 100px;
        margin-left: 50vw;
        transform: translateX(-50%);
        top: 0;
        z-index: 6;
        position: fixed;

        &.home-search {
            position: sticky;
            margin-top: 125px;
        }
    }
    
    .search {
        height: 40px;
        background-color: white;
        overflow: hidden;

        @include sm {
            transform: translateX(-50%);
            min-width: 60vw;
            max-width: 60vw;
        }

        @include md-lg {
            min-width: 25vw;
            max-width: 25vw;
        }
    
        & > div {
            height: 37.6px;
            cursor: pointer;
            background-color: white;
        }
    
        input:focus {
            outline: none;
            box-shadow: none;
        }
    }
}

.welcome {
    height: 650px;
    background-position-y: top !important;
    text-align: center;

    @include md-lg {
        width: 99vw;
    }

    @include sm {
        width: 100vw;
    }
    
    h1 {
        @include md-lg {
            font-size: 35px;
        }

        @include sm {
            font-size: 30px;
        }
    }

    &>p:first-of-type {
        @include md-lg {
            font-size: 16px;
        }

        @include sm {
            font-size: 14px;
        }
    }

    &>p:last-child {
        padding: 0 35px;

        @include md-lg {
            font-size: 18px;
        }

        @include sm {
            font-size: 16px;
        }
    }
}
</style>