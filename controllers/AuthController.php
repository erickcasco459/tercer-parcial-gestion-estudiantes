<?php
declare(strict_types=1);

/**
 * Autenticación: login, auto-registro y logout.
 *   GET  index.php?c=auth&a=login          POST index.php?c=auth&a=autenticar
 *   GET  index.php?c=auth&a=registro       POST index.php?c=auth&a=auto_registro
 *   GET  index.php?c=auth&a=logout
 */
final class AuthController extends BaseController
{
    use GeneraClave;
    use ValidaEstudiante;

    private const MAX_INTENTOS = 5;
    private const VENTANA_SEG  = 300;
    // Hash bcrypt de una cadena aleatoria descartada: iguala tiempos cuando el usuario no existe.
    private const HASH_FICTICIO = '$2b$10$sObUs5pegw29b4SpPnmeiOqlMBkcbpjk6.r7noa5ddzaPH0rrD9u2';

    private EstudianteModel $estudiantes;

    public function __construct()
    {
        $this->estudiantes = new EstudianteModel();
    }

    public function login(): void
    {
        if ($this->usuarioActual() !== null) {
            redirect('index.php?c=evento&a=index');
        }
        render('auth/login', [
            'tituloPagina' => 'Iniciar sesión',
            'layoutSimple' => true,
            'scripts'      => ['assets/js/auth.js'],
        ]);
    }

    public function autenticar(): void
    {
        $this->exigirPost();
        $this->exigirCsrf();

        if ($this->bloqueado()) {
            json_response(false, 'Demasiados intentos fallidos. Espera unos minutos e inténtalo de nuevo.', [], 429);
        }

        $usuario = strtolower($this->inputPost('usuario'));
        $clave   = $_POST['clave'] ?? '';   // sin trim: las claves no se recortan
        if ($usuario === '' || !is_string($clave) || $clave === '') {
            json_response(false, 'Ingresa tu usuario y tu clave.', [], 422);
        }

        try {
            $fila = $this->estudiantes->buscarPorUsuario($usuario);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            json_response(false, 'No se pudo iniciar sesión. Inténtalo más tarde.', [], 500);
        }

        $hash = $fila['clave'] ?? self::HASH_FICTICIO;
        $ok   = password_verify($clave, $hash) && $fila !== null;
        if (!$ok) {
            $this->registrarFallo();
            json_response(false, 'Usuario o clave incorrectos.', [], 401);
        }

        session_regenerate_id(true);
        unset($_SESSION['login_intentos'], $_SESSION['csrf']);
        $_SESSION['usuario'] = [
            'id'      => (int) $fila['id'],
            'nombre'  => $fila['nombre'],
            'usuario' => $fila['usuario'],
            'rol'     => $fila['rol'],
        ];

        json_response(true, 'Bienvenido, ' . $fila['nombre'] . '.', [
            'redirect' => base_url('index.php?c=evento&a=index'),
        ]);
    }

    public function registro(): void
    {
        if ($this->usuarioActual() !== null) {
            redirect('index.php?c=evento&a=index');
        }
        render('auth/registro', [
            'tituloPagina' => 'Crear cuenta',
            'layoutSimple' => true,
            'scripts'      => ['assets/js/clave.js', 'assets/js/auth.js'],
        ]);
    }

    public function auto_registro(): void
    {
        $this->exigirPost();
        $this->exigirCsrf();
        if ($this->usuarioActual() !== null) {
            json_response(false, 'Ya has iniciado sesión.', [], 403);
        }
        $this->altaEstudiante('estudiante'); // el rol NUNCA viene del formulario
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires'  => time() - 42000,
                'path'     => $p['path'],
                'domain'   => $p['domain'],
                'secure'   => $p['secure'],
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        session_destroy();
        session_start();
        session_regenerate_id(true);
        set_flash('success', 'Sesión cerrada correctamente.');
        redirect('index.php?c=auth&a=login');
    }

    private function bloqueado(): bool
    {
        $i = $_SESSION['login_intentos'] ?? null;
        if (!is_array($i) || (time() - (int) ($i['desde'] ?? 0)) > self::VENTANA_SEG) {
            return false;
        }
        return (int) ($i['n'] ?? 0) >= self::MAX_INTENTOS;
    }

    private function registrarFallo(): void
    {
        $i = $_SESSION['login_intentos'] ?? null;
        if (!is_array($i) || (time() - (int) ($i['desde'] ?? 0)) > self::VENTANA_SEG) {
            $i = ['n' => 0, 'desde' => time()];
        }
        $i['n'] = (int) $i['n'] + 1;
        $_SESSION['login_intentos'] = $i;
    }
}
