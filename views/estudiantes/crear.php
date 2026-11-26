<?php
declare(strict_types=1);
?>
<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">
        <div class="card card-primary card-outline mb-4">
            <div class="card-header"><h3 class="card-title mb-0">Datos del estudiante</h3></div>
            <form id="formEstudiante" novalidate
                  data-url-guardar="<?= e(base_url('index.php?c=estudiante&a=guardar')) ?>"
                  data-url-lista="<?= e(base_url('index.php?c=estudiante&a=index')) ?>">
                <div class="card-body">
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
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-key me-1"></i>
                        La clave se genera automáticamente y se mostrará <strong>una sola vez</strong> al guardar.
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2">
                    <a href="<?= e(base_url('index.php?c=estudiante&a=index')) ?>" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
