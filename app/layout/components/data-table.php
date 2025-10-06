<?php
function renderDataTableHeader($title, $subtitle, $actions = [], $searchable = true) {
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $subtitle = htmlspecialchars($subtitle, ENT_QUOTES, 'UTF-8');
    ?>
    <div class="modern-card mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white mb-1"><?= $title ?></h2>
                <p class="text-sm text-gray-400"><?= $subtitle ?></p>
            </div>
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
        </div>
        
        <?php if ($searchable) { ?>
        <div class="search-bar-modern mb-6">
            <input 
                type="text" 
                class="search-input" 
                placeholder="Search..."
                id="table-search">
            <i class="lni lni-search-alt search-icon"></i>
        </div>
        <?php } ?>
        
        <div class="modern-table-container">
            <?php
}

function renderDataTableFooter() {
    ?>
        </div>
    </div>
    <?php
}
?>
