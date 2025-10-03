<template>
    <div>
        <div class="img-bg" :style="imgClass"></div>
    </div>
</template>
<script>
export default {
    name: "Pic",
    props: {
        source: {
            type: String,
            required: true
        },
        w: {
            type: String
        },
        h: {
            type: String
        }
    },
    data() {
        return {
            imgClass: {}
        }
    },
    async created() {
        const vm = this;

        vm.imgClass.backgroundImage = `url(${vm.source})`;

        vm.imgClass.width = vm.w || '150px';

        vm.imgClass.height = vm.h || '200px';
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/css/style.scss';

.img-bg {
    border-radius: 20px;
}
</style>