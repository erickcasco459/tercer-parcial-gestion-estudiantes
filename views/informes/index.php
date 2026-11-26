<?php
declare(strict_types=1);

/**
 * @var bool  $esAdmin
 * @var array $estudiantes  (id, carnet, nombre, apellido)  vacío si no es admin
 */
?>
<div id="informes-app"
     data-url-listar="<?= e(base_url('index.php?c=informe&a=listar')) ?>">

    <div class="card card-primary card-outline mb-4">
        <div class="card-header d-flex flex-wrap align-items-center gap-2">
            <h3 class="card-title mb-0">Eventos inscritos y asistencia</h3>
            <div class="ms-auto d-flex gap-2">
                <?php if ($esAdmin): ?>
                    <select id="filtro-estudiante" class="form-select form-select-sm" aria-label="Filtrar por estudiante">
                        <option value="">Todos los estudiantes</option>
                        <?php foreach ($estudiantes as $est): ?>
                            <option value="<?= e($est['id']) ?>">
                                <?= e($est['carnet']) ?> — <?= e($est['apellido']) ?>, <?= e($est['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <button type="button" id="btnCsv" class="btn btn-outline-success btn-sm text-nowrap">
                    <i class="bi bi-filetype-csv me-1"></i>Exportar CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="tabla-informe"></div>
        </div>
        <div class="card-footer text-secondary small" id="resumen"></div>
    </div>
</div>
