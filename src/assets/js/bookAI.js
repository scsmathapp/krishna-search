// src/utils/bookAI.js

let worker = null;
let onSearching = null; // callback when semantic search starts

function getWorker() {
    if (worker) return worker;
    worker = new Worker('/book-ai-worker.js', { type: 'module' });
    return worker;
}

export function loadAndIndex(books, onProgress) {
    return new Promise((resolve) => {
        const w = getWorker();

        w.onmessage = (e) => {
            const msg = e.data;
            if (msg.type === 'progress') {
                onProgress && onProgress({ pct: msg.value, label: msg.label });
            }
            if (msg.type === 'ready') {
                resolve();
            }
        };

        w.postMessage({ type: 'load', payload: { books } });
    });
}

export function chat(message, onStatus) {
    return new Promise((resolve) => {
        const w = getWorker();

        w.onmessage = (e) => {
            if (e.data.type === 'status') {
                onStatus && onStatus(e.data.label);
            }
            if (e.data.type === 'response') {
                resolve(e.data.payload);
            }
        };

        w.postMessage({ type: 'chat', payload: { message } });
    });
}