<?php
namespace backend\modules\calendar\forms\schedule;

use common\entities\shop\ShopCalendar;
use common\helper\DatetimeHelper;
use yii\web\UploadedFile;

/**
 * Class ShopScheduleImportForm
 * @package backend\modules\calendar\forms\schedule
 */
class ShopScheduleImportForm extends ShopScheduleExportForm
{
    public function toImportSchedule(UploadedFile $file, $date)
    {
        $filePath = \App::getAlias("@upload/cache/import/calendar");
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fileName = time() . "_" . $file->name;
        $file->saveAs("$filePath/$fileName");
        //todo import data
        try {
            $inputFileType = \PHPExcel_IOFactory::identify("$filePath/$fileName");
            $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load("$filePath/$fileName");
            //todo get sheet data
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $schedulesData = $sheet->rangeToArray("D2:$highestColumn$highestRow");

            $schedules = [];

            //todo get worker id
            $workerIds = [];
            foreach ($sheet->rangeToArray("A3:A$highestRow") as $workerId) {
                $workerIds[] = $workerId[0];
            }
            //todo get data
            for ($rowIndex = 1; $rowIndex < count($schedulesData); $rowIndex++) {
                $schedules[$workerIds[$rowIndex - 1]] = [
                    "from" => $schedulesData[$rowIndex][0],
                    "to" => $schedulesData[$rowIndex][0 + 2],
                ];
            }

            //todo prepare data
            $scheduleWorkers = [];
            $errors = [];
            foreach ($schedules as $worker_id => $data) {
                $config = ShopCalendar::find()
                    ->where("shop_id = :shop_id", [
                        ":shop_id" => $this->shop_id
                    ])
                    ->andWhere("worker_id = :worker_id", [
                        ":worker_id" => $worker_id,
                    ])
                    ->andWhere("date = :date", [
                        ":date" => $date,
                    ])
                    ->andWhere("status = :status_active", [
                        ":status_active" => ShopCalendar::STATUS_ACTIVE,
                    ])
                    ->one();
                if (!$config) {
                    $config = new ShopCalendar();
                    $config->shop_id = $this->shop_id;
                    $config->worker_id = $worker_id;
                    $config->date = $date;
                    $config->type = ShopCalendar::TYPE_HOLIDAY;
                    $config->work_start_time = $this->openDoorAt;
                    $config->work_end_time = $this->closeDoorAt;
                    $config->status = ShopCalendar::STATUS_ACTIVE;
                }
                if ($data["from"] && $data["to"]) {
                    $config->type = ShopCalendar::TYPE_WORKING_DAY;
                    $config->work_start_time = $data["from"];
                    $config->work_end_time = $data["to"];
                } else {
                    $config->type = ShopCalendar::TYPE_HOLIDAY;
                    $config->work_start_time = $this->openDoorAt;
                    $config->work_end_time = $this->closeDoorAt;
                }
                if (!$config->validate()) {
                    $errors[$date . "-" . $worker_id] = $config->getErrors("date");
                }
                $workerShopCalendar = $config->toArray([], ['workerInfo']);
                $workerShopCalendar["work_start_hour"] = DatetimeHelper::getHourFromTimeFormat($config->work_start_time, ":");
                $workerShopCalendar["work_start_minute"] = DatetimeHelper::getMinuteFromTimeFormat($config->work_start_time, ":");
                $workerShopCalendar["work_end_hour"] = DatetimeHelper::getHourFromTimeFormat($config->work_end_time, ":");
                $workerShopCalendar["work_end_minute"] = DatetimeHelper::getMinuteFromTimeFormat($config->work_end_time, ":");
                $workerShopCalendar["is_work_day"] = $config->type == ShopCalendar::TYPE_WORKING_DAY ? 1 : 0;

                $scheduleWorkers[$config->worker_id] = $workerShopCalendar;
            }
            return [
                "schedule" => $scheduleWorkers,
                "error" => $errors,
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}