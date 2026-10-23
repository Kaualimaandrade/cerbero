<?php
/**
 * Renderizador de Toast via Sessão
 * Sistema: Cerbero
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['toast'])):
    $toast = $_SESSION['toast'];
    unset($_SESSION['toast']);
?>
    <script id="toast-data" type="application/json">
        <?php echo json_encode($toast, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
    </script>
<?php endif; ?>
