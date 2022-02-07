<?php

namespace frontend\controllers;

use common\entities\forum\Comment;
use frontend\models\FrontendController;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class CommentController extends FrontendController
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
        $comment = new Comment();
        $comment->post_id = $this->request->post('postId');
        $comment->comment = $this->request->post('comment');
        $comment->user_id = \Yii::$app->user->identity->getId();
        $comment->del_flg = Comment::STATUS_ACTIVE;
        if ($comment->save()) {
            return json_encode([
                'success' => true,
                'message' => '書き込みしました。管理人がチェックして問題がなければ反映されます。',
            ]);
        }
        return json_encode([
            'success' => false,
            'message' => 'エラーが発生しました。再度コメントを送ってください！'
        ]);
    }
}
