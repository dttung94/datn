<?php
namespace backend\modules\service\forms\file;

use common\entities\resource\FileInfo;
use common\entities\system\SystemSound;
use common\helper\HtmlHelper;
use yii\web\UploadedFile;

/**
 * Class FileForm
 * @package app\backend\modules\service\forms\file
 */
class FileForm extends FileSearchForm
{
    public static function deleteFile($file_id)
    {
        $model = FileDeleteForm::findOne($file_id);
        if ($model != null) {
            /**
             * @var $model FileDeleteForm
             */
            return $model->toDelete();
        }
        return false;
    }

    public static function toUpload(UploadedFile $file, $path, $name = null, $provider = FileInfo::PROVIDER_SYSTEM)
    {
        $full_path = \Yii::getAlias('@upload') . "/" . $path;
        if (!file_exists($full_path)) {
            mkdir($full_path, 0777, true);
        }
        $file_name = (!empty($name)) ? "$name.$file->extension" : time() . "_$file->name";
        if (file_exists($full_path . "/" . $file_name)) {
            unlink($full_path . "/" . $file_name);
        }
        if ($file->saveAs($full_path . "/" . $file_name)) {
            //TODO save file info to database
            $fileInfo = new FileInfo();
            $fileInfo->provider = $provider;
            $fileInfo->original_name = $file->baseName;
            $fileInfo->name = $file_name;
            $fileInfo->path = $path;
            $fileInfo->extension = $file->extension;
            $fileInfo->size = $file->size;
            if ($fileInfo->save()) {
                return $fileInfo;
            } else {
                \App::error(HtmlHelper::errorSummary($fileInfo));
            }
        }
        return false;
    }

    public static function toUploadAudio(UploadedFile $file, $name = null)
    {
        $full_path = \Yii::getAlias('@backend/web/resource/audio');
        if (!file_exists($full_path)) {
            mkdir($full_path, 0777, true);
        }
        $file_name = (!empty($name)) ? "$name.$file->extension" : time() . "_$file->name";
        if (file_exists($full_path . "/" . $file_name)) {
            unlink($full_path . "/" . $file_name);
        }
        if ($file->saveAs($full_path . "/" . $file_name)) {
            //TODO save file info to database
            $fileInfo = new SystemSound();
            $fileInfo->name_sound = $file_name;
            if ($fileInfo->save(false)) {
                return $fileInfo;
            } else {
                \App::error(HtmlHelper::errorSummary($fileInfo));
            }
        }
        return false;
    }

    public static function deleteFileAudio($file_name)
    {
        $full_path = \Yii::getAlias('@backend/web/resource/audio');
        if (file_exists($full_path . "/" . $file_name)) {
            @unlink($full_path . "/" . $file_name);
        }
    }
}