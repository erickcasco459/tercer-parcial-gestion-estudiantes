'use strict';

/**
 * Registro de asistencia (admin): elige un evento, lista sus inscritos (Tabulator)
 * y registra estado + fecha en un formulario SweetAlert2 (fetch + FormData).
 * URLs y estados vienen de data-* en la vista. Depende de assets/js/app.js (window.App).
 */
document.addEventListener('DOMContentLoaded', () => {
    const raiz = document.getElementById('asistencias-app');
    if (!raiz) return;

    const urls = { listar: raiz.dataset.urlListar, registrar: raiz.dataset.urlRegistrar };
    const estados = JSON.parse(raiz.dataset.estados || '[]');
    const selEvento = document.getElementById('evento');

    const fmtFecha = (v) => (v ? v.split('-').reverse().join('/') : '');
    // 'YYYY-MM-DD HH:MM:SS' -> 'DD/MM/YYYY HH:MM'
    const fmtFechaHora = (v) => (v ? `${fmtFecha(v.slice(0, 10))} ${v.slice(11, 16)}` : '');
    const pad = (n) => String(n).padStart(2, '0');
    // Date -> valor para <input type="datetime-local">
    const aInputLocal = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;

    const claseEstado = { Presente: 'success', Ausente: 'danger', Justificado: 'warning' };

    // Insignia creada con DOM (textContent evita inyectar HTML)
    function insignia(estado) {
        const span = document.createElement('span');
        span.className = `badge text-bg-${claseEstado[estado] || 'secondary'}`;
        span.textContent = estado || 'Sin registrar';
        return span;
    }

    const tabla = new Tabulator('#tabla-asistencias', {
        data: [],
        ajaxConfig: { method: 'GET', headers: { Accept: 'application/json' } },
        ajaxResponse: (url, params, resp) => {
            if (!resp.estado) {
                App.toast(false, resp.mensaje || 'No se pudo cargar la lista.');
                return [];
            }
            return resp.datos;
        },
        layout: 'fitColumns',
        pagination: true,
        paginationSize: 10,
        paginationSizeSelector: [5, 10, 20, 50],
        placeholder: 'Selecciona un evento para ver sus inscritos',
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
            { title: 'Carnet', field: 'carnet', width: 120 },
            { title: 'Apellido', field: 'apellido', minWidth: 140 },
            { title: 'Nombre', field: 'nombre', minWidth: 140 },
            { title: 'Inscrito el', field: 'fecha_inscripcion', width: 160, formatter: (c) => fmtFechaHora(c.getValue()) },
            { title: 'Asistencia', field: 'estado', width: 130, hozAlign: 'center', formatter: (c) => insignia(c.getValue()) },
            { title: 'Fecha asistencia', field: 'fecha_asistencia', width: 160, formatter: (c) => fmtFechaHora(c.getValue()) },
            {
                title: 'Acción', hozAlign: 'center', width: 120, headerSort: false,
                formatter: () => '<button type="button" class="btn btn-sm btn-primary" data-accion="registrar">'
                    + '<i class="bi bi-clipboard-check me-1"></i>Registrar</button>',
                cellClick: (e, cell) => {
                    if (e.target.closest('[data-accion="registrar"]')) registrar(cell.getRow().getData());
                },
            },
        ],
    });

    tabla.on('dataLoaded', (data) => {
        document.getElementById('total-registros').textContent = `Total de inscritos: ${data.length}`;
    });
    tabla.on('dataLoadError', () => App.toast(false, 'No se pudo cargar la lista.'));

    selEvento.addEventListener('change', () => {
        const id = selEvento.value;
        if (id === '') {
            tabla.setData([]);
            document.getElementById('total-registros').textContent = '';
            return;
        }
        tabla.setData(urls.listar, { evento_id: id });
    });

    // Formulario SweetAlert2: el HTML es estático; los datos se asignan por DOM
    async function registrar(fila) {
        const { isConfirmed, value } = await Swal.fire({
            title: 'Registrar asistencia',
            html: '<p id="sw-quien" class="text-secondary mb-3"></p>'
                + '<select id="sw-estado" class="form-select mb-2" aria-label="Estado"></select>'
                + '<input id="sw-fecha" type="datetime-local" class="form-control" aria-label="Fecha de asistencia">',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            didOpen: () => {
                document.getElementById('sw-quien').textContent = `${fila.carnet} · ${fila.nombre} ${fila.apellido}`;
                const sel = document.getElementById('sw-estado');
                estados.forEach((est) => sel.add(new Option(est, est)));
                sel.value = fila.estado || estados[0];
                document.getElementById('sw-fecha').value = fila.fecha_asistencia
                    ? fila.fecha_asistencia.replace(' ', 'T').slice(0, 16)
                    : aInputLocal(new Date());
            },
            preConfirm: () => ({
                estado: document.getElementById('sw-estado').value,
                fecha: document.getElementById('sw-fecha').value,
            }),
        });
        if (!isConfirmed) return;

        const datos = new FormData();
        datos.append('inscripcion_id', fila.inscripcion_id);
        datos.append('estado', value.estado);
        datos.append('fecha', value.fecha);
        const r = await App.post(urls.registrar, datos);
        App.toast(r.estado, r.mensaje);
        if (r.estado) tabla.replaceData(); // recarga conservando la página actual
    }
});
