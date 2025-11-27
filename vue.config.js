module.exports = {
    lintOnSave: false,
    outputDir: 'dist',
    // Place all static files (JS, CSS, assets) into 'src' folder inside 'dist'
    assetsDir: 'v2',
    // Modify how index.html refers to these assets
    publicPath: './',
    pwa: {
        name: 'Krishna Search',
        themeColor: '#012863',
        workboxPluginMode: 'GenerateSW',
        workboxOptions: {
            // 1. Force the new worker to immediately skip the waiting phase
            skipWaiting: true,
            // 2. Ensure the worker takes control of existing clients (tabs)
            clientsClaim: true
        }
    }
}