<?php
namespace backend\modules\worker\forms;

use backend\modules\service\forms\file\FileForm;
use common\entities\calendar\BookingInfo;
use common\entities\calendar\Rating;
use common\entities\customer\CustomerInfo;
use common\entities\resource\FileInfo;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerConfig;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\DatetimeHelper;
use common\helper\HtmlHelper;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

/**
 * @property integer $totalAllBooking
 *
 * @property object $avatar_file
 * @property integer $is_show_rank_name
 * @property array $shops
 *
 * @property string $keyword
 *
 * @property string $workerBookingUrl
 */
class WorkerForm extends WorkerInfo
{
    public $avatar_file, $is_show_rank_name, $shops;

    public $keyword;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['avatar_file'], 'image'],
            [['is_show_rank_name'], 'integer'],
            [['shops', 'keyword'], 'safe'],
        ]);
    }

    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'keyword' => \Yii::t('common.label', 'Keyword'),
            'avatar_file' => \Yii::t('common.label', 'Ảnh nhân viên'),
        ]);
    }

    public function prepareData()
    {
        $this->shops = [];
        $shops = ShopInfo::findAll([
            "status" => ShopInfo::STATUS_ACTIVE
        ]);
        foreach ($shops as $shop) {
            $workerShop = WorkerMappingShop::find()
                ->where([
                    "worker_id" => $this->worker_id,
                    "shop_id" => $shop->shop_id,
                ])
                ->one();
            if ($workerShop == null) {
                $workerShop = new WorkerMappingShop();
                $workerShop->shop_id = $shop->shop_id;
            }
            $isExistCalendar = ShopCalendar::find()
                ->where([
                    "shop_id" => $shop->shop_id,
                    "worker_id" => $this->worker_id,
                    "status" => ShopCalendar::STATUS_ACTIVE,
                    "type" => ShopCalendar::TYPE_WORKING_DAY,
                ])
                ->andWhere("date >= :today", [
                    ":today" => DatetimeHelper::now(DatetimeHelper::FULL_DATE),
                ])
                ->exists();
            $this->shops[$shop->shop_id] = ArrayHelper::merge([
                "isEnable" => !$workerShop->isNewRecord,
                "isSwitchable" => !$isExistCalendar,
            ], $workerShop->toArray(["ref_id", "worker_url"], ["shopInfo"]));
        }
    }

    public static function getRecentRating($id)
    {
        return (new Query())
            ->from(['r' => Rating::tableName()])
            ->innerJoin(['ui' => UserInfo::tableName()], "r.user_id = ui.user_id")
            ->select([
                'ui.full_name',
                'r.behavior',
                'r.technique',
                'r.service',
                'r.price',
                'r.satisfaction',
                'r.memo',
                '(behavior + technique + service + price + satisfaction) AS total_point',
                'r.created_at',
                'r.user_id'
            ])
            ->where("r.worker_id = $id")
            ->orderBy('r.created_at DESC')
            ->limit(10);
    }

    public function getTotalAllBooking()
    {
        $query = BookingInfo::find();
        $query->innerJoin(UserInfo::tableName(), UserInfo::tableName() . ".user_id = " . BookingInfo::tableName() . ".member_id");
        $query->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id");
        $query->innerJoin(ShopInfo::tableName(), ShopCalendarSlot::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id");
        $query->innerJoin(WorkerInfo::tableName(), ShopCalendarSlot::tableName() . ".worker_id = " . WorkerInfo::tableName() . ".worker_id");
        $query->andWhere(BookingInfo::tableName() . ".status = :STATUS_ACCEPTED", [
            ':STATUS_ACCEPTED' => BookingInfo::STATUS_ACCEPTED
        ]);
        $query->andWhere(WorkerInfo::tableName() . ".worker_id = :worker_id", [
            ':worker_id' => $this->worker_id,
        ]);
        return $query->count();
    }

    /**
     * Todo search user
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = parent::find();
        $query->andWhere(self::tableName() . ".status != :status_deleted", [
                ':status_deleted' => self::STATUS_DELETED
            ])
            ->with('mappingShops');
        if (\App::$app->user->identity->role == UserInfo::ROLE_MANAGER) {
            $query->innerJoin(WorkerMappingShop::tableName(), WorkerInfo::tableName() . ".worker_id = " . WorkerMappingShop::tableName() . ".worker_id");
            $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));
            $query->andWhere(["IN", WorkerMappingShop::tableName() . ".shop_id", $shopIds]);
        }
        if ($this->status) {
            $query->andWhere(self::tableName() . ".status = :status", [
                ":status" => $this->status
            ]);
        }
        if ($this->keyword != null) {
            $queryWorkers = WorkerMappingShop::find()
                ->select('worker_id')
                ->distinct('worker_id')
                ->where(
                    ['LIKE', 'worker_id', $this->keyword]
                )
                ->all();
            $workerIds = ArrayHelper::getColumn($queryWorkers, 'worker_id');
            $query->andFilterWhere([
                'or',
                ['LIKE', static::tableName() . '.worker_name', $this->keyword],
                ['LIKE', static::tableName() . '.description', $this->keyword],
                ['IN', static::tableName() . '.worker_id', $workerIds],
            ]);
        }


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->setSort([
            'defaultOrder' => ['worker_name' => SORT_ASC],
            'attributes' => [
                'worker_name',
                'ref_id',
                'status',
                'created_at',
            ]
        ]);

        return $dataProvider;
    }

    public function toToggleStatus()
    {
        if ($this->status == self::STATUS_ACTIVE) {
            //todo check worker is valid for inactive
            $totalSlotAvailable = ShopCalendarSlot::find()
                ->where([
                    "worker_id" => $this->worker_id,
                    "status" => ShopCalendarSlot::STATUS_ACTIVE
                ])
                ->count();
            $totalBookingAvailable = ShopCalendarSlot::find()
                ->addSelect([
                    'STR_TO_DATE(CONCAT(CONCAT(CONCAT(shop_calendar_slot.date,\' \'),shop_calendar_slot.start_time),":00"), \'%Y-%m-%d %H:%i:%s\') as "datetime"'
                ])
                ->where([
                    "worker_id" => $this->worker_id,
                    "status" => ShopCalendarSlot::STATUS_BOOKED
                ])
                ->andHaving("datetime >= :now", [
                    ":now" => DatetimeHelper::now(DatetimeHelper::FULL_DATETIME),
                ])
                ->count();
            if ($totalSlotAvailable == 0 && $totalBookingAvailable == 0) {
                $this->status = self::STATUS_INACTIVE;
            } else {
                $this->addError("worker_id", \App::t("backend.worker.message", "Không cập nhật được do nhân viên đang có lượt book"));
            }
        } else {
            $this->status = self::STATUS_ACTIVE;
        }
        if (!$this->hasErrors()) {
            return $this->save(false);
        }
        return false;
    }

    public function toSave()
    {
        $trans = \App::$app->db->beginTransaction();
        if ($this->validate()) {
            //todo save worker info
            $this->save();
            //todo upload file
            if (!$this->hasErrors()) {
                $avatar_file = UploadedFile::getInstance($this, "avatar_file");
                if ($avatar_file) {
                    if (($fileInfo = self::toUpload($avatar_file, "avatar/worker/$this->worker_id"))) {
                        //todo save worker avatar
                        $this->avatar_url = $fileInfo;
                        $this->save();
                    } else {
                        $this->addError("avatar", "Have error when upload avatar.");
                    }
                }
            }

            //todo add shop to worker
            if (!$this->hasErrors()) {
                if (!empty($this->shops)) {
                    foreach ($this->shops as $shop_id => $shopData) {
                        $shop = ShopInfo::findOne($shop_id);
                        if ($shop) {
                            $model = WorkerMappingShop::findOne([
                                "shop_id" => $shop_id,
                                "worker_id" => $this->worker_id,
                            ]);
                            if (!$model) {
                                $model = new WorkerMappingShop();
                                $model->worker_id = $this->worker_id;
                                $model->shop_id = $shop_id;
                            }
                            $isEnable = ArrayHelper::getValue($shopData, "isEnable", 0);
                            $isExistCalendar = ShopCalendar::find()
                                ->where([
                                    "shop_id" => $shop_id,
                                    "worker_id" => $this->worker_id,
                                    "status" => ShopCalendar::STATUS_ACTIVE,
                                    "type" => ShopCalendar::TYPE_WORKING_DAY,
                                ])
                                ->andWhere("date >= :today", [
                                    ":today" => DatetimeHelper::now(DatetimeHelper::FULL_DATE),
                                ])
                                ->exists();
                            if ($isEnable == "1") {
                                //todo add worker working with shop
                                $model->status = WorkerMappingShop::STATUS_ACTIVE;
                                if (!$model->save()) {
                                    $this->addErrors($model->getErrors());
                                }
                            } else {
                                //todo remove worker on this shop
                                if (!$isExistCalendar) {
                                    $model->delete();
                                } else {
                                    $this->addError("shops", \App::t("backend.worker.message", "Can not disabled shop [{shop}] because have calendar on future.", [
                                        "shop" => $shop->shop_name,
                                    ]));
                                }
                            }
                            $this->shops[$shop_id] = ArrayHelper::merge($this->shops[$shop_id],
                                $model->toArray(["ref_id", "worker_url"], ["shopInfo"]));
                            $this->shops[$shop_id]["isSwitchable"] = !$isExistCalendar;
                        } else {
                            $this->addError("shops", \App::t("backend.worker.message", "Shop [{shop}] is not exist.", [
                                "shop" => $shop->shop_name,
                            ]));
                            break;
                        }
                    }
                }
            }
        }
        if (!$this->hasErrors()) {
            $trans->commit();
            return true;
        }
        $trans->rollBack();
        return false;
    }

    public function getWorkerBookingUrl()
    {
        return \App::$app->urlManagerFrontend->createAbsoluteUrl([
            "booking/worker/$this->worker_id"
        ]);
    }

    public static function toUpload(UploadedFile $file, $path, $name = null)
    {
        $full_path = \Yii::getAlias('@upload') . "/" . $path;
        if (!file_exists($full_path)) {
            mkdir($full_path, 0777, true);
        }
        $file_name = (!empty($name)) ? "$name.$file->extension" : time() . "_$file->name";
        if (file_exists($full_path . "/" . $file_name)) {
            unlink($full_path . "/" . $file_name);
        }
        if ($file->saveAs($full_path . "/" . $file_name)) {
            //TODO save file info to database

                return $file_name;
         }
        return false;
    }
}