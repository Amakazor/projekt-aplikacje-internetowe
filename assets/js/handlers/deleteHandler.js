class DeleteHandler {
    location;

    delete = () => {
        fetch(this.location, {
            method: 'DELETE',
        }).then(response => {
            window.location.reload();
        })
    };

    onClick = () => {
        this.delete();
    };

    constructor(element) {
        this.location = element.dataset.location;
        element.addEventListener('click', this.onClick);
    }
}

document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.Delete').forEach((element) => new DeleteHandler(element)));