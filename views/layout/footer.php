</main>

<footer class="bg-white border-top py-3 mt-auto">
    <div class="container text-center text-muted small">
        &copy; <?= e(date('Y')) ?> <?= e($_ENV['APP_NAME'] ?? 'Control de Eventos') ?>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(base_url('assets/js/app.js')) ?>"></script>
</body>
</html>
