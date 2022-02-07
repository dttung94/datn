<?php
use frontend\assets\WorkerAsset;

/**
 * @var $this \yii\web\View
 * @var $content string
 */
$bundle = WorkerAsset::register($this);
?>
<?php $this->beginContent('@frontend/views/layouts/layout_base.php'); ?>
    <body>
    <?php $this->beginBody() ?>
    <?php echo $content; ?>
    <?php $this->endBody() ?>
    </body>
<?php $this->endcontent(); ?>