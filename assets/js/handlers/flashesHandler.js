class FlashHandler {
    element;
    flashes = [];
    flashesIntervalHandle;
    flashesIntervalTime = 8000;

    deleteFlash = (element = null) => {
        if (this.flashes.length) {
            if (element) {
                this.flashes = this.flashes.filter(flashesElement => flashesElement !== element);
            } else {
                element = this.flashes.shift();
            }
            element.remove();
            if (this.flashes.length) this.flashes[0].classList.add('flashes__flash--fading');
        } else {
            clearInterval(this.flashesIntervalHandle);
        }
    };

    onMouseEnter = () => {
        clearInterval(this.flashesIntervalHandle);
        this.flashes.forEach(flashesElement => flashesElement.classList.remove('flashes__flash--fading'))
    };

    onMouseLeave = () => {
        this.flashesIntervalHandle = setInterval(this.deleteFlash, this.flashesIntervalTime);
        this.flashes[0].classList.add('flashes__flash--fading');
    };

    constructor(element) {
        this.element = element;
        this.flashes = Array.from(this.element.querySelectorAll('.flashes__flash'));
        this.element.addEventListener('mouseenter', this.onMouseEnter);
        this.element.addEventListener('mouseleave', this.onMouseLeave);

        if (this.flashes.length) {
            this.flashesIntervalHandle = setInterval(this.deleteFlash, this.flashesIntervalTime);
            this.flashes.forEach(flashesElement => flashesElement.querySelector('.flashes__close_button').addEventListener('click', () => this.deleteFlash(flashesElement)));
            this.flashes[0].classList.add('flashes__flash--fading');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.flashes').forEach((element) => new FlashHandler(element)));