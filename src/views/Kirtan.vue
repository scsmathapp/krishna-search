<template>
    <div class="book d-flex flex-fill">
        <div class="paragraph-list flex-fill d-flex flex-column align-items-center"
             :style="`font-size: ${fontSize}%`"
             ref="paragraphList">
            <div class="paragraph-section" v-if="kirtan">
                <div class="paragraph" v-for="paragraph in kirtan.paragraphs">
                    <div v-html="paragraph.text" :class="paragraph.class"
                         v-if="showTranslation || !paragraph.translationFlag"></div>
                </div>
            </div>
        </div>
        <!-- Kirtan actions -->
<!--        <div class="kirtan-actions d-flex">-->
<!--            <div class="px-3 d-flex align-items-center ks-border" @click="navigateKirtan(false)">-->
<!--                <i class="fa fa-arrow-left"></i>-->
<!--            </div>-->
<!--            <div class="ks-border px-3 mx-3">-->
<!--                <div class="form-check form-switch d-flex align-items-center h-100">-->
<!--                    <input class="form-check-input me-3" type="checkbox" id="flexSwitchCheckDefault" v-model="showTranslation">-->
<!--                    <label class="form-check-label" for="flexSwitchCheckDefault">Translations</label>-->
<!--                </div>-->
<!--            </div>-->
<!--            <div class="px-3 d-flex align-items-center ks-border" @click="navigateKirtan(true)">-->
<!--                <i class="fa fa-arrow-right"></i>-->
<!--            </div>-->
<!--        </div>-->
    </div>
</template>
<script>
import contents from '@/assets/kirtan/contents.json';
import {mapGetters} from "vuex";

export default {
    name: "Kirtan",
    computed: {
        ...mapGetters(['fontSize']),
        kirtan() {
            return this.$store.getters.kirtan(this.$route.params.kirtanCode);
        }
    },
    data() {
        return {
            contents,
            showTranslation: true
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.kirtan-actions {
    position: fixed;
    transform: translateX(-50%);

    @include sm-md {
        left: 50%;
        bottom: 90px;
        height: 50px;
    }
    
    @include lg {
        left: 62.5%; // 50% for center 12.5% for half of side bar
        bottom: 20px;
        height: 40px;
    }

    & > div {
        height: 100%;
        cursor: pointer;
    }
    
    & > .ks-border {
        background-color: white;
        box-shadow: 0 0 10px black;
        justify-content: center;
        
        &:first-of-type,
        &:last-of-type {
            color: $secondary;
            background-color: $primary;

            @include sm-md {
                width: 50px;
            }

            @include lg {
                width: 40px;
            }
        }
    }
}

.b60 {
    padding-bottom: 0 !important;
}

.book .paragraph-list .paragraph-section {
    @include sm-md {
        padding-bottom: 60px;
    }
}
</style>