<template>
    <div>
        <div class="d-flex book">
            <div class="w-25 overflow-y-auto paragraph-title-list">
                <b v-if="searchCount || searchCount === 0" class="d-flex justify-content-center m-2">
                    {{ searchCount }} results found
                </b>
                <div class="spinner" v-if="searchProgress"></div>
                <ul class="list-group list-group-flush" v-else>
                    <div v-for="book in searchResultsObj">
                        <h5 class="p-2 d-flex position-sticky top-0 z-3 mb-0">
                            <span class="flex-fill">{{ book.title }}</span>
                            <a :href="`/#/book/${book.code}`"><i class="fa-solid fa-arrow-up-right-from-square"></i></a>
                        </h5>
                        <li class="list-group-item list-group-item-action p-2"
                            v-for="paragraph in book.paragraphs"
                            :class="selectedBookCode === book.code + '-' + paragraph.chapterIndex + '-' + paragraph.paragraphIndex ? 'active' : ''"
                            @click="selectSearch(paragraph, book.code)"
                            v-html="paragraph.text">
                        </li>
                    </div>
                </ul>
            </div>
            <div class="paragraph-list flex-fill d-flex flex-column align-items-center">
                <section v-for="(chapter, chapterId) in selectedBook.chapters" class="paragraph-section"
                         style="width: 85%;">
                    <div class="paragraph" v-for="(paragraph, paragraphId) in chapter.paragraphs">
                        <div v-html="paragraph.highlightedText || paragraph.text" :class="paragraph.class"
                             :id="chapterId + '-' + paragraphId"></div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</template>
<script>
import { mapGetters } from 'vuex';

export default {
    computed: {
        ...mapGetters(['books'])
    },
    data() {
        return {
            searchVal: '',
            searchResultsObj: {},
            selectedBook: {},
            searchCount: 0,
            selectedBookCode: '',
            searchProgress: true
        }
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
            const vm = this;
            let searchVal = vm.$route.query ? vm.$route.query.q : '';

            vm.searchResultsObj = {};
            vm.searchCount = 0;
            vm.selectedBook = {};
            vm.selectedBookCode = '';

            if (searchVal && vm.books.length) {
                const tokens = tokenize(removeDiacritics(searchVal));
                const ast = parse(tokens);
                let bookIndex = 0;

                vm.searchProgress = true;

                while (bookIndex < vm.books.length) {
                    const book = vm.books[bookIndex];
                    let chapterIndex = 0;

                    while (chapterIndex < book.chapters.length) {
                        const chapter = book.chapters[chapterIndex];
                        let paragraphIndex = 0;

                        while (paragraphIndex < chapter.paragraphs.length) {
                            const paragraph = chapter.paragraphs[paragraphIndex];
                            const matchResult = evaluate(ast, removeDiacritics(paragraph.text));

                            paragraph.highlightedText = '';

                            if (matchResult && matchResult.matched) {
                                paragraph.highlightedText = highlightMatches(paragraph.text, matchResult.matches);

                                if (!vm.searchResultsObj[book.code]) {
                                    vm.searchResultsObj[book.code] = {
                                        title: book.title,
                                        code: book.code,
                                        paragraphs: []
                                    }
                                }

                                vm.searchResultsObj[book.code].paragraphs.push({
                                    chapter: chapter.title,
                                    paragraphIndex: paragraphIndex,
                                    chapterIndex: chapterIndex,
                                    text: extractHighlightedSnippets(paragraph.highlightedText),
                                    class: paragraph.class
                                });

                                vm.searchCount++;
                            }
                            paragraphIndex++;
                        }
                        chapterIndex++;
                    }
                    bookIndex++;
                }
                
                vm.searchProgress = false;
            }
            
            ////////////////////////

            function removeDiacritics(text) {
                return text.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function evaluate(ast, paragraph) {
                const stack = [];
                const lowerParagraph = paragraph.toLowerCase();

                for (const token of ast) {
                    if (['+', '-', '|'].includes(token)) {
                        const right = stack.pop();
                        const left = stack.pop();

                        switch (token) {
                            case '+':
                                // For AND operation, combine matches from both sides
                                if (left && right) {
                                    stack.push({matched: true, matches: [...left.matches, ...right.matches]});
                                } else {
                                    stack.push(false);
                                }
                                break;
                            case '-':
                                // For NOT operation, return left matches if right is not present
                                if (left && !right) {
                                    stack.push(left);
                                } else {
                                    stack.push(false);
                                }
                                break;
                            case '|':
                                // For OR operation, combine matches from both sides
                                if (left || right) {
                                    const matches = [];
                                    if (left) matches.push(...left.matches);
                                    if (right) matches.push(...right.matches);
                                    stack.push({matched: true, matches});
                                } else {
                                    stack.push(false);
                                }
                                break;
                        }
                    } else {
                        // Find all occurrences of the term in the paragraph
                        const term = token.toLowerCase();
                        const matches = [];
                        let index = lowerParagraph.indexOf(term);

                        while (index !== -1) {
                            matches.push({term: token, index});
                            index = lowerParagraph.indexOf(term, index + term.length);
                        }

                        stack.push(matches.length > 0 ? {matched: true, matches} : false);
                    }
                }

                return stack.pop();
            }

            function parse(tokens) {
                const output = [];
                const operators = [];
                const precedence = {'+': 2, '-': 2, '|': 1};

                for (const token of tokens) {
                    if (token === '(') {
                        operators.push(token);
                    } else if (token === ')') {
                        while (operators.length > 0 && operators[operators.length - 1] !== '(') {
                            output.push(operators.pop());
                        }
                        operators.pop(); // Remove '('
                    } else if (['+', '-', '|'].includes(token)) {
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

            function tokenize(query) {
                const tokens = [];
                const regex = /("[^"]+"|\+|\-|\||\(|\)|\b\w+\b)/g;
                let match;

                while ((match = regex.exec(query)) !== null) {
                    const token = match[0].replace(/"/g, ''); // Remove quotes from phrases
                    tokens.push(token);
                }

                // Combine consecutive non-operator tokens into phrases
                const combinedTokens = [];
                let phraseBuffer = [];

                for (const token of tokens) {
                    if (['+', '-', '|', '(', ')'].includes(token)) {
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
        selectSearch(paragraph, bookCode) {
            const vm = this;
            let timeoutVal = 0;

            if (vm.selectedBook.code !== bookCode) {
                vm.selectedBook = vm.books.find(book => book.code === bookCode);
                timeoutVal = 200;
            }

            vm.selectedBookCode = bookCode + '-' + paragraph.chapterIndex + '-' + paragraph.paragraphIndex;

            setTimeout(() => {
                const element = document.getElementById(paragraph.chapterIndex + '-' + paragraph.paragraphIndex);

                if (element) {
                    element.scrollIntoView({block: "start"});
                    vm.menuDisplay = false;
                }
            }, timeoutVal);
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