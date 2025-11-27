/* eslint-disable no-console */

import {register} from 'register-service-worker'

if (process.env.NODE_ENV === 'production') {
    register(`${process.env.BASE_URL}service-worker.js`, {
        ready() {
            console.log(
                'App is being served from cache by a service worker.\n' +
                'For more details, visit https://goo.gl/AFskqB'
            )
        },
        registered() {
            console.log('Service worker has been registered.')
        },
        cached() {
            console.log('Content has been cached for offline use.')
        },
        updatefound() {
            console.log('New content is downloading.')
        },
        updated() {
            console.log('New content is available. Auto-reloading...')

            // The 'updated' hook means a new Service Worker has been installed.
            // Since skipWaiting: true is set, it will activate shortly.

            // We listen for the Service Worker to officially take control (transition to 'activated' state).
            if (registration.waiting) {
                registration.waiting.addEventListener('statechange', (event) => {
                    if (event.target.state === 'activated') {
                        console.log('New Service Worker activated. Reloading page.')
                        // Once the new worker is active, reload the page immediately 
                        // to fetch the new application code/assets.
                        window.location.reload();
                    }
                })
            }
        },
        offline() {
            console.log('No internet connection found. App is running in offline mode.')
        },
        error(error) {
            console.error('Error during service worker registration:', error)
        }
    })
}
