<?php
namespace frontend\controllers;


use frontend\forms\coupon\CouponForm;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Class CouponController
 * @package frontend\controllers
 */
class CouponController extends FrontendController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'load-coupons',
                            'add-coupon',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    public function actionLoadCoupons()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $form = new CouponForm();
        return [
            "success" => true,
            "data" => $form->getMemberCoupon($this->userInfo->user_id),
            "courses" => $form->getCourses()
        ];
    }

    public function actionAddCoupon()
    {
        if (!$this->request->isAjax) {
            return $this->redirect([
                "/shop"
            ]);
        }
        $this->response->format = Response::FORMAT_JSON;
        $couponCode = $this->request->post("coupon_code");
        $userId = $this->userInfo->user_id;
        $couponForm = new CouponForm();
        if ($couponForm->addCoupon($userId, $couponCode)) {
            return [
                "success" => true,
                "message" => \App::t("frontend.coupon.message", "追加する"),
            ];
        } else {
            return [
                "success" => false,
                "message" => \App::t("frontend.coupon.message", "このクーポンコードは存在しません。"),
                "request" => $this->request->post(),
            ];
        }
    }
}