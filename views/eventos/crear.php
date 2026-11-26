<?php
declare(strict_types=1);
?>
<div class="card card-primary card-outline mb-4">
    <div class="card-header">
        <h3 class="card-title mb-0">Registrar evento</h3>
    </div>
    <form id="formEvento" novalidate
          data-url-guardar="<?= e(base_url('index.php?c=evento&a=guardar')) ?>"
          data-url-index="<?= e(base_url('index.php?c=evento&a=index')) ?>">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="codigo" class="form-label">Código</label>
                    <input type="text" class="form-control" id="codigo" name="codigo" maxlength="20" pattern="[A-Za-z0-9_\-]+" required>
                    <div class="invalid-feedback">Letras, números, guion o guion bajo (máx. 20).</div>
                </div>
                <div class="col-md-8">
                    <label for="nombre" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" maxlength="150" required>
                    <div class="invalid-feedback">El nombre es obligatorio.</div>
                </div>
                <div class="col-md-6">
                    <label for="lugar" class="form-label">Lugar</label>
                    <input type="text" class="form-control" id="lugar" name="lugar" maxlength="150" required>
                    <div class="invalid-feedback">El lugar es obligatorio.</div>
                </div>
                <div class="col-md-3">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" required>
                    <div class="invalid-feedback">Selecciona una fecha.</div>
                </div>
                <div class="col-md-3">
                    <label for="hora" class="form-label">Hora</label>
                    <input type="time" class="form-control" id="hora" name="hora" required>
                    <div class="invalid-feedback">Selecciona una hora.</div>
                </div>
                <div class="col-md-4">
                    <label for="cupo_disponible" class="form-label">Cupo disponible</label>
                    <input type="number" class="form-control" id="cupo_disponible" name="cupo_disponible" min="0" step="1" required>
                    <div class="invalid-feedback">Ingresa un entero mayor o igual a 0.</div>
                </div>
                <div class="col-12">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3" maxlength="2000"></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            <a href="<?= e(base_url('index.php?c=evento&a=index')) ?>" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </form>
</div>
