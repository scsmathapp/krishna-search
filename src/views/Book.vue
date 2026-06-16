<template>
    <div class="d-flex flex-column">
        <div class="book d-flex">
            <div class="paragraph-title-list position-fixed top-0 start-0 w-100"
                 :class="menuDisplay ? 'd-flex' : 'd-none'"
                 @click="setMenuDisplay(false)"
            ></div>
            <BookSidebar :books="books"
                         :selectedParagraphCode="selectedChapterId"
                         :hasParagraph="false"
                         :class="menuDisplay ? 'mob-show' : 'mob-hide'"
                         @selectBookContent="scrollToSection"></BookSidebar>
            <BookChapters :chapters="selectedBook.chapters" :bookCode="code"></BookChapters>
        </div>
        <!-- Control functions for mobile -->
        <BookControls @setMenuDisplay="setMenuDisplay" pageType="chapters"></BookControls>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';
import BookControls from "../components/BookControls.vue";
import BookChapters from "../components/BookChapters.vue";
import BookSidebar from "@/components/BookSidebar.vue";

export default {
    components: {BookSidebar, BookControls, BookChapters},
    data() {
        return {
            books: {},
            selectedChapterId: null,
            menuDisplay: false,
            sharedFlag: false,
            paragraphListElement: null,
        }
    },
    computed: {
        ...mapGetters(['selectedBook', 'fontSize']),
        code() {
            return this.$route.params.code;
        }
    },
    beforeDestroy() {
        this.removeScrollListener();
        this.$store.commit('SET_FILTERED_BOOKS', []);
        this.$store.dispatch('resetAuthorsFilterList');
    },
    mounted() {
        const vm = this;

        vm.loadBook(vm.code);
    },
    methods: {
        async loadBook(code) {
            const vm = this;

            await vm.$store.dispatch('setSelectedBook', code);

            if (window.innerWidth > 992) {
                vm.$nextTick(() => {
                    vm.setupIntersectionObserver();
                });
            }

            vm.books = [{
                title: vm.selectedBook.title,
                chapters: vm.selectedBook.chapters
            }];

            setTimeout(() => {
                vm.paragraphListElement = document.getElementById('paragraph-list');
                if (vm.$route.query.c && vm.$route.query.p) {
                    vm.sharedFlag = true;
                    vm.scrollToSection(0, vm.$route.query.c, vm.$route.query.p);
                } else {
                    vm.restoreScrollPosition();
                }

                vm.addScrollListener();
            }, 1000);
        },
        scrollToSection(bookId, chapterId, paragraphId) {
            const vm = this,
                element = document.getElementById(paragraphId ? (chapterId + '-' + paragraphId) : chapterId);

            if (element) {
                if (vm.sharedFlag) {
                    element.innerHTML = '<span class="highlight">' + element.innerHTML + '</span>';
                }

                element.scrollIntoView({block: "start"});
                vm.menuDisplay = false;
            }
        },
        setupIntersectionObserver() {
            const vm = this,
                sections = document.querySelectorAll('.paragraph-section'),
                container = document.querySelector('.paragraph-list'),
                options = {
                    root: container, // Use the viewport as the root
                    rootMargin: '0px',
                    threshold: 0.01, // Trigger when 1% of the section is visible
                },
                observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            // Update currentSection with the visible section's ID
                            vm.selectedChapterId = '0-' + entry.target.id;
                        }
                    });
                }, options);

            // Observe each section
            sections.forEach((section) => {
                observer.observe(section);
            });
        },
        saveScrollPosition() {
            const vm = this,
                el = vm.paragraphListElement;

            if (el) {
                // Save the vertical scroll position (scrollTop)
                localStorage.setItem(vm.code, JSON.stringify([el.scrollTop, new Date()]));
                // console.log(`Saved scroll for ${vm.articleId}: ${el.scrollTop}`);
            }
        },
        // Throttle the save function to prevent performance issues
        throttledSaveScroll() {
            const vm = this;

            if (vm.scrollTimer) {
                clearTimeout(vm.scrollTimer);
            }

            vm.scrollTimer = setTimeout(() => {
                vm.saveScrollPosition();
            }, vm.scrollThrottle);
        },
        // Attempt to restore scroll position from localStorage
        restoreScrollPosition() {
            const vm = this,
                bookScrollConfig = localStorage.getItem(vm.code);

            let savedPosition;

            if (bookScrollConfig) {
                if (bookScrollConfig.startsWith('[')) {
                    try {
                        const configArr = JSON.parse(bookScrollConfig);
                        savedPosition = configArr[0];
                    } catch (e) {
                    }
                } else {
                    savedPosition = bookScrollConfig;
                }

                if (savedPosition) {
                    // Use nextTick to ensure the DOM has rendered before scrolling
                    vm.$nextTick(() => {
                        const el = vm.paragraphListElement;

                        if (el) {
                            el.scrollTop = parseInt(savedPosition, 10);
                        }
                    });
                }
            }
        },
        addScrollListener() {
            const vm = this,
                el = vm.paragraphListElement;

            if (el) {
                el.addEventListener('scroll', vm.throttledSaveScroll);
            }
        },
        removeScrollListener() {
            const vm = this,
                el = vm.paragraphListElement;

            if (el) {
                el.removeEventListener('scroll', vm.throttledSaveScroll);
                vm.saveScrollPosition();
            }
        },
        setMenuDisplay(value) {
            this.menuDisplay = value;
        }
    },
    watch: {
        $route(to) {
            this.loadBook(to.params.code);
        }
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>
