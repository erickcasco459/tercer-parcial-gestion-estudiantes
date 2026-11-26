<?php
declare(strict_types=1);

/**
 * Módulo de estudiantes (solo administrador).
 *   GET  index.php?c=estudiante&a=index    -> listado (vista)
 *   GET  index.php?c=estudiante&a=listar   -> JSON (Tabulator)
 *   GET  index.php?c=estudiante&a=crear    -> formulario (vista)
 *   POST index.php?c=estudiante&a=guardar  -> alta con clave generada
 */
final class EstudianteController extends BaseController
{
    use GeneraClave;
    use ValidaEstudiante;

    private EstudianteModel $estudiantes;

    public function __construct()
    {
        $this->estudiantes = new EstudianteModel();
    }

    public function index(): void
    {
        $this->exigirAdmin();
        render('estudiantes/index', [
            'tituloPagina' => 'Estudiantes',
            'scripts'      => ['assets/js/estudiantes.js'],
        ]);
    }

    public function listar(): void
    {
        $this->exigirAdmin(true);
        try {
            json_response(true, '', ['datos' => $this->estudiantes->listar()]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo cargar el listado de estudiantes.', [], 500);
        }
    }

    public function crear(): void
    {
        $this->exigirAdmin();
        render('estudiantes/crear', [
            'tituloPagina' => 'Registrar estudiante',
            'scripts'      => ['assets/js/clave.js', 'assets/js/estudiantes.js'],
        ]);
    }

    public function guardar(): void
    {
        $this->exigirPost();
        $this->exigirAdmin(true);
        $this->exigirCsrf();
        $this->altaEstudiante('estudiante');
    }
}
