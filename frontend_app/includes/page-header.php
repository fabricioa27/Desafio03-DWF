<?php
/**
 * Cabecera de página reutilizable.
 * @var string $pageHeading
 * @var string|null $pageSubtitle
 * @var string|null $pageActions HTML opcional (botones)
 */
$pageHeading = $pageHeading ?? ($pageTitle ?? '');
$pageSubtitle = $pageSubtitle ?? null;
$pageActions = $pageActions ?? null;
?>
<header class="page-header">
    <div class="page-header-text">
        <h1 class="page-title"><?= htmlspecialchars($pageHeading) ?></h1>
        <?php if ($pageSubtitle !== null && $pageSubtitle !== ''): ?>
            <p class="page-subtitle"><?= htmlspecialchars($pageSubtitle) ?></p>
        <?php endif; ?>
    </div>
    <?php if ($pageActions !== null && $pageActions !== ''): ?>
        <div class="page-header-actions"><?= $pageActions ?></div>
    <?php endif; ?>
</header>
