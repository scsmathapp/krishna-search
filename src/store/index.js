import Vue from 'vue';
import Vuex from 'vuex';
import allBooks from '@/assets/books/allBooks.js';
import vaishnavas from '@/assets/js/vaishnavas.js';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        books: [],
        selectedBook: {},
        authorsFilterList: [],
        filteredBooks: []
    },

    mutations: {
        SET_BOOKS(state, books) {
            state.books = books;
        },
        SET_SELECTED_BOOK(state, book) {
            state.selectedBook = book;
        },
        SET_AUTHORS_FILTER_LIST(state, authorsFilterList) {
            state.authorsFilterList = authorsFilterList;
        },
        SET_FILTERED_BOOKS(state, filteredBooks) {
            state.filteredBooks = filteredBooks;
        }
    },

    actions: {
        async loadBooks({ commit }) {
            const books = [], authorsFilterList = [];

            for (const code in vaishnavas) {
                if (vaishnavas[code].books && vaishnavas[code].books.length) {
                    authorsFilterList.push(vaishnavas[code]);
                }
            }

            for (const filename of allBooks) {
                // Adjust the path as needed to your book folder
                const module = await import(`@/assets/books/json/${filename}.json`);

                books.push(module.default || module);
            }

            commit('SET_BOOKS', books);
            commit('SET_AUTHORS_FILTER_LIST', authorsFilterList);
        },

        async setSelectedBook({ state, commit, dispatch }, code) {
            const bookIndex = '' + allBooks.findIndex(bookCode => bookCode === code);

            commit('SET_FILTERED_BOOKS', [bookIndex]);
            dispatch('resetAuthorsFilterList');
            
            if (state.books.length > 0) {
                // If books are already loaded, just find and set the one
                const foundBook = state.books.find(book => book.code === code);
                commit('SET_SELECTED_BOOK', foundBook || null);
            } else {
                // Else, try to dynamically import the single book file
                try {
                    const module = await import(`@/assets/books/json/${code}.json`);
                    
                    commit('SET_SELECTED_BOOK', module.default || module);
                } catch (error) {
                    console.error(`Failed to load book with code "${code}":`, error);
                    commit('SET_SELECTED_BOOK', null);
                }
            }
        },

        updateFilteredBooks({ state, commit }, book) {
            if (book.selected) {
                commit('SET_FILTERED_BOOKS', [ ...state.filteredBooks, '' + allBooks.findIndex(bookCode => bookCode === book.code) ]);
                return;
            }

            commit('SET_FILTERED_BOOKS', state.filteredBooks.filter(index => allBooks[index] !== book.code));
        },

        selectBook({ state, commit, dispatch }, { bookIndex, authorIndex }) {
            const tmpAuthors = [ ...state.authorsFilterList ],
                authorBooks = tmpAuthors[authorIndex].books;

            authorBooks[bookIndex].selected = authorBooks[bookIndex].selected ? false : true;
            tmpAuthors[authorIndex].selected = authorBooks.every(book => book.selected);
            tmpAuthors[authorIndex].booksSelectedCount = authorBooks.filter(book => book.selected).length;

            dispatch('updateFilteredBooks', authorBooks[bookIndex]);
            commit('SET_AUTHORS_FILTER_LIST', tmpAuthors);
        },
        
        selectAuthor({ state, commit, dispatch }, authorIndex) {
            const tmpAuthors = [ ...state.authorsFilterList ],
                author = tmpAuthors[authorIndex];

            author.selected = author.selected ? false : true;

            author.books.forEach(function (book, bookIndex) {
                if ((book.selected && !author.selected) || (!book.selected && author.selected)) {
                    book.selected = author.selected;
                    dispatch('updateFilteredBooks', book);
                }
            });

            author.booksSelectedCount = author.books.filter(book => book.selected).length;

            commit('SET_AUTHORS_FILTER_LIST', tmpAuthors);
        },

        resetAuthorsFilterList({ state, commit }) {
            const tmpAuthors = [ ...state.authorsFilterList ];

            tmpAuthors.forEach((author, authorIndex) => {
                author.books.forEach((book, bookIndex) => {
                    const allBooksIndex = '' + allBooks.findIndex(bookCode => bookCode === book.code);

                    book.selected = state.filteredBooks.includes(allBooksIndex);
                });

                author.selected = author.books.every(book => book.selected);
                author.booksSelectedCount = author.books.filter(book => book.selected).length;
            });
            
            commit('SET_AUTHORS_FILTER_LIST', tmpAuthors);
        },

    },

    getters: {
        books: (state) => state.books,
        selectedBook: (state) => state.selectedBook,
        authorsFilterList: (state) => state.authorsFilterList,
        filteredBooks: (state) => state.filteredBooks
    }
});