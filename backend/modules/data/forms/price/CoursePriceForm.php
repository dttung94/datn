<?php
namespace backend\modules\data\forms\price;

use common\entities\calendar\CourseInfo;
use common\entities\calendar\CoursePrice;
use common\entities\calendar\OptionFee;
use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;

/**
 * Class CoursePriceForm
 * @package backend\modules\calendar\forms\price
 */
class CoursePriceForm extends CourseInfo
{
    public static function getUnitPriceTable()
    {
        //todo load course type
        $courseTypes = self::find()
            ->where([
                "status" => self::STATUS_ACTIVE,
            ])
            ->all();
        $courseTypesData = [];
        foreach ($courseTypes as $courseType) {
            $courseTypesData[$courseType->course_id] = $courseType->toArray(['course_name', 'description','price']);
        }

        return [
            "course-type" => $courseTypesData,
        ];
    }

    public static function toDeleteCoursePrice($data)
    {
        $hasError = false;
        $trans = \App::$app->db->beginTransaction();
        foreach ($data as $item) {
            $course_id = ArrayHelper::getValue($item, "course_id");
            $duration_minute = ArrayHelper::getValue($item, "duration_minute");
            $worker_rank = ArrayHelper::getValue($item, "worker_rank");
            $model = self::findOne([
                "course_id" => $course_id,
                "duration_minute" => $duration_minute,
                "worker_rank" => $worker_rank,
            ]);
            if ($model) {
                if (!$model->delete()) {
                    $hasError = true;
                    break;
                }
            }
        }
        if (!$hasError) {
            $trans->commit();
            return true;
        } else {
            $trans->rollBack();
            return false;
        }
    }
}