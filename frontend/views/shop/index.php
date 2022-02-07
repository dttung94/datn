<?php

use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\user\UserConfig;
use common\helper\HtmlHelper;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use frontend\assets\MemberAsset;
use frontend\forms\shop\ShopForm;
use common\entities\calendar\CourseInfo;

/**
 * @var $this \frontend\models\FrontendView
 * @var $model ShopForm
 * @var $worker_id integer
 * @var $slot_today integer
 * @var $slot_tomorow integer
 * @var $workers_today array
 * @var $workers_tomorow array
 */
$bundle = App::$app->assetManager->getBundle(MemberAsset::className());
//$this->title = App::t("frontend.homepage.title", "Shop View");
$this->subTitle = $model->shop_name;

$timeZone = App::$app->timeZone;
$bookingTomorrowAt = ShopConfig::getValue(ShopConfig::KEY_SHOP_BOOKING_TOMORROW_AT, $shop_id, "");
$now = date('H:i');
$result = strlen($bookingTomorrowAt) > 0 && strtotime($now) > strtotime($bookingTomorrowAt) ? true : false;
function convertTime($time)
{
    return date('H:i', strtotime($time));
}
$isAllowFreeBooking = $model->isAllowFreeBooking;


/**
 * Task-594
 * Delete Duplicated
 * @var $courses CourseInfo[]
 */
$courses = CourseInfo::find()
    ->where([
        "status" => CourseInfo::STATUS_ACTIVE,
    ])
    ->all();
?>
<div ng-controller="ShopController" ng-init="init('<?php echo $shop_id ?>', '<?php echo $worker_id ?>', '<?php echo $model->date ?>', '<?php echo $bookingTomorrowAt ?>','<?php echo $timeZone ?>')">
    <div class="shop-form">
                <div class="tac mb20">
            <span>
                <strong><?php echo App::t("frontend.shop.title", "{today-date}（{today-day-of-week})", [
                    "today-date" => App::$app->formatter->asDate(time(), "d/M/y"),
                    "today-day-of-week" => App::$app->formatter->asDayOfWeek(time()),
                ]) ?></strong>
            </span>
            <?php if (strlen($bookingTomorrowAt) > 0) : ?>
                <span>
                </span>
            <?php endif; ?>
        </div>

        <div class="mb20 box-container box-space-between box-middle">
            <span><?php echo App::t("frontend.shop.message", "Chỉ hiển thị khung làm việc trống") ?></span>
            <label class="checkbox-switch">
                <?php echo HtmlHelper::checkbox("showBookedSlot", true, [
                    "ng-model" => "showBookedSlot"
                ]) ?>
                <span class="checkbox-slider"></span>
            </label>
        </div>



    <?php
    if ($slot_today > 0) {
        echo $this->render("_booking_slot_today", [
            "model" => $model,
            "worker_id" => $worker_id,
            "workers" => $workers_today,
            'shop_id' => $shop_id,
        ]);
    }
    ?>
    <?php
    if ($result && $slot_tomorow > 0) {
        echo $this->render("_booking_slot_tomorrow", [
            "model" => $model,
            "worker_id" => $worker_id,
            "workers" => $workers_tomorow,
            'shop_id' => $shop_id,
        ]);
    }
    ?>

    <div class="panel-group mt40 mb20">
        <p class="tac mb20">
            <?php
            if ($slot_today <= 0 && $slot_tomorow <= 0) {
                echo App::t("frontend.shop.message", "Hiện không có lịch làm việc nào<br/> Xin vui lòng đợi một lúc và thử lại", []);
            }
            ?>
        </p>
    </div>

    <?php echo $this->render("modals/_modal_booking_online", []); ?>
    <?php echo $this->render("modals/_modal_booking_online_confirm", ['courses' => $courses]); ?>
</div>