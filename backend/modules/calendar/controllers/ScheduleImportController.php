<?php
namespace backend\modules\calendar\controllers;

use backend\models\BackendController;
use backend\modules\calendar\forms\schedule\ShopScheduleExportForm;
use backend\modules\calendar\forms\schedule\ShopScheduleImportForm;
use common\helper\DatetimeHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use App;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * Class ScheduleImportController
 * @package backend\modules\calendar\controllers
 */
class ScheduleImportController extends BackendController
{
    public $layout = "layout_admin";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [
                            'download-template',
                            'upload-data'
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'download-template' => ['get', 'post'],
                ],
            ],
        ];
    }

    public function actionDownloadTemplate($shop_id, $date = null)
    {
        $model = ShopScheduleExportForm::findOne($shop_id);
        if ($model) {
            ini_set('memory_limit', '-1');
            if ($date == null) {
                $date = App::$app->formatter->asDate(date(time()) + 60 * 60 * 24 * 0, "yyyy-MM-dd");
            }
            $filePath = $model->downloadTemplate($date);
            return $this->response->sendContentAsFile(file_get_contents($filePath), $model->shop_name . "-schedule-" . DatetimeHelper::now("Y-m-d_H_i_s") . ".xlsx");
        }
        throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
    }

    public function actionUploadData($shop_id, $date)
    {
        $model = ShopScheduleImportForm::findOne($shop_id);
        if ($model) {
            $this->response->format = Response::FORMAT_JSON;
            return [
                "success" => true,
                "data" => $model->toImportSchedule(UploadedFile::getInstanceByName("import-file"), $date),
            ];
        }
        throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
    }
}