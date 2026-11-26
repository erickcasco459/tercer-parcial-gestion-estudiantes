<?php
declare(strict_types=1);

/**
 * Controlador de eventos: coordina, valida y responde. Sin SQL ni HTML.
 *   GET  index.php?c=evento&a=index     -> vista
 *   GET  index.php?c=evento&a=listar    -> JSON (Tabulator)
 *   GET  index.php?c=evento&a=obtener&id=N
 *   POST index.php?c=evento&a=guardar   -> crea (id vacío) o actualiza
 *   POST index.php?c=evento&a=eliminar
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

    public function obtener(): void
    {
        $this->exigirLogin(true);
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if (!$id || $id < 1) {
            json_response(false, 'Identificador inválido.', [], 422);
        }
        try {
            $evento = $this->eventos->obtener($id);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo consultar el evento.', [], 500);
        }
        if ($evento === null) {
            json_response(false, 'El evento no existe.', [], 404);
        }
        json_response(true, '', ['datos' => $evento]);
    }

    public function guardar(): void
    {
        $this->exigirPost();
        $this->exigirAdmin(true);
        $this->exigirCsrf();

        $id = (int) $this->inputPost('id');
        [$datos, $errores] = $this->validar();
        if ($errores !== []) {
            json_response(false, implode(' ', $errores), ['errores' => $errores], 422);
        }

        try {
            if ($this->eventos->existeCodigo($datos['codigo'], $id > 0 ? $id : null)) {
                json_response(false, 'Ya existe un evento con ese código.', [], 409);
            }
            if ($id > 0) {
                if ($this->eventos->obtener($id) === null) {
                    json_response(false, 'El evento no existe.', [], 404);
                }
                $this->eventos->actualizar($id, $datos);
                json_response(true, 'Evento actualizado correctamente.', ['id' => $id]);
            }
            $nuevoId = $this->eventos->crear($datos);
            json_response(true, 'Evento creado correctamente.', ['id' => $nuevoId], 201);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo guardar el evento.', [], 500);
        }
    }

    public function eliminar(): void
    {
        $this->exigirPost();
        $this->exigirAdmin(true);
        $this->exigirCsrf();

        $id = (int) $this->inputPost('id');
        if ($id < 1) {
            json_response(false, 'Identificador inválido.', [], 422);
        }
        try {
            if (!$this->eventos->eliminar($id)) {
                json_response(false, 'El evento no existe.', [], 404);
            }
            json_response(true, 'Evento eliminado correctamente.');
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo eliminar el evento.', [], 500);
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

        $f = DateTime::createFromFormat('!Y-m-d', $fecha);
        if ($f === false || $f->format('Y-m-d') !== $fecha) {
            $errores[] = 'La fecha no es válida.';
        }

        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $hora)) {
            $errores[] = 'La hora no es válida.';
            $hora = '';
        } else {
            $hora = substr($hora, 0, 5) . ':00';
        }

        $cupo = filter_var($this->inputPost('cupo_disponible'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
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
