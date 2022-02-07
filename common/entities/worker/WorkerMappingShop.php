<?php

namespace common\entities\worker;

use common\entities\shop\ShopInfo;
use common\helper\ArrayHelper;
use common\models\base\AbstractObject;
use Yii;

/**
 * This is the model class for table "worker_mapping_shop".
 *
 * @property string $worker_id
 * @property string $shop_id
 * @property integer $ref_id
 * @property string $worker_url
 *
 * @property string $status
 * @property string $created_at
 * @property string $modified_at
 *
 * @property WorkerInfo $workerInfo
 * @property ShopInfo $shopInfo
 */
class WorkerMappingShop extends AbstractObject
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'worker_mapping_shop';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['worker_id', 'shop_id'], 'required'],
            [['worker_id', 'shop_id'], 'unique', 'targetAttribute' => ['worker_id', 'shop_id']],
            [['worker_id', 'shop_id', 'status'], 'integer'],
            [['status', 'created_at', 'modified_at'], 'safe'],
            [['worker_id'], 'exist', 'skipOnError' => true, 'targetClass' => WorkerInfo::className(), 'targetAttribute' => ['worker_id' => 'worker_id']],
            [['shop_id'], 'exist', 'skipOnError' => true, 'targetClass' => ShopInfo::className(), 'targetAttribute' => ['shop_id' => 'shop_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'worker_id' => Yii::t('app.attribute.worker_mapping_shop.label', 'Worker'),
            'shop_id' => Yii::t('app.attribute.worker_mapping_shop.label', 'Shop'),
            'worker_url' => Yii::t('app.attribute.worker_mapping_shop.label', 'Worker URL'),

            'status' => Yii::t('app.attribute.worker_mapping_shop.label', 'Status'),
            'created_at' => Yii::t('app.attribute.worker_mapping_shop.label', 'Created At'),
            'modified_at' => Yii::t('app.attribute.worker_mapping_shop.label', 'Modified At'),
        ];
    }

    public function validateRefId($attribute, $params)
    {
        $pattern = '/^[0-9]+$/';
        if (!$this->hasErrors()) {
            if (!preg_match($pattern, $this->ref_id)) {
                $this->addError($attribute, \App::t("backend.worker.message", 'Ref ID 数字のみを含む'));
            }
        }
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($insert) {
                $this->created_at = self::currentDatetime();
            }
            $this->modified_at = self::currentDatetime();
            return true;
        }
        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWorkerInfo()
    {
        return $this->hasOne(WorkerInfo::className(), ['worker_id' => 'worker_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopInfo()
    {
        return $this->hasOne(ShopInfo::className(), ['shop_id' => 'shop_id']);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "shopInfo",
            "workerInfo"
        ]);
    }
}
