<template>
    <div>
        <div class="book d-flex">
            <div class="paragraph-title-list" style="width: 25%;">
                <h4 class="p-2 position-sticky top-0 z-3 mb-0">{{ selectedBook.title }}</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item list-group-item-action"
                        v-for="(chapter, chapterId) in selectedBook.chapters"
                        :class="('' + selectedChapterId) === ('' + chapterId) ? 'active' : ''"
                        @click="scrollToSection(chapterId)">
                        {{ chapter.title || (chapterId === 0 ? 'Cover' : '-- No title --') }}
                    </li>
                </ul>
            </div>
            <div class="paragraph-list flex-fill d-flex flex-column align-items-center">
                <div v-for="(chapter, chapterId) in selectedBook.chapters" :id="chapterId" class="paragraph-section"
                     style="width: 85%;">
                    <div class="paragraph" v-for="paragraph in chapter.paragraphs">
                        <div v-html="paragraph.text" :class="paragraph.class"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            book: {},
            selectedChapterId: null
        }
    },
    computed: {
        ...mapGetters(['selectedBook']),
        code() {
            return this.$route.params.code;
        }
    },
    created() {
        const vm = this;

        vm.loadBook();
    },
    methods: {
        async loadBook() {
            const vm = this;

            await vm.$store.dispatch('setSelectedBook', vm.code);

            if (window.innerWidth > 767) {
                vm.$nextTick(() => {
                    vm.setupIntersectionObserver();
                });
            }
        },
        scrollToSection(chapterId) {
            const vm = this;
            const element = document.getElementById(chapterId);

            if (element) {
                element.scrollIntoView({behavior: "smooth", block: "start"});
                vm.menuDisplay = false;
            }
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
};
</script>
