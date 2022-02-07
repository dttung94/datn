<?php
namespace backend\modules\data\forms\price;


use common\entities\calendar\CoursePrice;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use yii\base\Model;

/**
 * Class CoursePriceCreateForm
 * @package backend\modules\data\forms\price
 *
 * @property integer $course_id
 * @property double $price
 */
class CoursePriceCreateForm extends Model
{
    public $course_id;
    public $duration_minute, $price;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
//            [['course_id', 'duration_minute', 'price_worker_standard', 'price_worker_premium', 'price_worker_platinum'], 'required'],
            [['course_id', 'price'], 'required'],
            [['course_id'], 'integer'],
            [['price'], 'double'],
            [[ 'price'], 'safe'],
            [['price'], 'validatePlusMinus'],
        ]);
    }

//    public function validateDurationTime($attribute, $params)
//    {
//        $minutes = $this->duration_minute;
//        if (!$this->hasErrors()) {
//            if ($minutes % 5 != 0 || $minutes < 20) {
//                $this->addError($attribute, \App::t("backend.price.message", "Duration time take of the great than 20 minutes and  block 5 minutes"));
//            }
//            $isExist = CoursePrice::findOne([
//                "course_id" => $this->course_id,
//                "duration_minute" => $this->duration_minute,
//            ]);
//            if ($isExist) {
//                $this->addError($attribute, \App::t("backend.price.message", "Duration minute is exist"));
//            }
//        }
//    }

    public function validatePlusMinus($attribute, $params)
    {
        $pattern = '/^[0-9\.]+$/';
        if (!$this->hasErrors()) {
            if(!preg_match($pattern, $this->price)) {
                $this->addError($attribute, \App::t("backend.price.message", "{attribute}"));
            }
        }
    }

    public function toSave()
    {
        if ($this->validate()) {
            $trans = \App::$app->db->beginTransaction();
            //todo save course price for worker Rank
            if (!$this->hasErrors()) {
                $model = new CoursePrice();
                $model->course_id = $this->course_id;
                $model->price = $this->price_worker_standard;
                $model->status = CoursePrice::STATUS_ACTIVE;
                if (!$model->save()) {
                    $this->addErrors($model->getErrors());
                }
            }

            if (!$this->hasErrors()) {
                $trans->commit();
                return true;
            } else {
                $trans->rollBack();
            }
        }
        return false;
    }
}