<template>
    <div>
        <div class="d-flex book">
            <!--Void when sidebar is open-->
            <div class="paragraph-title-list position-fixed top-0 start-0 w-100 d-lg-none d-xl-none"
                 :class="menuDisplay ? 'd-flex' : 'd-none'"
                 @click="menuDisplay = false"
            ></div>
            <!--Sidebar-->
            <div class="paragraph-title-list flex-column" :class="menuDisplay ? 'mob-show' : 'mob-hide'">
                <b v-if="pageType === 'results'"
                   class="d-flex kirtan-opt justify-content-center align-items-center position-sticky top-0 z-4">
                    {{ searchCount }} results from {{ bookCount }} books
                </b>
                <div class="position-sticky kirtan-opt z-4 d-flex" v-if="pageType === 'kirtans'">
                    <div class="btn-group ks-border flex-fill">
                        <button class="btn" :class="showCategorizedFlag ? 'active' : ''"
                                @click="$emit('showCategorized', true)">Categorized
                        </button>
                        <button class="btn" :class="!showCategorizedFlag ? 'active' : ''"
                                @click="$emit('showCategorized', false)">A to Z
                        </button>
                    </div>
                </div>
                <div class="spinner" v-if="searchProgress"></div>
                <ul class="list-group list-group-flush" v-else>
                    <div v-for="(book, bookIndex) in books">
                        <h5 class="p-2 d-flex position-sticky z-3 mb-0"
                            :class="pageType === 'results' || pageType === 'kirtans' ? 'add-top' : ''"
                            v-if="book.title !== undefined">
                            <span class="flex-fill ks-font">{{ book.title }}</span>
                            <a :href="`/#/book/${book.code}`" v-if="defaultMenuDisplay && book.code">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                            </a>
                        </h5>
                        <div v-for="(chapter, chapterIndex) in book.chapters">
                            <div v-if="defaultMenuDisplay"><!-- Default menu display as same condition with paragraph-->
                                <h6 class="p-2 d-flex position-sticky z-2 mb-0"
                                    :class="pageType === 'results' || pageType === 'kirtans' ? 'add-top' : ''">
                                    <span class="flex-fill ks-font">
                                        {{ chapter.title || '-- No chapter title --' }}
                                    </span>
                                </h6>
                                <li class="list-group-item list-group-item-action p-2 cursor-pointer"
                                    v-for="(paragraph, paragraphIndex) in chapter.paragraphs"
                                    :class="selectedItemCode === (bookIndex + '-' + chapterIndex + '-' + paragraphIndex) ? 'active ks-font-secondary' : 'ks-font'"
                                    @click="$emit('selectBookContent', bookIndex, chapterIndex, paragraphIndex)"
                                    v-html="paragraph.text">
                                </li>
                            </div>
                            <li class="list-group-item list-group-item-action p-2 cursor-pointer"
                                :class="selectedItemCode === (bookIndex + '-' + chapterIndex) ? 'active ks-font-secondary' : 'ks-font'"
                                @click="$emit('selectBookContent', bookIndex, chapterIndex)"
                                v-else>
                                {{ chapter.title || (chapterIndex === 0 ? 'Cover' : '&#45;&#45; No title &#45;&#45;') }}
                            </li>
                        </div>
                    </div>
                </ul>
            </div>
            <!--Book / Kirtan content-->
            <div class="paragraph-list flex-fill d-flex flex-column align-items-center"
                 :style="`font-size: ${fontSize}%`"
                 id="paragraph-list">
                <div v-for="(chapter, chapterId) in selectedBook.chapters" :id="chapterId" class="paragraph-section">
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
        </div>
        <!--Book Controls-->
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
            <div class="border-0" @click="menuDisplay = true" v-if="isMobile">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-angle-left me-1"></i>
                    <i class="fa-solid fa-list"></i>
                </div>
                <div class="control-text">{{ pageType }}</div>
            </div>
        </div>
    </div>
</template>
<script>
import {mapGetters} from "vuex";

export default {
    name: "BookData",
    computed: {
        ...mapGetters(['fontSize', 'showTranslation']),
        bookIndex() {
            return this.$store.getters.bookIndex(this.selectedBook.code);
        },
        isMobile() {
            return this.windowWidth < 991.98;
        }
    },
    props: [
        'searchCount',
        'bookCount',
        'searchProgress',
        'books',
        'selectedItemCode',
        'selectedBook',
        'hasParagraph',
        'pageType',
        'showCategorizedFlag'
    ],
    data() {
        return {
            menuDisplay: false,
            windowWidth: window.innerWidth,
            alertTimer: null,
            msg: {},
            favBooks: {},
            defaultMenuDisplay: false
        }
    },
    created() {
        const vm = this;

        vm.defaultMenuDisplay = vm.pageType === 'results' || vm.pageType === 'favs';

        vm.menuDisplay = vm.defaultMenuDisplay;
    },
    methods: {
        setMenuDisplay(value) {
            this.menuDisplay = value;
        },
        selectBookContent(bookIndex, chapterIndex, paragraphIndex) {
            this.$emit('selectBookContent', bookIndex, chapterIndex, paragraphIndex);
            this.menuDisplay = false;
        },
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
        navigateKirtan(nextFlag) {
            this.$emit('navigateKirtan', nextFlag);
        },
        toggleTranslation() {
            this.$store.commit('SET_SHOW_TRANSLATION', this.showTranslation ? false : true);
        },
        setFavBooks() {
            const vm = this;

            try {
                const localFav = localStorage.fav;

                if (localFav) {
                    vm.favBooks = JSON.parse(localFav);
                }
            } catch (e) {
            }
        },
        async copyUrl(chapterId, paragraphId) {
            const vm = this;

            try {
                await navigator.clipboard.writeText(window.location.origin +
                    `/#/book/${vm.selectedBook.code}?c=${chapterId}&p=${paragraphId}`);
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
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.paragraph-title-list {
    h5 {
        top: 0;

        &.add-top {
            top: 60px;
        }
    }

    h6 {
        top: 40px;
        background-color: $secondary !important;

        &.add-top {
            top: 100px;
        }
    }
}

.kirtan-opt {
    top: 0;
    min-height: 60px;
    background-color: #fff !important;
    padding: 10px;
}

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

.paragraph-section {
    @include sm-md {
        padding-bottom: 60px !important;
    }
}

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
    box-shadow: rgba(0, 0, 0, 0.5) 0 0 7px;
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
    0% {
    }
    10% {
        width: 150px;
    }
    90% {
        width: 150px;
    }
    100% {
    }
}

@keyframes book-controls-div-animation {
    0% {
    }
    10% {
        transform: translate(50px, 2.5px);
    }
    90% {
        transform: translate(50px, 2.5px);
    }
    100% {
    }
}

@keyframes book-controls-text-animation {
    0% {
    }
    10% {
        font-size: 16px;
        transform: translateY(-13px);
    }
    90% {
        font-size: 16px;
        transform: translateY(-13px);
    }
    100% {
    }
}
</style>