<template>
    <!-- Search -->
    <div class="input-list-container d-flex flex-column justify-content-center">
        <div class="mx-3 mb-3">
            <div class="pt-3">
                <i class="fa fa-info-circle"></i>
                Separate your words with <b>|</b> to search for paragraphs containing <b>either</b> words
            </div>
            <div class="pt-2">
                <i class="fa fa-info-circle"></i>
                Separate your words with <b>+</b> to search for paragraphs containing <b>both</b> words
            </div>
        </div>
        <h4 class="ms-3">Search filter</h4>
        <div class="px-2 d-flex w-100">
            <div class="input-group input-box ks-border d-flex align-items-center px-2">
                <input type="text" class="form-control border-0" placeholder="Filter books..." v-model="searchVal">
            </div>
        </div>
        <!-- Search filter -->
        <div class="list-group">
            <div v-for="(author, authorIndex) in authorsFilterList" :key="authorIndex">
                <div class="list-group-item d-flex align-items-center p-0 author" :class="author.selected ? 'active' : ''">
                    <div class="d-flex p-3 show-books-btn"
                         @click.stop="$set(author, 'showBooks', author.showBooks ? false : true);">
                        <i :class="author.showBooks ? 'fas fa-caret-up' : 'fas fa-caret-down'"></i>
                    </div>
                    <div @click.stop="$store.dispatch('selectAuthor', authorIndex)"
                         class="d-flex align-items-center">
                        <b class="mx-2 d-flex justify-content-center align-items-center books-count"
                           :style="{borderColor: author.selected ? 'white' : 'black'}">
                            {{ author.booksSelectedCount ? author.booksSelectedCount : '' }}
                        </b>
                        <b>{{ author.name }}</b>
                    </div>
                </div>
                <div v-if="author.showBooks">
                    <div v-for="(book, bookIndex) in author.books" :key="bookIndex"
                         class="list-group-item d-flex align-items-center"
                         :class="book.selected ? 'active' : ''"
                         @click.stop="$store.dispatch('selectBook', { bookIndex, authorIndex })"
                         v-if="book.name.toLowerCase().includes(searchVal.toLowerCase())">
                        <i :class="book.selected ? 'fas fa-check-circle' : 'far fa-circle'" class="me-2"></i>
                        <span>{{ book.name }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {mapGetters} from "vuex";

export default {
    name: "BookFilter",
    computed: {
        ...mapGetters(['authorsFilterList', 'filteredBooks'])
    },
    data() {
        return {
            searchVal: '',
            showInputListItems: false
        }
    },
    methods: {
        showInputListItemsClick() {
            const vm = this;

            // Delay attaching the listener so the current click doesn’t trigger it
            setTimeout(() => {
                vm.showInputListItems = true;
                document.body.addEventListener("click", vm.hideSearchFilter);
            }, 0);
        },
        hideSearchFilter(e) {
            const vm = this,
                isSearchFilter = e.target.closest('.input-list-container');

            if (!isSearchFilter) {
                vm.showInputListItems = false;
                document.body.removeEventListener("click", vm.hideSearchFilter);
            }
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.input-list-container {
    color: $primary;

    .list-group {
        overflow-y: auto;
        width: 100%;
        margin-top: 15px;
        
        @include lg {
            height: calc(100vh - 350px);
        }
        
        @include sm-md {
            height: calc(100vh - 250px);
        }
        
        .author {
            background-color: rgb(240, 240, 240);
            
            .show-books-btn {
                border-right: var(--bs-list-group-border-width) solid var(--bs-list-group-border-color);
            }
            
            .books-count {
                border: 2px solid black;
                border-radius: 10px;
                min-width: 20px;
                height: 20px;
                font-size: 13px;
            }
        }

        .list-group-item {
            cursor: pointer;

            &:hover {
                background-color: #f8f9fa;
            }
        }
    }

    .input-box {
        background-color: white;
        overflow: hidden;
        transition: all 0.5s ease;

        @include sm-md {
            height: 50px;
            font-size: 20px;
        }

        @include lg {
            height: 40px;
        }

        & > div {
            height: 37.6px;
            cursor: pointer;
            background-color: white;
        }

        input:focus {
            outline: none;
            box-shadow: none;
        }
    }
}
</style>