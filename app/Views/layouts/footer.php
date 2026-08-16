</main>
<?php $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); ?>
<?php if ($flash): ?>
<div id="flash-message" data-tipo="<?= htmlspecialchars($flash['tipo']) ?>" data-texto="<?= htmlspecialchars($flash['texto']) ?>"></div>
<?php endif; ?>
<script src="<?= BASE_URL ?>assets/js/sweetalert2.all.min.js"></script>
<script src="<?= BASE_URL ?>assets/js/jquery.min.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/jquery.min.js') ?>"></script>
<script src="<?= BASE_URL ?>assets/js/dataTables.min.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/dataTables.min.js') ?>"></script>
<script src="<?= BASE_URL ?>assets/js/app.js?v=<?= @filemtime(__DIR__ . '/../../../public/assets/js/app.js') ?>"></script>
</body>
</html>
