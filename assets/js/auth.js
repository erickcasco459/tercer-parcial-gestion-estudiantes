'use strict';

/** Login y auto-registro (fetch + FormData + SweetAlert2). Depende de app.js y clave.js (registro). */
document.addEventListener('DOMContentLoaded', () => {
    const formLogin = document.getElementById('formLogin');
    const formRegistro = document.getElementById('formRegistro');

    if (formLogin) {
        formLogin.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!formLogin.checkValidity()) {
                formLogin.classList.add('was-validated');
                return;
            }
            const btn = document.getElementById('btnLogin');
            btn.disabled = true;
            const r = await App.post(formLogin.dataset.url, new FormData(formLogin));
            if (r.estado && r.redirect) {
                window.location.href = r.redirect;
                return;
            }
            btn.disabled = false;
            App.toast(false, r.mensaje || 'No se pudo iniciar sesión.');
            formLogin.elements.clave.value = '';
            formLogin.elements.clave.focus();
        });
    }

    if (formRegistro) {
        ['carnet', 'usuario'].forEach((campo) => {
            formRegistro.elements[campo].addEventListener('input', (e) => {
                e.target.value = campo === 'carnet' ? e.target.value.toUpperCase() : e.target.value.toLowerCase();
            });
        });

        formRegistro.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!formRegistro.checkValidity()) {
                formRegistro.classList.add('was-validated');
                return;
            }
            const btn = document.getElementById('btnRegistro');
            btn.disabled = true;
            const r = await App.post(formRegistro.dataset.url, new FormData(formRegistro));
            if (r.estado) {
                await window.mostrarClaveGenerada({ usuario: r.usuario, clave: r.clave });
                window.location.href = formRegistro.dataset.urlLogin;
                return;
            }
            btn.disabled = false;
            App.toast(false, r.mensaje);
        });
    }
});
