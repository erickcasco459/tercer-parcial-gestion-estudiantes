<?php
declare(strict_types=1);

/**
 * Controlador de inscripciones: el estudiante autenticado se inscribe a sí mismo
 * (id tomado de $_SESSION['usuario']['id'], nunca del formulario).
 *   GET  index.php?c=inscripcion&a=index                -> vista
 *   GET  index.php?c=inscripcion&a=listar_disponibles   -> JSON (eventos con cupo)
 *   GET  index.php?c=inscripcion&a=listar_mias          -> JSON (mis inscripciones)
 *   POST index.php?c=inscripcion&a=inscribir            -> JSON
 */
final class InscripcionController extends BaseController
{
    private InscripcionModel $inscripciones;

    public function __construct()
    {
        $this->inscripciones = new InscripcionModel();
    }

    public function index(): void
    {
        $this->exigirLogin();
        render('inscripciones/index', [
            'tituloPagina' => 'Inscripciones',
            'scripts'      => ['assets/js/inscripciones.js'],
        ]);
    }

    public function listar_disponibles(): void
    {
        $usuario = $this->exigirLogin(true);
        try {
            json_response(true, '', ['datos' => $this->inscripciones->listarDisponibles((int) $usuario['id'])]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo cargar los eventos disponibles.', [], 500);
        }
    }

    public function listar_mias(): void
    {
        $usuario = $this->exigirLogin(true);
        try {
            json_response(true, '', ['datos' => $this->inscripciones->listarPorEstudiante((int) $usuario['id'])]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo cargar tus inscripciones.', [], 500);
        }
    }

    public function inscribir(): void
    {
        $this->exigirPost();
        $usuario = $this->exigirLogin(true);
        $this->exigirCsrf();

        $eventoId = filter_var(
            $this->inputPost('evento_id'),
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );
        if ($eventoId === false) {
            json_response(false, 'Evento inválido.', [], 422);
        }

        try {
            $resultado = $this->inscripciones->inscribir((int) $usuario['id'], $eventoId);
        } catch (PDOException $e) {
            // 23000 = violación de integridad; 1062 = entrada duplicada (UNIQUE estudiante+evento)
            if ((string) $e->getCode() === '23000') {
                $duplicada = (int) ($e->errorInfo[1] ?? 0) === 1062;
                json_response(
                    false,
                    $duplicada ? 'Ya estás inscrito en este evento.' : 'No se pudo completar la inscripción.',
                    [],
                    409
                );
            }
            error_log($e->getMessage());
            json_response(false, 'No se pudo completar la inscripción.', [], 500);
        }

        switch ($resultado) {
            case InscripcionModel::OK:
                json_response(true, 'Inscripción realizada correctamente.', [], 201);
            case InscripcionModel::DUPLICADA:
                json_response(false, 'Ya estás inscrito en este evento.', [], 409);
            case InscripcionModel::SIN_CUPO:
                json_response(false, 'El evento ya no tiene cupo disponible.', [], 409);
            default:
                json_response(false, 'El evento no existe.', [], 404);
        }
    }
}
