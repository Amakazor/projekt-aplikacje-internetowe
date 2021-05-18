class HamburgerHandler {
    element;
    navbar;

    isOpen = false;

    onClick = () => {
        this.isOpen = !this.isOpen;
        this.handleMenuToggle();
    };

    onBlur = (event) => {
        if (!this.navbar.contains(event.target)) {
            this.isOpen = false;
            this.handleMenuToggle();
        }
    };

    handleMenuToggle = () => {
        if (!this.isOpen) {
            this.navbar.classList.remove('menuOpen');
        } else {
            this.navbar.classList.add('menuOpen');
        }
    };

    constructor(element) {
        this.element = element;
        this.navbar = this.element.closest('nav');

        this.isOpen = this.navbar.classList.contains('menuOpen');

        element.addEventListener('click', this.onClick);

        if(element.dataset.autohide) {
            document.addEventListener('click', this.onBlur);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.HamburgerHandler').forEach((element) => new HamburgerHandler(element)));