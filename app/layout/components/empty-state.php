<?php
function renderEmptyState($icon, $title, $message, $actionLabel = null, $actionModal = null) {
    $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $actionLabel = $actionLabel ? htmlspecialchars($actionLabel, ENT_QUOTES, 'UTF-8') : null;
    $actionModal = $actionModal ? htmlspecialchars($actionModal, ENT_QUOTES, 'UTF-8') : null;
    ?>
    <div class="modern-card text-center py-16 fade-in">
        <div class="inline-flex items-center justify-center w-20 h-20 mb-6 rounded-full bg-gradient-blue">
            <i class="<?= $icon ?> text-4xl text-white"></i>
        </div>
        <h3 class="text-2xl font-bold text-white mb-3"><?= $title ?></h3>
        <p class="text-gray-400 mb-6 max-w-md mx-auto"><?= $message ?></p>
        <?php if ($actionLabel && $actionModal) { ?>
        <button 
            type="button"
            class="btn-modern btn-primary"
            data-modal-toggle="<?= $actionModal ?>">
            <i class="lni lni-plus"></i>
            <?= $actionLabel ?>
        </button>
        <?php } ?>
    </div>
    <?php
}
?>
