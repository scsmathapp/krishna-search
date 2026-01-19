<template>
    <div class="pt-4 p-2 d-flex flex-column align-items-center">
        <div class="mb-4 d-flex flex-column-reverse flex-md-row flex-lg-row flex-xl-row">
            <div class="d-flex mb-3 month-select" v-if="calendar.location.name">
                <div class="btn-group flex-fill" role="group">
                    <button type="button" class="ks-button ks-font-secondary" @click="calendar.currentMonth.setPrev()">
                        <i class="fa fa-caret-left"></i>
                    </button>
                    <button type="button" class="btn">
                        {{ calendar.currentMonth.name }} {{ calendar.currentMonth.year }}
                    </button>
                    <button type="button" class="ks-button ks-font-secondary" @click="calendar.currentMonth.setNext()">
                        <i class="fa fa-caret-right"></i>
                    </button>
                </div>
            </div>
            <div class="location">
                <select class="form-select ks-border" v-model="newLocation"
                        @change="calendar.onLocationChanged(newLocation)">
                    <option :value="{ id: 0 }" disabled>Select Location...</option>
                    <option :value="nabadwip">{{ nabadwip.name }}</option>
                    <option v-for="location in locationList" :key="location['city_id']" :value="location">
                        {{ location.name }}
                    </option>
                </select>
            </div>
        </div>
        <div class="d-flex flex-column flex-md-row flex-lg-row" v-if="calendar.location.name">
            <CalMonth :calendar="calendar"/>
            <CalEventList v-if="calendar.selectedDate._dateText" :calendar="calendar"/>
        </div>
    </div>
</template>
<script>
import getCalendar from '@/assets/js/calendar';
import _ from 'lodash';
import cities from '@/assets/js/cities';
import CalEventList from '@/components/CalEventList.vue';
import CalMonth from '@/components/CalMonth.vue';

export default {
    name: 'Calendar',
    components: {
        CalEventList,
        CalMonth
    },
    data() {
        return {
            newLocation: {id: 0},
            locationList: cities,
            nabadwip: _.find(cities, city => city['city_id'] == 43),
            calendar: getCalendar()
        }
    },
    created() {
        const vm = this,
            location = localStorage.location;

        if (location) {
            vm.newLocation = _.find(cities, city => city['city_id'] == location);
            vm.calendar.onLocationChanged(vm.newLocation);
        } else {
            vm.newLocation = vm.nabadwip;
            vm.calendar.onLocationChanged(vm.newLocation);
        }
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

*:not(.fa) {
    @extend .ks-font;
}

.month-select {
    @include lg {
        min-width: 50vw;
        max-width: 50vw;
    }

    @include md {
        min-width: 60vw;
        max-width: 60vw;
    }

    @include sm {
        min-width: 100vw;
        max-width: 100vw;
        padding: 0 2vw;
    }
}

.location {
    @include lg {
        min-width: 40vw;
        max-width: 40vw;
        padding-left: 40px;
        height: 40px;
    }

    @include md {
        min-width: 40vw;
        max-width: 40vw;
        padding-left: 40px;
        height: 50px;
    }

    @include sm {
        min-width: 100vw;
        max-width: 100vw;
        padding: 0 2vw;
        height: 50px;
        margin-bottom: 20px;
    }
    
    select {
        height: 100%;
    }
}
</style>