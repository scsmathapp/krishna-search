<template>
    <div class="book d-flex">
        <div :class="menuDisplay ?
                        'paragraph-title-list d-flex position-fixed top-0 start-0 w-30 z-3' : 'd-none'"
             @click="menuDisplay = false"
        ></div>
        <div class="paragraph-title-list flex-column"
             :class="menuDisplay ? 'mob-show' : 'mob-hide'">
            <div class="position-sticky kirtan-opt z-3 d-flex">
                <div class="btn-group ks-border flex-fill">
                    <button class="btn" :class="displayContent ? 'active' : ''" @click="displayContent = true; display = contents;">Categorized</button>
                    <button class="btn" :class="!displayContent ? 'active' : ''" @click="displayContent = false; display = index;">A to Z</button>
                </div>
            </div>
            <div v-for="(content, contentIndex) in display" :key="contentIndex">
                <h4 class="kirtan-title p-2 position-sticky z-3 mb-0 ks-font">{{ content.name }}</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item list-group-item-action cursor-pointer"
                       v-for="(item, itemIndex) in content.items" :key="itemIndex"
                        @click="goToKirtan(item.id)"
                       :class="$route.params.kirtanCode === item.id ?
                       'active ks-font-secondary' : 'ks-font'">
                        {{ item.title }}
                    </li>
                </ul>
            </div>
        </div>
        <router-view></router-view>
        <!-- Control functions for mobile -->
        <BookControls @setMenuDisplay="setMenuDisplay" @navigateKirtan="navigateKirtan" :kirtanFlag="true"></BookControls>
    </div>
</template>
<script>
import contents from '@/assets/kirtan/contents.json';
import index from '@/assets/kirtan/index.json';
import BookControls from "../components/BookControls.vue";

export default {
    name: "KirtanList",
    components: {BookControls},
    data() {
        return {
            contents,
            index,
            contentIndex: -1,
            itemIndex: -1,
            display: contents,
            displayContent: true,
            menuDisplay: false
        }
    },
    created() {
        const vm = this;

        vm.setIndexes(vm.$route.params.kirtanCode);
    },
    methods: {
        goToKirtan(kirtanCode) {
            const vm = this;

            vm.$router.push(`/kirtan/${kirtanCode}`);
            vm.menuDisplay = false;
        },
        setMenuDisplay(value) {
            this.menuDisplay = value;
        },
        setIndexes(kirtanCode) {
            const vm = this;

            vm.contents.forEach((content, contentIndex) => {
                content.items.forEach((item, itemIndex) => {
                    if (item.id === kirtanCode) {
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

            if (vm.contents[vm.contentIndex] && vm.contents[vm.contentIndex].items &&
                vm.contents[vm.contentIndex].items.length && vm.contents[vm.contentIndex].items[vm.itemIndex]) {
                newItemIndex = nextFlag ? vm.itemIndex + 1 : vm.itemIndex - 1;

                if (vm.contents[vm.contentIndex].items[newItemIndex]) {
                    vm.itemIndex = newItemIndex;
                } else {
                    newContentIndex = nextFlag ? vm.contentIndex + 1 : vm.contentIndex - 1;

                    if (vm.contents[newContentIndex]) {
                        vm.contentIndex = newContentIndex;
                        newItemIndex = nextFlag ? 0 : vm.contents[newContentIndex].items.length - 1;

                        if (vm.contents[newContentIndex].items && vm.contents[newContentIndex].items[newItemIndex]) {
                            vm.itemIndex = newItemIndex;
                        }
                    }
                }

                if (vm.contents[vm.contentIndex].items[vm.itemIndex] &&
                    vm.contents[vm.contentIndex].items[vm.itemIndex].id !== vm.$route.params.kirtanCode) {
                    vm.$router.push(`/kirtan/${vm.contents[vm.contentIndex].items[vm.itemIndex].id}`);
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
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.kirtan-opt {
    top: 0;
    min-height: 60px;
    background-color: #fff !important;
    padding: 10px;
}

.kirtan-title {
    top: 60px;
}
</style>