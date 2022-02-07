<?php
namespace backend\modules\worker\forms;


use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerMappingShop;
use yii\data\Pagination;

/**
 * Class WorkerWidgetForm
 * @package backend\modules\worker\forms
 *
 * @property string $workerBookingUrl
 */
class WorkerWidgetForm extends WorkerMappingShop
{
    /**
     * @return ShopInfo[]
     */
    public static function getListShops()
    {
        return ShopInfo::find()
            ->where([
                "status" => ShopInfo::STATUS_ACTIVE,
            ])
            ->all();
    }

    /**
     * @param ShopInfo $shopInfo
     * @return self[]
     */
    public static function getListMappingInShop(ShopInfo $shopInfo, $page)
    {
        if ($page < 1) {
            return [];
        }
        $models = self::find()
            ->where([
                "status" => self::STATUS_ACTIVE,
                "shop_id" => $shopInfo->shop_id,
            ]);
        $pages = new Pagination(['totalCount' => $models->count()]);
        $per = 20;
        $pages->setPageSize($per);
        $offset = ($page - 1)*$per;
        $models = $models->offset($offset)
            ->limit($per)
            ->all();
        return [
            'models' => $models,
            'pages' => $pages
        ];
    }

    public function getWorkerBookingUrl()
    {
        if($this->ref_id) {
            return \App::$app->urlManagerFrontend->createAbsoluteUrl([
                "view/schedule",
                "domain" => $this->shopInfo->shop_domain,
                "id" => $this->ref_id,
            ]);
        }
        return false;
    }
}