<?php
namespace backend\modules\service\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\service\ServiceModule;
use common\entities\user\UserInfo;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DatabaseController extends BackendController
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
                            'index',
                            'prepare-data',
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'clear-db' => ["POST", "GET"]
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $command = \Yii::$app->getDb()->createCommand("SHOW TABLE STATUS");
        $data = $command->queryAll();
        $columns = [
            "Name",
//            "Engine",
//            "Version",
//            "Row_format",
            "Rows",
//            "Avg_row_length",
            "Data_length",
//            "Max_data_length",
            "Index_length",
//            "Data_free",
//            "Auto_increment",
            "Create_time",
//            "Update_time",
//            "Check_time",
//            "Collation",
//            "Checksum",
//            "Create_options",
//            "Comment",
        ];
        return $this->render('index', [
            "model" => $data,
            "columns" => $columns
        ]);
    }
}