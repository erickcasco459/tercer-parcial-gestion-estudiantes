'use strict';

/**
 * Módulo de inscripciones (estudiante autenticado): dos tablas Tabulator
 * (eventos con cupo y mis inscripciones) + inscripción con fetch/FormData + SweetAlert2.
 * Las URLs vienen de data-* en la vista. Depende de assets/js/app.js (window.App).
 */
document.addEventListener('DOMContentLoaded', () => {
    const raiz = document.getElementById('inscripciones-app');
    if (!raiz) return;

    const urls = {
        disponibles: raiz.dataset.urlDisponibles,
        mias: raiz.dataset.urlMias,
        inscribir: raiz.dataset.urlInscribir,
    };

    const fmtFecha = (v) => (v ? v.split('-').reverse().join('/') : '');
    const fmtHora = (v) => (v || '').slice(0, 5);
    // 'YYYY-MM-DD HH:MM:SS' -> 'DD/MM/YYYY HH:MM'
    const fmtFechaHora = (v) => (v ? `${fmtFecha(v.slice(0, 10))} ${v.slice(11, 16)}` : '');

    const idioma = {
        es: {
            pagination: {
                page_size: 'Filas', first: 'Primera', first_title: 'Primera página',
                last: 'Última', last_title: 'Última página', prev: 'Anterior', prev_title: 'Página anterior',
                next: 'Siguiente', next_title: 'Página siguiente',
            },
        },
    };

    // Crea una tabla con su contador de registros
    function crearTabla(selector, url, columnas, placeholder, idContador) {
        const tabla = new Tabulator(selector, {
            ajaxURL: url,
            ajaxConfig: { method: 'GET', headers: { Accept: 'application/json' } },
            ajaxResponse: (u, params, resp) => {
                if (!resp.estado) {
                    App.toast(false, resp.mensaje || 'No se pudo cargar el listado.');
                    return [];
                }
                return resp.datos;
            },
            layout: 'fitColumns',
            pagination: true,
            paginationSize: 5,
            paginationSizeSelector: [5, 10, 20],
            placeholder,
            locale: 'es',
            langs: idioma,
            columns: columnas,
        });
        tabla.on('dataLoaded', (data) => {
            document.getElementById(idContador).textContent = `Total de registros: ${data.length}`;
        });
        tabla.on('dataLoadError', () => App.toast(false, 'No se pudo cargar el listado.'));
        return tabla;
    }

    // Recarga ambas tablas desde el servidor
    const recargar = () => {
        tablaDisponibles.setData();
        tablaMias.setData();
    };

    async function inscribir(evento, boton) {
        const ok = await App.confirmar({
            titulo: '¿Inscribirte a este evento?',
            texto: `${evento.codigo} · ${evento.nombre}`,
            boton: 'Sí, inscribirme',
        });
        if (!ok) return;

        boton.disabled = true;
        const datos = new FormData();
        datos.append('evento_id', evento.id);
        const r = await App.post(urls.inscribir, datos);
        App.toast(r.estado, r.mensaje);
        recargar(); // también si falla: el cupo o la inscripción pudieron cambiar
    }

    const tablaDisponibles = crearTabla('#tabla-disponibles', urls.disponibles, [
        { title: 'Código', field: 'codigo', width: 120 },
        { title: 'Nombre', field: 'nombre', minWidth: 180 },
        { title: 'Lugar', field: 'lugar', minWidth: 140 },
        { title: 'Fecha', field: 'fecha', width: 120, formatter: (c) => fmtFecha(c.getValue()) },
        { title: 'Hora', field: 'hora', width: 90, formatter: (c) => fmtHora(c.getValue()) },
        {
            title: 'Cupo', field: 'cupo_disponible', width: 90, hozAlign: 'center', sorter: 'number',
            formatter: (c) => `<span class="badge text-bg-success">${Number(c.getValue()) || 0}</span>`,
        },
        {
            title: 'Acción', hozAlign: 'center', width: 140, headerSort: false,
            formatter: () => '<button type="button" class="btn btn-sm btn-primary" data-accion="inscribir">'
                + '<i class="bi bi-journal-plus me-1"></i>Inscribirme</button>',
            cellClick: (e, cell) => {
                const boton = e.target.closest('[data-accion="inscribir"]');
                if (boton) inscribir(cell.getRow().getData(), boton);
            },
        },
    ], 'No hay eventos con cupo disponible', 'total-disponibles');

    const tablaMias = crearTabla('#tabla-mias', urls.mias, [
        { title: 'Código', field: 'codigo', width: 120 },
        { title: 'Evento', field: 'nombre', minWidth: 180 },
        { title: 'Lugar', field: 'lugar', minWidth: 140 },
        { title: 'Fecha', field: 'fecha', width: 120, formatter: (c) => fmtFecha(c.getValue()) },
        { title: 'Hora', field: 'hora', width: 90, formatter: (c) => fmtHora(c.getValue()) },
        { title: 'Inscrito el', field: 'fecha_inscripcion', width: 170, formatter: (c) => fmtFechaHora(c.getValue()) },
    ], 'Aún no tienes inscripciones', 'total-mias');
});
