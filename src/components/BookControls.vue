<template>
    <div class="book-controls position-fixed p-0 d-flex flex-column
            justify-content-center align-items-center" style="font-size: 17px;">
        <div class="d-flex justify-content-center align-items-center border-0 show-translation fw-bold"
             :class="showTranslation ? 'active' : ''"
             v-if="kirtanFlag"
             @click="toggleTranslation()">
            En
        </div>
        <div class="d-flex justify-content-center align-items-center border-0" v-if="kirtanFlag" @click="navigateKirtan()">
            <i class="fa-solid fa-arrow-left"></i>
        </div>
        <div class="d-flex justify-content-center align-items-center border-0" v-if="kirtanFlag" @click="navigateKirtan(true)">
            <i class="fa-solid fa-arrow-right"></i>
        </div>
        <div class="d-flex justify-content-center align-items-center border-0" @click="changeFontSize(true)">
            <i class="fa-solid fa-font"></i>
            <i class="fa-solid fa-plus"></i>
        </div>
        <div class="d-flex justify-content-center align-items-center border-0" @click="changeFontSize()">
            <i class="fa-solid fa-font"></i>
            <i class="fa-solid fa-minus"></i>
        </div>
        <div class="d-flex justify-content-center align-items-center border-0" @click="setMenuDisplay()" v-if="isMobile">
            <i class="fa-solid fa-angles-left"></i>
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
        kirtanFlag: {
            type: Boolean
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
                if (vm.fontSize < 125) {
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
    background-color: white;
    color: $primary;
    border: none;
    font-size: 24px;
    width: 35px;
    border-radius: 25px;
    box-shadow: black 0 0 10px;
    overflow: hidden;
    
    @include sm-md {
        margin-right: 3px;
    }
    
    @include lg {
        margin-right: 20px;
    }
    
    .show-translation,
    .fa-solid {
        font-size: 14px;
    }
    
    .fa-plus,
    .fa-minus {
        font-size: 8px;
    }

    & > div {
        padding: 10px 0;
        width: 100%;
        border-radius: 17.5px;
        height: 35px;

        &:active {
            color: $secondary;
            background-color: $primary;
        }
    }
}
</style>