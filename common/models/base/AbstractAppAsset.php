<?php
namespace common\models\base;


use yii\web\AssetBundle;

abstract class AbstractAppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [];
    public $js = [];

    public $depends = [
        'yii\web\YiiAsset',
    ];

    public function registerAssetFiles($view)
    {
        parent::registerAssetFiles($view);
    }
}