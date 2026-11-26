<?php
declare(strict_types=1);

/**
 * Controlador de asistencia (solo admin). Valida, coordina y responde; sin SQL ni HTML.
 *   GET  index.php?c=asistencia&a=index              -> vista
 *   GET  index.php?c=asistencia&a=listar&evento_id=N -> JSON (inscritos del evento)
 *   POST index.php?c=asistencia&a=registrar          -> JSON (crea o actualiza)
 */
final class AsistenciaController extends BaseController
{
    private AsistenciaModel $asistencias;

    public function __construct()
    {
        $this->asistencias = new AsistenciaModel();
    }

    public function index(): void
    {
        $this->exigirAdmin();
        render('asistencias/index', [
            'tituloPagina' => 'Registro de asistencia',
            'scripts'      => ['assets/js/asistencias.js'],
            'eventos'      => $this->asistencias->listarEventos(),
            'estados'      => AsistenciaModel::ESTADOS,
        ]);
    }

    public function listar(): void
    {
        $this->exigirAdmin(true);
        $eventoId = filter_input(INPUT_GET, 'evento_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$eventoId) {
            json_response(false, 'Selecciona un evento válido.', [], 422);
        }
        try {
            json_response(true, '', ['datos' => $this->asistencias->listarInscritos($eventoId)]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo cargar la lista de inscritos.', [], 500);
        }
    }

    public function registrar(): void
    {
        $this->exigirPost();
        $this->exigirAdmin(true);
        $this->exigirCsrf();

        $inscripcionId = filter_var(
            $this->inputPost('inscripcion_id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if ($inscripcionId === false) {
            json_response(false, 'Inscripción inválida.', [], 422);
        }

        $estado = $this->inputPost('estado');
        if (!in_array($estado, AsistenciaModel::ESTADOS, true)) {
            json_response(false, 'El estado de asistencia no es válido.', [], 422);
        }

        $fecha = $this->normalizarFecha($this->inputPost('fecha'));
        if ($fecha === false) {
            json_response(false, 'La fecha de asistencia no es válida.', [], 422);
        }

        try {
            if (!$this->asistencias->existeInscripcion($inscripcionId)) {
                json_response(false, 'La inscripción no existe.', [], 404);
            }
            $this->asistencias->registrar($inscripcionId, $estado, $fecha);
            json_response(true, 'Asistencia registrada correctamente.');
        } catch (PDOException $e) {
            // 23000 = la inscripción se eliminó mientras se guardaba (FK)
            if ((string) $e->getCode() === '23000') {
                json_response(false, 'La inscripción ya no existe.', [], 409);
            }
            error_log($e->getMessage());
            json_response(false, 'No se pudo registrar la asistencia.', [], 500);
        }
    }

    /** '' => null (la BD usa la hora actual); válida => 'Y-m-d H:i:s'; inválida => false. */
    private function normalizarFecha(string $valor): string|false|null
    {
        if ($valor === '') {
            return null;
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}(:\d{2})?$/', $valor)) {
            return false;
        }
        $texto = str_replace('T', ' ', $valor);
        if (strlen($texto) === 16) {
            $texto .= ':00';
        }
        $f = DateTime::createFromFormat('!Y-m-d H:i:s', $texto);
        return ($f !== false && $f->format('Y-m-d H:i:s') === $texto) ? $texto : false;
    }
}
