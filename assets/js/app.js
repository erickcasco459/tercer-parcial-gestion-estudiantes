'use strict';

document.addEventListener('DOMContentLoaded', () => {
    // Cierra automáticamente las alertas flash tras 5 segundos
    document.querySelectorAll('.alert-dismissible').forEach((alerta) => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 5000);
    });

    // Confirmación para acciones destructivas: <a data-confirm="¿Seguro?"> o <form data-confirm="...">
    document.querySelectorAll('[data-confirm]').forEach((el) => {
        const evento = el.tagName === 'FORM' ? 'submit' : 'click';
        el.addEventListener(evento, (e) => {
            if (!window.confirm(el.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });
});
