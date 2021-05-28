class StylizedSelect {
    element;
    stylizedElement;

    stylizedSelectElement;

    options;
    currentOption;

    optionDictionary;

    isOpen = false;

    onSelectClick = () => {
        this.isOpen = !this.isOpen;
        this.onOpenChange();
    };

    onOpenChange = () => {
        if (this.isOpen) {
            this.stylizedSelectElement.classList.add('stylized_select_element--active');
            this.optionDictionary.forEach(option => option.stylizedOption.tabIndex = 0);
        } else {
            this.stylizedSelectElement.classList.remove('stylized_select_element--active');
            this.optionDictionary.forEach(option => option.stylizedOption.tabIndex = -1);
        }
    };

    onOptionClick = event => {
        const target = event.target.closest('.stylized_select__option');
        this.currentOption = this.getOptionElement(target);
        this.element.value = this.currentOption.value;
        this.stylizedSelectElement.innerHTML = this.currentOption.innerHTML;
        this.element.dispatchEvent(new Event('change'));
    };
    
    getOptionElement = stylizedOptionElement => {
        return this.optionDictionary.find(option => option.stylizedOption === stylizedOptionElement).option;
    };

    onBlur = event => {
        this.handleBlur(event.target);
    };

    handleBlur = (element, force = false) => {
        if (force || !this.isOpen || !this.stylizedElement.contains(element)) {
            this.isOpen = false;
            this.onOpenChange();
        }
    };

    onKeyDown = event => {
        setTimeout(() =>{
            if (event.code === 'Tab' && this.isOpen) {
                this.handleBlur(document.activeElement);
            } else if (event.code === 'Escape' && this.isOpen) {
                this.handleBlur(document.activeElement, true);
                this.stylizedSelectElement.focus();
            }
        }, 50)
    };

    constructor(element) {
        this.element = element;
        this.options = element.options;
        this.optionDictionary = [];

        if (this.options) {
            this.currentOption = this.options[element.selectedIndex];
        }

        this.element.setAttribute('hidden', '');

        this.stylizedElement = document.createElement("div");
        this.stylizedElement.classList.add("stylized_select");
        this.element.parentNode.insertBefore(this.stylizedElement, this.element.nextSibling);

        this.stylizedSelectElement = document.createElement("button");
        this.stylizedSelectElement.innerHTML = this.currentOption.innerHTML;
        this.stylizedSelectElement.classList.add("stylized_select_element");
        this.stylizedSelectElement.addEventListener("click", this.onSelectClick);
        this.stylizedElement.appendChild(this.stylizedSelectElement);

        document.addEventListener("keydown", this.onKeyDown);

        let stylizedSelectOptionlist = document.createElement("div");
        stylizedSelectOptionlist.classList.add("stylized_select_optionlist");
        this.stylizedElement.appendChild(stylizedSelectOptionlist);

        this.options.forEach(option => {
            let stylizedOptionElement = document.createElement('button');
            stylizedOptionElement.classList.add("stylized_select__option");
            stylizedOptionElement.innerHTML = option.innerHTML;
            stylizedOptionElement.dataset.value = option.value;
            stylizedOptionElement.tabIndex = -1;
            stylizedOptionElement.addEventListener('click', this.onOptionClick);

            this.optionDictionary.push({option: option, stylizedOption: stylizedOptionElement});
            stylizedSelectOptionlist.appendChild(stylizedOptionElement);
        });

        document.addEventListener('click', this.onBlur);
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.stylizedSelect').forEach((element) => new StylizedSelect(element)));