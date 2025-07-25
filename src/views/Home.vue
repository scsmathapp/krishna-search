<template>
    <div class="pt-3">
        <div class="welcome color-default d-flex justify-content-center mx-auto my-5">
            <div class="d-flex w-50">
                <img src="@/assets/KrishnaSearchHome.png" class="w-100">
            </div>
            <div class="m-5 w-50">
                <h1>Welcome to Krishna Search</h1>
                <p>Digital Library of Sri Chaitanya Saraswat Math</p>
                <p>A dedicated tool for reading and exploring the teachings of Bhakti from Srila Sridhar Maharaj and
                    Srila Govinda Maharaj. This site offers access to their books, rich with spiritual insight, clarity
                    and devotional depth. Discover the living current of Sri Chaitanya Saraswat Math tradition through
                    their transcendental words.</p>
            </div>
        </div>
        <h1 class="color-default text-center">Our Library</h1>
        <div class="d-flex flex-wrap">
            <div v-for="vaishnava in vaishnavas" :class="vaishnava.hasBookCover ? 'w-100' : 'w-25'">
                <div v-if="vaishnava.books && vaishnava.books.length" class="vaishnava-book my-3 mx-5">
                    <h2 class="p-3">
                        {{ vaishnava.name }}
                    </h2>
                    <div class="d-flex overflow-x-auto" v-if="vaishnava.hasBookCover">
                        <div v-for="book in vaishnava.books">
                            <a :href="'/#/book/' + book.code" class="book-card m-3 d-flex flex-column">
                                <div v-if="book.bookCode" class="img-bg ks-border"
                                     :style="`background-image: url(${require(`@/assets/books/cover/${book.bookCode}.jpg`)});`"></div>
                                <div class="d-flex justify-content-center align-items-center text-center p-2">
                                    <p class="card-title">{{ book.name }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex overflow-x-auto flex-column" v-else>
                        <div v-for="book in vaishnava.books">
                            <a :href="'/#/book/' + book.code" class="book-card d-flex flex-column">
                                <div class="d-flex justify-content-center align-items-center text-center p-2">
                                    <p class="card-title">{{ book.name }}</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import vaishnavas from "@/assets/vaishnavas.js";

export default {
    data() {
        return {
            vaishnavas
        }
    }
};
</script>