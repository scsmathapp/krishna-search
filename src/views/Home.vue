<template>
    <div class="pt-3 home">
        <!-- Welcome text + bg -->
        <div :style="'background-image: url(' + require('@/assets/KSWelcome.jpg') + ');'"
             class="welcome position-absolute top-0 left-0 d-flex flex-column justify-content-center align-items-center img-bg">
            <h1 class="mb-5 mt-4 ks-font-italic">Welcome to Krishna Search</h1>
            <p class="mt-5 ks-font-italic">Digital Library of Śrī Chaitanya Sāraswat Maṭh</p>
            <p class="ks-font">A dedicated tool for reading and exploring the teachings of devotion<br class="d-none d-md-flex d-lg-flex d-xl-flex" />
                in the tradition of Śrīla Bhakti Rakṣak Śrīdhar Dev-Goswāmī Mahārāj<br class="d-none d-md-flex d-lg-flex d-xl-flex" />
                and Śrīla Bhakti Sundar Govinda Dev-Goswāmī Mahārāj.</p>
        </div>
        <!-- Books -->
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
        <!-- Footer -->
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

.welcome {
    height: 650px;
    background-position-y: top !important;
    text-align: center;

    @include lg {
        width: 99vw;
    }

    @include sm-md {
        width: 100vw;
    }

    h1 {
        @include lg {
            font-size: 35px;
        }

        @include sm-md {
            font-size: 30px;
        }
    }

    &>p:first-of-type {
        @include lg {
            font-size: 16px;
        }

        @include sm-md {
            font-size: 14px;
        }
    }

    &>p:last-child {
        padding: 0 35px;

        @include lg {
            font-size: 18px;
        }

        @include sm-md {
            font-size: 16px;
        }
    }
}

h1 {
    @include sm-md {
        font-size: 30px;
    }
}

h2 {
    @include sm-md {
        font-size: 20px;
    }
}

.link {
    margin: 0;

    @include lg {
        padding: 10px;
    }

    @include sm-md {
        font-size: 16px;
        padding: 5px;
    }
}

.home {
    @include sm-md {
        margin-top: 650px;
    }

    @include lg {
        margin-top: 570px;
    }
}

footer {
    margin-top: 20px;
    height: 80px;
    background-color: $theme-color-secondary;
    
    @include sm-md {
        height: 180px;
        padding-bottom: 100px;
    }
    
    @include lg {
        height: 80px;
    }
}
</style>