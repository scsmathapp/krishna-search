<template>
    <div class="d-flex flex-column">
        <div class="book d-flex">
            <div :class="menuDisplay ?
                        'paragraph-title-list d-flex position-fixed top-0 start-0 w-30 z-3' : 'd-none'"
                 @click="setMenuDisplay(false)"
            ></div>
            <div class="paragraph-title-list flex-column"
                 :class="menuDisplay ? 'mob-show' : 'mob-hide'">
                <h4 class="p-2 position-sticky top-0 z-3 mb-0 ks-font">{{ selectedBook.title }}</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item list-group-item-action cursor-pointer"
                        v-for="(chapter, chapterId) in selectedBook.chapters"
                        :class="('' + selectedChapterId) === ('' + chapterId) ? 'active ks-font-secondary' : 'ks-font'"
                        @click="scrollToSection(chapterId)">
                        {{ chapter.title || (chapterId === 0 ? 'Cover' : '-- No title --') }}
                    </li>
                </ul>
            </div>
            <div class="paragraph-list flex-fill d-flex flex-column align-items-center"
                 :style="`font-size: ${fontSize}%`"
                 ref="paragraphList">
                <div v-for="(chapter, chapterId) in selectedBook.chapters" :id="chapterId" class="paragraph-section">
                    <div class="paragraph" v-for="paragraph in chapter.paragraphs">
                        <div v-html="paragraph.text" :class="paragraph.class"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Control functions for mobile -->
        <BookControls @setMenuDisplay="setMenuDisplay"></BookControls>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';
import BookControls from "../components/BookControls.vue";

export default {
    components: {BookControls},
    data() {
        return {
            book: {},
            selectedChapterId: null,
            menuDisplay: false
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
    },
    mounted() {
        const vm = this;

        vm.loadBook();
    },
    methods: {
        async loadBook() {
            const vm = this;

            await vm.$store.dispatch('setSelectedBook', vm.code);

            if (window.innerWidth > 992) {
                vm.$nextTick(() => {
                    vm.setupIntersectionObserver();
                });
            }

            setTimeout(() => {
                this.restoreScrollPosition();
                this.addScrollListener();
            }, 1000);
        },
        scrollToSection(chapterId) {
            const vm = this,
                element = document.getElementById(chapterId);

            if (element) {
                element.scrollIntoView({behavior: "smooth", block: "start"});
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
                            vm.selectedChapterId = entry.target.id;
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
                el = vm.$refs.paragraphList;

            if (el) {
                // Save the vertical scroll position (scrollTop)
                localStorage.setItem(vm.code, el.scrollTop);
                // console.log(`Saved scroll for ${vm.articleId}: ${el.scrollTop}`);
            }
        },
        // Throttle the save function to prevent performance issues
        throttledSaveScroll() {
            console.log('vm.scrollTimer', this);
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
                savedPosition = localStorage.getItem(vm.code);

            if (savedPosition) {
                // Use nextTick to ensure the DOM has rendered before scrolling
                vm.$nextTick(() => {
                    const el = vm.$refs.paragraphList;

                    if (el) {
                        el.scrollTop = parseInt(savedPosition, 10);
                    }
                });
            }
        },
        addScrollListener() {
            const vm = this,
                el = vm.$refs.paragraphList;

            if (el) {
                el.addEventListener('scroll', vm.throttledSaveScroll);
            }
        },
        removeScrollListener() {
            const vm = this,
                el = vm.$refs.paragraphList;

            if (el) {
                el.removeEventListener('scroll', vm.throttledSaveScroll);
                vm.saveScrollPosition();
            }
        },
        setMenuDisplay(value) {
            this.menuDisplay = value;
        }
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>
