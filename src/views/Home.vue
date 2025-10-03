<template>
    <div class="pt-3 home">
        <h1 class="color-default text-center ks-font">Our Library</h1>
        <div class="d-flex flex-wrap mx-1 mx-md-3 mx-lg-5 mx-xl-5">
            <div v-for="vaishnava in vaishnavas" v-if="vaishnava.books && vaishnava.books.length" :class="vaishnava.hasBookCover ? 'w-100' : 'w-sm-100 w-md-50 w-lg-50'">
                <div class="vaishnava-book my-3" v-if="vaishnava.hasBookCover">
                    <h2 class="p-3 ks-font">
                        {{ vaishnava.name }}
                    </h2>
                    <div class="d-flex overflow-x-auto">
                        <ImageScroller :vaishnava="vaishnava" />
                    </div>
                </div>
                <div class="vaishnava-book my-3" v-else>
                    <h2 class="p-2 ks-font">
                        {{ vaishnava.shortName }}
                    </h2>
                    <div class="d-flex overflow-x-auto flex-column">
                        <div v-for="book in vaishnava.books">
                            <a :href="'/#/book/' + book.code" class="d-flex flex-column ks-link">
                                <div class="d-flex">
                                    <p class="link ks-font f-18">{{ book.name }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <footer class="d-flex justify-content-center align-items-center">
            <a href="https://www.scsmath.com" class="card-title ks-link f-18">© Śrī Chaitanya Sāraswat Maṭh</a>
        </footer>
    </div>
</template>
<script>
import vaishnavas from "@/assets/vaishnavas.js";
import ImageScroller from '@/components/ImageScroller.vue';
import Pic from '@/components/Pic.vue';

export default {
    components: { ImageScroller, Pic },
    data() {
        return {
            vaishnavas
        }
    },
    created() {
        const vm = this;
        
        vm.imageList = vm.vaishnavas.GovindaMhj.books.map(book => require(`@/assets/books/cover/${book.bookCode}.jpg`));
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/css/style.scss';

h1 {
    @include sm {
        font-size: 30px;
    }
}

h2 {
    @include sm {
        font-size: 20px;
    }
}

.link {
    margin: 0;

    @include md-lg {
        padding: 10px;
    }

    @include sm {
        font-size: 16px;
        padding: 5px;
    }
}

.home {
    @include sm {
        margin-top: 650px;
    }

    @include md-lg {
        margin-top: 350px;
    }
}

footer {
    margin-top: 20px;
    height: 80px;
    background-color: $theme-color-secondary;
    
    @include sm {
        height: 180px;
        padding-bottom: 100px;
    }
    
    @include md-lg {
        height: 80px;
    }
}
</style>