<?php
declare(strict_types=1);

$appName = $_ENV['APP_NAME'] ?? 'Control de Eventos';
?>
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header text-center">
        <h1 class="h4 mb-0"><?= e($appName) ?></h1>
    </div>
    <div class="card-body">
        <p class="text-center text-secondary">Crea tu cuenta de estudiante. Tu clave se genera automáticamente y se muestra <strong>una sola vez</strong>.</p>
        <form id="formRegistro" novalidate
              data-url="<?= e(base_url('index.php?c=auth&a=auto_registro')) ?>"
              data-url-login="<?= e(base_url('index.php?c=auth&a=login')) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="carnet" class="form-label">Carnet</label>
                <input type="text" class="form-control" id="carnet" name="carnet" maxlength="20"
                       pattern="[A-Za-z0-9\-]{3,20}" required>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="100" required>
                </div>
                <div class="col-sm-6">
                    <label for="apellido" class="form-label">Apellido</label>
                    <input type="text" class="form-control" id="apellido" name="apellido" maxlength="100" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" id="usuario" name="usuario" maxlength="50"
                       pattern="[a-z0-9._]{3,50}" autocapitalize="none" required>
                <div class="form-text">Minúsculas, números, punto o guion bajo (3 a 50).</div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btnRegistro">Crear cuenta</button>
        </form>
    </div>
    <div class="card-footer text-center">
        ¿Ya tienes cuenta?
        <a href="<?= e(base_url('index.php?c=auth&a=login')) ?>">Inicia sesión</a>
    </div>
</div>
