<?php
declare(strict_types=1);

/** @var bool $esAdmin  (lo entrega EventoController::index) */
?>
<div class="card card-primary card-outline mb-4">
    <div class="card-header d-flex flex-wrap align-items-center gap-2">
        <h3 class="card-title mb-0">Listado de eventos</h3>
        <div class="ms-auto d-flex gap-2">
            <input type="search" id="filtro" class="form-control form-control-sm" placeholder="Buscar…" aria-label="Buscar eventos">
            <?php if ($esAdmin): ?>
                <a href="<?= e(base_url('index.php?c=evento&a=crear')) ?>" class="btn btn-primary btn-sm text-nowrap">
                    <i class="bi bi-plus-lg me-1"></i>Nuevo evento
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div id="tabla-eventos" data-url-listar="<?= e(base_url('index.php?c=evento&a=listar')) ?>"></div>
    </div>
    <div class="card-footer text-secondary small" id="total-registros"></div>
</div>
