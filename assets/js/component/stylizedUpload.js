class StylizedUpload {
    element;

    stylizedElement;
    buttonElement;
    labelElement;

    emptyLabelText;
    buttonText;

    filterLabel = (text) => text.indexOf('C:\\fakepath\\') !== -1
        ? text.substring('C:\\fakepath\\'.length)
        : text;

    setLabel = (event) => {
        this.labelElement.innerText = event.target.value
            ? this.filterLabel(event.target.value)
            : this.emptyLabelText;
    };

    constructor(element) {
        this.element = element;
        this.element.setAttribute('hidden', '');
        this.element.addEventListener('change', this.setLabel);

        this.emptyLabelText = this.element.dataset.labeltext;
        this.buttonText = this.element.dataset.buttontext;

        this.stylizedElement = document.createElement("button");
        this.stylizedElement.type = "button";
        this.stylizedElement.classList.add('stylized_upload');

        this.stylizedElement.addEventListener('click', () => this.element.click());

        this.buttonElement = document.createElement("div");
        this.buttonElement.classList.add('stylized_upload_button');
        this.buttonElement.innerText = this.buttonText;

        this.labelElement = document.createElement("div");
        this.labelElement.classList.add('stylized_upload_label');
        this.labelElement.innerText = this.emptyLabelText;

        this.stylizedElement.appendChild(this.buttonElement);
        this.stylizedElement.appendChild(this.labelElement);

        this.element.parentNode.insertBefore(this.stylizedElement, this.element.nextSibling);
        console.log(element.value);

        element.addEventListener('change', () => {
            console.log(element.value);
        })
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.stylizedUpload').forEach((element) => new StylizedUpload(element)));