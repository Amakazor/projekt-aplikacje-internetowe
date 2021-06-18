export class StylizedNumber {
    element;
    stylizedElement;
    textElement;

    increment = () => {
        this.element.value = Math.max(Math.min(parseInt(this.element.value) + 1, this.element.getAttribute('max')), this.element.getAttribute('min'));
        this.textElement.innerText = this.element.value;
        this.element.dispatchEvent(new Event('change'));
    };

    decrement = () => {
        this.element.value = Math.max(Math.min(parseInt(this.element.value) - 1, this.element.getAttribute('max')), this.element.getAttribute('min'));
        this.textElement.innerText = this.element.value;
        this.element.dispatchEvent(new Event('change'));
    };

    constructor(element) {
        console.log(element);
        this.element = element;
        this.element.setAttribute('hidden', '');

        this.stylizedElement = document.createElement('div');
        this.stylizedElement.classList.add('stylized_number');

        const incrementElement = document.createElement('button');
        incrementElement.type = 'button';
        incrementElement.classList.add('stylized_number__increment');
        incrementElement.addEventListener('click', this.increment);
        incrementElement.innerText = '>';

        const decrementElement = document.createElement('button');
        decrementElement.type = 'button';
        decrementElement.classList.add('stylized_number__decrement');
        decrementElement.addEventListener('click', this.decrement);
        decrementElement.innerText = '<';

        this.textElement = document.createElement('div');
        this.textElement.classList.add('stylized_number__text');
        this.textElement.innerText = element.value;

        this.stylizedElement.appendChild(decrementElement);
        this.stylizedElement.appendChild(this.textElement);
        this.stylizedElement.appendChild(incrementElement);

        this.element.parentNode.insertBefore(this.stylizedElement, this.element.nextSibling);
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.stylizedNumber').forEach((element) => new StylizedNumber(element)));