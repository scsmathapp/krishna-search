<template>
    <div>
        <BookData :books="paragraphList"
                  :selectedItemCode="selectedItemCode"
                  :selectedBook="selectedBook"
                  pageType="favs"
                  @selectBookContent="loadFavParagraph"></BookData>
    </div>
</template>
<script>
import {mapGetters} from "vuex";
import BookData from "@/components/BookData.vue";

export default {
    name: 'Favourites',
    components: {BookData},
    computed: {
        ...mapGetters(['books', 'fontSize'])
    },
    data() {
        return {
            selectedItemCode: null,
            menuDisplay: true,
            selectedBook: {},
            paragraphList: {},
            favBooks: {}
        }
    },
    created() {
        this.loadFavBooks();
    },
    methods: {
        loadFavBooks() {
            const vm = this;

            vm.setFavBooks();

            for (const key in vm.favBooks) {
                const splitKey = key.split('-');

                if (splitKey.length === 3) {
                    const b = splitKey[0], c = splitKey[1], p = splitKey[2];

                    if (vm.books[b] && vm.books[b].chapters && vm.books[b].chapters[c] &&
                        vm.books[b].chapters[c].paragraphs && vm.books[b].chapters[c].paragraphs[p]) {
                        const newPara = {...vm.books[b].chapters[c].paragraphs[p]};

                        if (!vm.paragraphList[b]) {
                            vm.paragraphList[b] = {
                                title: vm.books[b].title,
                                code: vm.books[b].code,
                                chapters: {},
                            }
                        }

                        if (!vm.paragraphList[b].chapters[c]) {
                            vm.paragraphList[b].chapters[c] = {
                                title: vm.books[b].chapters[c].title,
                                paragraphs: {},
                            }
                        }

                        newPara.highlightedText = '<hr style="width: 100%">\n' + newPara.text;
                        newPara.text = newPara.text.slice(0, 100) + '<span>...</span>';

                        vm.paragraphList[b].chapters[c].paragraphs[p] = newPara;
                    }
                }
            }

            vm.paragraphList = {...vm.paragraphList};
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
        loadFavParagraph(bookIndex, chapterIndex, paragraphIndex) {
            const vm = this;
            let timeoutVal = 0;

            if (vm.selectedBook.code !== vm.paragraphList[bookIndex].code) {
                vm.selectedBook = vm.paragraphList[bookIndex];
                timeoutVal = 200;
            }
            vm.selectedItemCode = bookIndex + '-' + chapterIndex + '-' + paragraphIndex;

            setTimeout(() => {
                const element = document.getElementById(chapterIndex + '-' + paragraphIndex);

                if (element) {
                    element.scrollIntoView({block: "start", behavior: "instant"});
                    vm.menuDisplay = false;
                }
            }, timeoutVal);
        },
        setMenuDisplay(value) {
            this.menuDisplay = value;
        }
    },
    watch: {
        books(newBooks) {
            if (newBooks.length > 0) {
                this.loadFavBooks();
            }
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>