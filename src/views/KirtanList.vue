<template>
    <div class="book d-flex">
        <div class="paragraph-title-list flex-column"
             :class="menuDisplay ? 'mob-show' : 'mob-hide'">
            <div class="btn-group ks-border kirtan-opt position-sticky z-3">
                <button class="btn" :class="display[1] ? 'active' : ''" @click="display = contents">Categorized</button>
                <button class="btn" :class="!display[1] ? 'active' : ''" @click="display = index">A to Z</button>
            </div>
            <div v-for="(content, contentIndex) in display" :key="contentIndex">
                <h4 class="kirtan-title p-2 position-sticky z-3 mb-0 ks-font">{{ content.name }}</h4>
                <ul class="list-group list-group-flush">
                    <a class="list-group-item list-group-item-action cursor-pointer"
                       v-for="(item, itemIndex) in content.items" :key="itemIndex"
                       :href="'/#/kirtan/' + item.id"
                       :class="$route.params.kirtanCode === item.id ?
                       'active ks-font-secondary' : 'ks-font'">
                        {{ item.title }}
                    </a>
                </ul>
            </div>
        </div>
        <div :class="menuDisplay ?
                    'paragraph-title-list d-flex position-fixed top-0 end-0 w-30 z-3' : 'd-none'"
             @click="menuDisplay = false"
        ></div>
        <router-view></router-view>
    </div>
</template>
<script>
import contents from '@/assets/kirtan/contents.json';
import index from '@/assets/kirtan/index.json';

export default {
    name: "KirtanList",
    data() {
        return {
            contents,
            index,
            display: contents,
            menuDisplay: false
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.kirtan-opt {
    top: 10px;
    min-height: 40px;
    background-color: #fff !important;
    margin: 10px;
}

.kirtan-title {
    top: 60px;
}
</style>