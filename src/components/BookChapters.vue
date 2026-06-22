<template>
    <div class="paragraph-list flex-fill d-flex flex-column align-items-center"
         :style="`font-size: ${fontSize}%`"
         id="paragraph-list">
        <div v-for="(chapter, chapterId) in chapters" :id="chapterId" class="paragraph-section">
            <div class="paragraph" v-for="(paragraph, paragraphId) in chapter.paragraphs">
                <div v-html="paragraph.highlightedText || paragraph.text" :class="paragraph.class"
                     v-if="showTranslation || !paragraph.translationFlag"
                     :id="chapterId + '-' + paragraphId"></div>
                <div class="paragraph-action">
                    <i class="fa-solid fa-share-from-square" @click="copyUrl(chapterId, paragraphId)"></i>
                    <i class="fa-solid fa-heart-circle-plus"
                       v-if="!favBooks[`${bookIndex}-${chapterId}-${paragraphId}`]"
                       @click="addToFav(chapterId, paragraphId)"></i>
                    <i class="fa-solid fa-heart-circle-minus" v-else
                       @click="removeFav(chapterId, paragraphId)"></i>
                </div>
            </div>
        </div>
        <div v-if="msg.text" class="alert position-fixed fs-6" :class="msg.class" role="alert">
            {{ msg.text }}
        </div>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';

export default {
    props: ['bookCode', 'chapters'],
    computed: {
        ...mapGetters(['fontSize', 'showTranslation']),
        bookIndex() {
            return this.$store.getters.bookIndex(this.bookCode);
        }
    },
    data() {
        return {
            alertTimer: null,
            msg: {},
            favBooks: {}
        }
    },
    created() {
        this.setFavBooks();
    },
    methods: {
        setFavBooks() {
            const vm = this;

            try {
                const localFav = localStorage.fav;

                if (localFav) {
                    vm.favBooks = JSON.parse(localFav);
                }
            } catch (e) {}
        },
        async copyUrl(chapterId, paragraphId) {
            const vm = this;

            try {
                await navigator.clipboard.writeText(window.location.origin + `/#/book/${vm.bookCode}?c=${chapterId}&p=${paragraphId}`);
                vm.triggerAlert('goodShare');
            } catch (e) {
                vm.triggerAlert('badShare');
            }
        },
        addToFav(chapterId, paragraphId) {
            const vm = this;

            vm.setFavBooks();

            if (vm.bookIndex < 0) {
                vm.triggerAlert('badFav');
                return;
            }

            vm.favBooks[`${vm.bookIndex}-${chapterId}-${paragraphId}`] = 1;
            localStorage.fav = JSON.stringify(vm.favBooks);
            vm.triggerAlert('goodFav');
        },
        removeFav(chapterId, paragraphId) {
            const vm = this;

            vm.setFavBooks();

            if (vm.bookIndex < 0) {
                vm.triggerAlert('badFav');
                return;
            }

            delete vm.favBooks[`${vm.bookIndex}-${chapterId}-${paragraphId}`];
            localStorage.fav = JSON.stringify(vm.favBooks);
        },
        triggerAlert(msgType) {
            const vm = this;

            // Clear any existing timer to prevent overlapping logic
            if (vm.alertTimer) {
                clearTimeout(this.alertTimer);
            }

            switch (msgType) {
                case 'goodShare':
                    vm.msg = {
                        text: 'Sharing link copied to clipboard!',
                        class: 'alert-success'
                    }
                    break;

                case 'badShare':
                    vm.msg = {
                        text: 'Copying link FAILED!',
                        class: 'alert-danger'
                    }
                    break;

                case 'goodFav':
                    vm.msg = {
                        text: 'Text added to favourites!',
                        class: 'alert-success'
                    }
                    break;

                case 'badFav':
                    vm.msg = {
                        text: 'Adding to favourites FAILED!',
                        class: 'alert-danger'
                    }
                    break;
            }

            // Hide the alert after 3000ms (3 seconds)
            vm.alertTimer = setTimeout(() => {
                vm.msg = {};
            }, 3000);
        }
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.alert {
    border-radius: 50px;
    color: $primary;

    @include lg {
        bottom: 10px;
    }

    @include sm-md {
        top: 10px;
    }
}

.b59 {
    & + .paragraph-action {
        bottom: -10px !important;
        z-index: 2;
    }
}

.kirtan {
    .paragraph-list .paragraph-section {
        @include sm-md {
            padding-bottom: 60px;
        }
    }
}
</style>
