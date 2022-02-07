<?php
/**
 * @var $this \frontend\models\FrontendView
 */
use common\helper\HtmlHelper;

$isHasAlertMessage = App::$app->session->hasFlash("ALERT_MESSAGE") ? 1 : 0;
$isHasErrorMessage = App::$app->session->hasFlash("ERROR_MESSAGE") ? 1 : 0;
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        if($(".alert-message").length > 0){
            toastr.options.timeOut = 6000;
            toastr.options.onHidden = function() {
            };
            toastr.options.onclick = function() { 
            };
            var message = $(".alert-message").data("alert-message"),
            type = $(".alert-message").data("alert-type");
            console.log("Has message", message, type);
            if(type == 'error'){
                toastr.error(message);
            }else{
                toastr.info(message);
            }
        }
    });
JS
    , \yii\web\View::POS_END, "register-js-alert-message");

if ($isHasAlertMessage || $isHasErrorMessage) {
    $message = App::$app->session->getFlash("ALERT_MESSAGE", App::$app->session->getFlash("ERROR_MESSAGE"));
    if (App::$app->user->isGuest) {
        $message .= "<br/>";
        $message .= App::t("frontend.global.message", '数秒後に自動的にログイン画面に切り替わります。', [
        ]);
    }
    echo HtmlHelper::tag("span", "", [
        "class" => "alert-message",
        "data-alert-type" => ($isHasErrorMessage ? 'error' : 'alert'),
        "data-alert-message" => "$message",
    ]);
}