<?php
/**
 * Partial compartido: footer, modales reutilizables y scripts base de las vistas del cliente.
 */
?>
    <?= $this->include('modals/footer') ?>
    <?= $this->include('modals/modalsBase') ?>

    <script src="<?= base_url('public/assets/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.APP_CONFIG = {
            baseUrl: '<?= rtrim(base_url(), '/') ?>',
            apiUrl: '<?= rtrim(base_url('api'), '/') ?>',
            brand: '#7d562d'
        };
    </script>
    <script src="<?= base_url('public/assets/js/index.js?v=3.1') ?>" defer></script>

