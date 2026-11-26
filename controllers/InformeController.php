<?php
declare(strict_types=1);

/**
 * Controlador de informes. Admin: todos los estudiantes (filtro opcional).
 * Estudiante: solo su propio informe (id tomado de la sesión).
 *   GET index.php?c=informe&a=index                      -> vista
 *   GET index.php?c=informe&a=listar[&estudiante_id=N]   -> JSON
 */
final class InformeController extends BaseController
{
    private AsistenciaModel $asistencias;

    public function __construct()
    {
        $this->asistencias = new AsistenciaModel();
    }

    public function index(): void
    {
        $this->exigirLogin();
        $esAdmin = $this->esAdmin();
        render('informes/index', [
            'tituloPagina' => $esAdmin ? 'Informe de asistencia' : 'Mi informe',
            'scripts'      => ['assets/js/informes.js'],
            'esAdmin'      => $esAdmin,
            'estudiantes'  => $esAdmin ? $this->asistencias->listarEstudiantes() : [],
        ]);
    }

    public function listar(): void
    {
        $usuario = $this->exigirLogin(true);

        if ($this->esAdmin()) {
            $crudo        = trim((string) ($_GET['estudiante_id'] ?? ''));
            $estudianteId = null;
            if ($crudo !== '') {
                $estudianteId = filter_var($crudo, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($estudianteId === false) {
                    json_response(false, 'Estudiante inválido.', [], 422);
                }
            }
        } else {
            $estudianteId = (int) $usuario['id']; // un estudiante nunca ve datos ajenos
        }

        try {
            json_response(true, '', ['datos' => $this->asistencias->informe($estudianteId)]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo cargar el informe.', [], 500);
        }
    }
}
