<?php
namespace backend\modules\worker\forms;


use backend\modules\coupon\forms\CouponForm;
use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerConfig;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use yii\base\Model;
use yii\web\UploadedFile;

class WorkerImportForm extends Model
{
    public static function toReadImportFile(UploadedFile $file = null)
    {
        if ($file != null) {
            $filePath = \App::getAlias("@upload/cache/import/worker");
            if (!file_exists($filePath)) {
                mkdir($filePath, 0777, true);
            }
            $fileName = time() . "_" . $file->name;
            $file->saveAs("$filePath/$fileName");
            //todo import data
            try {
                $model = new CouponForm();
                $shopIds = $model->getListShop();
                $inputFileType = \PHPExcel_IOFactory::identify("$filePath/$fileName");
                $objReader = \PHPExcel_IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load("$filePath/$fileName");
                //todo get sheet data
                $sheet = $objPHPExcel->getSheet(0);
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $shopNames = ArrayHelper::getValue($sheet->rangeToArray("D2:" . $highestColumn . "2"), 0, []);
                foreach ($shopNames as $shopName) {
                    if (!empty($shopName) && !in_array($shopName, $shopIds)) {
                        return 'shop_error';
                    }
                }
                $shops = [];
                for ($index = 0; $index < count($shopNames); $index += 2) {
                    $shopName = $shopNames[$index];
                    if (!empty($shopName)) {
                        $shop = ShopInfo::find()
                            ->where([
                                "shop_name" => $shopName,
                            ])
                            ->one();
                        if ($shop) {
                            $shops[] = $shop;
                        }
                    }
                }
                $workerData = $sheet->rangeToArray("A3:$highestColumn$highestRow");
                $fileData = [];
                $workerRanks = [-1, 1, 8, 10];
                foreach ($workerData as $data) {
                    $workerId = ArrayHelper::getValue($data, 0, null);
                    $workerName = ArrayHelper::getValue($data, 1, "");
                    $workerRank = ArrayHelper::getValue($data, 2, "");
                    $findWorkerRank = $workerRanks[$workerRank];
                    if (!empty($workerName)) {
                        $worker = WorkerInfo::findOne([
                            "worker_id" => $workerId
                        ]);
                        if ($worker == null) {
                            $worker = new WorkerInfo();
                        }
                        $worker->worker_name = $workerName;
//                        $rankId = array_search($findWorkerRank, WorkerInfo::getListRank());
                        $worker->worker_rank = $findWorkerRank ? $findWorkerRank : WorkerInfo::RANK_STANDARD;
                        $itemData = $worker->toArray(["worker_id", "worker_name", "worker_rank", "description"], []);
                        foreach ($shops as $index => $shop) {
                            /**
                             * @var $shop ShopInfo
                             */
                            $value = ArrayHelper::getValue($data, 3 + ($index * 2), null);
                            if ($value == "YES") {
                                $itemData["workShops"][] = ArrayHelper::merge(
                                    $shop->toArray(["shop_id", "shop_name"]),
                                    [
                                        "ref_id" => ArrayHelper::getValue($data, 3 + ($index * 2) + 1, ""),
                                    ]
                                );
                            }
                        }
                        $fileData[] = $itemData;
                    }
                }
                return $fileData;
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return false;
    }

    public static function toImportData($data)
    {
        set_time_limit(3000);
        $result = [];
        foreach ($data as $workerData) {
            $trans = \App::$app->db->beginTransaction();
            $workerData["hasError"] = false;
            //1. todo save worker info
            $worker_id = ArrayHelper::getValue($workerData, "worker_id", null);
            $model = WorkerInfo::findOne([
                "worker_id" => $worker_id
            ]);
            if (!$model) {
                $model = new WorkerInfo();
                $model->status = WorkerInfo::STATUS_ACTIVE;
            }

            $model->worker_name = ArrayHelper::getValue($workerData, "worker_name", "");
            $model->description = ArrayHelper::getValue($workerData, "description", "");
            $model->worker_rank = ArrayHelper::getValue($workerData, "worker_rank", WorkerInfo::RANK_STANDARD);
            if ($model->worker_rank == WorkerInfo::RANK_NONE) {
                $isShowRank = 1;
                $model->worker_rank = WorkerInfo::RANK_STANDARD;
            } else {
                $isShowRank = 0;
            }
            if ($model->save()) {
                WorkerConfig::setValue(WorkerConfig::KEY_IS_SHOW_RANK_NAME, $model->worker_id, $isShowRank);
                //2. todo remove all current shop of worker
                WorkerMappingShop::deleteAll([
                    "worker_id" => $model->worker_id,
                ]);
                //3. todo save worker shop
                $shops = ArrayHelper::getValue($workerData, "workShops", []);
                foreach ($shops as $shop) {
                    $workerShop = new WorkerMappingShop();
                    $workerShop->worker_id = $model->worker_id;
                    $workerShop->shop_id = ArrayHelper::getValue($shop, "shop_id", null);
                    $workerShop->ref_id = ArrayHelper::getValue($shop, "ref_id", null);
                    $workerShop->status = WorkerMappingShop::STATUS_ACTIVE;
                    if (!$workerShop->save()) {
                        $workerData["errors"] = $workerShop->getErrors();
                        $workerData["hasError"] = true;
                        break;
                    }
                }
            } else {
                $workerData["errors"] = $model->getErrors();
                $workerData["hasError"] = true;
            }
            if (!$workerData["hasError"]) {
                $trans->commit();
            } else {
                $trans->rollBack();
            }
            $result[] = $workerData;
        }
        return $result;
    }
}