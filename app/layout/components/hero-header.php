<?php
function renderHeroHeader($title, $subtitle, $actions = []) {
    ?>
    <div class="modern-card mb-6 fade-in">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
                <h1 class="text-4xl font-bold text-gradient mb-2"><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="modern-card-subtitle"><?= htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <?php if (!empty($actions)) { ?>
            <div class="flex flex-wrap gap-3">
                <?php foreach ($actions as $action) { 
                    $modalId = isset($action['modal']) ? htmlspecialchars($action['modal'], ENT_QUOTES, 'UTF-8') : '';
                    $btnClass = isset($action['class']) ? htmlspecialchars($action['class'], ENT_QUOTES, 'UTF-8') : 'btn-primary';
                    $label = htmlspecialchars($action['label'], ENT_QUOTES, 'UTF-8');
                    $icon = isset($action['icon']) ? htmlspecialchars($action['icon'], ENT_QUOTES, 'UTF-8') : '';
                ?>
                    <button 
                        type="button"
                        class="btn-modern <?= $btnClass ?>"
                        <?= $modalId ? 'data-modal-toggle="' . $modalId . '"' : '' ?>>
                        <?php if ($icon) { ?>
                            <i class="<?= $icon ?>"></i>
                        <?php } ?>
                        <?= $label ?>
                    </button>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php
}
?>
