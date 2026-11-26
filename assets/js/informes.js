'use strict';

/**
 * Informe de eventos inscritos, fecha de inscripción y asistencia (Tabulator 6).
 * Admin: filtro por estudiante. Estudiante: el servidor devuelve solo lo suyo.
 * URLs desde data-* en la vista. Depende de assets/js/app.js (window.App).
 */
document.addEventListener('DOMContentLoaded', () => {
    const raiz = document.getElementById('informes-app');
    if (!raiz) return;

    const fmtFecha = (v) => (v ? v.split('-').reverse().join('/') : '');
    const fmtFechaHora = (v) => (v ? `${fmtFecha(v.slice(0, 10))} ${v.slice(11, 16)}` : '');
    const claseEstado = { Presente: 'success', Ausente: 'danger', Justificado: 'warning' };

    // Insignia creada con DOM (textContent evita inyectar HTML)
    function insignia(estado) {
        const span = document.createElement('span');
        span.className = `badge text-bg-${claseEstado[estado] || 'secondary'}`;
        span.textContent = estado || 'Sin registrar';
        return span;
    }

    const tabla = new Tabulator('#tabla-informe', {
        ajaxURL: raiz.dataset.urlListar,
        ajaxConfig: { method: 'GET', headers: { Accept: 'application/json' } },
        ajaxResponse: (url, params, resp) => {
            if (!resp.estado) {
                App.toast(false, resp.mensaje || 'No se pudo cargar el informe.');
                return [];
            }
            return resp.datos;
        },
        layout: 'fitColumns',
        pagination: true,
        paginationSize: 10,
        paginationSizeSelector: [5, 10, 20, 50],
        placeholder: 'No hay inscripciones para mostrar',
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
            { title: 'Carnet', field: 'carnet', width: 110 },
            { title: 'Estudiante', field: 'estudiante', minWidth: 160 },
            { title: 'Código', field: 'codigo', width: 110 },
            { title: 'Evento', field: 'evento', minWidth: 170 },
            { title: 'Fecha evento', field: 'fecha', width: 130, formatter: (c) => fmtFecha(c.getValue()) },
            { title: 'Inscrito el', field: 'fecha_inscripcion', width: 160, formatter: (c) => fmtFechaHora(c.getValue()) },
            {
                title: 'Asistencia', field: 'estado', width: 130, hozAlign: 'center',
                formatter: (c) => insignia(c.getValue()),
                // En el CSV se exporta el texto, no el HTML de la insignia
                accessorDownload: (valor) => valor || 'Sin registrar',
            },
            { title: 'Fecha asistencia', field: 'fecha_asistencia', width: 160, formatter: (c) => fmtFechaHora(c.getValue()) },
        ],
    });

    // Resumen por estado calculado en el cliente
    tabla.on('dataLoaded', (data) => {
        const cuenta = { Presente: 0, Ausente: 0, Justificado: 0, 'Sin registrar': 0 };
        data.forEach((f) => { cuenta[f.estado || 'Sin registrar'] += 1; });
        document.getElementById('resumen').textContent = `Total: ${data.length} · `
            + Object.entries(cuenta).map(([k, v]) => `${k}: ${v}`).join(' · ');
    });
    tabla.on('dataLoadError', () => App.toast(false, 'No se pudo cargar el informe.'));

    const filtro = document.getElementById('filtro-estudiante');
    if (filtro) {
        filtro.addEventListener('change', () => {
            tabla.setData(raiz.dataset.urlListar, filtro.value === '' ? {} : { estudiante_id: filtro.value });
        });
    }

    document.getElementById('btnCsv').addEventListener('click', () => {
        tabla.download('csv', 'informe-asistencia.csv');
    });
});
