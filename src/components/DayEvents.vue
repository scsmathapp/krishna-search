<template>
    <div class="my-3">
        <div class="card ks-border">
            <div class="card-header fw-bold btn text-start" @click="calendar.onDateSelected(day)">
                <span>{{ calendar.days[day._dayIndex] }} {{ day._date }} {{ calendar.months[day._monthIndex] }} {{ day._year }}</span>
                <span v-if="day._dateText === calendar.today._dateText"> - Today</span>
                <span v-if="day['lunar-day']"> - {{ day['lunar-day'] }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item special" v-if="day.special">{{ day.special.name }}</li>
                    <li class="list-group-item special11" v-if="day.ekadashi">{{ day.ekadashi.name }}</li>
                    <a v-for="(event, eventIndex) in day.events" :key="eventIndex" :href="event.link" target="_blank"
                       class="list-group-item d-flex align-items-center" :class="{ 'fw-bold btn text-start': event.link }">
                        <div v-if="event.url" class="event-image"
                             :style="{ 'background-image': event.url }"></div>
                        <div>{{ event.name }}</div>
                    </a>
                </ul>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: 'DayEvents',
    props: {
        day: {},
        calendar: {}
    }
}
</script>
<style lang="scss" scoped>
@import '@/assets/style/style.scss';

.list-group-item {
    border: none;
}

.card-header {
    background-color: $primary !important;
    color: white;
    
    &:hover {
        color: white;
    }
}

.dark {
    .special {
        background-color: rgba(218, 165, 32, 0.5);
    }

    .special11 {
        background-color: rgba(250, 88, 88, 0.5);
    }
}

.special {
    background-color: $primary-pale;
}

.special11 {
    background-color: rgba(250, 88, 88, 0.3);
}

.event-image {
    min-width: 20px;
    max-width: 20px;
    height: 20px;
    border: 1px solid grey;
    border-radius: 10px;
    margin-right: 8px;
    background-repeat: no-repeat;
    background-size: cover;
    background-position-x: center;
}
</style>