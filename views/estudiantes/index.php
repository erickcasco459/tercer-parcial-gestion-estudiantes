<?php
declare(strict_types=1);
?>
<div id="estudiantes-app"
     data-url-listar="<?= e(base_url('index.php?c=estudiante&a=listar')) ?>">
    <div class="card card-primary card-outline mb-4">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Listado de estudiantes</h3>
            <div class="ms-auto d-flex gap-2">
                <input type="search" id="filtro" class="form-control form-control-sm"
                       placeholder="Buscar…" aria-label="Buscar estudiantes">
                <a href="<?= e(base_url('index.php?c=estudiante&a=crear')) ?>"
                   class="btn btn-primary btn-sm text-nowrap">
                    <i class="bi bi-person-plus me-1"></i>Nuevo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div id="tabla-estudiantes"></div>
        </div>
        <div class="card-footer text-secondary small" id="total-registros"></div>
    </div>
</div>
