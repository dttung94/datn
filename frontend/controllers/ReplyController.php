<?php

namespace frontend\controllers;

use common\entities\forum\Reply;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ReplyController extends FrontendController
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
                            'create'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [],
            ],
        ];
    }

    public function actionCreate()
    {
        $reply = new Reply();
        $reply->comment_id = $this->request->post('commentId');
        $reply->content = $this->request->post('content');
        $reply->user_id = \Yii::$app->user->identity->getId();
        $reply->del_flg = Reply::STATUS_ACTIVE;
        if ($reply->save()) {
            return json_encode([
                'message' => '書き込みしました。管理人がチェックして問題がなければ反映されます。',
                'success' => true,
            ]);
        }
        return json_encode([
            'success' => false,
            'message' => 'エラーが発生しました。再度コメントを送ってください！'
        ]);
    }
}
