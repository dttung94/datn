<?php
namespace backend\assets;


use yii\helpers\ArrayHelper;

class AdminAsset extends AppAsset
{
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->js = ArrayHelper::merge($this->js, [
            "scripts/quick-sidebar.js",

            "js/controllers/main.js",
        ]);
        $this->css = ArrayHelper::merge($this->css, [
        ]);
    }
}