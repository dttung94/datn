<?php
namespace backend\modules\data\forms\price;

use common\entities\calendar\OptionFee;
use common\entities\worker\WorkerInfo;

/**
 * Class ShimeiPriceForm
 * @package backend\modules\calendar\forms\price
 */
class OptionFeeForm extends OptionFee
{
    public function getUnitPriceTable()
    {
        $systemOptions = [];
        $models = self::find()
            ->where(["in", "key", [
                self::KEY_EXTEND_TIME,
                self::KEY_SHIMEI,
            ]])
            ->all();
        foreach ($models as $model) {
            /**
             * @var $model OptionFeeForm
             */
            if (!isset($systemOptions[$model->key])) {
                $systemOptions[$model->key] = [];
            }
            $systemOptions[$model->key][$model->worker_rank] = $model;
        }
        return [
            "worker-rank" => WorkerInfo::getListWorkerRank(),
            "option-key" => self::getListKeys(),
            "systemOptions" => $systemOptions,
        ];
    }
}