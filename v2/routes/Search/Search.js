import allBooks from '../../utils/allBooks.js';

export default {
    data() {
        return {
            searchVal: '',
            books: {},
            searchResultsObj: {},
            selectedBook: {}
        }
    },
    created() {
        const vm = this;
        
        if (!vm.$route.query || !vm.$route.query.q) {
            vm.$router.push('/');
            return;
        }

        // Fetch each JSON file
        const promises = allBooks.map(file => {
            return fetch(`../../assets/books/json/${file}`)
                .then(response => response.json())
                .catch(error => {
                    console.error(`Failed to load ${file}:`, error);
                    return null;
                });
        });

        Promise.all(promises)
            .then(data => {
                vm.books = data; // Array of JSON objects
                vm.filterBooks();
            });
    },
    methods: {
        filterBooks() {
            const vm = this;
            const searchVal = vm.$route.query ? vm.$route.query.q : '';

            vm.searchResultsObj = {};

            if (searchVal) {
                const tokens = tokenize(removeDiacritics(searchVal));
                const ast = parse(tokens);
                let bookIndex = 0;

                while (bookIndex < vm.books.length) {
                    const book = vm.books[bookIndex];
                    let chapterIndex = 0;

                    while (chapterIndex < book.chapters.length) {
                        const chapter = book.chapters[chapterIndex];
                        let paragraphIndex = 0;

                        while (paragraphIndex < chapter.paragraphs.length) {
                            const paragraph = chapter.paragraphs[paragraphIndex];
                            const matchResult = evaluate(ast, removeDiacritics(paragraph.text));

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
                            }
                            paragraphIndex++;
                        }
                        chapterIndex++;
                    }
                    bookIndex++;
                }
            }

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
                const tagRegex = /<[^>]+>/g;
                const wordRegex = /\p{L}+/gu;

                const tokens = [];
                const highlights = [];

                let index = 0;
                let buffer = '';
                let lastIndex = 0;
                let match;

                // Tokenize with tag tracking
                while ((match = tagRegex.exec(htmlText)) !== null) {
                    const between = htmlText.slice(lastIndex, match.index);
                    let wordMatch;
                    while ((wordMatch = wordRegex.exec(between)) !== null) {
                        tokens.push({
                            type: 'word',
                            text: wordMatch[0],
                            index: lastIndex + wordMatch.index,
                            raw: wordMatch[0]
                        });
                    }

                    const tag = match[0];
                    const isHighlightStart = tag.toLowerCase() === '<span class="highlight">';
                    const isHighlightEnd = tag.toLowerCase() === '</span>';

                    tokens.push({
                        type: 'tag',
                        text: tag,
                        index: match.index,
                        raw: tag,
                        highlightStart: isHighlightStart,
                        highlightEnd: isHighlightEnd
                    });

                    lastIndex = tagRegex.lastIndex;
                }

                // Final section after last tag
                const remaining = htmlText.slice(lastIndex);
                let wordMatch;
                while ((wordMatch = wordRegex.exec(remaining)) !== null) {
                    tokens.push({
                        type: 'word',
                        text: wordMatch[0],
                        index: lastIndex + wordMatch.index,
                        raw: wordMatch[0]
                    });
                }

                // Identify highlight word indices
                let insideHighlight = false;
                const wordIndices = tokens.filter(t => t.type === 'word');
                const highlightWordIndexes = [];

                for (let i = 0, wordCounter = 0; i < tokens.length; i++) {
                    if (tokens[i].highlightStart) {
                        insideHighlight = true;
                    } else if (tokens[i].highlightEnd) {
                        insideHighlight = false;
                    } else if (tokens[i].type === 'word') {
                        if (insideHighlight) {
                            highlightWordIndexes.push(wordCounter);
                        }
                        wordCounter++;
                    }
                }

                if (!highlightWordIndexes.length) return '';

                // Create snippet ranges
                highlightWordIndexes.sort((a, b) => a - b);

                const ranges = [];
                let start = highlightWordIndexes[0];
                let end = highlightWordIndexes[0];

                for (let i = 1; i < highlightWordIndexes.length; i++) {
                    if (highlightWordIndexes[i] - end <= 10) {
                        end = highlightWordIndexes[i];
                    } else {
                        ranges.push([Math.max(0, start - 5), end + 5]);
                        start = highlightWordIndexes[i];
                        end = highlightWordIndexes[i];
                    }
                }
                ranges.push([Math.max(0, start - 5), end + 5]);

                // Extract snippets from original tokens
                const wordsOnly = tokens.filter(t => t.type === 'word' || t.type === 'tag');

                let result = '';
                let wordPointer = 0;
                for (let i = 0; i < ranges.length; i++) {
                    const [start, end] = ranges[i];
                    if (start > 0) result += '... ';

                    let currentCount = 0;
                    for (let j = 0; j < tokens.length; j++) {
                        const t = tokens[j];

                        if (t.type === 'word') {
                            if (currentCount >= start && currentCount <= end) {
                                result += t.raw + ' ';
                            }
                            currentCount++;
                        } else if (t.type === 'tag') {
                            // Always keep tags intact
                            result += t.raw;
                        }

                        if (currentCount > end) break;
                    }

                    if (end < wordIndices.length - 1) result += '... ';
                }

                return result.trim();
            }
        },
        selectSearch(paragraph, bookCode) {
            const vm = this;
            let timeoutVal = 0;

            if (vm.selectedBook.code !== bookCode) {
                vm.selectedBook = vm.books.find(book => book.code === bookCode);
                timeoutVal = 200;
            }

            setTimeout(() => {
                const element = document.getElementById(paragraph.chapterIndex + '-' + paragraph.paragraphIndex);

                if (element) {
                    element.scrollIntoView({behavior: "smooth", block: "start"});
                    vm.menuDisplay = false;
                }
            }, timeoutVal);
        }
    },
    watch: {
        '$route.query'() {
            this.filterBooks();
        }
    }
}