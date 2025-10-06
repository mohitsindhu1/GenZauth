<?php
function renderStatsCard($icon, $value, $label, $change = null, $changeType = 'neutral', $gradient = 'gradient-blue') {
    $icon = htmlspecialchars($icon, ENT_QUOTES, 'UTF-8');
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $label = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $changeType = in_array($changeType, ['positive', 'negative', 'neutral']) ? $changeType : 'neutral';
    $gradient = preg_match('/^[a-z-]+$/', $gradient) ? $gradient : 'gradient-blue';
    $gradientClass = "var(--$gradient)";
    ?>
    <div class="stats-card scale-in">
        <div class="stats-icon" style="background: <?= $gradientClass ?>;">
            <i class="<?= $icon ?>"></i>
        </div>
        <div class="stats-value"><?= $value ?></div>
        <div class="stats-label"><?= $label ?></div>
        <?php if ($change !== null) { 
            $change = htmlspecialchars($change, ENT_QUOTES, 'UTF-8');
        ?>
            <div class="stats-change <?= $changeType ?>">
                <i class="lni <?= $changeType === 'positive' ? 'lni-arrow-up' : 'lni-arrow-down' ?>"></i>
                <?= $change ?>
            </div>
        <?php } ?>
    </div>
    <?php
}

function renderStatsGrid($stats) {
    ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <?php foreach ($stats as $stat) { ?>
            <?php renderStatsCard(
                $stat['icon'] ?? 'lni-stats-up',
                $stat['value'] ?? '0',
                $stat['label'] ?? 'Stat',
                $stat['change'] ?? null,
                $stat['changeType'] ?? 'neutral',
                $stat['gradient'] ?? 'gradient-blue'
            ); ?>
        <?php } ?>
    </div>
    <?php
}
?>
