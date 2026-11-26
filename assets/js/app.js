'use strict';

/**
 * Utilidades compartidas (fetch JSON + SweetAlert2). Contrato de respuesta del servidor:
 *   {"estado": true|false, "mensaje": "...", ...extra}
 */
window.App = (() => {
    const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    async function request(url, opciones = {}) {
        try {
            const r = await fetch(url, {
                credentials: 'same-origin',
                headers: { Accept: 'application/json' },
                ...opciones,
            });
            return await r.json(); // json_response también responde JSON en 4xx/5xx
        } catch (_) {
            return { estado: false, mensaje: 'No se pudo conectar con el servidor.' };
        }
    }

    const get = (url) => request(url);

    function post(url, formData) {
        if (!formData.has('csrf')) {
            formData.append('csrf', csrfToken());
        }
        return request(url, { method: 'POST', body: formData });
    }

    // Se usa `text` (texto plano) y no `html`, para evitar XSS con mensajes del servidor.
    function toast(estado, mensaje) {
        return Swal.fire({
            toast: true,
            position: 'top-end',
            icon: estado ? 'success' : 'error',
            title: estado ? 'Listo' : 'Atención',
            text: mensaje,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
    }

    async function confirmar({ titulo = '¿Estás seguro?', texto = '', boton = 'Sí, continuar' } = {}) {
        const r = await Swal.fire({
            title: titulo,
            text: texto,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: boton,
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
        });
        return r.isConfirmed;
    }

    return { csrfToken, get, post, toast, confirmar };
})();

document.addEventListener('DOMContentLoaded', () => {
    // Cierra automáticamente las alertas flash tras 5 segundos
    document.querySelectorAll('.alert-dismissible').forEach((alerta) => {
        setTimeout(() => bootstrap.Alert.getOrCreateInstance(alerta).close(), 5000);
    });
});
