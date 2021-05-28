class PaginationHandler {
    element;
    perPageElement;

    onChange = (event) => {
        let url = new URL(window.location);
        let params = new URLSearchParams(url.search);
        params.set('per_page', event.target.value);
        params.set('page', 1);
        url.search = params.toString();
        window.location = url.toString();
    };

    constructor(element) {
        this.element = element;
        this.perPageElement = element.querySelector('.per_page');

        if (this.perPageElement) {
            this.perPageElement.addEventListener('change', this.onChange);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.pagination').forEach((element) => new PaginationHandler(element)));