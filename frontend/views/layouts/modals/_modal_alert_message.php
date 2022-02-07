<?php
/**
 * @var $this \frontend\models\FrontendView
 */
use common\helper\HtmlHelper;

$isGuest = App::$app->user->isGuest ? 1 : 0;
$loginUrl = App::$app->urlManager->createUrl(["site/login"]);
$isHasAlertMessage = App::$app->session->hasFlash("ALERT_MESSAGE") ? 1 : 0;
$isHasErrorMessage = App::$app->session->hasFlash("ERROR_MESSAGE") ? 1 : 0;
$message = App::$app->session->getFlash("ALERT_MESSAGE", App::$app->session->getFlash("ERROR_MESSAGE"));
$this->registerJs(<<<JS
    jQuery(document).ready(function () {
        if($isHasAlertMessage || $isHasErrorMessage){
            var redirectUrl = undefined;
            if($isGuest){
                redirectUrl = '$loginUrl';                
            }
            toastr.options.timeOut = 0;
            if($isHasAlertMessage){
                toastr.options.onclick = function() { 
                    if(redirectUrl != undefined){
                        window.location.href = redirectUrl;
                    }
                };
                toastr.info($(".alert-message").data("alert-message"));
            }else if($isHasErrorMessage){
                toastr.error($(".alert-message").data("alert-message"));
            }
        }
    });
JS
    , \yii\web\View::POS_END, "register-js-alert-message");

if ($isHasAlertMessage || $isHasErrorMessage) {
    echo HtmlHelper::tag("span", "", [
        "class" => "alert-message",
        "data-alert-message" => "$message",
    ]);
}