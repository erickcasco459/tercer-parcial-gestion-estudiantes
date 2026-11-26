'use strict';

/**
 * Plantilla de módulo AJAX: Tabulator (listado) + modal (crear/editar) + fetch/FormData + SweetAlert2.
 * Las URLs vienen de data-* en la vista (nada quemado). Depende de assets/js/app.js (window.App).
 */
document.addEventListener('DOMContentLoaded', () => {
    const raiz = document.getElementById('eventos-app');
    if (!raiz) return;

    const esAdmin = raiz.dataset.esAdmin === '1';
    const urls = {
        listar: raiz.dataset.urlListar,
        obtener: raiz.dataset.urlObtener,
        guardar: raiz.dataset.urlGuardar,
        eliminar: raiz.dataset.urlEliminar,
    };

    const form = document.getElementById('formEvento');
    const modalEl = document.getElementById('modalEvento');
    const modal = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
    const btnGuardar = document.getElementById('btnGuardar');
    const tituloModal = document.getElementById('tituloModal');

    const fmtFecha = (v) => (v ? v.split('-').reverse().join('/') : '');

    // ---------- Tabulator ----------
    const columnas = [
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
    ];

    if (esAdmin) {
        columnas.push({
            title: 'Acciones', hozAlign: 'center', width: 120, headerSort: false,
            formatter: (c) => {
                const id = Number(c.getRow().getData().id);
                return `<button type="button" class="btn btn-sm btn-outline-primary me-1" data-accion="editar" data-id="${id}" title="Editar"><i class="bi bi-pencil"></i></button>`
                    + `<button type="button" class="btn btn-sm btn-outline-danger" data-accion="eliminar" data-id="${id}" title="Eliminar"><i class="bi bi-trash"></i></button>`;
            },
        });
    }

    const tabla = new Tabulator('#tabla-eventos', {
        ajaxURL: urls.listar,
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
        columns: columnas,
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

    if (!esAdmin) return;

    // ---------- Modal crear / editar ----------
    function abrirModal(evento = null) {
        form.reset();
        form.classList.remove('was-validated');
        form.elements.id.value = evento ? evento.id : '';
        tituloModal.textContent = evento ? 'Editar evento' : 'Nuevo evento';
        if (evento) {
            ['codigo', 'nombre', 'lugar', 'fecha', 'descripcion', 'cupo_disponible'].forEach((k) => {
                form.elements[k].value = evento[k] ?? '';
            });
            form.elements.hora.value = (evento.hora || '').slice(0, 5);
        }
        modal.show();
    }

    document.getElementById('btnNuevo').addEventListener('click', () => abrirModal());

    form.elements.codigo.addEventListener('input', (e) => {
        e.target.value = e.target.value.toUpperCase();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        btnGuardar.disabled = true;
        const r = await App.post(urls.guardar, new FormData(form));
        btnGuardar.disabled = false;
        App.toast(r.estado, r.mensaje);
        if (r.estado) {
            modal.hide();
            tabla.setData(); // vuelve a pedir el listado al servidor
        }
    });

    // ---------- Acciones de la tabla (delegación de eventos) ----------
    document.getElementById('tabla-eventos').addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-accion]');
        if (!btn) return;
        const id = btn.dataset.id;

        if (btn.dataset.accion === 'editar') {
            const r = await App.get(`${urls.obtener}&id=${encodeURIComponent(id)}`);
            if (!r.estado) return App.toast(false, r.mensaje);
            abrirModal(r.datos);
            return;
        }

        const ok = await App.confirmar({
            titulo: '¿Eliminar evento?',
            texto: 'También se eliminarán sus inscripciones y asistencias. Esta acción no se puede deshacer.',
            boton: 'Sí, eliminar',
        });
        if (!ok) return;
        const datos = new FormData();
        datos.append('id', id);
        const r = await App.post(urls.eliminar, datos);
        App.toast(r.estado, r.mensaje);
        if (r.estado) tabla.setData();
    });
});
