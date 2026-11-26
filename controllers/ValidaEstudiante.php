<?php
declare(strict_types=1);

/**
 * Lógica compartida de alta de estudiantes (auto-registro y registro por el admin):
 * validar, evitar duplicados, generar clave, cifrarla con password_hash() y responder JSON.
 * La clase que lo use debe: extender BaseController, usar el trait GeneraClave
 * y declarar `private EstudianteModel $estudiantes;`
 */
trait ValidaEstudiante
{
    /** @return array{0: array, 1: string[]} [datos limpios, errores] */
    protected function validarEstudiante(): array
    {
        $errores  = [];
        $carnet   = strtoupper($this->inputPost('carnet'));
        $nombre   = (string) preg_replace('/\s+/u', ' ', $this->inputPost('nombre'));
        $apellido = (string) preg_replace('/\s+/u', ' ', $this->inputPost('apellido'));
        $usuario  = strtolower($this->inputPost('usuario'));
        $patronNombre = '/^\p{L}[\p{L} .\'-]*$/u';

        if (!preg_match('/^[A-Z0-9-]{3,20}$/', $carnet)) {
            $errores[] = 'El carnet debe tener entre 3 y 20 caracteres (letras, números o guion).';
        }
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100 || !preg_match($patronNombre, $nombre)) {
            $errores[] = 'El nombre es obligatorio (2 a 100 letras).';
        }
        if (mb_strlen($apellido) < 2 || mb_strlen($apellido) > 100 || !preg_match($patronNombre, $apellido)) {
            $errores[] = 'El apellido es obligatorio (2 a 100 letras).';
        }
        if (!preg_match('/^[a-z0-9._]{3,50}$/', $usuario)) {
            $errores[] = 'El usuario debe tener de 3 a 50 caracteres (minúsculas, números, punto o guion bajo).';
        }

        return [[
            'carnet'   => $carnet,
            'nombre'   => $nombre,
            'apellido' => $apellido,
            'usuario'  => $usuario,
        ], $errores];
    }

    /** Valida, crea el estudiante y responde JSON con la clave generada (UNA sola vez). Termina la ejecución. */
    protected function altaEstudiante(string $rol = 'estudiante'): void
    {
        [$datos, $errores] = $this->validarEstudiante();
        if ($errores !== []) {
            json_response(false, implode(' ', $errores), ['errores' => $errores], 422);
        }

        try {
            if ($this->estudiantes->existeCarnet($datos['carnet'])) {
                json_response(false, 'Ya existe un estudiante con ese carnet.', [], 409);
            }
            if ($this->estudiantes->existeUsuario($datos['usuario'])) {
                json_response(false, 'Ese nombre de usuario ya está en uso.', [], 409);
            }
            $clave          = $this->generarClave();
            $datos['clave'] = password_hash($clave, PASSWORD_BCRYPT);
            $id             = $this->estudiantes->crear($datos, $rol);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            if ($e->getCode() === '23000') {
                json_response(false, 'El carnet o el usuario ya están registrados.', [], 409);
            }
            json_response(false, 'No se pudo registrar al estudiante.', [], 500);
        }

        // La respuesta contiene la clave en texto plano: que nadie la cachee.
        header('Cache-Control: no-store');
        header('Pragma: no-cache');
        json_response(true, 'Estudiante registrado correctamente.', [
            'id'      => $id,
            'usuario' => $datos['usuario'],
            'clave'   => $clave,
        ], 201);
    }
}
