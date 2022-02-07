<?php
namespace backend\modules\data\forms\price;


use common\entities\calendar\CourseInfo;

/**
 * Class CourseForm
 * @package backend\modules\data\forms\price
 */
class CourseForm extends CourseInfo
{
    public function toSave()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    public function toDelete()
    {
        $this->status = self::STATUS_ARCHIVED;
        return $this->save();
    }
}