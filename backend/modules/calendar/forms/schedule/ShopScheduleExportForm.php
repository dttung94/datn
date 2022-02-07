<?php
namespace backend\modules\calendar\forms\schedule;

use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopInfo;
use common\helper\ArrayHelper;

/**
 * Class ShopScheduleExportForm
 * @package backend\modules\calendar\forms\schedule
 */
class ShopScheduleExportForm extends ShopInfo
{
    //TODO set style Excel
    /**
     * @param $objPHPExcel \PHPExcel
     * @param $cell
     * @param $value
     * @param $params
     */
    private function _setStyleValue($objPHPExcel, $cell, $value, $params = [])
    {
        $params = ArrayHelper::merge($params, [
        ]);
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

    public function downloadTemplate($date)
    {
        $objPHPExcel = new \PHPExcel();
        //TODO Set the active Excel worksheet to sheet 0
        $objPHPExcel->setActiveSheetIndex(0);

        //TODO set title for file excel
        $rowCount = 1;
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':F' . $rowCount);
        $this->_setStyleValue($objPHPExcel, "A" . $rowCount, \Yii::t('backend.schedule.title', "Shop schedule config - [{shop-name}] - {date} ({day})", [
            "shop-name" => $this->shop_name,
            "date" => $date,
            "day" => \App::$app->formatter->asDayOfWeek(strtotime($date), \App::$app->language, false),
        ]), [
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
        $this->_setStyleValue($objPHPExcel, $this->_getColName(0) . $rowCount, \App::t("backend.schedule.title", "ID"), [
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
        $this->_setStyleValue($objPHPExcel, $this->_getColName(1) . $rowCount, \App::t("backend.schedule.title", "女の子"), [
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
        $this->_setStyleValue($objPHPExcel, $this->_getColName(2) . $rowCount, \App::t("backend.schedule.title", "備考"), [
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
            $index = 0;
            //todo set width of column
            $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName(($index * 3) + 3))->setWidth(10);
            $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName(($index * 3) + 4))->setWidth(3);
            $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName(($index * 3) + 5))->setWidth(10);
            //todo set date header
//            $objPHPExcel->getActiveSheet()->mergeCells($this->_getColName(($index * 3) + 3) . $rowCount . ':' . $this->_getColName(($index * 3) + 4) . $rowCount);
            $this->_setStyleValue($objPHPExcel, $this->_getColName(($index * 3) + 3) . $rowCount, "From", [
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
            $this->_setStyleValue($objPHPExcel, $this->_getColName(($index * 3) + 4) . $rowCount, "-", [
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
            $this->_setStyleValue($objPHPExcel, $this->_getColName(($index * 3) + 5) . $rowCount, "To", [
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
            //todo set worker rank
            $this->_setStyleValue($objPHPExcel, $this->_getColName(2) . $rowCount, $worker::getListWorkerRank()[$worker->worker_rank], [
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
                        'style' => \PHPExcel_Style_Border::BORDER_NONE
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
            {
                $index = 0;
                //todo set worker calendar
                $shopCalendar = ShopCalendar::findOne([
                    "status" => ShopCalendar::STATUS_ACTIVE,
                    "shop_id" => $this->shop_id,
                    "worker_id" => $worker->worker_id,
                    "date" => $date,
                ]);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(($index * 3) + 3) . $rowCount, ($shopCalendar) ? $shopCalendar->work_start_time : "", [
                    'font' => [
                        'size' => 11,
                        'bold' => false,
                        'color' => []
                    ],
                    'alignment' => [
                        'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                        'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'left' => [
                            'style' => \PHPExcel_Style_Border::BORDER_THIN
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
                $this->_setStyleValue($objPHPExcel, $this->_getColName(($index * 3) + 4) . $rowCount, "-", [
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
                $this->_setStyleValue($objPHPExcel, $this->_getColName(($index * 3) + 5) . $rowCount, ($shopCalendar) ? $shopCalendar->work_end_time : "", [
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
                            'style' => \PHPExcel_Style_Border::BORDER_NONE
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
            }
            $rowCount += 1;
        }

        //TODO Redirect output to a client’s web browser (Excel2007)
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $filePath = \App::getAlias("@upload/cache/export/calendar");
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fileName = "$this->shop_name-" . time() . ".xlsx";
        $objWriter->save(\App::getAlias("$filePath/$fileName"));
        return "$filePath/$fileName";
    }
}