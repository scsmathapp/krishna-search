import moment from "moment";
import _ from "lodash";

export default function () {
    return {
        index: 0,
        year: 0,
        name: '',
        weeks: {},
        firstDate: 0,
        lastDate: 0,
        init(date) {
            this.index = date._monthIndex;
            this.year = date._year;

            this.firstDate = moment([this.year, this.index, 1]);
            this.lastDate = moment(this.firstDate).endOf('month');
            this.name = this.firstDate.format('MMMM');

            // While loading calendar, we set today and while adding dates, we check for events in calDates
            // But we don't do same selectedDate, so we have to reset it
            this.parent.resetSelectedDate();
        },
        setWeeks() {
            const itrDate = moment(this.firstDate);

            let lastDateWeek = this.lastDate.week();

            this.weeks = [];

            // This is done because in December, after week 52, week num becomes 1, so it is set to 53 to have proper range
            if (lastDateWeek === 1 && this.lastDate.dayOfYear() > 350) {
                lastDateWeek = 53;
            }

            _.each(_.range(this.firstDate.week(), lastDateWeek + 1), (weekIndex, arrIndex) => {
                this.weeks.push([]);

                _.each(_.range(0, 7), (dayIndex) => {
                    let appDate = {};

                    // Checking is date or before to range from first date to last date of month
                    // Check dayIndex to ensure that dates are not added before first date and after last date
                    if (itrDate.isSameOrBefore(this.lastDate) && itrDate.day() === dayIndex) {
                        if (!this.parent.today.isSame(itrDate, 'day')) {
                            appDate = moment(itrDate);
                            appDate.init();
                        } else {
                            appDate = this.parent.today;
                        }
    
                        if (this.parent.calDates[appDate._dateText]) {
                            _.merge(appDate, this.parent.calDates[appDate._dateText]);
                        }

                        itrDate.add(1, 'day');
                    }

                    this.weeks[arrIndex].push(appDate);
                });
            });
        },
        setNext() {
            const nextMonthDate = moment([this.year, this.index, 1]).add(1, 'month');

            this.parent.setCurrentMonth({_monthIndex: nextMonthDate.month(), _year: nextMonthDate.year()});
        },
        setPrev() {
            const prevMonthDate = moment([this.year, this.index, 1]).subtract(1, 'month');

            this.parent.setCurrentMonth({_monthIndex: prevMonthDate.month(), _year: prevMonthDate.year()});
        }
    };
}