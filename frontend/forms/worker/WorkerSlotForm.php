<?php
namespace frontend\forms\worker;

use common\entities\shop\ShopCalendarSlot;
use common\entities\worker\WorkerInfo;

/**
 * Class WorkerSlotForm
 * @package frontend\forms\worker
 *
 * @property integer $shop_id
 * @property string $date
 */
class WorkerSlotForm extends WorkerInfo
{
    public $shop_id, $date;

    /**
     * @param $date
     * @param null $shop_id
     * @return ShopCalendarSlot[]
     */
    public function getSlots($date, $shop_id = null)
    {
        return parent::getSlots($date, $this->shop_id);
    }
}