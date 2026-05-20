<template>
    <div class="chat-wrap">
        <!-- Loading overlay -->
        <div v-if="loading" class="chat-loader">
            <div class="loader-box">
                <div class="loader-icon">📚</div>
                <p>{{ loadLabel || 'Loading…' }}</p>
                <div class="progress-bar">
                    <div class="progress-fill" :style="{ width: loadProgress + '%' }"></div>
                </div>
                <small>{{ loadProgress }}% — downloads once, cached offline</small>
            </div>
        </div>

        <!-- Chat history -->
        <div class="chat-messages" ref="msgBox">
            <div
                v-for="(msg, i) in messages"
                :key="i"
                :class="['chat-msg', msg.role]"
            >
                <div class="bubble">
                    <p v-if="msg.text">{{ msg.text }}</p>

                    <!-- Book quotes -->
                    <div v-if="msg.sources && msg.sources.length" class="sources book">
                        <div v-for="(src, j) in msg.sources" :key="j"
                             :class="['source-card', 'paragraph-list', src.openFlag ? '' : 'limit-src']"
                             style="font-size: 50%;">
                            <div class="source-meta">
                                📖 <strong>{{ src.bookTitle }}</strong> — {{ src.chapterTitle }}
                            </div>
                            <p v-for="(para, k) in src.chunkParas"
                               :key="k"
                               :class="para.class"
                               v-html="para.text"></p>
                            <div class="show-more" v-if="!src.openFlag" @click.stop="$set(src, 'openFlag', true);">Show more...</div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="thinking" class="chat-msg assistant">
                <div class="bubble thinking-wrap">
                    <span class="thinking-text">{{ thinkingMsg }}</span>
                    <span class="dot"></span><span class="dot"></span><span class="dot"></span>
                </div>
            </div>
        </div>

        <!-- Input bar -->
        <div>
            <div class="d-flex flex-column align-items-center justify-content-center m-2 info-text">
                <span>
                    <i class="fa fa-info-circle"></i>
                    AI can make mistakes. Verify the books by searching the AI responses received to confirm.
                </span>
                <span>
                    <i class="fa fa-info-circle"></i>
                    Refreshing will lose the chat history. Please copy the AI responses if you wish to save them.
                </span>
            </div>
            <div class="chat-input-row position-relative">
                <input
                    v-model="userInput"
                    @keyup.enter="send"
                    placeholder="Ask something about the books…"
                    :disabled="loading || thinking"
                    class="chat-input ks-border"
                />
                <button @click="send" :disabled="loading || thinking || !userInput.trim()"
                        class="ks-button position-absolute end-0 top-0 d-flex align-items-center cursor-pointer">
                    <i class="fas fa-paper-plane fs-5 m-3"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { loadAndIndex, chat } from '@/assets/js/bookAI';
import {mapGetters} from "vuex";

export default {
    name: 'BookChat',
    computed: {
        ...mapGetters(['books'])
    },
    data() {
        return {
            loading: true,
            loadProgress: 0,
            loadLabel: 'Loading AI models…',
            thinking: false,
            thinkingMsg: 'Thinking…',
            userInput: '',
            messages: [
                {
                    role: 'assistant',
                    text: "Hi! I'm your book assistant. Ask me anything about your library.",
                    sources: [],
                },
            ],
        };
    },
    mounted() {
        this.waitForData();
    },
    methods: {
        async waitForData() {
            if (this.books && this.books.length) {
                await loadAndIndex(this.books, ({ pct, label }) => {
                    this.loadProgress = pct;
                    this.loadLabel = label;
                });
                this.loading = false;
            } else {
                setTimeout(() => this.waitForData(), 100);
            }
        },
        async send() {
            const msg = this.userInput.trim();
            if (!msg || this.thinking) return;

            this.messages.push({ role: 'user', text: msg, sources: [] });
            this.userInput = '';
            this.thinking = true;
            this.thinkingMsg = 'Thinking…';
            this.$nextTick(() => this.scrollBottom());

            const response = await chat(msg, (label) => {
                this.thinkingMsg = label;
            });

            this.messages.push(
                response.text
                    ? { role: 'assistant', text: response.text, class: response.class, sources: [] }
                    : { role: 'assistant', text: 'Here is what I found in the books:', sources: response.sources }
            );

            this.thinking = false;
            this.$nextTick(() => this.scrollBottom());
        },
        scrollBottom() {
            const box = this.$refs.msgBox;
            if (box) box.scrollTop = box.scrollHeight;
        },
    },
};
</script>

<style lang="scss" scoped>
@import '@/assets/style/book.scss';

.chat-wrap {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 100px);
    max-width: 760px;
    margin: 0 auto;
    overflow: hidden;
    padding-bottom: 10px;
}

/* Loader */
.chat-loader {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}
.loader-box {
    text-align: center;
    padding: 2rem;
}
.loader-icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
.progress-bar {
    border-radius: 99px;
    height: 8px;
    width: 220px;
    margin: 0.75rem auto 0.25rem;
    overflow: hidden;
}
.progress-fill {
    height: 100%;
    background: $primary;
    border-radius: 99px;
    transition: width 0.3s;
}

/* Messages */
.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    border-bottom: 1px solid $primary;
}
.chat-msg { display: flex; }
.chat-msg.user { justify-content: flex-end; }
.chat-msg.assistant { justify-content: flex-start; }

.bubble {
    max-width: 85%;
    padding: 0.75rem 1rem;
    border-radius: 20px;
    line-height: 1.6;
    font-size: 0.95rem;
}
.chat-msg.user .bubble {
    background: $primary;
    color: #fff;
    border-bottom-right-radius: 2px;
}
.chat-msg.assistant .bubble {
    background: #fff;
    border: 1px solid $primary;
    border-bottom-left-radius: 2px;
    color: #2c2416;
}

/* Sources / quotes */
.sources { margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.75rem; }
.source-card {
    background: $secondary;
    border-left: 3px solid $primary;
    border-radius: 20px;
    padding: 0.6rem 0.8rem;
    position: relative;
    max-height: unset;
    height: unset !important;
    
    &.limit-src {
        max-height: 200px;
        overflow: hidden;
        cursor: pointer;
    }
    
    .show-more {
        color: white;
        font-weight: bold;
        font-size: 1.2rem;
        position: absolute;
        display: flex;
        justify-content: center;
        align-items: flex-end;
        background-image: linear-gradient(transparent, transparent, rgba(0,0,0,0.5));
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }
}
.source-meta { font-size: 0.8rem; margin-bottom: 0.4rem; }
blockquote {
    margin: 0;
    font-style: italic;
    font-size: 0.92rem;
    line-height: 1.65;
}

/* Thinking dots */
.thinking { display: flex; gap: 6px; align-items: center; padding: 0.9rem 1.2rem; }
.thinking span {
    width: 8px; height: 8px;
    background: $primary;
    border-radius: 50%;
    animation: bounce 1.2s infinite ease-in-out;
}
.thinking span:nth-child(2) { animation-delay: 0.2s; }
.thinking span:nth-child(3) { animation-delay: 0.4s; }
@keyframes bounce {
    0%, 80%, 100% { transform: translateY(0); }
    40% { transform: translateY(-8px); }
}

.thinking-wrap {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 0.9rem 1.2rem;
}
.thinking-text {
    font-size: 0.85rem;
    font-style: italic;
}
.dot {
    width: 7px; height: 7px;
    background: $primary;
    border-radius: 50%;
    animation: bounce 1.2s infinite ease-in-out;
}
.dot:nth-child(3) { animation-delay: 0.2s; }
.dot:nth-child(4) { animation-delay: 0.4s; }

/* Input row */
.chat-input-row {
    display: flex;
    gap: 0.5rem;
    background: #fff;
    align-items: center;
    
    button {
        margin: 2px 4px 0 0;
        width: 40px !important;
        min-width: 0 !important;
    }
}
.chat-input {
    flex: 1;
    padding: 0.6rem 0.9rem;
    font-size: 0.95rem;
    font-family: inherit;
    outline: none;
}
.chat-input:focus { border-color: #a0845c; }
.chat-send:hover:not(:disabled) { background: #7a6040; }
.chat-send:disabled { opacity: 0.45; cursor: not-allowed; }

.info-text {
    font-size: 12px;
}
</style>