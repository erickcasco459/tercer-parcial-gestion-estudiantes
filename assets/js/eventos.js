'use strict';

/**
 * Módulo de eventos. Un solo script para dos vistas:
 *  - eventos/index : listado con Tabulator + buscador
 *  - eventos/crear : formulario (fetch + FormData + SweetAlert2)
 * Las URLs vienen de data-* en la vista. Depende de assets/js/app.js (window.App).
 */
document.addEventListener('DOMContentLoaded', () => {
    const contenedor = document.getElementById('tabla-eventos');
    const form = document.getElementById('formEvento');
    if (contenedor) iniciarListado(contenedor);
    if (form) iniciarFormulario(form);
});

const fmtFecha = (v) => (v ? v.split('-').reverse().join('/') : '');

function iniciarListado(contenedor) {
    const tabla = new Tabulator(contenedor, {
        ajaxURL: contenedor.dataset.urlListar,
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
        placeholder: 'No hay eventos registrados',
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
            { title: 'Código', field: 'codigo', width: 120 },
            { title: 'Nombre', field: 'nombre', minWidth: 180 },
            { title: 'Lugar', field: 'lugar', minWidth: 140 },
            { title: 'Fecha', field: 'fecha', width: 120, formatter: (c) => fmtFecha(c.getValue()) },
            { title: 'Hora', field: 'hora', width: 90, formatter: (c) => (c.getValue() || '').slice(0, 5) },
            {
                title: 'Cupo', field: 'cupo_disponible', width: 100, hozAlign: 'center', sorter: 'number',
                formatter: (c) => {
                    const n = Number(c.getValue()) || 0;
                    return `<span class="badge text-bg-${n > 0 ? 'success' : 'danger'}">${n}</span>`;
                },
            },
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
        // Arreglo anidado = OR entre filtros
        tabla.setFilter([[
            { field: 'codigo', type: 'like', value: v },
            { field: 'nombre', type: 'like', value: v },
            { field: 'lugar', type: 'like', value: v },
        ]]);
    });
}

function iniciarFormulario(form) {
    const btn = document.getElementById('btnGuardar');

    // El código siempre en mayúsculas
    form.elements.codigo.addEventListener('input', (e) => {
        e.target.value = e.target.value.toUpperCase();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        btn.disabled = true;
        const r = await App.post(form.dataset.urlGuardar, new FormData(form));

        if (r.estado) {
            App.toast(true, r.mensaje);
            setTimeout(() => { window.location.href = form.dataset.urlIndex; }, 1200);
            return;
        }
        btn.disabled = false;
        if (r.errores) {
            Swal.fire({ icon: 'error', title: 'Revisa los datos', text: r.mensaje });
        } else {
            App.toast(false, r.mensaje);
        }
    });
}
