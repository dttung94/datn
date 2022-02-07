<?php
namespace backend\modules\calendar\forms\booking;

use common\entities\calendar\BookingInfo;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;
use common\entities\user\UserConfig;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use common\helper\DatetimeHelper;
use yii\helpers\Json;
use yii\db\Query;

/**
 * Class CalendarForm
 * @package backend\modules\calendar\forms\booking
 *
 * @property string $date
 * @property array $shop_ids
 *
 * @property ShopInfo[] $shops
 * @property ShopInfo[] $allShop
 * @property WorkerInfo[] $workers
 * @property ShopCalendarSlot[] $slots
 * @property array $dates
 */
class CalendarForm extends ShopInfo
{
    const CURRENCY_CODE = "VNĐ";
    public $date, $shop_ids = [], $type;

    public function rules()
    {
        return ArrayHelper::merge(parent::rules(), [
            [['shop_ids'], 'array'],
            [['date', 'shop_ids'], 'safe'],
        ]);
    }

    public function getShops($justWorking = true)
    {
        $query = ShopInfo::find();
        if ($justWorking) {
            $query->innerJoin(ShopCalendar::tableName(),
                ShopCalendar::tableName() . ".shop_id = " . ShopInfo::tableName() . ".shop_id AND " . ShopCalendar::tableName() . ".date = :date AND " . ShopCalendar::tableName() . ".type = :type AND " . ShopCalendar::tableName() . ".status = :status", [
                    ":type" => ShopCalendar::TYPE_WORKING_DAY,
                    ":date" => $this->date,
                    ":status" => ShopCalendar::STATUS_ACTIVE,
                ]);
        }
        if (\App::$app->user->identity->role == UserInfo::ROLE_MANAGER) {
            $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));
            $query->andWhere(["IN", ShopInfo::tableName() . ".shop_id", $shopIds]);
        }
        $query->andWhere([
            ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
        ]);
        return $query->all();
    }

    public function getAllShop()
    {
        $query = ShopInfo::find();
        if (\App::$app->user->identity->role == UserInfo::ROLE_MANAGER) {
            $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));
            $query->andWhere(['IN', ShopInfo::tableName() . ".shop_id", $shopIds]);
        }
        return $query->andWhere([
            ShopInfo::tableName() . ".status" => ShopInfo::STATUS_ACTIVE,
        ])->all();
    }

    public function getWorkers($justWorking = true)
    {
        $query = (new Query())
            ->from(['wi' => WorkerInfo::tableName()])
            ->where(['wi.status' => WorkerInfo::STATUS_ACTIVE])
            ->innerJoin(['wms' => WorkerMappingShop::tableName()], 'wi.worker_id'.' = wms.worker_id')
            ->andWhere(["IN", "wms.shop_id", $this->shop_ids]);
//            ->leftJoin(['wc' => WorkerConfig::tableName()], "wi.worker_id = wc.worker_id and wc.key = '".WorkerConfig::KEY_WORKER_NOTE."'");

        if ($justWorking) {
            $query->innerJoin(['sc' => ShopCalendar::tableName()], 'wi.worker_id = sc.worker_id and wms.shop_id = sc.shop_id')
                ->andWhere([
                    "sc.date" => $this->date,
                    "sc.type" => ShopCalendar::TYPE_WORKING_DAY,
                    "sc.status" => ShopCalendar::STATUS_ACTIVE,
                ])
                ->andWhere(["IN", "sc.shop_id", $this->shop_ids]);
        }
        $totalBookingQuery = (new Query())
            ->select('COUNT(*)')
            ->from(BookingInfo::tableName())
            ->innerJoin(ShopCalendarSlot::tableName(), ShopCalendarSlot::tableName() . ".slot_id = " . BookingInfo::tableName() . ".slot_id")
            ->where([
                BookingInfo::tableName() . ".status" => BookingInfo::STATUS_ACCEPTED,
                ShopCalendarSlot::tableName() . ".date" => $this->date,
            ])
            ->andWhere(ShopCalendarSlot::tableName() . ".worker_id = wi.worker_id");
        $countingShop = (new Query())
            ->from(ShopCalendar::tableName())
            ->where([
                "date" => $this->date,
                "type" => ShopCalendar::TYPE_WORKING_DAY,
                "status" => ShopCalendar::STATUS_ACTIVE,
            ])
            ->andWhere("worker_id = wi.worker_id")
            ->select(['COUNT(shop_id)']);
        return $query
            ->select([
                'wi.worker_id',
//                'wi.worker_rank',
                'wi.worker_name',
                'wi.description',
//                'wms.ref_id',
                'wi.status',
                'wms.shop_id',
                'sc.work_start_time as startTime',
                'sc.work_end_time as endTime',
//                'IFNULL(wc.value, "") as '.WorkerConfig::KEY_WORKER_NOTE,
                'totalBooking' => $totalBookingQuery,
                'key' => 'CONCAT(wms.shop_id, "_", wi.worker_id)',
                'working_shop_count' => $countingShop,
            ])
            ->orderBy(['wms.shop_id' => SORT_ASC])
            ->all();
    }

    public function getDates()
    {
        return [
            [
                "date" => DatetimeHelper::now(DatetimeHelper::FULL_DATE),
                "label" => \App::t("backend.booking.label", "Hôm nay"),
            ],
            [
                "date" => date(DatetimeHelper::FULL_DATE, time() + 60 * 60 * 24 * 1),
                "label" => \App::t("backend.booking.label", "Ngày mai"),
            ]
        ];
    }

    public function extraFields()
    {
        return ArrayHelper::merge(parent::extraFields(), [
            'date',
            'shop_ids'
        ]);
    }

    public function getCalendarDataSecond($workerId)
    {
        $date = $this->date;
        $modelWorkerInfo = new WorkerInfo();
        $shopIds = $workerId ? $_GET['shop_ids'] : $this->shop_ids;
        $dataSchedules = $modelWorkerInfo->getScheduleSecond($date, $shopIds, true, $workerId);
        $schedules = $dataSchedules['schedules'];
        $slots = $modelWorkerInfo->slotExists($date, $this->shop_ids, $workerId);
        $responses = [];
        foreach ($schedules as $workerId => $schedule) {
            $responses[$workerId]['schedules'] = $schedule;
            if (isset($slots[$workerId])) {
                $responses[$workerId]['slots'] = $slots[$workerId];
                foreach ($responses[$workerId]['slots'] as $key => $value) {
                    if (!$value['bookingInfo']) {
                        $responses[$workerId]['slots'][$key]['colorSlot'] = SystemConfig::getValue(SystemConfig::CATEGORY_COLOR, SystemConfig::SLOT_NONE);
                    }
                }
            }
        }
        return [
            'startTime' => $dataSchedules['startTime'],
            'data' => $responses
        ];
    }

    public function getCalendarData($worker_id = null)
    {
        $data = [];
        $timeStep = 5;
        $workers = $this->workers;
        $date = $this->date;
        $dayData = Json::decode(file_get_contents(\App::getAlias("@common/data/day-data.json")));
        $modelWorkerInfo = new WorkerInfo();
        $scheduleAll = $modelWorkerInfo->getScheduleSecond($date, $this->shop_ids);
        $slotAll = $modelWorkerInfo->getSlotSecond($date, $this->shop_ids);

        foreach ($workers as $worker) {
            if (!empty($worker_id) && $worker->worker_id != $worker_id) {
                continue;
            }
            $workerCalendarData = $dayData;
            foreach ($scheduleAll as $shop_id => $schedule) {
                if ($schedule->worker_id == $worker->worker_id) {
                    $work_start_hour = DatetimeHelper::getHourFromTimeFormat($schedule->work_start_time);
                    $work_start_time = DatetimeHelper::getMinuteFromTimeFormat($schedule->work_start_time);
                    $work_end_hour = DatetimeHelper::getHourFromTimeFormat($schedule->work_end_time);
                    $work_end_time = DatetimeHelper::getMinuteFromTimeFormat($schedule->work_end_time);
                    $startMinute = intval($work_start_hour) * 60 + intval($work_start_time);
                    $startMinute = ($startMinute > 0) ? $startMinute : 0;
                    $endMinute = intval($work_end_hour) * 60 + intval($work_end_time);
                    $endMinute = ($endMinute <= 1435) ? $endMinute : 1435;
                    for ($time = $startMinute; $time <= $endMinute; $time += $timeStep) {
                        $workerCalendarData[$time] = ArrayHelper::merge($workerCalendarData[$time], [
                            "date" => $date,
                            "worker_id" => $worker->worker_id,
                            "worker_rank" => $worker->worker_rank,
                            "isWorkingTime" => true,
                            "shop_id" => $schedule->shop_id,
                            "colorShop" => ShopConfig::getValue(ShopConfig::KEY_SHOP_COLOR, $schedule->shop_id, ShopInfo::getShopColorDefault()[$schedule->shop_id]),
                        ]);
                    }
                }
            }
            foreach ($slotAll as $slot) {
                if ($slot->worker_id == $worker->worker_id) {
                    /**
                     * @var $slot ShopCalendarSlot
                     */
                    $slotCalendarData = self::getSlotData($slot, $timeStep, $dayData);
                    foreach ($slotCalendarData as $time => $slotTimeData) {
                        $workerCalendarData[$time] = $slotTimeData;
                    }
                }
            }
            if (!empty($worker_id) && $worker->worker_id == $worker_id) {
                return array_values($workerCalendarData);
            }
            $data[$worker->worker_id] = array_values($workerCalendarData);
        }
        return $data;
    }

    public static function getSlotData(ShopCalendarSlot $slot, $timeStep = 5, $dayData = null)
    {
        $endTimeCalendar = $slot->end_time > '24:00' ?
            $slot->end_time :
            date('H:i', strtotime($slot->end_time));
        if ($slot->status !== ShopCalendarSlot::STATUS_DELETE) {
            $start = date('H:i', strtotime($slot->start_time));
            $end = date('H:i', strtotime($slot->end_time));
            $condition = [
                'date' => $slot->date,
                'worker_id' => $slot->worker_id,
                'shop_id' => $slot->shop_id,
            ];
            $slots = ShopCalendarSlot::find()
                ->where($condition)
                ->andWhere(['!=', 'status', ShopCalendarSlot::STATUS_EXPIRED])
                ->all();
            foreach ($slots as $value) {
                $startTime = date('H:i', strtotime($value->start_time));
                if (
                    $startTime > $start &&
                    $startTime < $end &&
                    date('H:i', strtotime($value->end_time)) > $endTimeCalendar
                ) {
                    $endTimeCalendar = date('H:i', strtotime($value->end_time));
                }
            }
        }
        if ($dayData == null) {
            $dayData = Json::decode(file_get_contents(\App::getAlias("@common/data/day-data.json")));
        }
        $slotCalendarData = [];
        $slot_start_hour = DatetimeHelper::getHourFromTimeFormat($slot->start_time);
        $slot_start_time = DatetimeHelper::getMinuteFromTimeFormat($slot->start_time);
        if ($slot->end_time == "24:00") {
            $slot_end_hour = 24;
            $slot_end_time = 0;
        } else {
            $slot_end_hour = DatetimeHelper::getHourFromTimeFormat($endTimeCalendar);
            $slot_end_time = DatetimeHelper::getMinuteFromTimeFormat($endTimeCalendar);
        }
        $startMinute = $slot_start_hour * 60 + $slot_start_time;
        $startMinute = ($startMinute >= 0) ? $startMinute : 0;
        $endMinute = $slot_end_hour * 60 + $slot_end_time;
        $endMinuteSlot = DatetimeHelper::getHourFromTimeFormat($slot->end_time) * 60 + DatetimeHelper::getMinuteFromTimeFormat($slot->end_time);
        $endMinute = ($endMinute <= 1440) ? $endMinute : 1440;

        $slotCalendarData[$startMinute] = ArrayHelper::merge($dayData[$startMinute], [
            "date" => $slot->date,
            "worker_id" => $slot->worker_id,
//            "worker_rank" => $slot->workerInfo->worker_rank,
            "isWorkingTime" => true,
            "shop_id" => $slot->shop_id,
            "colorShop" => ShopConfig::getValue(ShopConfig::KEY_SHOP_COLOR, $slot->shop_id, ShopInfo::getShopColorDefault()[$slot->shop_id]),
        ]);
        if ($slot->status == ShopCalendarSlot::STATUS_DELETE) {
            $slotCalendarData[$startMinute]['isInvisible'] = true;
        } else {
            $slotCalendarData[$startMinute]['colspan'] = $slot->duration_minute / $timeStep;
            $slotCalendarData[$startMinute]['slotData'] = $slot->toArray([], [
                "bookingInfo",
            ]);
        }
        if (
            ($slot->bookingInfo) &&
            ($slot->bookingInfo->status == BookingInfo::STATUS_PENDING || $slot->bookingInfo->status == BookingInfo::STATUS_UPDATING)
        ) {
            $totalSecondLeft =
                intval(SystemConfig::getValue(SystemConfig::CATEGORY_BOOKING, SystemConfig::BOOKING_MAX_TIME_CONFIRM_ONLINE_BOOKING)) -
                (time() - strtotime($slot->bookingInfo->modified_at));
            $slotCalendarData[$startMinute]["slotData"]["total_second_waiting_expired"] = $totalSecondLeft > 0 ? $totalSecondLeft : 0;
            $slotCalendarData[$startMinute]["slotData"]["is_waiting_confirm"] = true;
        }
        for ($time = $startMinute + $timeStep; $time < $endMinute; $time += $timeStep) {
            $slotCalendarData[$time] = ArrayHelper::merge($dayData[$time], [
                "date" => $slot->date,
                "worker_id" => $slot->worker_id,
//                "worker_rank" => $slot->workerInfo->worker_rank,
                "isWorkingTime" => true,
                "shop_id" => $slot->shop_id,
                "isInvisible" => $time < $endMinuteSlot ? true : false,
                "colorShop" => ShopConfig::getValue(ShopConfig::KEY_SHOP_COLOR, $slot->shop_id, ShopInfo::getShopColorDefault()[$slot->shop_id]),
            ]);
        }
        return $slotCalendarData;
    }

    public static function getSlotDataRemove(ShopCalendarSlot $slot, $timeStep = 5, $dayData = null)
    {
        if ($dayData == null) {
            $dayData = Json::decode(file_get_contents(\App::getAlias("@common/data/day-data.json")));
        }
        $slotCalendarData = [];
        $slot_start_hour = DatetimeHelper::getHourFromTimeFormat($slot->start_time);
        $slot_start_time = DatetimeHelper::getMinuteFromTimeFormat($slot->start_time);
        $slot_end_hour = DatetimeHelper::getHourFromTimeFormat($slot->end_time);
        $slot_end_time = DatetimeHelper::getMinuteFromTimeFormat($slot->end_time);

        $startMinute = $slot_start_hour * 60 + $slot_start_time;
        $startMinute = ($startMinute > 0) ? $startMinute : 0;
        $endMinute = $slot_end_hour * 60 + $slot_end_time;
        $endMinute = ($endMinute <= 1440) ? $endMinute : 1440;

        $slotCalendarData[$startMinute] = ArrayHelper::merge($dayData[$startMinute], [
            "date" => $slot->date,
            "worker_id" => $slot->worker_id,
//            "worker_rank" => $slot->workerInfo->worker_rank,
            "isWorkingTime" => true,
            "shop_id" => $slot->shop_id,
            "colorShop" => ShopConfig::getValue(ShopConfig::KEY_SHOP_COLOR, $slot->shop_id, ShopInfo::getShopColorDefault()[$slot->shop_id]),
        ]);
        for ($time = $startMinute + $timeStep; $time < $endMinute; $time += $timeStep) {
            $slotCalendarData[$time] = ArrayHelper::merge($dayData[$time], [
                "date" => $slot->date,
                "worker_id" => $slot->worker_id,
//                "worker_rank" => $slot->workerInfo->worker_rank,
                "isWorkingTime" => true,
                "shop_id" => $slot->shop_id,
                "isInvisible" => false,
                "colorShop" => ShopConfig::getValue(ShopConfig::KEY_SHOP_COLOR, $slot->shop_id, ShopInfo::getShopColorDefault()[$slot->shop_id]),
            ]);
        }
        return $slotCalendarData;
    }
}
