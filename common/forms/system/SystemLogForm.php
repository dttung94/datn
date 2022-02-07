<?php
namespace common\forms\system;

use common\entities\system\SystemLog;
use common\helper\DatetimeHelper;
use yii\helpers\VarDumper;
use yii\log\DbTarget;

/**
 * Date: 10/19/15
 * Time: 10:57 AM
 */
class SystemLogForm extends DbTarget
{
    /**
     * TODO add admin log
     * @param $category
     * @param $message
     */
    public static function addAdminLog($category, $message)
    {
        \Yii::getLogger()->log($message, SystemLog::LEVEL_ADMIN, $category);
    }

    /**
     * TODO save log to db
     * @throws \yii\db\Exception
     */
    public function export()
    {
        $tableName = $this->db->quoteTableName($this->logTable);
        $sql = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";
        $command = $this->db->createCommand($sql);
        foreach ($this->messages as $message) {
            list($text, $level, $category, $timestamp) = $message;
            if (!is_string($text)) {
                //TODO exceptions may not be serializable if in the call stack somewhere is a Closure
                if ($text instanceof \Exception) {
                    $text = (string)$text;
                } else {
                    $text = VarDumper::export($text);
                }
            }
            $command->bindValues([
                ':level' => $level,
                ':category' => $category,
                ':log_time' => DatetimeHelper::now(),
                ':prefix' => $this->getMessagePrefix($message),
                ':message' => $text,
            ])->execute();
        }
    }
}