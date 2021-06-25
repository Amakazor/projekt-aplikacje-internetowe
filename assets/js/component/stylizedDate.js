import { StylizedNumber } from './stylizedNumber.js';

class StylizedDate {
    element;
    date;

    wrapperElement;
    days;
    dateElement;
    formattedDate;

    constructor(element) {
        this.element = element;
        this.date = new Date(this.element.value);

        element.addEventListener('click', this.openInput);

        const superWrapperElement = document.createElement('div');
        superWrapperElement.classList.add('stylized_date__wrapper');

        this.wrapperElement = document.createElement('div');
        this.wrapperElement.classList.add('stylized_date');

        this.formattedDate = document.createElement('div');
        this.formattedDate.classList.add('stylized_date__formattedDate');
        this.formattedDate.innerText = this.constructFormattedDate();

        const confirmButton = document.createElement('button');
        confirmButton.innerText = '✔️';
        confirmButton.type = 'button';
        confirmButton.classList.add('stylized_date__button');
        confirmButton.addEventListener('click', this.onConfirm);

        const cancelButton = document.createElement('button');
        cancelButton.innerText = '❌';
        cancelButton.type = 'button';
        cancelButton.classList.add('stylized_date__button');
        cancelButton.addEventListener('click', this.onCancel);

        const buttonsElement = document.createElement('div');
        buttonsElement.classList.add('stylized_date__buttons');
        buttonsElement.appendChild(confirmButton);
        buttonsElement.appendChild(cancelButton);

        const formatTimeElement = document.createElement('div');
        formatTimeElement.classList.add('stylized_date__formatTimeWrapper');
        formatTimeElement.appendChild(this.formattedDate);
        formatTimeElement.appendChild(this.constructTime(this.date.getHours(), this.date.getMinutes()));
        formatTimeElement.appendChild(buttonsElement);

        this.wrapperElement.appendChild(formatTimeElement);
        this.wrapperElement.appendChild(this.constructDate(this.date.getFullYear(), this.date.getMonth()));

        superWrapperElement.appendChild(this.wrapperElement);

        this.element.parentNode.insertBefore(superWrapperElement, this.element.nextSibling);
    }

    onConfirm = () => {
        this.element.value = this.formatDateToValue();
        this.closeInput();
    };

    onCancel = () => {
        this.closeInput();
    };

    onDayChange = (event) => {
        this.date = new Date(parseInt(event.target.dataset.year), parseInt(event.target.dataset.month) - 1, parseInt(event.target.dataset.day), this.date.getHours(), this.date.getMinutes());
        this.formattedDate.innerText = this.constructFormattedDate();

        this.refreshDate();
    };

    onYearChange = (event) => {
        this.date.setFullYear(event.target.value);
        this.formattedDate.innerText = this.constructFormattedDate();

        this.refreshDays();
    };

    onMonthChange = (event) => {
        this.date.setMonth(event.target.value - 1);
        this.formattedDate.innerText = this.constructFormattedDate();

        this.refreshDays();
    };

    refreshDays = () => {
        const parent = this.days.parentElement;
        parent.removeChild(this.days);
        this.days = this.constructArrayOfDays(this.date.getFullYear(), this.date.getMonth());
        parent.appendChild(this.days);
    };

    refreshDate = () => {
        const parent = this.dateElement.parentElement;
        parent.removeChild(this.dateElement);
        this.dateElement = this.constructDate(this.date.getFullYear(), this.date.getMonth())
        parent.appendChild(this.dateElement);
    };

    onHourChange = (event) => {
        this.date.setHours(event.target.value);
        this.formattedDate.innerText = this.constructFormattedDate();
    };

    onMinuteChange = (event) => {
        this.date.setMinutes(event.target.value);
        this.formattedDate.innerText = this.constructFormattedDate();
    };

    getNumberOfDaysInMonth = (year, month) => new Date(year, month - 1, 0).getDate();
    getDayOfWeekForFirstDayOfMonth = (year, month) => new Date(year, month, 1).getDay();

    getNLastDaysOfMonth = (year, month, n) => new Array(n).fill(0).map((item, index) => this.getNumberOfDaysInMonth(year, month) - index).reverse();
    getAllDaysOfMonth = (year, month) => new Array(this.getNumberOfDaysInMonth(year, month)).fill(0).map((item, index) => index + 1);
    getNFirstDayOfMonth = (n) => new Array(n).fill(0).map((item, index) => index + 1);

    constructArrayOfDays = (year, month) => {
        console.log(month);
        let daysToAddAtTheBeginning = this.getDayOfWeekForFirstDayOfMonth(year, month);
        daysToAddAtTheBeginning = daysToAddAtTheBeginning === 0
            ? 6
            : daysToAddAtTheBeginning -1;
        const totalDays = this.getNumberOfDaysInMonth(year, month) + daysToAddAtTheBeginning;

        const daysElement = document.createElement('div');

        daysElement.classList.add('stylized_date__days');
        [
            ...this.getNLastDaysOfMonth(year, month - 1, daysToAddAtTheBeginning).map(element => this.constructDay(element, -1)),
            ...this.getAllDaysOfMonth(year, month).map(element => this.constructDay(element)),
            ...this.getNFirstDayOfMonth((totalDays % 7 ? 7 - totalDays % 7 : 0) + (totalDays <= 5 * 7 ? 7 : 0)).map(element => this.constructDay(element, 1))
        ].map(element => daysElement.appendChild(element));

        return daysElement;
    };

    constructDay = (day, month = 0) => {
        const dayElement = document.createElement("button");
        dayElement.type = 'button';
        dayElement.classList.add('stylized_date__day');

        if (month !== 0) dayElement.classList.add('stylized_date__day--notThisMonth');
        if (day === this.date.getDate() && month === 0) dayElement.classList.add('stylized_date__day--current');

        dayElement.addEventListener('click', this.onDayChange);
        dayElement.innerText = day;

        const dataset = {'day': day, 'month': (new Date(this.date.getFullYear(), this.date.getMonth() + month, this.date.getDate()).getMonth() + 1).toString(), 'year': new Date(this.date.getFullYear(), this.date.getMonth() + month, this.date.getDate()).getFullYear().toString()};
        for (const property in dataset) dayElement.dataset[property] = dataset[property];

        return dayElement;
    };

    constructDate = (year, month) => {
        const yearInput = document.createElement('input');
        yearInput.classList.add('stylized_date__numberInput');
        yearInput.type = 'number';
        yearInput.setAttribute('min', '0');
        yearInput.setAttribute('max', '9999');
        yearInput.value = year;
        yearInput.addEventListener('change', this.onYearChange);

        const monthInput = document.createElement('input');
        monthInput.classList.add('stylized_date__numberInput');
        monthInput.type = 'number';
        monthInput.setAttribute('min', '0');
        monthInput.setAttribute('max', '12');
        monthInput.value = month + 1;
        monthInput.addEventListener('change', this.onMonthChange);

        const yearMonthWrapper = document.createElement('div');
        yearMonthWrapper.classList.add('stylized_date__yearMonthInput');
        yearMonthWrapper.appendChild(yearInput);
        yearMonthWrapper.appendChild(monthInput);

        new StylizedNumber(yearInput);
        new StylizedNumber(monthInput);

        const weekLabels = document.createElement('div');
        weekLabels.classList.add('stylized_date__weekLabels');

        ['PN', 'WT', 'ŚR', 'CZ', 'PT', 'SB', 'ND'].map(label => {
            const weekLabel = document.createElement('div');
            weekLabel.classList.add('stylized_date__weekLabel');
            weekLabel.innerText = label;

           weekLabels.appendChild(weekLabel);
        });

        this.days = this.constructArrayOfDays(year, month);

        const dateWrapper = document.createElement('div');
        dateWrapper.classList.add('stylized_date__dateWrapper');
        dateWrapper.appendChild(yearMonthWrapper);
        dateWrapper.appendChild(weekLabels);
        dateWrapper.appendChild(this.days);

        this.dateElement = dateWrapper;

        return dateWrapper;
    };

    constructTime = (hour, minute) => {
        const hourInput = document.createElement('input');
        hourInput.classList.add('stylized_date__numberInput');
        hourInput.type = 'number';
        hourInput.setAttribute('min', '0');
        hourInput.setAttribute('max', '23');
        hourInput.value = hour;
        hourInput.addEventListener('change', this.onHourChange);

        const separator = document.createElement('div');
        separator.classList.add('stylized_date__separator');
        separator.text = ':';

        const minuteInput = document.createElement('input');
        minuteInput.classList.add('stylized_date__numberInput');
        minuteInput.type = 'number';
        minuteInput.setAttribute('min', '0');
        minuteInput.setAttribute('max', '59');
        minuteInput.value = minute;
        minuteInput.addEventListener('change', this.onMinuteChange);

        const timeWrapper = document.createElement('div');
        timeWrapper.classList.add('stylized_date__timeWrapper');

        timeWrapper.appendChild(hourInput);
        timeWrapper.appendChild(separator);
        timeWrapper.appendChild(minuteInput);

        new StylizedNumber(hourInput);
        new StylizedNumber(minuteInput);


        return timeWrapper;
    };

    constructFormattedDate = () => this.date.toLocaleDateString('pl-PL', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric'
    });

    formatDateToValue = () => this.date.getFullYear() + '-' + (this.date.getMonth() + 1 < 10 ? '0' + (this.date.getMonth() + 1).toString() : this.date.getMonth() + 1) + '-' + (this.date.getDate() < 10 ? '0' + this.date.getDate().toString() : this.date.getDate()) + ' ' + (this.date.getHours() < 10 ? '0' + this.date.getHours().toString() : this.date.getHours()) + ':' + (this.date.getMinutes() < 10 ? '0' + this.date.getMinutes().toString() : this.date.getMinutes());

    openInput = () => {
        document.querySelectorAll('.stylized_date').forEach((element) => element.classList.remove('active'));
        this.wrapperElement.classList.add('active');
    };

    closeInput = () => this.wrapperElement.classList.remove('active');
}


document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.stylizedDate').forEach((element) => new StylizedDate(element)));