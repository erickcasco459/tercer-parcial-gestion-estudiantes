<?php
declare(strict_types=1);

/**
 * Front Controller. Uso: index.php?c=controlador&a=accion
 * Ejemplo: index.php?c=evento&a=index  ->  EventoController::index()
 * NO MODIFICAR: las ramas solo agregan controllers/, models/ y views/.
 */

define('BASE_PATH', __DIR__);

require_once BASE_PATH . '/config/EnvLoader.php';
require_once BASE_PATH . '/config/Database.php';

try {
    EnvLoader::load(BASE_PATH . '/.env');
} catch (RuntimeException $e) {
    http_response_code(500);
    exit('Error de configuración: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
ini_set('display_errors', $debug ? '1' : '0');
error_reporting(E_ALL);

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => $https]);
session_start();

/* ------------------------- Helpers globales ------------------------- */

/** Escapa salida HTML (protección XSS). Úsalo en TODAS las vistas. */
function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL absoluta basada en APP_URL. */
function base_url(string $path = ''): string
{
    return rtrim($_ENV['APP_URL'] ?? '', '/') . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . base_url($path));
    exit;
}

/** Mensaje flash. $tipo: success | error | warning | info */
function set_flash(string $tipo, string $mensaje): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/** Renderiza views/{$vista}.php dentro del layout. Ej: render('eventos/index', ['eventos' => $x]) */
function render(string $vista, array $datos = [], int $codigo = 200): void
{
    $archivo = BASE_PATH . '/views/' . $vista . '.php';
    if (!is_file($archivo)) {
        throw new RuntimeException("Vista no encontrada: $vista");
    }
    http_response_code($codigo);
    extract($datos, EXTR_SKIP);
    require BASE_PATH . '/views/layout/header.php';
    require $archivo;
    require BASE_PATH . '/views/layout/footer.php';
}

function error_page(int $codigo, string $titulo, string $mensaje): void
{
    http_response_code($codigo);
    $tituloPagina = $titulo;
    require BASE_PATH . '/views/layout/header.php';
    echo '<div class="text-center py-5"><h1 class="display-4">' . e($codigo) . '</h1>'
       . '<p class="lead">' . e($mensaje) . '</p>'
       . '<a class="btn btn-primary" href="' . e(base_url('index.php?c=auth&a=login')) . '">Volver al inicio</a></div>';
    require BASE_PATH . '/views/layout/footer.php';
}

/* ----------------------------- Autoload ----------------------------- */

spl_autoload_register(static function (string $clase): void {
    if (!preg_match('/^[A-Za-z][A-Za-z0-9]*$/', $clase)) {
        return;
    }
    foreach (['controllers', 'models'] as $dir) {
        $archivo = BASE_PATH . "/$dir/$clase.php";
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

/* ------------------------------ Router ------------------------------ */

$c = strtolower((string) ($_GET['c'] ?? 'auth'));
$a = strtolower((string) ($_GET['a'] ?? 'login'));

try {
    if (!preg_match('/^[a-z][a-z0-9]*$/', $c) || !preg_match('/^[a-z][a-z0-9_]*$/', $a)) {
        error_page(404, 'Página no encontrada', 'La ruta solicitada no es válida.');
        exit;
    }

    $clase = ucfirst($c) . 'Controller';

    if (!is_file(BASE_PATH . "/controllers/$clase.php") || !class_exists($clase)) {
        error_page(404, 'Módulo no disponible', 'El módulo solicitado aún no existe.');
        exit;
    }

    $controlador = new $clase();

    if (!is_callable([$controlador, $a])) {
        error_page(404, 'Acción no encontrada', 'La acción solicitada no existe en este módulo.');
        exit;
    }

    $controlador->$a();
} catch (Throwable $ex) {
    error_log($ex->getMessage() . ' @ ' . $ex->getFile() . ':' . $ex->getLine());
    error_page(500, 'Error interno', $debug ? $ex->getMessage() : 'Ocurrió un error inesperado.');
}
