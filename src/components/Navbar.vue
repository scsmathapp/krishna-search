<template>
    <div class="ks-nav">
        <div :style="'background-image: url(' + require('@/assets/img/KrishnaSearchLogo.png') + ');'"
             class="ks-logo d-none d-lg-block d-xl-block img-bg"></div>
        <NavButtons :isMobile="isMobile"></NavButtons>
        <form @submit="doSearch" class="search-container d-flex justify-content-center align-items-center"
              :class="inputClass">
            <div class="input-group search ks-border d-flex align-items-center">
                <div class="d-flex align-items-center border-0 p-2" v-if="isMobile && showSearchFilter"
                     @click="showSearchFilter = false">
                    <i class="fa fa-arrow-left"></i>
                </div>
                <input type="text" class="form-control border-0" v-model="searchVal" v-if="isInputShown"
                       @click="showSearchFilterClick()">
                <div class="d-flex align-items-center border-0 p-2"
                     v-if="searchVal !== '' && inputClass !== 'shrink'"
                     @click.stop="searchVal = ''">
                    <i class="fa fa-close"></i>
                </div>
                <div class="d-flex align-items-center border-0 p-2"
                     @click="doSearch">
                    <i class="fa fa-search"></i>
                    <span class="filter-badge d-flex justify-content-center align-items-center"
                          v-if="filteredBooks.length">
                        {{ filteredBooks.length }}
                    </span>
                </div>
            </div>
        </form>
        <div class="search-filter" :class="showSearchFilter ? 'show-search-filter' : ''">
            <BookFilter @hideSearchFilter="hideSearchFilter"></BookFilter>
        </div>
    </div>
</template>
<script>
import BookFilter from "./BookFilter.vue";
import {mapGetters} from "vuex";
import NavButtons from "./NavButtons.vue";

export default {
    name: "Navbar",
    components: {NavButtons, BookFilter},
    computed: {
        ...mapGetters(['filteredBooks']),
        isMobile() {
            return this.windowWidth < 991.98;
        },
        isHome() {
            return this.$route.name === "Home";
        },
        isInputShown() {
            return !this.isMobile || (this.isHome && this.isScrollTop) || !this.isShrunk || this.showSearchFilter;
        },
        inputClass() {
            const {isMobile, showSearchFilter, isScrollTop, isHome} = this;

            if (isMobile && showSearchFilter) {
                return 'search-top';
            } else if (isHome && ((!isMobile && isScrollTop) || (isMobile && isScrollTop && !showSearchFilter))) {
                return 'search-center';
            } else if (!isHome || (isMobile && !isScrollTop && !showSearchFilter)) {
                return 'shrink';
            }

            return '';
        }
    },
    data() {
        return {
            windowWidth: window.innerWidth,
            isScrollTop: true,
            isShrunk: true,
            searchVal: '',
            showSearchFilter: false,
            viewElement: {}
        }
    },
    mounted() {
        const vm = this;
        
        vm.viewElement = document.getElementsByClassName('view')[0];

        window.addEventListener('resize', vm.updateWidth);
        vm.viewElement.addEventListener('scroll', vm.handleScroll);
    },
    created() {
        const vm = this,
            storedRoute = localStorage.getItem('route');

        if (vm.$route.path === '/' && storedRoute && vm.$route.path !== storedRoute) {
            vm.$router.push(storedRoute);
        }

        setTimeout(() => {
            vm.searchVal = vm.$route.query && vm.$route.query.q ? vm.$route.query.q : '';
        }, 500);
    },
    beforeDestroy() {
        const vm = this;

        window.removeEventListener('resize', vm.updateWidth);
        vm.viewElement.removeEventListener('scroll', vm.handleScroll);
    },
    methods: {
        doSearch(e) {
            const vm = this,
                bookFilterQuery = vm.filteredBooks.length ? vm.filteredBooks.join('_') : '';

            let url = '/search?q=';

            e.preventDefault();

            if (vm.isMobile && vm.inputClass === 'shrink') {
                vm.$set(vm, 'isShrunk', false);
                vm.showSearchFilterClick();

                setTimeout(() => {
                    document.querySelector('.search input').focus();
                    document.body.addEventListener('click', vm.shrink);
                }, 0);

                return;
            }
            if (vm.searchVal && (vm.$route.query.q !== vm.searchVal || !vm.$route.query.b || vm.$route.query.b !== bookFilterQuery)) {
                url += vm.searchVal.replaceAll('+', '%2B');

                if (bookFilterQuery) {
                    url += `&b=${bookFilterQuery}`;
                }

                vm.hideSearchFilter();
                vm.$router.push(url);
            }
        },
        shrink(e) {
            const vm = this,
                isSearch = e.target.closest('.search-container');

            if (!isSearch) {
                vm.$set(vm, 'isShrunk', true);
                document.body.removeEventListener('click', this.shrink);
            }
        },
        updateWidth() {
            this.windowWidth = window.innerWidth;
        },
        handleScroll() {
            const vm = this;

            if (vm.viewElement.scrollTop > 0 && vm.isScrollTop) {
                vm.isScrollTop = false;
            } else if (vm.viewElement.scrollTop <= 0 && !vm.isScrollTop) {
                vm.isScrollTop = true;
            }
        },
        showSearchFilterClick() {
            const vm = this;

            // Delay attaching the listener so the current click doesn’t trigger it
            setTimeout(() => {
                vm.showSearchFilter = true;
                document.body.addEventListener("click", vm.hideSearchFilter);
            }, 0);
        },
        hideSearchFilter(e) {
            const vm = this,
                isSearchFilter = e && (e.target.closest('.search-filter') || e.target.closest('.search-container'));

            if (!isSearchFilter) {
                vm.showSearchFilter = false;
                document.body.removeEventListener("click", vm.hideSearchFilter);
            }
        }
    },
    watch: {
        '$route'(to, from) {
            localStorage.setItem('route', to.path);
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.ks-nav {
    display: flex;
    align-items: center;

    @include lg {
        box-shadow: black 0 0 10px;
        height: 100px;
        background-color: white;
        position: relative;
    }

    @include sm-md {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        margin-bottom: 20px;
        z-index: 6;
    }
    
    .ks-logo {
        height:100px;
        width:150px;
    }

    // Sidebar for filtering books
    .search-filter {
        min-height: 0;
        max-height: 0;
        transition: all 0.25s ease;
        right: 0;
        top: 0;
        overflow: hidden;
        position: fixed;
        min-width: 340px;
        max-width: 340px;

        @include lg {
            margin-top: 100px;
        }

        &.show-search-filter {
            margin-left: 0;
            background-color: white;
            overflow: hidden;
            z-index: 5;

            @include lg {
                // height of screen - height of nav bar
                min-height: calc(100vh - 100px);
                max-height: calc(100vh - 100px);
                min-width: 340px;
                max-width: 340px;
                box-shadow: black 0 14px 10px;
            }

            @include sm-md {
                // height of screen - height of search
                min-height: calc(100dvh - 50px);
                max-height: calc(100dvh - 50px);
                min-width: 100vw;
                max-width: 100vw;
                box-shadow: black 0 0 10px;
            }

            // X: (Screen width - width of (3 navs + more)) divide by 2 to center
            // Y: Remove 100dvh to bring to the top
            //    then move down by height of nav (50px) + margin bottom of nav (20px) + height of search (50px)
            @include nav-sm-md-3 {
                // X: (100vw - (50px * 4)) / 2
                transform: translate(calc(50vw - 100px), calc(-100dvh + 120px));
            }

            @include nav-sm-md-4 {
                // X: (100vw - (50px * 5)) / 2
                transform: translate(calc(50vw - 125px), calc(-100dvh + 120px));
            }

            @include nav-sm-md-5 {
                // X: (100vw - (50px * 6)) / 2
                transform: translate(calc(50vw - 150px), calc(-100dvh + 120px));
            }
        }
    }

    // Search input + action buttons
    .search-container {
        transition: all 0.25s ease;

        @include lg {
            margin-right: 20px;
        }

        @include sm-md {
            position: fixed;
            bottom: 0;
            right: 0;

            // in sm-md as only needed there
            // When search is shrunk at the bottom
            &.shrink {
                max-width: 50px !important;
                min-width: 50px !important;
                margin-left: 10px;
                position: relative;

                .search {
                    background-color: $primary;
                    max-width: 50px !important;
                    min-width: 50px !important;
                    display: flex;
                    justify-content: center;

                    div {
                        background-color: transparent;
                        color: white;
                    }

                    input {
                        display: none;
                    }
                }
            }
        }

        // When search is active on top
        &.search-top {
            // X: (Screen width - width of (3 navs + more)) divide by 2 to center
            // Y: Remove 100dvh to bring to the top
            //    then move down by height of nav (50px) + margin bottom of nav (20px)
            @include nav-sm-md-3 {
                // X: (100vw - (50px * 4)) / 2
                transform: translate(calc(50vw - 100px), calc(-100dvh + 70px));
            }

            @include nav-sm-md-4 {
                // X: (100vw - (50px * 5)) / 2
                transform: translate(calc(50vw - 125px), calc(-100dvh + 70px));
            }

            @include nav-sm-md-5 {
                // X: (100vw - (50px * 6)) / 2
                transform: translate(calc(50vw - 150px), calc(-100dvh + 70px));
            }

            .input-group {
                border-radius: 0;
                border: none;
                min-width: 100vw;
                max-width: 100vw;
            }
        }

        &.search-center {
            @include lg {
                margin-right: 0;
                // X: It is on the right most, so just center from the right
                // Y: 300px is a randomly chosen number to fit 
                transform: translate(calc(-50vw + 50%), 300px);
            }

            // X: (Search width - height of (3 navs + more)) divide by 2 to center
            // Y: Remove 100dvh to bring to the top, and then move down by the random no px to fit
            @include nav-sm-md-3 {
                // X: (270px - (50px * 4)) / 2
                transform: translate(35px, calc(-100dvh + 260px));
            }

            @include nav-sm-md-4 {
                // X: (270px - (50px * 5)) / 2
                transform: translate(10px, calc(-100dvh + 280px));
            }

            @include nav-sm-md-5 {
                // X: (270px - (50px * 6)) / 2
                transform: translate(-15px, calc(-100dvh + 290px));
            }
        }

        .search {
            background-color: white;
            overflow: hidden;
            transition: all 0.5s ease;

            @include sm-md {
                height: 50px;
                min-width: 270px;
                max-width: 270px;
                box-shadow: black 0 0 10px;
                font-size: 20px;
            }

            @include lg {
                height: 40px;
                min-width: 300px;
                max-width: 300px;
            }

            & > div {
                cursor: pointer;
                background-color: white;
                color: $primary;
            }

            input:focus {
                outline: none;
                box-shadow: none;
            }

            // Number of books selected 
            .filter-badge {
                background-color: #0d6efd;
                border-radius: 7.5px;
                width: 15px;
                height: 15px;
                font-size: 10px;
                color: white;
                position: absolute;
                z-index: 7;

                @include sm-md {
                    margin-left: 10px;
                    margin-top: -20px;
                }

                @include lg {
                    margin-left: 5px;
                    margin-top: -17px;
                }
            }
        }
    }
}
</style>