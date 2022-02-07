<?php
namespace backend\modules\member\forms;


use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use common\helper\JsonHelper;
use yii\helpers\Json;

/**
 * Class MemberBlackListConfigForm
 * @package backend\modules\member\forms
 *
 * @property boolean $isAddedBlackList
 * @property boolean $isAddedWorkerBlackList
 * @property array $blackListWorkerIds
 * @property WorkerInfo[] $blackListWorkers
 */
class MemberBlackListConfigForm extends UserInfo
{

    public function toSaveBlackList($isAddedBlackList, $isAddedWorkerBlackList, $blackListWorkerIds)
    {
        $trans = \App::$app->db->beginTransaction();
        if ($isAddedBlackList) {
            if ($isAddedWorkerBlackList) {
                $this->status = self::STATUS_WORKER_BLACK_LIST;
                UserConfig::setValue(UserConfig::KEY_BLACKLIST_WORKER_IDS, $this->user_id, Json::encode($blackListWorkerIds));
            } else {
                $this->status = self::STATUS_SHOP_BLACK_LIST;
                UserConfig::setValue(UserConfig::KEY_BLACKLIST_WORKER_IDS, $this->user_id, Json::encode([]));
            }
        } else {
            $this->status = self::STATUS_ACTIVE;
            UserConfig::setValue(UserConfig::KEY_BLACKLIST_WORKER_IDS, $this->user_id, Json::encode([]));
        }
        if ($this->save()) {
            $trans->commit();
            return true;
        } else {
            $trans->rollBack();
        }
        return false;
    }

    public function getIsAddedBlackList()
    {
        return ($this->status == self::STATUS_SHOP_BLACK_LIST || $this->status == self::STATUS_WORKER_BLACK_LIST) ? 1 : 0;
    }

    public function getIsAddedWorkerBlackList()
    {
        return ($this->status == self::STATUS_WORKER_BLACK_LIST) ? 1 : 0;
    }

    public function getBlackListWorkerIds()
    {
        return JsonHelper::decode(UserConfig::getValue(UserConfig::KEY_BLACKLIST_WORKER_IDS, $this->user_id, "[]"));
    }

    public function getBlackListWorkers()
    {
        return WorkerInfo::find()
            ->where(["IN", "worker_id", $this->blackListWorkerIds])
            ->all();
    }

    public function getListWorker()
    {
        return ArrayHelper::map(WorkerInfo::findAll([
            "status" => WorkerInfo::STATUS_ACTIVE
        ]), "worker_id", "worker_name");
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            "isAddedBlackList" => \Yii::t('app.attribute.user_info.label', 'BL/承認に加える'),
            "isAddedWorkerBlackList" => \Yii::t('app.attribute.user_info.label', '女の子NGに加える'),
        ]);
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            "isAddedBlackList",
            "isAddedWorkerBlackList",
            "blackListWorkerIds",
            "blackListWorkers",
        ]);
    }

    public function toArray(array $fields = [], array $expand = [
        "isAddedBlackList",
        "isAddedWorkerBlackList",
        "blackListWorkerIds",
        "blackListWorkers",
    ], $recursive = true)
    {
        return parent::toArray($fields, $expand, $recursive);
    }
}