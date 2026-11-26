<?php
declare(strict_types=1);

/**
 * Controlador de eventos: valida, coordina y responde. Sin SQL ni HTML.
 *   GET  index.php?c=evento&a=index    -> vista del listado (cualquier usuario autenticado)
 *   GET  index.php?c=evento&a=listar   -> JSON para Tabulator
 *   GET  index.php?c=evento&a=crear    -> vista del formulario (solo admin)
 *   POST index.php?c=evento&a=guardar  -> crea el evento, JSON (solo admin)
 */
final class EventoController extends BaseController
{
    private EventoModel $eventos;

    public function __construct()
    {
        $this->eventos = new EventoModel();
    }

    public function index(): void
    {
        $this->exigirLogin();
        render('eventos/index', [
            'tituloPagina' => 'Eventos',
            'scripts'      => ['assets/js/eventos.js'],
            'esAdmin'      => $this->esAdmin(),
        ]);
    }

    public function listar(): void
    {
        $this->exigirLogin(true);
        try {
            json_response(true, '', ['datos' => $this->eventos->listar()]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo cargar el listado de eventos.', [], 500);
        }
    }

    public function crear(): void
    {
        $this->exigirAdmin();
        render('eventos/crear', [
            'tituloPagina' => 'Nuevo evento',
            'scripts'      => ['assets/js/eventos.js'],
        ]);
    }

    public function guardar(): void
    {
        $this->exigirPost();
        $this->exigirAdmin(true);
        $this->exigirCsrf();

        [$datos, $errores] = $this->validar();
        if ($errores !== []) {
            json_response(false, implode(' ', $errores), ['errores' => $errores], 422);
        }

        try {
            if ($this->eventos->existeCodigo($datos['codigo'])) {
                json_response(false, 'Ya existe un evento con ese código.', [], 409);
            }
            $id = $this->eventos->crear($datos);
            json_response(true, 'Evento creado correctamente.', ['id' => $id], 201);
        } catch (PDOException $e) {
            // 23000 = violación de UNIQUE (código repetido por una petición simultánea)
            if ((string) $e->getCode() === '23000') {
                json_response(false, 'Ya existe un evento con ese código.', [], 409);
            }
            error_log($e->getMessage());
            json_response(false, 'No se pudo guardar el evento.', [], 500);
        }
    }

    /** @return array{0: array, 1: string[]} [datos limpios, errores] */
    private function validar(): array
    {
        $errores     = [];
        $codigo      = strtoupper($this->inputPost('codigo'));
        $nombre      = $this->inputPost('nombre');
        $lugar       = $this->inputPost('lugar');
        $descripcion = $this->inputPost('descripcion');
        $fecha       = $this->inputPost('fecha');
        $hora        = $this->inputPost('hora');

        if (!preg_match('/^[A-Z0-9_-]{1,20}$/', $codigo)) {
            $errores[] = 'El código es obligatorio (máx. 20: letras, números, guion o guion bajo).';
        }
        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $errores[] = 'El nombre es obligatorio (máx. 150 caracteres).';
        }
        if ($lugar === '' || mb_strlen($lugar) > 150) {
            $errores[] = 'El lugar es obligatorio (máx. 150 caracteres).';
        }
        if (mb_strlen($descripcion) > 2000) {
            $errores[] = 'La descripción no puede superar 2000 caracteres.';
        }

        // Fecha real (rechaza 2026-02-31)
        $f = DateTime::createFromFormat('!Y-m-d', $fecha);
        if ($f === false || $f->format('Y-m-d') !== $fecha) {
            $errores[] = 'La fecha no es válida.';
        }

        // Hora HH:MM o HH:MM:SS -> se guarda como HH:MM:00
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $hora)) {
            $errores[] = 'La hora no es válida.';
            $hora = '';
        } else {
            $hora = substr($hora, 0, 5) . ':00';
        }

        $cupo = filter_var(
            $this->inputPost('cupo_disponible'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0]]
        );
        if ($cupo === false) {
            $errores[] = 'El cupo debe ser un número entero mayor o igual a 0.';
            $cupo = 0;
        }

        return [[
            'codigo'          => $codigo,
            'nombre'          => $nombre,
            'descripcion'     => $descripcion !== '' ? $descripcion : null,
            'lugar'           => $lugar,
            'fecha'           => $fecha,
            'hora'            => $hora,
            'cupo_disponible' => $cupo,
        ], $errores];
    }
}
