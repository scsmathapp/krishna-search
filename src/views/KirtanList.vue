<template>
    <div>
        <BookData :books="kirtanList"
                  :selectedItemCode="selectedItemCode"
                  :selectedBook="kirtan"
                  :showCategorizedFlag="showCategorizedFlag"
                  pageType="kirtans"
                  @navigateKirtan="navigateKirtan"
                  @showCategorized="showCategorized"
                  @selectBookContent="goToKirtan"></BookData>
    </div>
</template>
<script>
import categorized from '@/assets/kirtan/categorized.json';
import indexed from '@/assets/kirtan/indexed.json';
import BookData from "@/components/BookData.vue";

export default {
    name: "KirtanList",
    components: {BookData},
    computed: {
        kirtan() {
            return {chapters: [this.$store.getters.kirtan(this.$route.params.kirtanCode) || {}]};
        }
    },
    data() {
        return {
            categorized,
            indexed,
            contentIndex: -1,
            itemIndex: -1,
            showCategorizedFlag: true,
            menuDisplay: false,
            selectedItemCode: '0-0-0',
            kirtanList: categorized
        }
    },
    created() {
        const vm = this;

        vm.setIndexes(vm.$route.params.kirtanCode);
    },
    methods: {
        showCategorized(showCategorizedFlag) {
            const vm = this;

            vm.showCategorizedFlag = showCategorizedFlag;
            vm.kirtanList = showCategorizedFlag ? vm.categorized : vm.indexed;
            vm.setIndexes(vm.$route.params.kirtanCode);
        },
        goToKirtan(bookIndex, chapterIndex) {
            const vm = this,
                kirtan = vm.kirtanList[bookIndex].chapters[chapterIndex];
            
            if (kirtan && vm.$route.params.kirtanCode !== kirtan.id) {
                vm.$router.push(`/kirtan/${kirtan.id}`);
                vm.menuDisplay = false;
            }
        },
        setIndexes(kirtanCode) {
            const vm = this;

            vm.kirtanList.forEach((content, contentIndex) => {
                content.chapters.forEach((item, itemIndex) => {
                    if (item.id === kirtanCode) {
                        vm.selectedItemCode = `${contentIndex}-${itemIndex}`;
                        vm.contentIndex = contentIndex;
                        vm.itemIndex = itemIndex;
                        return false;
                    }
                });
            });
        },
        navigateKirtan(nextFlag) {
            const vm = this;

            let newContentIndex = -1, newItemIndex = -1;

            if (vm.kirtanList[vm.contentIndex] && vm.kirtanList[vm.contentIndex].chapters &&
                vm.kirtanList[vm.contentIndex].chapters.length && vm.kirtanList[vm.contentIndex].chapters[vm.itemIndex]) {
                newItemIndex = nextFlag ? vm.itemIndex + 1 : vm.itemIndex - 1;

                if (vm.kirtanList[vm.contentIndex].chapters[newItemIndex]) {
                    vm.itemIndex = newItemIndex;
                } else {
                    newContentIndex = nextFlag ? vm.contentIndex + 1 : vm.contentIndex - 1;

                    if (vm.kirtanList[newContentIndex]) {
                        vm.contentIndex = newContentIndex;
                        newItemIndex = nextFlag ? 0 : vm.kirtanList[newContentIndex].chapters.length - 1;

                        if (vm.kirtanList[newContentIndex].chapters && vm.kirtanList[newContentIndex].chapters[newItemIndex]) {
                            vm.itemIndex = newItemIndex;
                        }
                    }
                }

                if (vm.kirtanList[vm.contentIndex].chapters[vm.itemIndex] &&
                    vm.kirtanList[vm.contentIndex].chapters[vm.itemIndex].id !== vm.$route.params.kirtanCode) {
                    vm.$router.push(`/kirtan/${vm.kirtanList[vm.contentIndex].chapters[vm.itemIndex].id}`);
                }
            }
        }
    },
    watch: {
        $route(to) {
            this.setIndexes(to.params.kirtanCode);
        }
    }
}
</script>