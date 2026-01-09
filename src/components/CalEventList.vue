<template>
    <div class="events w-sm-100 w-md-35 w-lg-35">
        <div class="input-group mb-3 ks-border d-flex align-items-center">
            <div class="cursor-pointer px-3" @click="toggleClose"
                 v-if="!calendar.isDateSelected">
                <i class="fa fa-close"></i>
            </div>
            <div class="cursor-pointer px-3" @click="calendar.onDateSelected(calendar.today)"
                 title="Go to Today" v-else>
                <i class="fa fa-undo"></i>
            </div>
            <input type="text" v-model="searchText" id="search" class="form-control flex-fill border-0" placeholder="Filter events"
                   @click="calendar.setIsDateSelected(false)">
            <div class="cursor-pointer px-3"
                 @click="calendar.setIsDateSelected(false); calendar.setFilteredEvents(calendar.calDates);"
                 title="View all events">
                <i class="fa fa-list"></i>
            </div>
        </div>
        <div class="list-container">
            <div v-if="calendar.isDateSelected">
                <DayEvents :day="calendar.selectedDate" :calendar="calendar"/>
                <div v-if="calendar.selectedDate._upcoming">
                    <div class="fw-bold upcoming d-flex">Upcoming Events</div>
                    <div v-for="(day, dayIndex) in calendar.today._upcoming" :key="dayIndex">
                        <DayEvents :day="day" :calendar="calendar"/>
                    </div>
                </div>
            </div>
            <div v-else>
                <div v-for="(day, dayIndex) in calendar.filteredEvents" :key="dayIndex">
                    <DayEvents :day="day" :calendar="calendar"/>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import DayEvents from './DayEvents.vue';

export default {
    name: 'CalEventList',
    components: {
        DayEvents
    },
    props: {
        calendar: Object
    },
    data() {
        return {
            searchText: ''
        }
    },
    methods: {
        toggleClose() {
            const vm = this;

            vm.searchText = '';
            vm.calendar.onDateSelected(vm.calendar.selectedDate);
            vm.calendar.setFilteredEvents({});
        }
    },
    watch: {
        searchText() {
            const vm = this;

            if (vm.searchText !== '') {
                vm.calendar.filterEvents(vm.searchText);
            } else {
                vm.calendar.setFilteredEvents({});
            }
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.events {
    min-width: 200px;
    overflow: visible;

    @include lg {
        min-width: 40vw;
        max-width: 40vw;
        padding-left: 40px;
    }

    @include md {
        min-width: 40vw;
        max-width: 40vw;
        padding-left: 40px;
    }

    @include sm {
        min-width: 100vw;
        max-width: 100vw;
        padding: 0 2vw;
        margin-top: 10px;
    }
    
    .list {
        margin: 20px 0 10px 5px;
        border-radius: 3px;
        overflow: hidden;
    }

    .upcoming {
        font-size: 20px;
        margin-top: 50px;
        border-bottom: 1px solid grey;
    }
    
    .input-group {
        @include lg {
            height: 40px;
        }

        @include sm-md {
            height: 50px;
        }
    }
}

.list-container {
    @include lg {
        max-height: calc(100vh - 280px);
    }

    @include md {
        max-height: calc(100vh - 225px);
    }
    
    @include sm {
        padding-bottom: 100px;
    }
}

// tablet + desktop
@media screen and (min-width: 768px) {
    .list-container {
        overflow-y: auto;
        padding-right: 5px;
    }
}
</style>