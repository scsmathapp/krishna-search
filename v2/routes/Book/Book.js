export default {
    data() {
        return {
            book: {},
            currentSection: null
        }
    },
    computed: {
        code() {
            return this.$route.params.code;
        }
    },
    created() {
        const vm = this;

        vm.loadBook();
    },
    methods: {
        async loadBook () {
            const vm = this;

            fetch(`../../v2/assets/books/json/${vm.$route.params.code}.json`)
                .then(response => response.json())
                .then(data => {
                    vm.book = data;
                    if (window.innerWidth > 767) {
                        vm.$nextTick(() => {
                            vm.setupIntersectionObserver();
                        });
                    }
                })
                .catch(error => {
                    console.error('Error loading JSON:', error);
                });
        },
        scrollToSection(chapterId) {
            const vm = this;
            const element = document.getElementById(chapterId);

            if (element) {
                element.scrollIntoView({ behavior: "smooth", block: "start" });
                vm.menuDisplay = false;
            }
        },
        setActive(chapterId) {
            const vm = this;

            vm.book.chapters.forEach((c, cId) => {
                c.activeFlag = ('' + cId) === ('' + chapterId);
            });
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
                        vm.setActive(entry.target.id); // Update currentSection with the visible section's ID
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
