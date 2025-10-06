<?php
function renderAlert($type, $title, $message, $closeable = false) {
    $allowedTypes = ['success', 'error', 'warning', 'info'];
    $type = in_array($type, $allowedTypes) ? $type : 'info';
    
    $types = [
        'success' => ['class' => 'alert-success', 'icon' => 'lni-checkmark-circle'],
        'error' => ['class' => 'alert-error', 'icon' => 'lni-cross-circle'],
        'warning' => ['class' => 'alert-warning', 'icon' => 'lni-warning'],
        'info' => ['class' => 'alert-info', 'icon' => 'lni-information']
    ];
    
    $config = $types[$type];
    $title = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    ?>
    <div class="alert-modern <?= $config['class'] ?> fade-in" role="alert" id="alert-box">
        <div class="alert-icon">
            <i class="lni <?= $config['icon'] ?>"></i>
        </div>
        <div class="alert-content">
            <div class="alert-title"><?= $title ?></div>
            <div class="alert-message"><?= $message ?></div>
        </div>
        <?php if ($closeable) { ?>
        <button 
            type="button" 
            class="ml-auto p-2 rounded-lg hover:bg-opacity-20 hover:bg-white transition"
            onclick="document.getElementById('alert-box').style.display='none'">
            <i class="lni lni-close text-lg"></i>
        </button>
        <?php } ?>
    </div>
    <?php
}
?>
