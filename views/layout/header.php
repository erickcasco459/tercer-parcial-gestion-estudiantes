<?php
declare(strict_types=1);

$tituloPagina = $tituloPagina ?? 'Inicio';
$appName      = $_ENV['APP_NAME'] ?? 'Control de Eventos';
$cActual      = strtolower((string) ($_GET['c'] ?? ''));

$menu = [
    'auth'        => ['Login',         'index.php?c=auth&a=login'],
    'estudiante'  => ['Estudiantes',   'index.php?c=estudiante&a=index'],
    'evento'      => ['Eventos',       'index.php?c=evento&a=index'],
    'inscripcion' => ['Inscripciones', 'index.php?c=inscripcion&a=index'],
    'asistencia'  => ['Asistencia',    'index.php?c=asistencia&a=index'],
    'informe'     => ['Informes',      'index.php?c=informe&a=index'],
];

// Bloque flash reutilizable: $_SESSION['flash'] = ['tipo' => 'success|error|warning|info', 'mensaje' => '...']
$usuarioSesion = $_SESSION['usuario'] ?? null;
$nombreUsuario = is_array($usuarioSesion)
    ? (string) ($usuarioSesion['nombre'] ?? $usuarioSesion['usuario'] ?? '')
    : (is_string($usuarioSesion) ? $usuarioSesion : '');

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$clasesFlash = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($tituloPagina) ?> | <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= e(base_url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="<?= e(base_url('index.php')) ?>"><?= e($appName) ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal"
                aria-controls="menuPrincipal" aria-expanded="false" aria-label="Alternar navegación">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menu as $clave => [$texto, $ruta]): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $cActual === $clave ? ' active' : '' ?>"
                           href="<?= e(base_url($ruta)) ?>"><?= e($texto) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (!empty($usuarioSesion)): ?>
                <span class="navbar-text text-white me-3"><?= e($nombreUsuario) ?></span>
                <a class="btn btn-outline-light btn-sm" href="<?= e(base_url('index.php?c=auth&a=logout')) ?>">Salir</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<main class="container py-4 flex-grow-1">
    <?php if (is_array($flash) && !empty($flash['mensaje'])): ?>
        <div class="alert alert-<?= e($clasesFlash[$flash['tipo'] ?? 'info'] ?? 'info') ?> alert-dismissible fade show" role="alert">
            <?= e($flash['mensaje']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>
