<template>
    <div class="book d-flex flex-fill">
        <BookChapters :chapters="[kirtan]" bookCode="en-KirtanGuide" v-if="kirtan"></BookChapters>
    </div>
</template>
<script>
import contents from '@/assets/kirtan/contents.json';
import {mapGetters} from "vuex";
import BookChapters from "../components/BookChapters.vue";

export default {
    name: "Kirtan",
    components: {BookChapters},
    computed: {
        ...mapGetters(['fontSize']),
        kirtan() {
            return this.$store.getters.kirtan(this.$route.params.kirtanCode);
        }
    },
    data() {
        return {
            contents
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.b60 {
    padding-bottom: 0 !important;
}

.book .paragraph-list .paragraph-section {
    @include sm-md {
        padding-bottom: 60px;
    }
}
</style>