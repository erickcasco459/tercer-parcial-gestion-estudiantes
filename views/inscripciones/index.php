<?php
declare(strict_types=1);
?>
<div id="inscripciones-app"
     data-url-disponibles="<?= e(base_url('index.php?c=inscripcion&a=listar_disponibles')) ?>"
     data-url-mias="<?= e(base_url('index.php?c=inscripcion&a=listar_mias')) ?>"
     data-url-inscribir="<?= e(base_url('index.php?c=inscripcion&a=inscribir')) ?>">

    <div class="card card-primary card-outline mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Eventos con cupo disponible</h3>
        </div>
        <div class="card-body">
            <div id="tabla-disponibles"></div>
        </div>
        <div class="card-footer text-secondary small" id="total-disponibles"></div>
    </div>

    <div class="card card-success card-outline mb-4">
        <div class="card-header">
            <h3 class="card-title mb-0">Mis inscripciones</h3>
        </div>
        <div class="card-body">
            <div id="tabla-mias"></div>
        </div>
        <div class="card-footer text-secondary small" id="total-mias"></div>
    </div>
</div>
