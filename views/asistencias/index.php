<?php
declare(strict_types=1);

/**
 * @var array    $eventos  (id, codigo, nombre, fecha)  lo entrega AsistenciaController::index
 * @var string[] $estados  estados válidos de asistencia
 */
?>
<div id="asistencias-app"
     data-estados="<?= e(json_encode($estados, JSON_UNESCAPED_UNICODE)) ?>"
     data-url-listar="<?= e(base_url('index.php?c=asistencia&a=listar')) ?>"
     data-url-registrar="<?= e(base_url('index.php?c=asistencia&a=registrar')) ?>">

    <div class="card card-primary card-outline mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Inscritos por evento</h3>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="evento" class="form-label">Evento</label>
                    <select id="evento" class="form-select">
                        <option value="">Selecciona un evento…</option>
                        <?php foreach ($eventos as $ev): ?>
                            <option value="<?= e($ev['id']) ?>">
                                <?= e($ev['codigo']) ?> — <?= e($ev['nombre']) ?> (<?= e($ev['fecha']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div id="tabla-asistencias"></div>
        </div>
        <div class="card-footer text-secondary small" id="total-registros"></div>
    </div>
</div>
