<template>
    <div class="book-controls position-fixed p-0 d-flex flex-column
            justify-content-center align-items-center initial">
        <div class="border-0 show-translation fw-bold"
             :class="showTranslation ? 'active' : ''"
             v-if="pageType === 'kirtans'"
             @click="toggleTranslation()">
            <div class="d-flex align-items-center">
                En
            </div>
            <div class="control-text">Translation</div>
        </div>
        <div class="border-0" v-if="pageType === 'kirtans'" @click="navigateKirtan()">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-arrow-left"></i>
            </div>
            <div class="control-text">Previous</div>
        </div>
        <div class="border-0" v-if="pageType === 'kirtans'" @click="navigateKirtan(true)">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-arrow-right"></i>
            </div>
            <div class="control-text">Next</div>
        </div>
        <div class="border-0" @click="changeFontSize(true)">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-font"></i>
                <i class="fa-solid fa-plus"></i>
            </div>
            <div class="control-text">Inc. font</div>
        </div>
        <div class="border-0" @click="changeFontSize()">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-font"></i>
                <i class="fa-solid fa-minus"></i>
            </div>
            <div class="control-text">Dec. font</div>
        </div>
        <div class="border-0" @click="setMenuDisplay()" v-if="isMobile">
            <div class="d-flex align-items-center">
                <i class="fa-solid fa-angle-left me-1"></i>
                <i class="fa-solid fa-list"></i>
            </div>
            <div class="control-text">{{ pageType }}</div>
        </div>
    </div>
</template>
<script>
import {mapGetters} from "vuex";

export default {
    computed: {
        ...mapGetters(['fontSize', 'showTranslation']),
        isMobile() {
            return this.windowWidth < 991.98;
        }
    },
    props: {
        pageType: {
            type: String
        }
    },
    data() {
        return {
            windowWidth: window.innerWidth
        }
    },
    methods: {
        changeFontSize(add) {
            const vm = this;

            if (add) {
                if (vm.fontSize < 150) {
                    vm.$store.commit('SET_FONT_SIZE', vm.fontSize + 12.5);
                }
            } else {
                if (vm.fontSize > 25) {
                    vm.$store.commit('SET_FONT_SIZE', vm.fontSize - 12.5);
                }
            }
        },
        setMenuDisplay() {
            this.$emit('setMenuDisplay', true);
        },
        navigateKirtan(nextFlag) {
            this.$emit('navigateKirtan', nextFlag);
        },
        toggleTranslation() {
            this.$store.commit('SET_SHOW_TRANSLATION', this.showTranslation ? false : true);
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.book-controls {
    top: 50dvh;
    right: 0;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(5px);
    color: $primary;
    border: none;
    font-size: 24px;
    width: 45px;
    border-radius: 25px;
    box-shadow: black 0 0 10px;
    overflow: hidden;
    cursor: pointer;
    animation: book-controls-animation 4s ease 1 forwards;

    @include sm-md {
        margin-right: 3px;
    }
    
    @include lg {
        margin-right: 20px;
    }
    
    .show-translation {
        &.active {
            background-color: #315891 !important;
            box-shadow: inset rgba(0, 0, 0, 0.7) 0 0 20px;
        }
    }
    
    .control-text {
        word-break: break-word;
        font-size: 9px;
        text-align: center;
        line-height: 10px;
        margin-top: 3px;
        font-weight: normal;
        text-transform: capitalize;
        animation: book-controls-text-animation 4s ease 1 forwards;
    }
    
    .show-translation,
    .fa-solid {
        font-size: 14px;
    }
    
    .fa-plus,
    .fa-minus {
        font-size: 8px;
    }
    
    .fa-angle-left {
        font-size: 10px;
    }

    & > div {
        padding: 10px 0;
        width: 100%;
        height: 45px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        &:active {
            color: $secondary;
            background-color: $primary;
        }
        
        & > div {
            animation: book-controls-div-animation 4s ease 1 forwards;
        }
    }
}

@keyframes book-controls-animation {
    0% {}
    10% {
        width: 150px;
    }
    90% {
        width: 150px;
    }
    100% {}
}

@keyframes book-controls-div-animation {
    0% {}
    10% {
        transform: translate(50px, 2.5px);
    }
    90% {
        transform: translate(50px, 2.5px);
    }
    100% {}
}

@keyframes book-controls-text-animation {
    0% {}
    10% {
        font-size: 16px;
        transform: translateY(-13px);
    }
    90% {
        font-size: 16px;
        transform: translateY(-13px);
    }
    100% {}
}
</style>