<?php
namespace backend\modules\service\controllers;

use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\service\forms\mail\MailSearchForm;
use backend\modules\service\forms\mail\MailTemplateForm;
use backend\modules\service\forms\mail\MailTemplateSearchForm;
use backend\modules\service\ServiceModule;
use common\entities\calendar\BookingInfo;
use common\entities\service\TemplateMail;
use common\entities\system\SystemConfig;
use common\entities\system\SystemData;
use common\entities\user\UserData;
use common\entities\user\UserInfo;
use common\forms\service\SendMailForm;
use common\helper\AmazonHelper;
use common\mail\forms\Mail;
use frontend\forms\profile\MemberHobbies;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\UploadedFile;

class MailController extends BackendController
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
                            "auto",
                            "manual",
                            'magazine',
                            'to-send-magazine',
                            'test-template',
                            'import-file',
                            'test',
                            'get-user-send',
                            'get-user-follow-hobbies'
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'to-send-magazine' => ['post'],
                    'index' => ['post', 'get'],
                ],
            ],
        ];
    }

    public function actionIndex($type = "history", $key = "", $id = null)
    {
        $mailHistory = MailSearchForm::findOne($id);
        if ($mailHistory == null) {
            $mailHistory = new MailSearchForm();
        }
//        $mailTemplate = new MailTemplateSearchForm($key);
//        if ($mailTemplate->load($this->request->post()) && $mailTemplate->toSave()) {
//            \Yii::$app->session->setFlash("message", \App::t("backend.service_mail.message", "Update email template success."));
//        }
        $mailHistory->load($this->request->get());
        $mailHistory->load($this->request->post());
        return $this->render("index", [
            "type" => $type,
            "mailHistory" => $mailHistory,
//            "mailTemplate" => $mailTemplate,
        ]);
    }

    public function actionImportFile()
    {
        $file = UploadedFile::getInstanceByName('file');
        if (!empty($file)) {
            $pathToS3 = \Yii::$app->params["aws.magazine.path"];
            $date = getdate();
            $dateUpload = $date['year'] . $date['mon'] . $date['mday'] . $date['hours'] . $date['minutes'] . $date['seconds'];
            $result = $this->uploadImgToS3($pathToS3, $file, $dateUpload);
            if ($result['success']) {
                return $result['full_path'];
            }
        }
        return false;
    }

    public function actionToSendMagazine()
    {
        if (!$this->request->isAjax) {
            return $this->redirect('/service/mail/magazine');
        }
        $mail = new Mail();
        $data = $this->request->post();
        $data = [
            'email' => $data['email'],
            'name' => $data['name'],
            'subject' => $data['subject'],
            'content' => $data['content'],
            'params' => []
        ];
        return $mail->toSend($data, true, true);
    }

    public function actionAuto($type = MailTemplateForm::TYPE_VERIFY_MAIL)
    {
        $form = MailTemplateForm::findOne([
            "type" => $type,
        ]);
        if ($form == null) {
            $form = new MailTemplateForm();
            $form->type = $type;
        }
        if ($form->load($this->request->post())) {
            if ($form->toSave()) {
                \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.service_mail.message", "Tạo template email thành công"));
            } else {
                \App::$app->session->setFlash("ERROR_MESSAGE", \App::t("backend.service_mail.message", "Lỗi tạo template không thành công"));
            }
        }
        return $this->render("template/auto/index", [
            "model" => $form,
            'type' => $type
        ]);
    }


    /**
     *
     * @param $type
     * @param $date
     * @param $typeMail: type of mail ex: TYPE_WORKER_NEW, . .
     * @param  bool  $isCheckReceiveEmail : is check filter member register email or not:
     * if send parameter will query where column(parameter) == UserInfo::RECEIVE
     * @return array|false|string|\yii\db\ActiveRecord[]
     */
    public function actionGetUserSend($type, $date, $tag, $isCheckReceiveEmail, $typeMail)
    {
        $status = [UserInfo::STATUS_ACTIVE, UserInfo::STATUS_WORKER_BLACK_LIST];
        $users = UserInfo::find()
            ->where([
                'role' => UserInfo::ROLE_USER,
                'type_notification' => UserInfo::TYPE_NOTIFICATION_EMAIL,
            ])
            ->andWhere(['in', 'status', $status])
            ->andWhere(['!=', 'email', ''])
            ->andWhere(['not', ['email' => null]]);
        // use variable == name column to query
        // if !empty column $isCheckReceiveEmail and typeSelect != all and $typeMail != list
        if (!empty($isCheckReceiveEmail) && $type != TemplateMail::USER_ALL
            && in_array($typeMail, [TemplateMail::TYPE_MAIL_MAGAZINE, TemplateMail::TYPE_WORKER_NEW])) {
            $users->andWhere([$isCheckReceiveEmail => UserInfo::RECEIVE]);
        }
        $users = $users->all();
        switch ($type) {
            case TemplateMail::USER_TIME:
                $response = $this->getUserTime($users, $date);
                break;
            case TemplateMail::USER_TAG:
                $response =  $this->getUserTag($users, $tag);
                break;
            case TemplateMail::USER_FAVORITE:
                $response =  $this->getUserFavorite($users);
                break;
            default:
                $response =  $users;
                break;
        }
        if ($this->request->isAjax) {
            $result = [];
            foreach ($response as $value) {
                $result[] = [
                    'id' => $value->user_id,
                    'email' => $value->email,
                    'name' => $value->full_name
                ];
            }
            return json_encode($result);
        }
        return $response;
    }

    private function getUserTime($users, $date)
    {
        $response = [];
        foreach ($users as $user) {
            $last = BookingInfo::find()
                ->where([
                    'member_id' => $user->user_id,
                    'status' => BookingInfo::STATUS_ACCEPTED
                ])
                ->andWhere(['<=', 'created_at', $date])
                ->orderBy('created_at DESC')
                ->one();
            if (!empty($last)) {
                $response[] = $user;
            }
        }
        return $response;
    }

    private function getUserTag($users, $tag)
    {
        $tags = !empty($tag) ? explode(',', $tag) : [];
        $response = [];
        foreach ($users as $user) {
            if (empty($tags) && !empty($user->tag)) {
                $response[] = $user;
            }
            if (array_intersect($tags, explode(',', $user->tag))) {
                $response[] = $user;
            }
        }
        return $response;
    }

    private function getUserFavorite($users)
    {
        $response = [];
        foreach ($users as $user) {
            $form = MemberHobbies::findOne($user->user_id);
            $hobbies = ArrayHelper::getColumn($form->getUserHobbies(), 'data_id');
            if (count($hobbies) > 2) {
                $response[] = $user;
            }
        }
        return $response;
    }


    private function sendEmail($request, $isCheckReceiveEmail, $typeMail)
    {
        $type = $request['send'];
        $params = [];
        $condition = [
            'role' => UserInfo::ROLE_USER,
            'status' => UserInfo::STATUS_ACTIVE,
        ];
        if ($type == TemplateMail::TYPE_WORKER_NEW) {
            $condition['mail_new_worker'] = UserInfo::RECEIVE;
        }
        if (
            $type == TemplateMail::TYPE_CHANGE_TIME_WORKING ||
            $type == TemplateMail::TYPE_CANCEL_CALENDAR_WORKER
        ) {
            $condition['mail_calendar'] = UserInfo::RECEIVE;
            $url = \Yii::$app->params["site.frontend"].'/site/index?token='.base64_encode('ON/OFF');
            $linkOnOff = '<a href="'.$url.'">'.$url.'</a>';
            $params = [
                'link-on-off-receive' => $linkOnOff
            ];
        }
        $tag = !empty($request['tags']) ? implode(',', $request['tags']) : '';
        if(!empty(json_decode($request['data_id_users']))) {
            $users = UserInfo::find()->andWhere(['in', 'user_id', json_decode($request['data_id_users'])])->all();
        } else {
            $users = $this->actionGetUserSend($request['type_user'], $request['date'], $tag, $isCheckReceiveEmail, $typeMail);
        }
        $mail = new Mail();
        $template = TemplateMail::getMailTemplate($type);
        foreach ($users as $user) {
            $data = [
                'email' => $user->email,
                'name' => $user->username,
                'subject' => $template->title,
                'content' => $template->content,
                'params' => $params,
                'mail_type' => TemplateMail::MAIL_MANUAL,
            ];
            $mail->toSend($data);
        }
    }

    public function actionTestTemplate($cat, $sentTo = "hieund.dev@gmail.com")
    {
        $this->response->format = Response::FORMAT_JSON;
        if (!isset(SendMailForm::$defaultVal[$cat])) {
            return [
                "success" => false,
                "message" => "$cat is not exist",
            ];
        }
        $params = [];
        $paramsKeys = isset(SendMailForm::$defaultVal[$cat]["params"]) ? SendMailForm::$defaultVal[$cat]["params"] : [];
        if (!empty($paramsKeys)) {
            foreach ($paramsKeys as $key) {
                $params[$key] = $key;
            }
        }
        $mail = new SendMailForm();
        $mail->params = $params;
        $mail->from_email = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
        $mail->subject = SendMailForm::getEmailTemplate($cat, "title", $mail->params);
        $mail->content = SendMailForm::getEmailTemplate($cat, "content", $mail->params);
        $mail->to = [
            [
                "email" => $sentTo
            ]
        ];
        $mail->tag = [
            "TEST_SEND_MAIL_TEMPLATE",
            "$cat",
            "$sentTo"
        ];
        return [
            "success" => $mail->toSend(),
            "data" => $mail,
        ];
    }

    public function actionTest($to = "hieund.dev@gmail.com")
    {
        $mail = new SendMailForm();
        $mail->from_email = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
        $mail->subject = "Test email";
        $mail->content = "Content test send email";
        $mail->to = $to;
        if ($mail->toSend() !== false) {
            echo Json::encode($mail);
        } else {
            var_dump($mail->getErrors());
        }
    }

    public function uploadImgToS3($pathToS3, $image, $dateUpload)
    {
        $imageType = explode('/', $image->type)[1];
        $fileName = "magazine-image-" . $dateUpload . '.' . $imageType;
        $locationFileTemp = $image->tempName;
        $pathToS3 = $pathToS3 . $fileName;

        $amazonHelper = new AmazonHelper();
        return $amazonHelper->uploadImageToS3($locationFileTemp, $pathToS3, 'public-read', $image->type);
    }

    public function actionGetUserFollowHobbies()
    {
        $users = $this->request->post('users');
        $hobbies = $this->request->post('data');

        $userIds = [];
        $userResult = [];

        if ($users != null) {
            foreach (json_decode($users) as $user) {
                $userIds[] = $user->id;
            }
        }

        $dataHobbies = UserData::find()->where(['field_key' => UserData::KEY_USER_HOBBIES])->andWhere(['in', 'user_id', $userIds])->all();

        foreach ($dataHobbies as $dataHobby) {
            if(count(array_intersect(json_decode($dataHobby->value), $hobbies)) > 0 ) {
                $userResult[] = $dataHobby->user;
            }
        }

        if ($this->request->isAjax) {
            $result = [];
            foreach ($userResult as $value) {
                $result[] = [
                    'id' => $value->user_id,
                    'email' => $value->email,
                    'name' => $value->full_name
                ];
            }
            return json_encode($result);
        }
        return json_encode($users);
    }
}
