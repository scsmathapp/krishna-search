<template>
    <div class="slide d-flex justify-content-center align-items-center">
        <!-- App logo -->
        <img src="@/assets/KrishnaSearchLogo.png" height="150" class="d-none d-lg-block d-xl-block">
        <!-- Navigation buttons -->
        <div class="bar d-flex align-items-center">
            <div class="bg" :style="'transform: translateX(' + translatedX + 'px);'"></div>
            <div class="slide-button" v-for="(btn, btnIndex) in btns" :key="btnIndex" :href="btn.href" @click="btnClick(btn)">
                <i :class="btn.iClass"></i>
                <span :class="btn.spanClass" v-if="!isMobile">{{ btn.text }}</span>
            </div>
        </div>
        <!-- Nav menu for more -->
        <div class="position-absolute nav-menu d-flex align-items-center"
             :class="showNavMenu ? 'show-nav-menu' : ''">
            <ul class="list-group list-group-flush flex-fill">
                <a href="/v1.php" class="list-group-item list-group-item-action cursor-pointer">
                    Old version
                </a>
            </ul>
        </div>
        <!-- Search -->
        <form @submit="doSearch" class="search-container d-flex justify-content-center align-items-center"
              :class="isHome && isScrollTop ? 'search-center' : isShrunk ? 'shrink' : ''">
            <div class="input-group search ks-border d-flex align-items-center">
                <input type="text" class="form-control border-0" v-model="searchVal" v-if="isInputShown">
                <div class="d-flex align-items-center border-0 p-2"
                     v-if="searchVal !== '' && isInputShown"
                     @click="searchVal = ''">
                    <i class="fa fa-close"></i>
                </div>
                <div class="d-flex align-items-center border-0 p-2"
                     @click="doSearch">
                    <i class="fa fa-search"></i>
                </div>
            </div>
        </form>
    </div>
</template>
<script>
export default {
    name: "Navbar",
    computed: {
        isMobile() {
            return this.windowWidth < 991.98;
        },
        isHome() {
            return this.$route.name === "Home";
        },
        isInputShown() {
            return !this.isMobile || (this.isHome && this.isScrollTop) || !this.isShrunk;
        }
    },
    data() {
        return {
            translatedX: 0,
            windowWidth: window.innerWidth,
            isScrollTop: true,
            isShrunk: true,
            searchVal: '',
            showNavMenu: false,
            btns: [
                { icon: 'home', text: 'Home', href: '/' },
                { icon: 'flag', text: 'Mission', href: '/mission' },
                { icon: 'calendar', text: 'Calendar', href: '/calendar' },
                { icon: 'ellipsis-h', text: 'More', href: '#', clickAction: this.showNavMenuClick }
            ],
            bgPositions: {
                '/': 0,
                '/mission': 1,
                '/calendar': 2
            }
        }
    },
    created() {
        const vm = this;

        vm.setBg(vm.$route.path);

        if (localStorage.getItem('calendar') && vm.$route.path === '/') {
            vm.$router.push('/calendar');
        }

        setTimeout(() => {
            vm.searchVal = vm.$route.query && vm.$route.query.q ? vm.$route.query.q : '';
        }, 500);

        window.addEventListener('resize', vm.updateWidth);
        window.addEventListener('scroll', this.handleScroll);
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.updateWidth);
        window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
        doSearch(e) {
            const vm = this;


            e.preventDefault();

            if (vm.isShrunk && (!vm.isHome || !vm.isScrollTop)) {
                vm.$set(vm, 'isShrunk', false);

                setTimeout(() => {
                    document.querySelector('.search input').focus();
                    document.body.addEventListener('click', vm.shrink);
                }, 0);

                return;
            }

            if (vm.searchVal && vm.$route.query.q !== vm.searchVal) {
                vm.$router.push('/search?q=' + vm.searchVal.replaceAll('+', '%2B'));
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
        btnClick(btn) {
            const vm = this;

            if (btn.clickAction) {
                btn.clickAction();
                return;
            }

            if (vm.$route.path !== btn.href) {
                vm.$router.push(btn.href);
            }
        },
        setBg(path) {
            const vm = this,
                btnIndex = vm.bgPositions.hasOwnProperty(path) ? vm.bgPositions[path] : 3;

            vm.translatedX = btnIndex * (vm.isMobile ? 50 : 120);

            vm.btns.forEach(function (btn, i) {
                const active = btnIndex === i ? 'active' : '';

                btn.spanClass = active;
                btn.iClass = `${active} fa fa-${btn.icon}`;
            });
        },
        updateWidth() {
            this.windowWidth = window.innerWidth;
        },
        handleScroll() {
            const vm = this;

            if (window.scrollY > 0 && vm.isScrollTop) {
                vm.isScrollTop = false;
            } else if (window.scrollY <= 0 && !vm.isScrollTop) {
                vm.isScrollTop = true;
            }
        },
        showNavMenuClick() {
            const vm = this;

            // Delay attaching the listener so the current click doesn’t trigger it
            setTimeout(() => {
                vm.showNavMenu = true;
                document.body.addEventListener("click", vm.hideNavMenu);
            }, 0);
        },
        hideNavMenu(e) {
            const vm = this,
                isNavMenu = e.target.closest('.nav-menu');

            if (!isNavMenu) {
                vm.showNavMenu = false;
                document.body.removeEventListener("click", vm.hideNavMenu);
            }
        }
    },
    watch: {
        '$route'(to, from) {
            const vm = this;

            if (to.path === '/calendar') {
                localStorage.setItem('calendar', 1);
            }

            if (from.path === '/calendar') {
                localStorage.removeItem('calendar');
            }

            vm.setBg(to.path);
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/css/style.scss';

* {
    box-sizing: border-box;
}

.slide {
    z-index: 3;
    width: 100%;
    height: 100px;

    @include lg {
        top: 0;
        box-shadow: 0 0 10px black;
        background-color: white;
        position: sticky;
    }

    @include sm-md {
        position: fixed;
        bottom: 0;
    }

    .bar {
        @include lg {
            width: 100%;
            top: 0;
            height: 100px;
        }

        @include sm-md {
            width: 200px;
            bottom: 0;
            margin-left: -70px;
            border-radius: 25px;
            height: 50px;
            box-shadow: 0 0 10px black;
            background-color: white;
        }

        .bg {
            background-color: black;
            position: absolute;
            z-index: 0;
            transition: transform 0.3s ease;

            @include lg {
                width: 120px;
                height: 40px;
                border-radius: 20px;
            }

            @include sm-md {
                width: 50px;
                height: 50px;
                border-radius: 25px;
            }
        }

        .slide-button {
            border-radius: 25px;
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1;

            @include lg {
                width: 120px;
                height: 40px;
            }

            @include sm-md {
                width: 50px;
                height: 50px;
            }

            i,
            span {
                color: $theme-color-primary;

                &.active {
                    color: white;
                }
            }
            
            span {
                margin-left: 5px;
            }
        }
    }

    .nav-menu {
        min-width: 0;
        max-width: 0;
        min-height: 0;
        max-height: 0;
        overflow: hidden;
        padding: 0;
        border: none;
        z-index: 7;
        border-radius: 25px;
        transition: all 0.2s ease;

        @include lg {
            left: 510px;
            margin-top: 0;
        }
        
        @include sm-md {
            margin-left: 50px;
            margin-top: -35px;
        }

        &.show-nav-menu {
            margin-left: 0px;
            min-width: 270px;
            max-width: 270px;
            min-height: 85px;
            max-height: 85px;
            background-color: white;
            box-shadow: black 0 0 10px;

            @include lg {
                margin-top: 185px;
            }
        }
    }

    .search-container {
        transition: all 0.5s ease;

        @include sm-md {
            height: 100px;
            position: absolute;
            z-index: 6;
            bottom: 0;

            &.search-center {
                transform: translate(0, calc(-100dvh + 350px));
            }

            &.shrink {
                max-width: 50px !important;
                min-width: 50px !important;
                margin-left: 200px;

                .search {
                    background-color: $theme-color-primary;
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

        @include lg {
            height: 100px;
            margin-right: 20px;
            top: 0;
            z-index: 6;
            position: absolute;
            transform: translate(calc(50vw - 160px), 0);
            
            &.search-center {
                transform: translate(0, 225px);
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
}
</style>