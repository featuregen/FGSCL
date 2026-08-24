<?php if (!empty($breadcrumb)): ?>
<nav class="breadcrumb-wrapper" aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= APP_URL ?>/dashboard"><i class="bi bi-house-door"></i></a>
        </li>
        <?php foreach ($breadcrumb as $index => $item): ?>
            <?php if ($index === array_key_last($breadcrumb)): ?>
                <li class="breadcrumb-item active"><?= htmlspecialchars($item['label']) ?></li>
            <?php else: ?>
                <li class="breadcrumb-item">
                    <a href="<?= APP_URL ?>/<?= $item['url'] ?>"><?= htmlspecialchars($item['label']) ?></a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
