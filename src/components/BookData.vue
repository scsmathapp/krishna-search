<template>
    <div class="d-flex flex-column">
        <div class="book d-flex" v-for="selectedBook in data">
            <div class="paragraph-title-list flex-column"
                 :class="menuDisplay ? 'd-flex position-fixed top-0 start-0 w-70 z-3 shadow' :
                         'd-none d-md-flex d-lg-flex d-xl-flex'">
                <h4 class="p-2 position-sticky top-0 z-3 mb-0 ks-font">{{ selectedBook.title }}</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item list-group-item-action cursor-pointer"
                        v-for="(chapter, chapterId) in selectedBook.chapters"
                        :class="('' + selectedChapterId) === ('' + chapterId) ? 'active ks-font-secondary' : 'ks-font'"
                        @click="vm.routeName.includes('book') ?
                            scrollToSection(chapterId) : selectSearch(chapter, selectedBook.code)">
                        {{ chapter.title || (chapterId === 0 ? 'Cover' : '-- No title --') }}
                    </li>
                </ul>
            </div>
            <div :class="menuDisplay ?
                        'paragraph-title-list d-flex position-fixed top-0 end-0 w-30 z-3' : 'd-none'"
                 @click="menuDisplay = false"
            ></div>
            <div class="paragraph-list flex-fill d-flex flex-column align-items-center">
                <div v-for="(chapter, chapterId) in selectedBook.chapters" :id="chapterId" class="paragraph-section">
                    <div class="paragraph" v-for="paragraph in chapter.paragraphs">
                        <div v-html="paragraph.text" :class="paragraph.class"></div>
                    </div>
                </div>
            </div>
        </div>
        <button class="list-button ks-button position-fixed start-0 m-4 shadow
                d-md-none d-lg-none d-xl-none p-0 d-flex justify-content-center align-items-center active"
                @click="menuDisplay = true">
            <i class="fa fa-list"></i>
        </button>
    </div>
</template>
<script>
import {mapGetters} from "vuex";

export default {
    name: 'BookData',
    props: {
        data: {
            type: Array,
            required: true
        }
    },
    computed: {
        routeName() {
            return this.$route.name;
        }
    },
    data() {
        return {
            selectedChapterId: null,
            menuDisplay: false
        }
    },
    created() {
        const vm = this;

        if (window.innerWidth > 767 && vm.routeName.includes('book')) {
            vm.$nextTick(() => {
                vm.setupIntersectionObserver();
            });
        }
    },
    methods: {
        scrollToSection(chapterId) {
            const vm = this;
            const element = document.getElementById(chapterId);

            if (element) {
                element.scrollIntoView({behavior: "smooth", block: "start"});
                vm.menuDisplay = false;
            }
        },
        selectSearch(paragraph, bookCode) {
            const vm = this;
            let timeoutVal = 0;

            if (vm.selectedBook.code !== bookCode) {
                vm.selectedBook = vm.data.find(book => book.code === bookCode);
                timeoutVal = 200;
            }

            vm.selectedBookCode = bookCode + '-' + paragraph.chapterIndex + '-' + paragraph.paragraphIndex;

            setTimeout(() => {
                const element = document.getElementById(paragraph.chapterIndex + '-' + paragraph.paragraphIndex);

                if (element) {
                    element.scrollIntoView({block: "start"});
                    vm.menuDisplay = false;
                }
            }, timeoutVal);
        },
        setupIntersectionObserver() {
            const vm = this;
            const sections = document.querySelectorAll('.paragraph-section');
            const container = document.querySelector('.paragraph-list');
            const options = {
                root: container, // Use the viewport as the root
                rootMargin: '0px',
                threshold: 0.01, // Trigger when 1% of the section is visible
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        // Update currentSection with the visible section's ID
                        vm.selectedChapterId = entry.target.id;
                    }
                });
            }, options);

            // Observe each section
            sections.forEach((section) => {
                observer.observe(section);
            });
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>