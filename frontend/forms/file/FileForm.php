<?php
namespace frontend\forms\file;

use common\entities\resource\FileInfo;
use common\entities\worker\WorkerInfo;
use yii\web\NotFoundHttpException;

class FileForm extends FileInfo
{
    public static function getPreview($id, $type)
    {
        $worker = WorkerInfo::findOne(['worker_id' => $id]);
        $contentType = "image/jpg";
        $path = \Yii::getAlias("@webs/resource/images/noimage.jpg");
        if ($worker != null) {
            if ($worker->avatar_url != null) {
                /**
                 * @var $file FileInfo
                 */
                if (file_exists($filePath = \Yii::getAlias("@upload") . "/avatar/worker/" . $id . '/' . $worker->avatar_url)) {
                    $path = $filePath;
                }
            }
        }
        \Yii::$app->response->setDownloadHeaders(
            "preview",
            $contentType,
            true,
            null
        );
        $path = str_replace('\\', '/', $path);
        if (file_exists($path)) {
            \Yii::$app->response->sendFile($path);
        }
        return "";
    }

    public static function toDownload($id)
    {
        $file = FileInfo::findOne($id);
        if ($file != null) {
            /**
             * @var $file FileInfo
             */
            if (file_exists($filePath = \Yii::getAlias("@upload") . "/" . $file->path . '/' . $file->name)) {
                $filePath = str_replace('\\', '/', $filePath);
                \Yii::$app->response->setDownloadHeaders(
                    $file->original_name . "." . $file->extension,
                    null,
                    false,
                    $file->size
                );
                if (file_exists($filePath)) {
                    \Yii::$app->response->sendFile($filePath);
                    return true;
                }
            }
        }
        throw new NotFoundHttpException(\App::t("backend.service_file.message", "File is not found!"));
    }
}