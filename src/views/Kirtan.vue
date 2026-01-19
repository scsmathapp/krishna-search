<template>
    <div class="book d-flex flex-fill">
        <div class="paragraph-list flex-fill d-flex flex-column align-items-center" ref="paragraphList">
            <div class="paragraph-section" v-if="kirtan">
                <div class="paragraph" v-for="paragraph in kirtan.paragraphs">
                    <div v-html="paragraph.text" :class="paragraph.class"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "Kirtan",
    computed: {
        kirtan() {
            return this.$store.getters.kirtan(this.$route.params.kirtanCode);
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

@include sm-md {
    .book .paragraph-list .paragraph-section {
        padding-bottom: 50px;
    }
}
</style>