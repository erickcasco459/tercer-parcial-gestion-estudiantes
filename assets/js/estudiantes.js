'use strict';

/** Módulo Estudiantes (admin): listado Tabulator + alta con clave generada. Depende de app.js y clave.js. */
document.addEventListener('DOMContentLoaded', () => {
    const fmtFecha = (v) => (v ? v.slice(0, 10).split('-').reverse().join('/') : '');

    // ---------- Listado ----------
    const raiz = document.getElementById('estudiantes-app');
    if (raiz) {
        const tabla = new Tabulator('#tabla-estudiantes', {
            ajaxURL: raiz.dataset.urlListar,
            ajaxConfig: { method: 'GET', headers: { Accept: 'application/json' } },
            ajaxResponse: (url, params, resp) => {
                if (!resp.estado) {
                    App.toast(false, resp.mensaje || 'No se pudo cargar el listado.');
                    return [];
                }
                return resp.datos;
            },
            layout: 'fitColumns',
            pagination: true,
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 50],
            placeholder: 'No hay estudiantes registrados',
            locale: 'es',
            langs: {
                es: {
                    pagination: {
                        page_size: 'Filas', first: 'Primera', first_title: 'Primera página',
                        last: 'Última', last_title: 'Última página', prev: 'Anterior', prev_title: 'Página anterior',
                        next: 'Siguiente', next_title: 'Página siguiente',
                    },
                },
            },
            columns: [
                { title: 'Carnet', field: 'carnet', width: 130 },
                { title: 'Nombre', field: 'nombre', minWidth: 140 },
                { title: 'Apellido', field: 'apellido', minWidth: 140 },
                { title: 'Usuario', field: 'usuario', minWidth: 130 },
                {
                    title: 'Rol', field: 'rol', width: 120, hozAlign: 'center',
                    formatter: (c) => {
                        const esAdmin = c.getValue() === 'admin';
                        return `<span class="badge text-bg-${esAdmin ? 'dark' : 'secondary'}">${esAdmin ? 'admin' : 'estudiante'}</span>`;
                    },
                },
                { title: 'Registro', field: 'creado_en', width: 120, formatter: (c) => fmtFecha(c.getValue()) },
            ],
        });

        tabla.on('dataLoaded', (data) => {
            document.getElementById('total-registros').textContent = `Total de registros: ${data.length}`;
        });
        tabla.on('dataLoadError', () => App.toast(false, 'No se pudo cargar el listado.'));

        document.getElementById('filtro').addEventListener('input', (e) => {
            const v = e.target.value.trim();
            if (v === '') {
                tabla.clearFilter(true);
                return;
            }
            tabla.setFilter([[
                { field: 'carnet', type: 'like', value: v },
                { field: 'nombre', type: 'like', value: v },
                { field: 'apellido', type: 'like', value: v },
                { field: 'usuario', type: 'like', value: v },
            ]]);
        });
    }

    // ---------- Alta ----------
    const form = document.getElementById('formEstudiante');
    if (form) {
        form.elements.carnet.addEventListener('input', (e) => { e.target.value = e.target.value.toUpperCase(); });
        form.elements.usuario.addEventListener('input', (e) => { e.target.value = e.target.value.toLowerCase(); });

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                return;
            }
            const btn = document.getElementById('btnGuardar');
            btn.disabled = true;
            const r = await App.post(form.dataset.urlGuardar, new FormData(form));
            if (r.estado) {
                await window.mostrarClaveGenerada({ usuario: r.usuario, clave: r.clave });
                window.location.href = form.dataset.urlLista;
                return;
            }
            btn.disabled = false;
            App.toast(false, r.mensaje);
        });
    }
});
