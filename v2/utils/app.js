import router from './router.js';

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
                vm.$router.push('/search?q=' + vm.searchVal);
            }
        }
    }
});

// Use the router
app.use(router);

// Mount the app
app.mount('#app');
