<?php
namespace backend\modules\service\forms\file;


class FileDeleteForm extends FileSearchForm
{
    public function toDelete()
    {
        if ($this->delete()) {//TODO delete file info
            //TODO delete file in storage
            if (file_exists($filePath = \Yii::getAlias("@upload") . "/" . $this->path . '/' . $this->name)) {
                @unlink($filePath);
            }
            return true;
        }
        return false;
    }
}