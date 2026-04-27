document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Deseas eliminar este producto?')) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-sku-input]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.toUpperCase().replace(/\s+/g, '-');
    });
});
