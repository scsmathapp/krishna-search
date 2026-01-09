import moment from "moment";
import _ from "lodash";

import getCurrentmonth from './currentmonth';

moment.fn.init = function () {
    this._date = this.date();
    this._dayIndex = this.day();
    this._monthIndex = this.month();
    this._year = this.year();
    this._dateText = this.format('YYYY-MM-DD');
}

export default function () {
    return {
        days: moment.weekdays(),
        shortDays: moment.weekdaysShort(),
        months: moment.months(),
        dark: localStorage.dark && JSON.parse(localStorage.dark),
        today: {},
        selectedDate: {},
        location: {},
        locationCityId: 0,
        calDates: {},
        filteredEvents: {},
        isDateSelected: true,
        currentMonth: getCurrentmonth(),
        setIsDateSelected(value) {
            this.isDateSelected = value;
        },
        setFilteredEvents(value) {
            this.filteredEvents = value;
        },
        setToday() {
            this.today = moment();
            this.today.init(this);

            this.setUpcoming();
        },
        setUpcoming() {
            const itrDay = moment(this.today._dateText);

            let keys = Object.keys(this.calDates),
                i = -1, count = 0;

            this.today._upcoming = [];

            // Get the next day that has an event (check for upto 60 days)
            while (count < 60) {
                itrDay.add(1, 'day');
                i = keys.indexOf(itrDay.format('YYYY-MM-DD'));

                // If event found i > -1
                if (i > -1) {
                    break;
                }

                count++;
            }

            // If 60 not reached and i not equal -1 (meaning no event found under 60 days)
            if (!(count >= 59 && i < 0)) {
                // Take next 7 events and put in upcoming
                while (i < keys.length && this.today._upcoming.length < 7) {
                    const day = this.calDates[keys[i]];

                    if (day) {
                        this.today._upcoming.push(day);
                    }

                    i++;
                }
            }
        },
        // On location changed, set calDates, set today & select date
        async onLocationChanged(newLocation) {
            if (this.location['city_id'] !== newLocation['city_id']) {
                this.location = newLocation;
                localStorage.location = newLocation['city_id'];
                this.currentMonth.weeks = [];

                await this.setCalDatesFromLocation();

                this.setToday();

                // If no date is selected, select today
                if (!this.selectedDate._dateText) {
                    this.onDateSelected(this.today);
                } else {
                    this.onDateSelected(this.selectedDate);
                }
            }
        },
        async setCalDatesFromLocation() {
            const calDatesFromLocation = await import('@/assets/calendar/location-' + this.location['city_id']);
            this.calDates = calDatesFromLocation.default;

            _.each(this.calDates, (date, dateText) => {
                const appDate = moment(dateText);

                appDate.init();

                _.each(date.events, dateEvent => {
                    if (dateEvent.img) {
                        const url = 'url(' + require('@/assets/img/' + dateEvent.img + '.jpg') + ')';

                        if (!date.url) {
                            date.url = url;
                        }

                        dateEvent.url = url;
                    }
                });

                _.each(date, (value, attr) => {
                    appDate[attr] = value;
                });

                this.calDates[dateText] = appDate;
            });
        },
        onDateSelected(date) {
            // Check if date is valid, because there is the option to click on black dates 
            if (date._dateText) {
                this.selectedDate = date;
                this.isDateSelected = true;

                if (!date.isSame(this.currentMonth.firstDate, 'month') || !this.currentMonth.weeks.length) {
                    this.setCurrentMonth(date);
                }
            }
        },
        setCurrentMonth(date) {
            this.currentMonth.weeks = [];
            this.currentMonth.parent = this;
            this.currentMonth.init(date);
            this.currentMonth.setWeeks();
        },
        resetSelectedDate() {
            const attrArr = ['events', 'ekadashi', 'special', 'img', 'url'];

            if (this.selectedDate && this.calDates[this.selectedDate._dateText]) {
                _.each(attrArr, (attr) => {
                    this.selectedDate[attr] = this.calDates[this.selectedDate._dateText][attr];
                });
            }
        },
        filterEvents(searchText) {
            this.filteredEvents = {};

            _.each(this.calDates, (date, _dateText) => {
                _.each(date.events, dateEvent => {
                    if (dateEvent.name.toLowerCase().includes(searchText.toLowerCase())) {
                        this.filteredEvents[_dateText] = date;
                    }
                });
            })
        }
    };
}
