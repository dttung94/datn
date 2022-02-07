<?php
namespace backend\modules\service\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\service\forms\sms\SmsHistoryForm;
use backend\modules\service\forms\sms\SmsTemplateForm;
use backend\modules\service\ServiceModule;
use common\entities\user\UserInfo;
use common\forms\service\SendSMSForm;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * Class SmsController
 * @package backend\modules\service\controllers
 */
class SmsController extends BackendController
{
    public $layout = "layout_admin";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => BackendAccessRule::className(),
                    "module" => ServiceModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            "index",
                            "template",
                            'test',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
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

    public function actionIndex()
    {
        $form = new SmsHistoryForm();
        return $this->render("history/index",[
            "model" => $form,
        ]);
    }

    public function actionTemplate($type = SmsTemplateForm::TYPE_MEMBER_REGISTER_VERIFY_PHONE_NUMBER)
    {
        $form = SmsTemplateForm::findOne([
            "type" => $type,
        ]);
        if ($form == null) {
            $form = new SmsTemplateForm();
            $form->type = $type;
        }
        if ($form->load($this->request->post())) {
            if ($form->toSave()) {
                \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.service_mail.message", "Tạo template SMS thành công"));
            } else {
                \App::$app->session->setFlash("ERROR_MESSAGE", \App::t("backend.service_mail.message", "Không tạo được template SMS"));
            }
        }
        return $this->render("template/index", [
            "model" => $form,
        ]);
    }
}