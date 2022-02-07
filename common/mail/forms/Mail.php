<?php
namespace common\mail\forms;

use common\entities\service\ServiceMail;
use common\entities\service\TemplateMail;
use common\entities\system\SystemConfig;
use common\entities\user\UserInfo;

class Mail
{
    //Data mail cần có email, name, content, subject, params
    public function toSend($data, $isChangeContent = true, $isMagazine = false)
    {
        if (empty($data['email'])) {
            return false;
        }
        $email = $data['email'];
        $customer = UserInfo::find()->where(['email' => $email])->one();
        if ($customer) {
            $typeNotification = $customer->type_notification;
        } else {
            $typeNotification = null;
        }
        if ($typeNotification == UserInfo::TYPE_NOTIFICATION_EMAIL || !$customer || $typeNotification == UserInfo::TYPE_NOTIFICATION_SMS_AND_EMAIL) {

            $name = $data['name'];

            $subject = !$isMagazine ? TemplateMail::convertString($data['subject'], $data['params']) : $data['subject'];
            $content = !$isMagazine ? TemplateMail::convertString($data['content'], $data['params']) : $data['content'];

            $urlProfile = \Yii::$app->params["site.frontend"].'/profile/delete-email';

            if ($isChangeContent) {
                $content .= '<br><a href="'.$urlProfile.'">Bấm vào đây nếu bạn muốn dừng nhận thông báo tới email này.</a>';
            }

            if (!isset($data['worker_info']) && empty($data['worker_info'])) {
                $workerInfo = null;
                $date = null;
            } else {
                $workerInfo = $data['worker_info'];
            }

            $formEmail = SystemConfig::getValue(SystemConfig::CATEGORY_SYSTEM, SystemConfig::SYSTEM_NO_REPLY_EMAIL);
            $formName = 'Hệ thống đặt lịch chuỗi salon tóc nam Tuấn Dũng';

            $mailer = \Yii::$app->mailer;
            $mailer->setViewPath("@common/mail");
            $result[$email] = $mailer->compose("mail", ['content' => $content, 'workers' => $workerInfo,])
                ->setTo($email)
                ->setFrom([$formEmail => $formName])
                ->setReplyTo('noreply@unpretty.com')
                ->setSubject($subject)
                ->send();

            $contentDB = \Yii::$app->view->renderFile('@common/mail/mail.php', ['content' => $content, 'workers' => $workerInfo]);

            $dataTos = [
                [
                    'email' => $email,
                    'name' => $name,
                    'type' => 'to'
                ]
            ];

            if ($isMagazine) {
                $mailType = TemplateMail::MAIL_MAGAZINE;
            } else {
                $mailType = TemplateMail::MAIL_AUTO;
            }

            if (isset($data['mail_type'])) {
                $mailType = $data['mail_type'] == TemplateMail::MAIL_MANUAL ? TemplateMail::MAIL_MANUAL : ($isMagazine ? TemplateMail::MAIL_MAGAZINE : TemplateMail::MAIL_AUTO);
            }
            $dataLogs = [
                'subject' => $subject,
                'content' => $contentDB,
                'from_email' => $formEmail,
                'from_name' => $formName,
                'to' => json_encode($dataTos),
                'result' => json_encode($result),
                'mail_type' => $mailType,
            ];

            if ($result[$email]) {
                $this->saveLog($dataLogs);
            }

            return $result[$email];
        }
        return false;
    }

    protected function saveLog($data)
    {
        $type = 'html';
        $save = new ServiceMail();
        $save->type = $type;
        $save->subject = $data['subject'];
        $save->content = $data['content'];
        $save->from_email = $data['from_email'];
        $save->from_name = $data['from_name'];
        $save->to = $data['to'];
        $save->result = $data['result'];
        $save->role = UserInfo::ROLE_USER;
        $save->status = ServiceMail::STATUS_SENT;
        $save->mail_type = $data['mail_type'];
        $save->save();
    }

}
