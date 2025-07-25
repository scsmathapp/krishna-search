import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
    state: {
        books: [],
        selectedBook: {}
    },

    mutations: {
        SET_BOOKS(state, books) {
            state.books = books;
        },
        SET_SELECTED_BOOK(state, book) {
            state.selectedBook = book;
        }
    },

    actions: {
        async loadBooks({ commit }, bookFilenames) {
            const books = [];

            for (const filename of bookFilenames) {
                // Adjust the path as needed to your book folder
                const module = await import(`@/assets/books/json/${filename}`);
                books.push(module.default || module);
            }

            commit('SET_BOOKS', books);
        },

        async setSelectedBook({ state, commit }, code) {
            if (state.books.length > 0) {
                // If books are already loaded, just find and set the one
                const foundBook = state.books.find(book => book.code === code);
                commit('SET_SELECTED_BOOK', foundBook || null);
            } else {
                // Else, try to dynamically import the single book file
                try {
                    const module = await import(`@/assets/books/json/${code}.json`);
                    const book = module.default || module;
                    commit('SET_SELECTED_BOOK', book);
                } catch (error) {
                    console.error(`Failed to load book with code "${code}":`, error);
                    commit('SET_SELECTED_BOOK', null);
                }
            }
        }
    },

    getters: {
        books: (state) => state.books,
        selectedBook: (state) => state.selectedBook
    }
});
