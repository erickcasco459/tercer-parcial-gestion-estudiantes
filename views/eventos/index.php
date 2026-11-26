<?php
declare(strict_types=1);

/** @var bool $esAdmin  (lo entrega EventoController::index) */
?>
<div id="eventos-app"
     data-es-admin="<?= $esAdmin ? '1' : '0' ?>"
     data-url-listar="<?= e(base_url('index.php?c=evento&a=listar')) ?>"
     data-url-obtener="<?= e(base_url('index.php?c=evento&a=obtener')) ?>"
     data-url-guardar="<?= e(base_url('index.php?c=evento&a=guardar')) ?>"
     data-url-eliminar="<?= e(base_url('index.php?c=evento&a=eliminar')) ?>">

    <div class="card card-primary card-outline mb-4">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Listado de eventos</h3>
            <div class="ms-auto d-flex gap-2">
                <input type="search" id="filtro" class="form-control form-control-sm" placeholder="Buscar…" aria-label="Buscar eventos">
                <?php if ($esAdmin): ?>
                    <button type="button" id="btnNuevo" class="btn btn-primary btn-sm text-nowrap">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div id="tabla-eventos"></div>
        </div>
        <div class="card-footer text-secondary small" id="total-registros"></div>
    </div>

    <?php if ($esAdmin): ?>
    <div class="modal fade" id="modalEvento" tabindex="-1" aria-labelledby="tituloModal" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="tituloModal">Nuevo evento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formEvento" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" maxlength="20" pattern="[A-Za-z0-9_\-]+" required>
                            </div>
                            <div class="col-md-8">
                                <label for="nombre" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" maxlength="150" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lugar" class="form-label">Lugar</label>
                                <input type="text" class="form-control" id="lugar" name="lugar" maxlength="150" required>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                            </div>
                            <div class="col-md-3">
                                <label for="hora" class="form-label">Hora</label>
                                <input type="time" class="form-control" id="hora" name="hora" required>
                            </div>
                            <div class="col-md-4">
                                <label for="cupo_disponible" class="form-label">Cupo disponible</label>
                                <input type="number" class="form-control" id="cupo_disponible" name="cupo_disponible" min="0" step="1" required>
                            </div>
                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="2000"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar" form="formEvento">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
