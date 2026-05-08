<template>
    <div class="bar d-flex align-items-center flex-fill">
        <div class="bg" :class="bgPos"></div>
        <div class="d-flex align-items-center flex-fill" id="btns">
            <a :href="btn.href" class="slide-button"
               :class="(btn.activeFlag ? 'active-nav' : '') + (showNavMenu ? ' selected' : '')"
               v-for="(btn, btnIndex) in btns" :key="btnIndex" @click="btnClick(btn)">
                <i :class="btn.class"></i>
                <span class="d-none d-lg-flex d-xl-flex">{{ btn.name }}</span>
            </a>
        </div>
        <div class="position-absolute nav-menu d-flex align-items-center"
             :class="showNavMenu ? 'show-nav-menu' : ''">
            <ul class="list-group list-group-flush flex-fill">
                <a :href="btn.href" class="list-group-item list-group-item-action"
                   v-for="(btn, btnIndex) in btns" :key="btnIndex" @click="showNavMenu = false;">
                    <i :class="btn.class"></i>
                    <span>{{ btn.name }}</span>
                </a>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
    name: "NavButtons",
    props: {
        isMobile: {
            type: Boolean
        }
    },
    data() {
        return {
            translatedX: 0,
            showNavMenu: false,
            bgPos: 'pos-0',
            btns: [
                {name: 'Home', href: '/#/', class: 'img-bg nav-btn-img home', activeFlag: true},
                {name: 'Kirtan', href: '/#/kirtan', class: 'img-bg nav-btn-img kirtan'},
                {name: 'Calendar', href: '/#/calendar', class: 'fa fa-calendar'},
                {name: 'AI Chat', href: '/#/chat', class: 'fa fa-comment-dots'},
                {name: 'Mission', href: '/#/mission', class: 'fa fa-flag'},
                {name: 'What\'s new?', href: '/#/versions', class: 'fa fa-bell'},
                {name: 'Old version', href: '/v1.php', class: 'fa fa-history'},
                {name: 'More', href: null, class: 'fa fa-ellipsis-h', clickAction: this.showNavMenuClick}
            ]
        }
    },
    created() {
        const vm = this;

        setTimeout(() => {
            vm.setBg(vm.$route.path);
        }, 1000);
    },
    methods: {
        btnClick(btn) {
            if (btn.clickAction) {
                btn.clickAction();
            }
        },
        setBg(path) {
            const vm = this,
                splitPath = path.split('/'),
                btnsEl = document.getElementById('btns'),
                lastBtnIndex = Array.from(btnsEl.children).filter(child => child.checkVisibility()).length - 1;
            
            let activatedFlag = false;

            vm.btns.forEach(function (btn, i) {
                // If splitPath is not an array
                // Or if it is and url doesn't match with btn url
                // Or if it matches, but btn idx is higher than visible nav indexes
                // Then set activeFlag false and move to next iteration
                if (!splitPath.length || btn.href !== '/#/' + splitPath[1] || i >= lastBtnIndex) {
                    vm.$set(btn, 'activeFlag', false);
                    return;
                }

                vm.$set(btn, 'activeFlag', true);
                vm.bgPos = 'pos-' + i;
                activatedFlag = true;
            });

            if (!activatedFlag) {
                // Either the nav option is in "More" or is some other nav (like "book" or "search")
                vm.$set(vm.btns[vm.btns.length - 1], 'activeFlag', true);
                vm.bgPos = 'pos-' + lastBtnIndex;
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
        '$route'(to) {
            const vm = this;

            vm.setBg(to.path);
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.bar {
    @include sm-md {
        border-radius: 25px;
        height: 50px;
        box-shadow: 0 0 10px black;
        background-color: white;
    }

    .bg {
        background-color: $primary;
        position: absolute;
        z-index: 0;
        transition: transform 0.3s ease;

        @include lg {
            width: 130px;
            height: 40px;
            border-radius: 20px;
        }

        @include sm-md {
            width: 50px;
            height: 50px;
            border-radius: 25px;
        }

        @for $i from 0 through 8 {
            &.pos-#{$i} {
                @include lg {
                    transform: translateX(#{$i*130}px);
                }

                @include sm-md {
                    transform: translateX(#{$i*50}px);
                }
            }
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
            width: 130px;
            height: 40px;
        }

        @include sm-md {
            width: 50px;
            height: 50px;
        }
        
        &.selected:has(.fa-ellipsis-h) {
            background-color: $primary-pale;
        }
        
        .nav-btn-img {
            height: 25px;
            width: 25px;
        }

        .home {
            background-image: url(~@/assets/icon/home.png);
        }
        
        .kirtan {
            background-image: url(~@/assets/icon/kirtan.png);
        }

        i, span {
            color: $primary;
        }

        &.active-nav {
            i, span {
                color: white;
            }

            .home {
                background-image: url(~@/assets/icon/home-s.png);
            }

            .kirtan {
                background-image: url(~@/assets/icon/kirtan-s.png);
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
    
    span {
        margin-left: 10px;
    }
    
    @include lg {
        top: -110px;
    }

    @include sm-md {
        margin-left: 50px;
    }

    &.show-nav-menu {
        margin-left: 0px;
        background-color: white;
        box-shadow: black 0 0 10px;

        @include lg {
            min-width: 270px;
            max-width: 270px;
            margin-top: 185px;
        }
    }

    .list-group-item {
        color: $primary;
    }
}

// Mobile + Tablet
@include nav-sm-md-3 {
    .slide-button:has(.fa-comment-dots, .fa-flag, .fa-bell, .fa-history) {
        display: none !important;
    }

    .nav-menu {
        // -163.2px [height of nav menu] + 50px [height of navbar]
        margin-top: -113.2px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan, .fa-calendar) {
            display: none !important;
        }
    }

    .show-nav-menu {
        // Height of list group item (40.8px) * 4 (hidden navs)
        min-height: 163.2px;
        max-height: 163.2px;
        // ((3 navs + more) * 50px) + 10px margin + 50px search
        min-width: 260px;
        max-width: 260px;
    }
}

@include nav-sm-md-4 {
    .slide-button:has(.fa-flag, .fa-bell, .fa-history) {
        display: none !important;
    }

    .nav-menu {
        // -122.4px [height of nav menu] + 50px [height of navbar]
        margin-top: -72.4px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan, .fa-calendar, .fa-comment-dots) {
            display: none !important;
        }
    }

    .show-nav-menu {
        // Height of list group item (40.8px) * 3 (hidden navs)
        min-height: 122.4px;
        max-height: 122.4px;
        // ((4 navs + more) * 50px) + 10px margin + 50px search
        min-width: 310px;
        max-width: 310px;
    }
}

@include nav-sm-md-5 {
    .slide-button:has(.fa-bell, .fa-history) {
        display: none !important;
    }

    .nav-menu {
        // -81.6px [height of nav menu] + 50px [height of navbar]
        margin-top: -31.6px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan, .fa-calendar, .fa-comment-dots, .fa-flag) {
            display: none !important;
        }
    }

    .show-nav-menu {
        // Height of list group item (40.8px) * 2 (hidden navs)
        min-height: 81.6px;
        max-height: 81.6px;
        // ((5 navs + more) * 50px) + 10px margin + 50px search
        min-width: 360px;
        max-width: 360px;
    }
}

// Desktop
@include nav-lg-2 {
    .slide-button:has(.fa-calendar, .fa-comment-dots, .fa-flag, .fa-bell, .fa-history) {
        display: none !important;
    }
    
    .nav-menu {
        // 150px [width of logo] + (2 * 130px) [width of 2 navs]
        left: 410px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan) {
            display: none !important;
        }
    }
    
    .show-nav-menu {
        min-height: 204px;
        max-height: 204px;
    }
}

@include nav-lg-3 {
    .slide-button:has(.fa-comment-dots, .fa-flag, .fa-bell, .fa-history) {
        display: none !important;
    }

    .nav-menu {
        // 150px [width of logo] + (3 * 130px) [width of 3 navs]
        left: 540px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan, .fa-calendar) {
            display: none !important;
        }
    }

    .show-nav-menu {
        min-height: 163.2px;
        max-height: 163.2px;
    }
}

@include nav-lg-4 {
    .slide-button:has(.fa-flag, .fa-bell, .fa-history) {
        display: none !important;
    }

    .nav-menu {
        // 150px [width of logo] + (4 * 130px) [width of 4 navs]
        left: 670px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan, .fa-calendar, .fa-comment-dots) {
            display: none !important;
        }
    }

    .show-nav-menu {
        min-height: 122.4px;
        max-height: 122.4px;
    }
}

@include nav-lg-5 {
    // 150px [width of logo] + (5 * 130px) [width of 5 navs]
    .slide-button:has(.fa-bell, .fa-history) {
        display: none !important;
    }

    .nav-menu {
        left: 800px;

        .list-group-item:has(.fa-ellipsis-h, .home, .kirtan, .fa-calendar, .fa-comment-dots, .fa-flag) {
            display: none !important;
        }
    }

    .show-nav-menu {
        min-height: 81.6px;
        max-height: 81.6px;
    }
}
</style>