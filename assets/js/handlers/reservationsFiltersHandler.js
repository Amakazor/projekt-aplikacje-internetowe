class CarFilterHandler {
    element;

    onChange = (event) => {
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);
        params.set('car', event.target.value);
        params.set('page', '1');
        url.search = params.toString();
        window.location = url.toString();
    };

    constructor(element) {
        this.element = element;
        this.element.addEventListener('change', this.onChange);
    }
}

class UserFilterHandler {
    element;

    onChange = (event) => {
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);
        params.set('user', event.target.value);
        params.set('page', '1');
        url.search = params.toString();
        window.location = url.toString();
    };

    constructor(element) {
        this.element = element;
        this.element.addEventListener('change', this.onChange);
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.reservations__car').forEach((element) => new CarFilterHandler(element)));
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.reservations__user').forEach((element) => new UserFilterHandler(element)));