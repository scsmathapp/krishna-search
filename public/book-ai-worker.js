import { pipeline, env } from '/transformers.min.js';

env.allowLocalModels = false;

let biEncoder    = null;
let crossEncoder = null;
let allParagraphs = [];   // { bookTitle, chapterTitle, chunkParas, anchorText, tokens }
let allTokenSets  = [];   // parallel array of Set<string> — avoids property lookup in hot loop

// ─── Load both models ─────────────────────────────────────────────────────────

async function loadModel() {
    biEncoder = await pipeline(
        'feature-extraction',
        'Xenova/all-MiniLM-L6-v2',
        {
            quantized: true,
            progress_callback(p) {
                if (p.status === 'progress') {
                    self.postMessage({ type: 'progress', value: Math.round(p.progress * 0.5), label: 'Loading retrieval model…' });
                }
            },
        }
    );

    crossEncoder = await pipeline(
        'text-classification',
        'Xenova/ms-marco-MiniLM-L-6-v2',
        {
            quantized: true,
            progress_callback(p) {
                if (p.status === 'progress') {
                    self.postMessage({ type: 'progress', value: 50 + Math.round(p.progress * 0.5), label: 'Loading answer model…' });
                }
            },
        }
    );
}

// ─── Mean pool + L2 normalize ─────────────────────────────────────────────────

function processOutput(out) {
    const dims      = out.dims;           // [1, seqLen, hiddenSize]
    const seqLen    = dims[1];
    const hiddenSize = dims[2];
    const data      = out.data;
    const vec       = new Float32Array(hiddenSize);

    let t = 0;
    while (t < seqLen) {
        const offset = t * hiddenSize;
        let h = 0;
        while (h < hiddenSize) {
            vec[h] += data[offset + h];
            h++;
        }
        t++;
    }

    let norm = 0;
    let h = 0;
    while (h < hiddenSize) {
        vec[h] /= seqLen;
        norm += vec[h] * vec[h];
        h++;
    }
    norm = Math.sqrt(norm);
    h = 0;
    while (h < hiddenSize) {
        vec[h] /= norm;
        h++;
    }

    return vec; // Float32Array
}

async function biEmbed(text) {
    const out = await biEncoder(text, { pooling: 'none' });
    return processOutput(out);
}

// ─── Cosine similarity (Float32Array inputs) ──────────────────────────────────

function cosineSim(a, b) {
    let dot = 0, normA = 0, normB = 0;
    const len = a.length;
    let i = 0;
    while (i < len) {
        const ai = a[i], bi = b[i];
        dot   += ai * bi;
        normA += ai * ai;
        normB += bi * bi;
        i++;
    }
    return dot / (Math.sqrt(normA) * Math.sqrt(normB));
}

// ─── Tokeniser ────────────────────────────────────────────────────────────────

const STOP = new Set([
    'a','an','the','and','or','but','not','is','are','was','were','be',
    'been','have','has','had','do','does','did','will','would','could',
    'should','may','might','can','to','of','in','on','at','by','for',
    'with','about','from','this','that','these','those','it','its',
    'what','when','where','who','why','how','which','there','their',
    'they','them','then','than','into','over','after','just','tell',
    'give','show','explain','describe','find','know','want','get',
]);

function tokenizeToSet(text) {
    const words = text.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').split(/\s+/);
    const set = new Set();
    let i = 0;
    while (i < words.length) {
        const w = words[i];
        if (w.length > 2) set.add(w);
        i++;
    }
    return set;
}

function tokenizeQuery(query) {
    const words = query.toLowerCase().replace(/[^a-z0-9\s]/g, ' ').split(/\s+/);
    const tokens = [];
    let i = 0;
    while (i < words.length) {
        const w = words[i];
        if (w.length > 2 && !STOP.has(w)) tokens.push(w);
        i++;
    }
    return tokens;
}

// ─── Store books with contextual chunks ───────────────────────────────────────

function storeBooks(books) {
    allParagraphs = [];
    allTokenSets  = [];

    const WINDOW = 2;

    let bi = 0;
    while (bi < books.length) {
        const book     = books[bi];
        const chapters = book.chapters || [];

        let ci = 0;
        while (ci < chapters.length) {
            const chapter = chapters[ci];
            const rawParas = chapter.paragraphs || [];

            // First pass: collect valid paragraphs for this chapter
            const chapterParas = []; // { text, class }
            let pi = 0;
            while (pi < rawParas.length) {
                const para = rawParas[pi];
                const text = (para.text || '').trim();
                if (text.length >= 40 && text.split(/\s+/).length >= 6) {
                    chapterParas.push({ text, class: para.class || '' });
                }
                pi++;
            }

            // Second pass: build overlapping windows
            const cpLen = chapterParas.length;
            let i = 0;
            while (i < cpLen) {
                const start = i - WINDOW > 0 ? i - WINDOW : 0;
                const end   = i + WINDOW < cpLen ? i + WINDOW : cpLen - 1;

                const anchorText = chapterParas[i].text;

                // Build chunkParas without slice+spread
                const chunkParas = [];
                let j = start;
                while (j <= end) {
                    chunkParas.push(chapterParas[j]);
                    j++;
                }

                const tokens = tokenizeToSet(anchorText);

                allParagraphs.push({
                    bookTitle:    book.title,
                    chapterTitle: chapter.title,
                    chunkParas,
                    anchorText,
                });
                allTokenSets.push(tokens); // parallel array — avoids object property lookup in hot loop

                i++;
            }
            ci++;
        }
        bi++;
    }
}

// ─── Stage 1: keyword pre-filter ─────────────────────────────────────────────
// Returns indices into allParagraphs, sorted by hit count, capped at maxCandidates

function keywordFilter(qTokens, maxCandidates) {
    const qLen   = qTokens.length;
    const pLen   = allParagraphs.length;
    const hits   = new Int32Array(pLen);  // typed array — faster than plain Array
    const hasHit = new Uint8Array(pLen);  // flag array to avoid pushing zero-score entries

    let pi = 0;
    while (pi < pLen) {
        const tokenSet = allTokenSets[pi];
        let score = 0;
        let qi = 0;
        while (qi < qLen) {
            const qt = qTokens[qi];
            if (tokenSet.has(qt)) {
                score += 2;
            } else {
                for (const pt of tokenSet) {
                    if (pt.length > 2 && (pt.includes(qt) || qt.includes(pt))) {
                        score += 1;
                        break;
                    }
                }
            }
            qi++;
        }
        if (score > 0) {
            hits[pi]   = score;
            hasHit[pi] = 1;
        }
        pi++;
    }

    // Collect indices that had hits
    const indices = [];
    let i = 0;
    while (i < pLen) {
        if (hasHit[i]) indices.push(i);
        i++;
    }

    // Sort descending by score
    indices.sort((a, b) => hits[b] - hits[a]);

    // Return top candidates as paragraph objects
    const cap = indices.length < maxCandidates ? indices.length : maxCandidates;
    const result = [];
    let k = 0;
    while (k < cap) {
        result.push(allParagraphs[indices[k]]);
        k++;
    }
    return result;
}

// ─── Stage 2: bi-encoder semantic filter ─────────────────────────────────────
// Embeds anchorText (not full chunk) for accurate matching

async function biEncoderFilter(qVec, candidates, topN) {
    const len    = candidates.length;
    const scores = new Float32Array(len);

    let i = 0;
    while (i < len) {
        const vec = await biEmbed(candidates[i].anchorText);
        scores[i] = cosineSim(qVec, vec);
        i++;
    }

    // Build index array and sort by score descending
    const indices = new Uint32Array(len);
    i = 0;
    while (i < len) { indices[i] = i; i++; }
    indices.sort((a, b) => scores[b] - scores[a]);

    const cap    = len < topN ? len : topN;
    const result = [];
    i = 0;
    while (i < cap) {
        result.push(candidates[indices[i]]);
        i++;
    }
    return result;
}

// ─── Stage 3: cross-encoder QA reranking ─────────────────────────────────────
// Scores (question, anchorText) pairs — understands answer intent

async function crossEncoderRerank(query, candidates, topN) {
    const len    = candidates.length;
    const scores = new Float32Array(len);

    let i = 0;
    while (i < len) {
        const result = await crossEncoder(query, { text_pair: candidates[i].anchorText });
        scores[i] = Array.isArray(result)
            ? (result.find(r => r.label === 'true' || r.label === '1')?.score ?? result[0].score)
            : result.score;
        i++;
    }

    const indices = new Uint32Array(len);
    i = 0;
    while (i < len) { indices[i] = i; i++; }
    indices.sort((a, b) => scores[b] - scores[a]);

    const cap    = len < topN ? len : topN;
    const result = [];
    i = 0;
    while (i < cap) {
        if (scores[indices[i]] > 0.1) result.push(candidates[indices[i]]);
        i++;
    }
    return result;
}

// ─── Main search ──────────────────────────────────────────────────────────────

async function search(query, topN) {
    const qTokens = tokenizeQuery(query);

    self.postMessage({ type: 'status', label: 'Scanning books…' });
    const keywordCandidates = qTokens.length > 0
        ? keywordFilter(qTokens, 150)
        : allParagraphs.slice(0, 150);

    if (keywordCandidates.length === 0) return [];

    self.postMessage({ type: 'status', label: 'Finding relevant passages…' });
    const qVec        = await biEmbed(query);
    const biCandidates = await biEncoderFilter(qVec, keywordCandidates, 30);

    self.postMessage({ type: 'status', label: 'Finding best answers…' });
    return crossEncoderRerank(query, biCandidates, topN);
}

// ─── Greeting / meta ──────────────────────────────────────────────────────────

const GREETING_RE = [
    /^(hi|hello|hey|greetings|good\s(morning|afternoon|evening))[!?.,]?\s*$/i,
    /^how are you[!?.,]?\s*$/i,
    /^what'?s up[!?.,]?\s*$/i,
];
const META_RE = [
    /what can you do/i,
    /who are you/i,
    /what are you/i,
    /your (capabilities|features|purpose)/i,
    /^help(\s?me)?[!?.,]?\s*$/i,
];

// ─── Message handler ──────────────────────────────────────────────────────────

self.onmessage = async (e) => {
    const { type, payload } = e.data;

    if (type === 'load') {
        await loadModel();
        storeBooks(payload.books);
        self.postMessage({ type: 'ready' });
        return;
    }

    if (type === 'chat') {
        const msg = payload.message.trim();

        if (GREETING_RE.some(r => r.test(msg))) {
            self.postMessage({
                type: 'response',
                payload: {
                    text: "Hello! I'm your book assistant. Ask me anything about the books in your library.",
                    sources: [],
                },
            });
            return;
        }

        if (META_RE.some(r => r.test(msg))) {
            self.postMessage({
                type: 'response',
                payload: {
                    text: "I can answer questions about the books in your library. I'll quote the relevant passages directly — I never rephrase or summarise. I can't help with topics not covered in the books.",
                    sources: [],
                },
            });
            return;
        }

        const results = await search(msg, 3);

        if (results.length === 0) {
            self.postMessage({
                type: 'response',
                payload: {
                    text: "I don't have data about that topic in the books I have access to.",
                    sources: [],
                },
            });
            return;
        }

        self.postMessage({
            type: 'response',
            payload: { text: null, sources: results },
        });
    }
};