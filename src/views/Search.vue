<template>
    <div>
        <BookData :searchCount="searchCount"
                  :bookCount="bookCount"
                  :searchProgress="searchProgress"
                  :books="searchResultsObj"
                  :selectedItemCode="selectedItemCode"
                  :selectedBook="selectedBook"
                  pageType="results"
                  @selectBookContent="selectSearch"></BookData>
    </div>
</template>
<script>
import {mapGetters} from 'vuex';
import allBooks from '@/assets/books/allBooks.js';
import BookData from "../components/BookData.vue";

export default {
    components: {BookData},
    computed: {
        ...mapGetters(['books', 'fontSize'])
    },
    data() {
        return {
            searchResultsObj: {},
            selectedBook: {},
            searchCount: 0,
            bookCount: 0,
            selectedItemCode: '',
            searchProgress: true,
            menuDisplay: false
        }
    },
    beforeDestroy() {
        this.books.forEach(book => {
            book.chapters.forEach(chapter => {
                chapter.paragraphs.forEach(paragraph => {
                    paragraph.highlightedText = '';
                });
            });
        });
    },
    created() {
        const vm = this;

        if (!vm.$route.query || !vm.$route.query.q) {
            vm.$router.push('/');
            return;
        }

        vm.filterBooks();
    },
    methods: {
        filterBooks() {
            const vm = this,
                searchVal = vm.$route.query ? vm.$route.query.q : '',
                tokens = tokenize(searchVal),
                ast = parse(tokens),
                filteredBooks = vm.$route.query && vm.$route.query.b ? vm.$route.query.b.split('_') : [],
                filterMap = {};

            const sanskritDiacriticMap = {
                'ā': 'a',  'Ā': 'a',
                'ī': 'i',  'Ī': 'i',
                'ū': 'u',  'Ū': 'u',
                'ṛ': 'r',  'Ṛ': 'r',
                'ṝ': 'r',  'Ṝ': 'r',
                'ḷ': 'l',  'Ḷ': 'l',
                'ḹ': 'l',  'Ḹ': 'l',
                'ṁ': 'm',  'Ṁ': 'm',
                'ṃ': 'm',  'Ṃ': 'm',
                'ḥ': 'h',  'Ḥ': 'h',
                'ṅ': 'n', 'Ṅ': 'n',
                'ṭ': 't',  'Ṭ': 't',
                'ḍ': 'd',  'Ḍ': 'd',
                'ṇ': 'n',  'Ṇ': 'n',
                'ṣ': 's', 'Ṣ': 's',
                'ś': 's', 'Ś': 's',
                'ñ': 'n', 'Ñ': 'n',
                'jñ':'jn',
                'ḻ': 'l',  'Ḻ': 'l',
                'ṯ': 't',  'Ṯ': 't',
            };

            // Only lowercase keys needed — buildSanskritPattern lowercases everything before lookup
            const romanToSanskritMap = {
                // Two-character (must come before single-char to be checked first)
                'sh': ['ṣ', 'ś'],
                'ri': ['ṛ', 'ṝ', 'rī'],
                'gy': ['jñ'],
                'aa': ['ā'],
                'ee': ['ī'],
                'oo': ['ū'],
                // Single characters
                's':  ['ṣ', 'ś'],   // bare s also matches diacritic forms
                'r':  ['ṛ', 'ṝ'],   // bare r also matches diacritic forms
                'a':  ['ā'],
                'i':  ['ī'],
                'u':  ['ū'],
                'l':  ['ḷ', 'ḹ', 'ḻ'],
                'm':  ['ṁ', 'ṃ'],
                'h':  ['ḥ'],
                'n':  ['ṇ', 'ñ', 'ṅ'],
                't':  ['ṭ', 'ṯ'],
                'd':  ['ḍ'],
            };

            let bookIndex = 0;

            vm.searchResultsObj = {};
            vm.searchCount = 0;
            vm.bookCount = 0;
            vm.selectedBook = {};
            vm.selectedItemCode = '';

            if (!searchVal || !vm.books.length) {
                return;
            }

            // Pre-build patterns for all tokens
            const patternCache = {};

            vm.searchProgress = true;

            if (filteredBooks.length) {
                filteredBooks.forEach(bIndex => {
                    if (allBooks[bIndex]) {
                        filterMap[bIndex] = true;
                    }
                });

                vm.$store.commit('SET_FILTERED_BOOKS', filteredBooks);
                vm.$store.dispatch('resetAuthorsFilterList');
            }

            while (bookIndex < vm.books.length) {
                const book = vm.books[bookIndex];
                let chapterIndex = 0;

                if (!filteredBooks.length || filterMap[book.bookIndex]) {
                    while (chapterIndex < book.chapters.length) {
                        const chapter = book.chapters[chapterIndex];
                        let paragraphIndex = 0;

                        while (paragraphIndex < chapter.paragraphs.length) {
                            const paragraph = chapter.paragraphs[paragraphIndex];
                            const matchResult = evaluate(ast, paragraph.text);

                            vm.$set(paragraph, 'highlightedText', '');

                            if (matchResult && matchResult.matched) {
                                paragraph.highlightedText = highlightMatches(paragraph.text, matchResult.matches);

                                if (!vm.searchResultsObj[book.bookIndex]) {
                                    vm.bookCount++;

                                    vm.searchResultsObj[book.bookIndex] = {
                                        title: book.title,
                                        code: book.code,
                                        chapters: {}
                                    }
                                }

                                if (!vm.searchResultsObj[book.bookIndex].chapters[chapterIndex]) {
                                    vm.searchResultsObj[book.bookIndex].chapters[chapterIndex] = {
                                        title: chapter.title,
                                        paragraphs: {}
                                    }
                                }

                                vm.searchResultsObj[book.bookIndex].chapters[chapterIndex]
                                    .paragraphs[paragraphIndex] = {
                                    text: extractHighlightedSnippets(paragraph.highlightedText),
                                    class: paragraph.class
                                };

                                vm.searchCount++;
                            }
                            paragraphIndex++;
                        }
                        chapterIndex++;
                    }
                }

                bookIndex++;
            }

            if (window.innerWidth < 991.98) {
                vm.menuDisplay = true;
            }

            vm.searchProgress = false;

            function normalizeSanskrit(text) {
                let result = text.toLowerCase();
                // IMPORTANT: multi-character diacritics (jñ) must be replaced before single ones
                // to avoid partial replacements
                const entries = Object.entries(sanskritDiacriticMap)
                    .sort((a, b) => b[0].length - a[0].length);
                for (const [diacritic, plain] of entries) {
                    result = result.split(diacritic.toLowerCase()).join(plain);
                }
                return result;
            }

            function buildSanskritPattern(term) {
                const normalized = normalizeSanskrit(term.toLowerCase());
                let pattern = '';
                let i = 0;

                while (i < normalized.length) {
                    const twoChar = normalized.slice(i, i + 2);
                    const oneChar = normalized[i];

                    if (romanToSanskritMap[twoChar]) {
                        const alternatives = romanToSanskritMap[twoChar]
                            .map(d => escapeRegex(d))
                            .join('|');
                        pattern += `(?:${escapeRegex(twoChar)}|${alternatives})`;
                        i += 2;
                    } else if (romanToSanskritMap[oneChar]) {
                        const alternatives = romanToSanskritMap[oneChar]
                            .map(d => escapeRegex(d))
                            .join('|');
                        pattern += `(?:${escapeRegex(oneChar)}|${alternatives})`;
                        i += 1;
                    } else {
                        pattern += escapeRegex(oneChar);
                        i += 1;
                    }
                }

                return new RegExp(pattern, 'gi');
            }

            function escapeRegex(str) {
                return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            }

            function tokenize(query) {
                const tokens = [];
                // CHANGE: This regex captures any non-whitespace sequence except operators
                // It will match symbols at the beginning, middle, or end of terms
                const regex = /("[^"]+"|\+|\||\(|\)|[^\s+|()"]+)/g;
                let match;

                while ((match = regex.exec(query)) !== null) {
                    let token = match[0];

                    // Remove quotes from quoted phrases
                    if (token.startsWith('"') && token.endsWith('"')) {
                        token = token.slice(1, -1);
                    }

                    tokens.push(token);
                }

                // Combine consecutive non-operator tokens into phrases
                const combinedTokens = [];
                let phraseBuffer = [];

                for (const token of tokens) {
                    if (['+', '|', '(', ')'].includes(token)) {
                        if (phraseBuffer.length > 0) {
                            combinedTokens.push(phraseBuffer.join(' '));
                            phraseBuffer = [];
                        }
                        combinedTokens.push(token);
                    } else {
                        phraseBuffer.push(token);
                    }
                }

                if (phraseBuffer.length > 0) {
                    combinedTokens.push(phraseBuffer.join(' '));
                }

                return combinedTokens;
            }

            function getPattern(token) {
                if (!patternCache[token]) {
                    patternCache[token] = buildSanskritPattern(token);
                }
                return patternCache[token];
            }

            function evaluate(ast, paragraph) {
                const stack = [];

                for (const token of ast) {
                    if (['+', '|'].includes(token)) {
                        const right = stack.pop();
                        const left = stack.pop();
                        switch (token) {
                            case '+':
                                stack.push(left && right
                                    ? { matched: true, matches: [...left.matches, ...right.matches] }
                                    : false);
                                break;
                            case '|':
                                if (left || right) {
                                    const matches = [];
                                    if (left)  matches.push(...left.matches);
                                    if (right) matches.push(...right.matches);
                                    stack.push({ matched: true, matches });
                                } else {
                                    stack.push(false);
                                }
                                break;
                        }
                    } else {
                        const re = getPattern(token);
                        re.lastIndex = 0; // CRITICAL — reset lastIndex before each paragraph
                        const matches = [];
                        let m;
                        while ((m = re.exec(paragraph)) !== null) {
                            matches.push({ term: m[0], index: m.index });
                        }
                        stack.push(matches.length > 0 ? { matched: true, matches } : false);
                    }
                }

                return stack.pop();
            }

            // Updated parse function (no changes needed, but included for completeness)
            function parse(tokens) {
                const output = [];
                const operators = [];
                const precedence = {'+': 2, '|': 1};

                for (const token of tokens) {
                    if (token === '(') {
                        operators.push(token);
                    } else if (token === ')') {
                        while (operators.length > 0 && operators[operators.length - 1] !== '(') {
                            output.push(operators.pop());
                        }
                        operators.pop(); // Remove '('
                    } else if (['+', '|'].includes(token)) {
                        while (
                            operators.length > 0 &&
                            operators[operators.length - 1] !== '(' &&
                            precedence[operators[operators.length - 1]] >= precedence[token]
                            ) {
                            output.push(operators.pop());
                        }
                        operators.push(token);
                    } else {
                        output.push(token); // Term or phrase
                    }
                }

                while (operators.length > 0) {
                    output.push(operators.pop());
                }

                return output;
            }

            function highlightMatches(paragraph, matches) {
                let highlightedParagraph = paragraph;

                // Sort matches by index in reverse order to avoid overlapping issues
                matches.sort((a, b) => b.index - a.index);

                for (const match of matches) {
                    const {term, index} = match;
                    const start = index;
                    const end = start + term.length;

                    highlightedParagraph =
                        highlightedParagraph.slice(0, start) +
                        `<span class="highlight">${highlightedParagraph.slice(start, end)}</span>` +
                        highlightedParagraph.slice(end);
                }

                return highlightedParagraph;
            }

            function extractHighlightedSnippets(htmlText) {
                // Find all highlighted spans
                const highlightRegex = /<span class="highlight">(.*?)<\/span>/gi;
                const highlights = [];
                let match;

                while ((match = highlightRegex.exec(htmlText)) !== null) {
                    highlights.push({
                        start: match.index,
                        end: match.index + match[0].length,
                        content: match[1]
                    });
                }

                if (highlights.length === 0) return '';

                // Get plain text for word counting
                const plainText = htmlText.replace(/<[^>]+>/g, '');
                const words = plainText.match(/\S+/g) || [];

                // Find word positions of highlights in plain text
                const highlightWordPositions = [];

                for (const highlight of highlights) {
                    // Find the position of this highlight in the plain text
                    const beforeHighlight = htmlText.substring(0, highlight.start).replace(/<[^>]+>/g, '');
                    const wordsBeforeHighlight = beforeHighlight.match(/\S+/g) || [];

                    const highlightPlainText = highlight.content;
                    const wordsInHighlight = highlightPlainText.match(/\S+/g) || [];

                    const startWordIndex = wordsBeforeHighlight.length;
                    const endWordIndex = startWordIndex + wordsInHighlight.length - 1;

                    highlightWordPositions.push({start: startWordIndex, end: endWordIndex});
                }

                // Merge overlapping or close ranges and expand context
                highlightWordPositions.sort((a, b) => a.start - b.start);
                const ranges = [];

                for (const pos of highlightWordPositions) {
                    const expandedStart = Math.max(0, pos.start - 5);
                    const expandedEnd = Math.min(words.length - 1, pos.end + 5);

                    if (ranges.length === 0 || expandedStart > ranges[ranges.length - 1].end + 10) {
                        ranges.push({start: expandedStart, end: expandedEnd});
                    } else {
                        ranges[ranges.length - 1].end = Math.max(ranges[ranges.length - 1].end, expandedEnd);
                    }
                }

                // Extract snippets by finding the corresponding HTML for each range
                let result = '';

                for (let i = 0; i < ranges.length; i++) {
                    const range = ranges[i];

                    if (i > 0) result += ' ... ';
                    if (range.start > 0) result += '... ';

                    // Find the HTML that corresponds to this word range
                    const snippet = extractHtmlForWordRange(htmlText, range.start, range.end, words);
                    result += snippet;

                    if (range.end < words.length - 1) result += ' ...';
                }

                return result.trim();
            }

            function extractHtmlForWordRange(htmlText, startWordIndex, endWordIndex, allWords) {
                // This is a simpler approach: reconstruct the range from the word indices
                const targetText = allWords.slice(startWordIndex, endWordIndex + 1).join(' ');

                // Find this text sequence in the HTML while preserving highlights
                const plainText = htmlText.replace(/<[^>]+>/g, '');
                const words = plainText.match(/\S+/g) || [];

                // Get the character positions for the start and end of our word range
                let charStart = 0;
                let charEnd = plainText.length;

                // Find character position of start word
                if (startWordIndex > 0) {
                    const textBeforeStart = words.slice(0, startWordIndex).join(' ');
                    charStart = plainText.indexOf(textBeforeStart);
                    if (charStart !== -1) {
                        charStart += textBeforeStart.length;
                        // Skip whitespace
                        while (charStart < plainText.length && /\s/.test(plainText[charStart])) {
                            charStart++;
                        }
                    } else {
                        charStart = 0;
                    }
                }

                // Find character position of end word
                const textUpToEnd = words.slice(0, endWordIndex + 1).join(' ');
                const endMatch = plainText.indexOf(textUpToEnd);
                if (endMatch !== -1) {
                    charEnd = endMatch + textUpToEnd.length;
                }

                // Now extract the HTML that corresponds to these character positions
                // We'll do this by walking through the HTML and tracking our position in the plain text
                let htmlResult = '';
                let plainTextPos = 0;
                let inRange = false;

                const htmlTokens = htmlText.split(/(<[^>]*>)/);

                for (const token of htmlTokens) {
                    if (token.startsWith('<')) {
                        // This is an HTML tag
                        if (inRange) {
                            htmlResult += token;
                        }
                    } else {
                        // This is text content
                        for (let i = 0; i < token.length; i++) {
                            const char = token[i];

                            if (plainTextPos === charStart) {
                                inRange = true;
                            }

                            if (inRange) {
                                htmlResult += char;
                            }

                            if (plainTextPos === charEnd - 1) {
                                inRange = false;
                                return htmlResult;
                            }

                            plainTextPos++;
                        }
                    }
                }

                return htmlResult;
            }
        },
        selectSearch(bookIndex, chapterIndex, paragraphIndex) {
            const vm = this;
            let timeoutVal = 0;
            
            bookIndex = parseInt(bookIndex);

            if (vm.selectedBook.bookIndex !== bookIndex) {
                vm.selectedBook = vm.books.find(book => book.bookIndex === bookIndex);
                timeoutVal = 200;
            }

            vm.selectedItemCode = bookIndex + '-' + chapterIndex + '-' + paragraphIndex;

            setTimeout(() => {
                const element = document.getElementById(chapterIndex + '-' + paragraphIndex);

                if (element) {
                    element.scrollIntoView({block: "start", behavior: "instant"});
                    vm.menuDisplay = false;
                }
            }, timeoutVal);
        },
        setMenuDisplay(value) {
            this.menuDisplay = value;
        }
    },
    watch: {
        '$route.query'() {
            this.filterBooks();
        },
        books(newBooks) {
            if (newBooks.length > 0) {
                this.filterBooks();
            }
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/book.scss';
</style>