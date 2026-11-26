<?php
declare(strict_types=1);

/**
 * Utilidades comunes de seguridad para los controladores (sesión, rol, POST, CSRF).
 * Los controladores de negocio la extienden: class XController extends BaseController
 */
abstract class BaseController
{
    protected function usuarioActual(): ?array
    {
        $u = $_SESSION['usuario'] ?? null;
        return is_array($u) ? $u : null;
    }

    protected function esAdmin(): bool
    {
        return ($this->usuarioActual()['rol'] ?? '') === 'admin';
    }

    /** Exige sesión. Con $json=true responde 401 JSON; si no, redirige al login. */
    protected function exigirLogin(bool $json = false): array
    {
        $usuario = $this->usuarioActual();
        if ($usuario !== null) {
            return $usuario;
        }
        if ($json) {
            json_response(false, 'Tu sesión expiró. Inicia sesión nuevamente.', [], 401);
        }
        set_flash('warning', 'Debes iniciar sesión para continuar.');
        redirect('index.php?c=auth&a=login');
        exit;
    }

    /** Exige rol admin. Con $json=true responde 403 JSON; si no, muestra página 403. */
    protected function exigirAdmin(bool $json = false): void
    {
        $this->exigirLogin($json);
        if ($this->esAdmin()) {
            return;
        }
        if ($json) {
            json_response(false, 'No tienes permisos para esta acción.', [], 403);
        }
        error_page(403, 'Acceso denegado', 'No tienes permisos para ver esta página.');
        exit;
    }

    protected function exigirPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            json_response(false, 'Método no permitido.', [], 405);
        }
    }

    protected function exigirCsrf(): void
    {
        $token = $_POST['csrf'] ?? null;
        if (!is_string($token) || !csrf_verify($token)) {
            json_response(false, 'Token de seguridad inválido. Recarga la página.', [], 403);
        }
    }

    /** Lee un campo POST como texto recortado ('' si no existe o no es texto). */
    protected function inputPost(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }
}
