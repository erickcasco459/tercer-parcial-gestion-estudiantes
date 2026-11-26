<?php
declare(strict_types=1);

/**
 * Layout AdminLTE 4. Variables opcionales que puede pasar render():
 *   $tituloPagina (string)  $layoutSimple (bool: sin menú, para login)  $scripts (en footer.php)
 */
$tituloPagina = $tituloPagina ?? 'Inicio';
$layoutSimple = $layoutSimple ?? false;
$appName      = $_ENV['APP_NAME'] ?? 'Control de Eventos';
$cActual      = strtolower((string) ($_GET['c'] ?? ''));

// Usuario de sesión: array (['nombre'] o ['usuario']) o string
$usuarioSesion = $_SESSION['usuario'] ?? null;
$haySesion     = !empty($usuarioSesion);
$nombreUsuario = is_array($usuarioSesion)
    ? (string) ($usuarioSesion['nombre'] ?? $usuarioSesion['usuario'] ?? '')
    : (is_string($usuarioSesion) ? $usuarioSesion : '');

$menu = [
    'auth'        => ['Login',         'index.php?c=auth&a=login',         'bi-box-arrow-in-right'],
    'estudiante'  => ['Estudiantes',   'index.php?c=estudiante&a=index',   'bi-people'],
    'evento'      => ['Eventos',       'index.php?c=evento&a=index',       'bi-calendar-event'],
    'inscripcion' => ['Inscripciones', 'index.php?c=inscripcion&a=index',  'bi-journal-check'],
    'asistencia'  => ['Asistencia',    'index.php?c=asistencia&a=index',   'bi-clipboard-check'],
    'informe'     => ['Informes',      'index.php?c=informe&a=index',      'bi-bar-chart-line'],
];

// Alertas flash: $_SESSION['flash'] = ['tipo' => 'success|error|warning|info', 'mensaje' => '...']
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$flashHtml = '';
if (is_array($flash) && !empty($flash['mensaje'])) {
    $clases    = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'];
    $flashHtml = '<div class="alert alert-' . e($clases[$flash['tipo'] ?? 'info'] ?? 'info')
        . ' alert-dismissible fade show" role="alert">' . e($flash['mensaje'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e($tituloPagina) ?> | <?= e($appName) ?></title>

    <!-- Evita el parpadeo de tema -->
    <script src="<?= e(base_url('assets/js/color-thema.js')) ?>"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.3.0/index.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/styles/overlayscrollbars.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/adminlte.min.css')) ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/css/tabulator_bootstrap5.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<?php if ($layoutSimple): ?>
<body class="login-page bg-body-secondary">
<main class="auth-box w-100 p-3">
    <?= $flashHtml ?>
<?php else: ?>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    <!-- Barra superior -->
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Alternar menú lateral">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if ($haySesion): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">
                            <i class="bi bi-person-circle me-1"></i><?= e($nombreUsuario) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="<?= e(base_url('index.php?c=auth&a=logout')) ?>">
                                    <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <!-- Menú lateral -->
    <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <div class="sidebar-brand">
            <a href="<?= e(base_url('index.php')) ?>" class="brand-link">
                <img src="<?= e(base_url('assets/img/AdminLTELogo.png')) ?>" alt="" class="brand-image opacity-75 shadow">
                <span class="brand-text fw-light"><?= e($appName) ?></span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2" aria-label="Navegación principal">
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false" id="navigation">
                    <?php foreach ($menu as $clave => [$texto, $ruta, $icono]): ?>
                        <?php if ($clave === 'auth' && $haySesion) { continue; } ?>
                        <li class="nav-item">
                            <a href="<?= e(base_url($ruta)) ?>" class="nav-link<?= $cActual === $clave ? ' active' : '' ?>">
                                <i class="nav-icon bi <?= e($icono) ?>"></i>
                                <p><?= e($texto) ?></p>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <!-- Contenido -->
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"><?= e($tituloPagina) ?></h3></div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                <?= $flashHtml ?>
<?php endif; ?>
