<template>
    <div class="d-flex flex-column">
        <div class="book d-flex">
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
            <div :class="menuDisplay ?
                        'paragraph-title-list d-flex position-fixed top-0 end-0 w-30 z-3' : 'd-none'"
                 @click="menuDisplay = false"
            ></div>
            <div class="paragraph-list flex-fill d-flex flex-column align-items-center" ref="paragraphList">
                <div v-for="(chapter, chapterId) in selectedBook.chapters" :id="chapterId" class="paragraph-section">
                    <div class="paragraph" v-for="paragraph in chapter.paragraphs">
                        <div v-html="paragraph.text" :class="paragraph.class"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Show chapter button for mobile -->
        <button class="list-button position-fixed start-0
                d-lg-none d-xl-none p-0 d-flex justify-content-center align-items-center"
                @click="menuDisplay = true">
            <i class="fa fa-caret-right"></i>
        </button>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';

export default {
    data() {
        return {
            book: {},
            selectedChapterId: null,
            menuDisplay: false
        }
    },
    computed: {
        ...mapGetters(['selectedBook']),
        code() {
            return this.$route.params.code;
        }
    },
    mounted() {
        this.restoreScrollPosition();
        this.addScrollListener();
    },
    beforeDestroy() {
        this.removeScrollListener();
    },
    created() {
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
                        // console.log(`Restored scroll for ${vm.articleId}: ${savedPosition}`);
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
                // Save the final position before component destruction
                vm.saveScrollPosition();
            }
        }
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>
