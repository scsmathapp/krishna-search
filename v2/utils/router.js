// Define routes
const routes = await getRoutes([
    {path: '/', url: 'Home'},
    {path: '/book/:code', url: 'Book'},
    {path: '/search', url: 'Search'},
]);

// Create Vue Router instance
const router = VueRouter.createRouter({
    history: VueRouter.createWebHashHistory(),
    routes,
    scrollBehavior(to) {
        if (to.hash) {
            const element = document.getElementById(to.hash.substring(1)); // Remove '#' from hash
            if (element) {
                element.scrollIntoView({ behavior: "smooth" });
            }
        }
    }
});

export default router;

async function getRoutes(routes) {
    for (const route of routes) {
        const obj = await import(`../routes/${route.url}/${route.url}.js`);
        route.component = obj.default;
        route.name = obj.url;
        route.component.template = await importHtmlFile(`../v2/routes/${route.url}/${route.url}.html`);
    }

    return routes;
}

async function importHtmlFile(filePath) {
    try {
        // Fetch the HTML file
        const response = await fetch(filePath);
        if (!response.ok) throw new Error(`Failed to fetch file: ${response.statusText}`);

        // Get the file's text content
        const htmlContent = await response.text();

        // Extract the text content
        return htmlContent;
    } catch (error) {
        console.error("Error importing HTML file:", error);
        return null;
    }
}
