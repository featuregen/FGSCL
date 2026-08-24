<?php
/**
 * Flash Messages / Alerts Partial
 */
$flashMessages = Session::getFlash();
?>

<?php foreach ($flashMessages as $flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>" id="flashAlert">
        <i class="bi bi-<?php 
            echo match($flash['type']) {
                'success' => 'check-circle-fill',
                'error', 'danger' => 'exclamation-triangle-fill',
                'warning' => 'exclamation-circle-fill',
                'info' => 'info-circle-fill',
                default => 'info-circle-fill',
            };
        ?>"></i>
        <span><?= $flash['message'] ?></span>
        <button class="alert-close" onclick="this.parentElement.remove()" aria-label="Close">&times;</button>
    </div>
<?php endforeach; ?>
