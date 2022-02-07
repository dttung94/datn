<?php
namespace common\entities\calendar;


use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * Class OptionFee
 * @package common\entities\calendar
 *
 * This is the model class for table "course_info".
 *
 * @property string $course_id
 * @property string $course_name
 * @property string $sort_name
 * @property string $description
 * @property string $shop_ids
 * @property double $price
 * @property integer $duration_minute
 *
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 */
class CourseInfo extends AbstractObject
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'course_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['course_name', 'price'], 'required'],
            ['status', 'in', 'range' => [
                self::STATUS_ACTIVE,
                self::STATUS_INACTIVE,
                self::STATUS_ARCHIVED,
            ]],
            [['course_id', 'status'], 'integer'],
            [['price'], 'double'],
            [['course_name', 'description', 'created_at', 'modified_at'], 'string'],
            [['course_id', 'course_name', 'description', 'status', 'created_at', 'modified_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'course_id' => Yii::t('app.attribute.course_info.label', 'ID'),

            'course_name' => Yii::t('app.attribute.course_info.label', 'Tên dịch vụ'),
//            'sort_name' => Yii::t('app.attribute.course_info.label', '読み方'),
            'description' => Yii::t('app.attribute.course_info.label', 'Mô tả'),
            'price' => Yii::t('app.attribute.course_info.label', 'Phí dịch vụ'),
            'status' => Yii::t('app.attribute.course_info.label', 'Status'),
            'created_at' => Yii::t('app.attribute.course_info.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.course_info.label', 'Modified At'),
        ];
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = $this->currentDatetime();
            }
            $this->modified_at = $this->currentDatetime();
            return true;
        }
        return false;
    }
}
