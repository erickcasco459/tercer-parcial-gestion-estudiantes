<?php
declare(strict_types=1);

$layoutSimple = $layoutSimple ?? false;
$scripts      = (isset($scripts) && is_array($scripts)) ? $scripts : []; // rutas relativas, ej. 'assets/js/eventos.js'
$appName      = $_ENV['APP_NAME'] ?? 'Control de Eventos';
?>
<?php if ($layoutSimple): ?>
</main>
<?php else: ?>
            </div>
        </div>
    </main>

    <footer class="app-footer">
        <strong>&copy; <?= e(date('Y')) ?> <?= e($appName) ?>.</strong> Todos los derechos reservados.
    </footer>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.16.0/browser/overlayscrollbars.browser.es6.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<?php if (!$layoutSimple): ?>
<script src="<?= e(base_url('assets/js/adminlte.js')) ?>"></script>
<script src="<?= e(base_url('assets/js/para-el-menu.js')) ?>"></script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/tabulator-tables@6.6.1/dist/js/tabulator.min.js" crossorigin="anonymous"></script>
<script src="<?= e(base_url('assets/js/app.js')) ?>"></script>
<?php foreach ($scripts as $ruta):
    $ruta    = ltrim((string) $ruta, '/');
    $archivo = BASE_PATH . '/' . $ruta;
    $version = is_file($archivo) ? (string) filemtime($archivo) : '1';
?>
<script src="<?= e(base_url($ruta) . '?v=' . $version) ?>"></script>
<?php endforeach; ?>
</body>
</html>
