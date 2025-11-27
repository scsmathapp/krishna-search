<template>
    <div class="image-gallery">
        <div class="scroll-btn left align-items-center justify-content-center"
             :class="{ hidden: isAtStart }"
             @click="scroll('left')">
            <span><i class="fa fa-solid fa-chevron-left"></i></span>
        </div>

        <div class="scroll-container"
             ref="scrollContainer"
             @scroll="updateButtons">
            <div v-for="book in vaishnava.books">
                <a :href="'/#/book/' + book.code" class="book-card d-flex flex-column cursor-pointer">
                    <div v-if="book.bookCode" class="ks-border img-bg"
                         :style="'background-image: url(' + require(`@/assets/books/cover/${book.bookCode}.jpg`) + ');'"></div>
                    <div class="d-flex justify-content-center align-items-center text-center pt-2">
                        <p class="ks-font f-18">{{ book.name }}</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="scroll-btn right align-items-center justify-content-center"
             :class="{ hidden: isAtEnd }"
             @click="scroll('right')">
            <span><i class="fa fa-solid fa-chevron-right"></i></span>
        </div>
    </div>
</template>
<script>
import Pic from "./Pic.vue";

export default {
    name: "ImageScroller",
    components: {Pic},
    props: {
        vaishnava: {
            type: Object,
            required: true
        },
        scrollAmount: {
            type: Number,
            default: 400 // pixels per click
        }
    },
    data() {
        return {
            isAtStart: true,
            isAtEnd: false
        };
    },
    mounted() {
        this.updateButtons();
    },
    methods: {
        scroll(direction) {
            const container = this.$refs.scrollContainer;
            const amount = direction === 'left' ? -this.scrollAmount : this.scrollAmount;
            container.scrollBy({left: amount, behavior: 'smooth'});
            // Allow time for smooth scroll to happen before updating
            setTimeout(this.updateButtons, 300);
        },
        updateButtons() {
            const container = this.$refs.scrollContainer;
            this.isAtStart = container.scrollLeft <= 0;
            this.isAtEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 1;
        }
    }
};
</script>
<style lang="scss" scoped>
@import '@/assets/css/style.scss';

p {
    margin-bottom: 0;
}

.image-gallery {
    position: relative;
    display: flex;
    align-items: center;
    border-radius: 20px;
    overflow: hidden;

    .scroll-container {
        display: flex;
        overflow-x: auto;
        scroll-behavior: smooth;
        gap: 10px;
        padding: 10px 0;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE & Edge */
        width: 100%; /* Adjust as needed */

        .book-card {
            margin: 10px 10px 0 10px;

            @include lg {
                margin: 10px 10px 0 10px;
            }

            .img-bg {
                border-radius: 20px;

                @include lg {
                    width: 150px;
                    height: 200px;
                }

                @include sm-md {
                    width: 100px;
                    height: 125px;
                }
            }

            &:hover .ks-border {
                box-shadow: grey 0 0 20px;
            }
            
            .ks-font {
                @include sm-md {
                    font-size: 16px;
                }
            }
        }

        &::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
        }
        
        img {
            flex: 0 0 auto;
            border-radius: 8px;
            user-select: none;
        }
    }

    /* Floating button styles */
    .scroll-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        padding: 0;
        height: 100%;
        width: 70px; /* Width of the gradient overlay area */
        cursor: pointer;
        z-index: 2;
        display: flex;

        &.left, &.right {
            &:hover {
                background: rgba(0, 0, 0, 0.6);
            }
        }

        /* Left gradient */
        &.left {
            left: 0;
            background: linear-gradient(to right, rgba(0, 0, 0, 0.6), transparent);
        }

        /* Right gradient */
        &.right {
            right: 0;
            background: linear-gradient(to left, rgba(0, 0, 0, 0.6), transparent);
        }

        /* Arrow text style */
        span {
            color: white;
            font-size: 24px;
            font-weight: bold;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* Hide button entirely when not needed */
        &.hidden {
            background: rgba(0, 0, 0, 0.05);
            cursor: default;

            &:hover {
                background: rgba(0, 0, 0, 0.05);
            }
        }
    }
}
</style>
