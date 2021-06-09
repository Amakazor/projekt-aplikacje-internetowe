class SortPropertyHandler {
    element;

    onChange = (event) => {
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);
        params.set('order', event.target.value);
        params.set('page', '1');
        url.search = params.toString();
        window.location = url.toString();
    };

    constructor(element) {
        this.element = element;
        this.element.addEventListener('change', this.onChange);
    }
}

class SortDirectionHandler {
    element;

    onChange = (event) => {
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);
        params.set('direction', event.target.value);
        params.set('page', '1');
        url.search = params.toString();
        window.location = url.toString();
    };

    constructor(element) {
        this.element = element;
        this.element.addEventListener('change', this.onChange);
    }
}


document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.sortable_list__sort--property').forEach((element) => new SortPropertyHandler(element)));
document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.sortable_list__sort--direction').forEach((element) => new SortDirectionHandler(element)));