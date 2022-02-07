<?php
namespace backend\modules\worker\forms;


use backend\modules\coupon\forms\CouponForm;
use common\entities\shop\ShopInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\ArrayHelper;
use yii\base\Model;

/**
 * Class WorkerExportForm
 * @package backend\modules\worker\forms
 *
 * @property ShopInfo[] $shops
 * @property WorkerInfo[] $workers
 */
class WorkerExportForm extends Model
{
    public function getShops()
    {
        $model = new CouponForm();
        $shopIds = $model->getListShop();
        $query = ShopInfo::find()
            ->where([
                "status" => ShopInfo::STATUS_ACTIVE,
            ])->andWhere([
                'in', 'shop_id', array_keys($shopIds)
            ]);
        return $query->all();
    }

    public function getWorkers()
    {
        return WorkerInfo::find()
            ->where([
                "status" => WorkerInfo::STATUS_ACTIVE,
            ])
            ->all();
    }

    /**
     * TODO set style Excel
     * @param $objPHPExcel \PHPExcel
     * @param $cell
     * @param $value
     * @param $params
     */
    private function _setStyleValue($objPHPExcel, $cell, $value, $params = [])
    {
        $params = ArrayHelper::merge($params, []);
        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($cell, $value)
            ->getStyle($cell)
            ->applyFromArray($params);
    }

    protected function _getColName($index)
    {
        $colNames = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
        return ($index >= 26) ? $colNames[intval($index / 26) - 1] . self::_getColName($index - 26) : $colNames[$index];
    }

    public function downloadTemplate()
    {
        $objPHPExcel = new \PHPExcel();
        //TODO Set the active Excel worksheet to sheet 0
        $objPHPExcel->setActiveSheetIndex(0);

        //TODO set title for file excel
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':F' . $rowCount);
        $this->_setStyleValue($objPHPExcel, "A" . $rowCount, \Yii::t('backend.worker.title', "Worker management", []), [
            'font' => [
                'size' => 16,
                'bold' => true,
                'color' => []
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'left' => [
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                ],
                'right' => [
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                ],
                'top' => [
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                ],
                'bottom' => [
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                ]
            ]
        ]);

        //TODO set header
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName(0))->setWidth(5);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName(1))->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName(2))->setWidth(50);
        $rowCount = 2;
        $this->_setStyleValue($objPHPExcel, $this->_getColName(0) . $rowCount, \App::t("backend.worker.title", "ID"), [
            'font' => [
                'size' => 12,
                'bold' => true,
                'color' => []
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'left' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'right' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'top' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'bottom' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ]);
        $this->_setStyleValue($objPHPExcel, $this->_getColName(1) . $rowCount, \App::t("backend.worker.title", "Name"), [
            'font' => [
                'size' => 12,
                'bold' => true,
                'color' => []
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'left' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'right' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'top' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'bottom' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ]);
        $this->_setStyleValue($objPHPExcel, $this->_getColName(2) . $rowCount, \App::t("backend.worker.title", "Rank"), [
            'font' => [
                'size' => 12,
                'bold' => true,
                'color' => []
            ],
            'alignment' => [
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'left' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'right' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'top' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ],
                'bottom' => [
                    'style' => \PHPExcel_Style_Border::BORDER_THIN
                ]
            ]
        ]);
        {
            $colIndex = 3;
            foreach ($this->shops as $shop) {
                $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName($colIndex))->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName($colIndex + 1))->setWidth(10);
                $objPHPExcel->getActiveSheet()->mergeCells($this->_getColName($colIndex) . "$rowCount:" . $this->_getColName($colIndex + 1) . "$rowCount");
                $this->_setStyleValue($objPHPExcel, $this->_getColName($colIndex) . $rowCount, $shop->shop_name, [
                    'font' => [
                        'size' => 12,
                        'bold' => true,
                        'color' => []
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'left' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'right' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ]
                    ]
                ]);
                $this->_setStyleValue($objPHPExcel, $this->_getColName($colIndex + 1) . $rowCount, "", [
                    'font' => [
                        'size' => 12,
                        'bold' => true,
                        'color' => []
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'left' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'right' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ]
                    ]
                ]);
                $colIndex += 2;
            }
        }
        //TODO freeze row
        $objPHPExcel->getActiveSheet()->freezePane('A3');
        //TODO set data
        $rowCount = 3;
        foreach ($this->workers as $worker) {
            //todo set worker id & name
            $this->_setStyleValue($objPHPExcel, $this->_getColName(0) . $rowCount, $worker->worker_id, [
                'font' => [
                    'size' => 11,
                    'bold' => false,
                    'color' => []
                ],
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'left' => [
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    ],
                    'right' => [
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    ],
                    'top' => [
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    ],
                    'bottom' => [
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    ]
                ]
            ]);
            $this->_setStyleValue($objPHPExcel, $this->_getColName(1) . $rowCount, $worker->worker_name, [
                'font' => [
                    'size' => 11,
                    'bold' => false,
                    'color' => []
                ],
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'left' => [
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    ],
                    'right' => [
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    ],
                    'top' => [
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    ],
                    'bottom' => [
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    ]
                ]
            ]);
            //todo set worker rank
            $workerRanks = [-1, 1, 8, 10];
            $this->_setStyleValue($objPHPExcel, $this->_getColName(2) . $rowCount, array_search($worker->worker_rank, $workerRanks), [
                'font' => [
                    'size' => 11,
                    'bold' => false,
                    'color' => []
                ],
                'alignment' => [
                    'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                    'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'left' => [
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    ],
                    'right' => [
                        'style' => \PHPExcel_Style_Border::BORDER_THIN
                    ],
                    'top' => [
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    ],
                    'bottom' => [
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
                    ]
                ]
            ]);
            //todo set worker in shop
            $colIndex = 3;
            foreach ($this->shops as $shop) {
                /**
                 * @var $shopMapping WorkerMappingShop
                 */
                $shopMapping = WorkerMappingShop::find()
                    ->where([
                        "shop_id" => $shop->shop_id,
                        "worker_id" => $worker->worker_id,
                        "status" => WorkerMappingShop::STATUS_ACTIVE,
                    ])
                    ->one();
                $this->_setStyleValue($objPHPExcel, $this->_getColName($colIndex) . $rowCount, ($shopMapping != null) ? "YES" : "", [
                    'font' => [
                        'size' => 12,
                        'bold' => false,
                        'color' => []
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'left' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'right' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_NONE
                        ],
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_NONE
                        ]
                    ]
                ]);
                $this->_setStyleValue($objPHPExcel, $this->_getColName($colIndex + 1) . $rowCount, ($shopMapping != null) ? $shopMapping->ref_id : "", [
                    'font' => [
                        'size' => 12,
                        'bold' => false,
                        'color' => []
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'left' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'right' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
                        ],
                        'top' => [
                            'style' => \PHPExcel_Style_Border::BORDER_NONE
                        ],
                        'bottom' => [
                            'style' => \PHPExcel_Style_Border::BORDER_NONE
                        ]
                    ]
                ]);
                $colIndex += 2;
            }
            $rowCount += 1;
        }

        //TODO Redirect output to a client’s web browser (Excel2007)
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $filePath = \App::getAlias("@upload/cache/export/worker");
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fileName = "worker-" . time() . ".xlsx";
        $objWriter->save(\App::getAlias("$filePath/$fileName"));
        return "$filePath/$fileName";
    }
}