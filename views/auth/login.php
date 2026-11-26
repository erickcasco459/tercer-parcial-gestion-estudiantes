<?php
declare(strict_types=1);

$appName = $_ENV['APP_NAME'] ?? 'Control de Eventos';
?>
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header text-center">
        <h1 class="h4 mb-0"><?= e($appName) ?></h1>
    </div>
    <div class="card-body">
        <p class="text-center text-secondary">Inicia sesión para continuar</p>
        <form id="formLogin" novalidate
              data-url="<?= e(base_url('index.php?c=auth&a=autenticar')) ?>">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" class="form-control" id="usuario" name="usuario"
                           autocomplete="username" autocapitalize="none" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Clave</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="clave" name="clave"
                           autocomplete="current-password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" id="btnLogin">Ingresar</button>
        </form>
    </div>
    <div class="card-footer text-center">
        ¿Aún no tienes cuenta?
        <a href="<?= e(base_url('index.php?c=auth&a=registro')) ?>">Regístrate</a>
    </div>
</div>
