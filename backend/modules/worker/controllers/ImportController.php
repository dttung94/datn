<?php
namespace backend\modules\worker\controllers;


use backend\models\BackendController;
use backend\modules\worker\forms\WorkerExportForm;
use backend\modules\worker\forms\WorkerImportForm;
use common\helper\DatetimeHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\UploadedFile;

class ImportController extends BackendController
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
                            "index",
                            'download-template',
                            'read-data',
                            'to-import-data',
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

    public function actionIndex()
    {
        return $this->render("index", []);
    }

    public function actionDownloadTemplate()
    {
        $model = new WorkerExportForm();
        if ($model) {
            ini_set('memory_limit', '-1');
            $filePath = $model->downloadTemplate();
            return $this->response->sendContentAsFile(file_get_contents($filePath), "worker-list-" . DatetimeHelper::now("Y-m-d_H_i_s") . ".xlsx");
        }
        throw new NotFoundHttpException(\Yii::t('common.message', 'The requested page does not exist.'));
    }

    public function actionReadData()
    {
        $this->response->format = Response::FORMAT_JSON;
        $data = WorkerImportForm::toReadImportFile(UploadedFile::getInstanceByName("import-file"));
        if ($data) {
            $_SESSION['data'] = $data;
            return [
                "success" => true,
                "data" => $data,
            ];
        } else {
            return [
                "success" => false,
            ];
        }
    }

    public function actionToImportData()
    {
//        $data = $this->request->post("data", []);
        $data = $_SESSION['data'];
        WorkerImportForm::toImportData($data);
        \App::$app->session->setFlash("ALERT_MESSAGE", \App::t("backend.worker.message", "Import worker data done."));
        return $this->redirect([
            "/worker/manage"
        ]);
    }
}