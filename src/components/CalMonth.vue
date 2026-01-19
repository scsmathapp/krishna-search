<template>
    <div class="d-flex flex-column month">
        <div class="d-flex justify-content-between" v-if="calendar.location.name">
            <div class="d-flex justify-content-center fw-bold mb-2 day"
                 v-for="(day, dayIndex) in calendar.shortDays"
                 :class="{ 'fc-red' : dayIndex === 0 }" :key="dayIndex">{{ day }}
            </div>
        </div>
        <div class="d-flex justify-content-between mb-2" v-for="(week, weekIndex) in calendar.currentMonth.weeks" :key="weekIndex">
            <div v-for="(day, dayIndex) in week" :key="dayIndex"
                 :class="{ selectedDate: day && calendar.selectedDate._dateText === day._dateText,
                        today: day && calendar.today._dateText === day._dateText, 'fc-red' : dayIndex === 0 }"
                 class="btn d-flex flex-column justify-content-center align-items-center date"
                 @click="() => { calendar.onDateSelected(day) }">
                <div v-if="day && day.url" :style="{'background-image': day.url}" style="min-height: 80%; min-width: 100%;" class="img-bg"></div>
                <div>{{ day._date }}</div>
                <div class="dot red d-flex justify-content-center align-items-center"
                     v-if="day && day.ekadashi"></div>
                <div class="dot blue d-flex justify-content-center align-items-center"
                     v-if="day && day.events && ((day.events.length > 0) || (day.special))">
                    {{ day.special ? day.events.length + 1 : day.events.length }}
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'CalMonth',
    props: {
        calendar: Object
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.dark .date {
    background-color: rgba(50, 50, 50, 0.6);
}

.month {
    z-index: 0;

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
        margin-bottom: 40px;
    }
}

.date,
.day {
    color: $primary;

    @include lg {
        width: 75px;
    }

    @include sm-md {
        width: 50px;
    }
}

.date {
    border-radius: 50%;
    position: relative;
    overflow: hidden;
    padding: 0;

    @include lg {
        height: 75px;
    }

    @include sm-md {
        height: 50px;
    }
    
    .img-bg {
        background-position-y: top;
    }
}

.dot {
    $dot-measure: 30px;

    @include sm-md {
        $dot-measure: 24px;
    }

    position: absolute;
    width: 15px;
    height: 15px;
    border-radius: 7.5px;
    margin-top: $dot-measure;

    &.red {
        background-color: rgb(255, 215, 0);
        margin-left: -$dot-measure;
    }

    &.blue {
        background-color: $primary;
        margin-left: $dot-measure;
        font-size: 12px;
        font-weight: bold;
        color: white;
    }
}

.fc-red {
    color: red;
}

.today,
.dark .today {
    background-color: $primary;
    color: white;
}

.selectedDate,
.dark .selectedDate {
    &:not(.today) {
        background-color: $primary-pale;
    }
}
</style>