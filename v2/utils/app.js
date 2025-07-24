import router from './router.js';
import allBooks from './allBooks.js';

// Create Vue app
const app = Vue.createApp({
    data() {
        return {
            searchVal: ''
        }
    },
    created() {
        const vm = this;

        setTimeout(() => {
            vm.searchVal = vm.$route.query && vm.$route.query.q ? vm.$route.query.q : '';
        }, 500);
    },
    methods: {
        doSearch(e) {
            const vm = this;

            e.preventDefault();
            
            if (vm.searchVal) {
                vm.$router.push('/search?q=' + vm.searchVal.replaceAll('+', '%2B'));
            }
        }
    }
});

// Use the router
app.use(router);

// Mount the app
app.mount('#app');

setTimeout(() => {
    // Fetch each JSON file
    const promises = allBooks.map(file => {
        return fetch(`../v2/assets/books/json/${file}`)
            .then(response => response.json())
            .catch(error => {
                console.error(`Failed to load ${file}:`, error);
                return null;
            });
    });

    Promise.all(promises)
        .then(data => {
            window.books = data;
            window.dispatchEvent(new CustomEvent('booksDataReady', { books: data }));
        });
});
