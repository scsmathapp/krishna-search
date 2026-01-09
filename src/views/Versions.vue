<template>
    <div class="mb-5 pb-5">
        <div v-for="(version, versionIndex) in versions"  class="card ks-border m-3" :key="versionIndex">
            <h2 class="card-header">Version {{ version.name }}</h2>
            <ul class="list-group">
                <li v-for="(detail, detailIndex) in version.details" :key="detailIndex" class="list-group-item">
                    <i class="fa fa-check me-2"></i> {{ detail }}
                </li>
            </ul>
        </div>
    </div>
</template>
<script>
export default {
    name: "Versions",
    data() {
        return {
            versions: [
                {
                    name: '26.01',
                    details: [
                        'Ability to filter by author or book title',
                        'Tips on how to search by using \'+\' and \'|\' operators',
                        'Filter is automatically set to the current book being read',
                        'Resume your reading from where you left off',
                        'Vaishnava calendar has been added',
                        'Enhanced navigation bar',
                    ]
                },
                {
                    name: '25.10',
                    details: [
                        'New design with new colors and fonts',
                        'Intuitive user experience',
                        'Books can be selected from Homepage',
                        'New search engine',
                        'Instant search',
                        'Books and search tool available offline',
                        'Enhanced mobile experience'
                    ]
                }
            ]
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.card * {
    color: $primary;
}
</style>