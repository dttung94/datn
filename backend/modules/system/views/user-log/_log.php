<?php
/**
 * @var $this \backend\models\BackendView
 * @var $model UserLogForm
 */
use backend\modules\system\forms\user\UserLogForm;
use yii\helpers\Html;

$user = $model->user;
$createdAt = Yii::$app->formatter->asDatetime($model->created_at);
$action = $model->getAttributeLabel($model->action);
switch ($model->action) {
    case UserLogForm::ACTION_FREE_BOOKING_REJECT:
    case UserLogForm::ACTION_FREE_BOOKING_CANCEL:
    case UserLogForm::ACTION_ONLINE_BOOKING_CANCEL:
        $actionHtml = "<label class='label label-danger user-log-action'>$action</label>";
        break;

    case UserLogForm::ACTION_FREE_BOOKING_ADD:
    case UserLogForm::ACTION_ONLINE_BOOKING_ADD:
        $actionHtml = "<label class='label label-warning user-log-action'>$action</label>";
        break;

    case UserLogForm::ACTION_USER_SIGN_UP:
        $actionHtml = "<label class='label label-info user-log-action'>$action</label>";
        break;

    case UserLogForm::ACTION_USER_CONFIRM:
    case UserLogForm::ACTION_USER_VERIFY_PHONE_NUMBER:
    case UserLogForm::ACTION_FREE_BOOKING_ACCEPT:
        $actionHtml = "<label class='label label-primary user-log-action'>$action</label>";
        break;

    case UserLogForm::ACTION_AUTO_CREATE_COUPON:
        $actionHtml = "<label class='label label-success user-log-action'>$action</label>";
        break;

    default:
        $actionHtml = "<label class='label label-default user-log-action'>$action</label>";
        break;
}
echo "<span style='color: white;'>$actionHtml $createdAt: </span>";
echo nl2br($model->message);