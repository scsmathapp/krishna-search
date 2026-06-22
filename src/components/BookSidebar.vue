<template>
    <div class="paragraph-title-list flex-column">
        <b v-if="searchCount || searchCount === 0"
           class="d-flex justify-content-center p-2 position-sticky top-0 z-4">
            {{ searchCount }} results from {{ bookCount }} books
        </b>
        <div class="spinner" v-if="searchProgress"></div>
        <ul class="list-group list-group-flush" v-else>
            <div v-for="(book, bookIndex) in books">
                <h5 class="p-2 d-flex position-sticky z-3 mb-0"
                    :class="searchCount !== undefined ? 'add-top' : ''"
                    v-if="book.title !== undefined">
                    <span class="flex-fill ks-font">{{ book.title }}</span>
                    <a :href="`/#/book/${book.code}`" v-if="book.code && book.code !== 'en-KirtanGuide'">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>
                </h5>
                <div v-for="(chapter, chapterIndex) in book.chapters">
                    <h6 class="p-2 d-flex position-sticky z-2 mb-0"
                        :class="searchCount !== undefined ? 'add-top' : ''"
                        v-if="hasParagraph">
                        <span class="flex-fill ks-font">{{ chapter.title || '-- No chapter title --' }}</span>
                    </h6>
                    <div v-if="hasParagraph">
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
</template>
<script>
export default {
    name: 'BookSidebar',
    props: [
        'searchCount',
        'bookCount',
        'searchProgress',
        'books',
        'selectedItemCode',
        'hasParagraph'
    ],
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
    created() {},
    methods: {}
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.paragraph-title-list {
    h5{
        top: 0;

        &.add-top {
            top: 40px;
        }
    }

    h6 {
        top: 40px;
        background-color: $secondary !important;
        
        &.add-top {
            top: 80px;
        }
    }
}
</style>