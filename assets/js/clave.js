'use strict';

/**
 * Muestra la clave generada UNA sola vez (no se auto-cierra). Devuelve una Promise que se
 * resuelve al cerrar el diálogo. Se construye con DOM (textContent) para evitar XSS.
 */
window.mostrarClaveGenerada = ({ usuario, clave }) => {
    const cont = document.createElement('div');

    const pUsuario = document.createElement('p');
    pUsuario.append('Usuario: ');
    const strong = document.createElement('strong');
    strong.textContent = usuario;
    pUsuario.append(strong);

    const code = document.createElement('code');
    code.className = 'd-block fs-3 user-select-all my-3';
    code.textContent = clave;

    const aviso = document.createElement('p');
    aviso.className = 'text-danger mb-0';
    aviso.textContent = 'Guárdala ahora: no se volverá a mostrar.';

    cont.append(pUsuario, code, aviso);

    return Swal.fire({
        icon: 'success',
        title: 'Registro exitoso',
        html: cont,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showDenyButton: true,
        denyButtonText: 'Copiar clave',
        confirmButtonText: 'Ya la guardé',
        preDeny: async () => {
            try {
                await navigator.clipboard.writeText(clave);
                Swal.getDenyButton().textContent = '¡Copiada!';
            } catch (_) {
                Swal.getDenyButton().textContent = 'Selecciónala y copia manualmente';
            }
            return false; // mantiene abierto el diálogo
        },
    });
};
