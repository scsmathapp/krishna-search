<template>
    <div class="paragraph-list flex-fill d-flex flex-column align-items-center"
         :style="`font-size: ${fontSize}%`"
         id="paragraph-list">
        <div v-for="(chapter, chapterId) in chapters" :id="chapterId" class="paragraph-section">
            <div class="paragraph" v-for="(paragraph, paragraphId) in chapter.paragraphs">
                <div v-html="paragraph.highlightedText || paragraph.text" :class="paragraph.class"
                     v-if="showTranslation || !paragraph.translationFlag"
                     :id="chapterId + '-' + paragraphId"></div>
                <i class="fa-solid fa-share-from-square" @click="copyUrl(chapterId, paragraphId)"></i>
            </div>
        </div>
        <div v-if="copySuccess" class="alert alert-success position-fixed bottom-0 fs-6" role="alert">
            Sharing link copied to clipboard!
        </div>
        <div v-if="copyFail" class="alert alert-danger position-fixed bottom-0 fs-6" role="alert">
            Copying link FAILED!
        </div>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';

export default {
    props: {
        bookCode: {
            type: String
        },
        chapters: {
            type: Array
        }
    },
    computed: {
        ...mapGetters(['fontSize', 'showTranslation'])
    },
    data() {
        return {
            copySuccess: false,
            copyFail: false,
            alertTimer: null
        }
    },
    methods: {
        async copyUrl(chapterId, paragraphId) {
            const vm = this;

            try {
                await navigator.clipboard.writeText(window.location.origin + `/#/book/${vm.bookCode}?c=${chapterId}&p=${paragraphId}`);
                vm.triggerAlert(true);
            } catch (e) {
                vm.triggerAlert(false);
            }
        },
        triggerAlert(success) {
            const vm = this;

            // Clear any existing timer to prevent overlapping logic
            if (vm.alertTimer) {
                clearTimeout(this.alertTimer);
            }

            if (success) {
                vm.copySuccess = true;
            } else {
                vm.copyFail = true;
            }

            // Hide the alert after 3000ms (3 seconds)
            vm.alertTimer = setTimeout(() => {
                vm.copySuccess = false;
                vm.copyFail = false;
            }, 3000);
        }
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>
