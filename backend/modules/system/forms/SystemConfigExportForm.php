<?php

namespace backend\modules\system\forms;

use common\entities\shop\ShopConfig;
use common\entities\system\SystemConfig;
use common\entities\worker\WorkerInfo;
use common\helper\ArrayHelper;
use yii\base\Model;

class SystemConfigExportForm extends Model
{
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
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':E' . $rowCount);
        $this->_setStyleValue($objPHPExcel, "A" . $rowCount, \Yii::t('backend.system.id', "Color", []), [
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

        $rowCount = 2;
        $headers = ['Title','Color code'];
        $statuses = [

            SystemConfig::ONLINE_PENDING_CHANGE => '????????????????????? - ?????????????????????????????????',
            SystemConfig::ONLINE_PENDING => '????????????????????? - ????????????',
            SystemConfig::ONLINE_CANCELED => '????????????????????? - ????????????????????????',
            SystemConfig::ONLINE_UPDATING => '????????????????????? - ??????????????????',
            SystemConfig::ONLINE_ACCEPTED => '????????????????????? - ?????????',
            SystemConfig::BACKGROUND => 'BackGround',
            SystemConfig::SLOT_NONE => 'T??n c???a h??ng - tr???ng',
        ];

        foreach ($headers as $key => $header) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName($key))->setWidth(20);
            $this->_setStyleValue($objPHPExcel, $this->_getColName($key) . $rowCount, \App::t("backend.system.id", $header), [
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
        $formats = [
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
        ];

        $colorSystemConfigDefault = SystemConfig::defaultConfigValue()[SystemConfig::CATEGORY_COLOR];

        $colorSystemConfig = SystemConfig::findAll([
            'category' => SystemConfig::CATEGORY_COLOR,
        ]);

        foreach ($colorSystemConfigDefault as $key => $value) {
            foreach ($colorSystemConfig as $item) {
                if ($key == $item->id) {
                    $colorSystemConfigDefault[$key] = $item->value;
                }
            }
        }

        $shopColorConfigDefault = [
            '?????????' => 'rgb(255, 205, 210)',
            '???????????????' => 'rgb(209, 196, 233)',
            '????????????' => 'rgb(179, 229, 252)',
            '?????????' => 'rgb(200, 230, 201)',
            '??????' => 'rgb(255, 226, 128)',
            '????????????' => 'rgb(255, 204, 188)',
            '?????????' => 'rgb(207, 216, 220)',
            '?????????' => 'rgb(248, 187, 208)',
            '?????????' => 'rgb(197, 202, 233)'
        ];
        $shopColorConfig = ShopConfig::findAll([
            'key' => ShopConfig::KEY_SHOP_COLOR,
        ]);

        foreach ($shopColorConfigDefault as $key => $value) {
            foreach ($shopColorConfig as $item) {
                if ($key == $item->shopInfo->shop_name) {
                    $shopColorConfigDefault[$key] = $item->value;
                }
            }
        }

        foreach ($colorSystemConfigDefault as $key => $value) {
            $this->_setStyleValue($objPHPExcel, $this->_getColName(0) . $rowCount, $statuses[$key], $formats);
            $this->_setStyleValue($objPHPExcel, $this->_getColName(1) . $rowCount, $value, $formats);
            $rowCount ++;
        }

        foreach ($shopColorConfigDefault as $key => $item) {
            $this->_setStyleValue($objPHPExcel, $this->_getColName(0) . $rowCount, $key, $formats);
            $this->_setStyleValue($objPHPExcel, $this->_getColName(1) . $rowCount, $item, $formats);
            $rowCount ++;
        }
        //TODO Redirect output to a client???s web browser (Excel2007)
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $filePath = \App::getAlias("@upload/cache/export/system");
        if (!file_exists($filePath)) {
            mkdir($filePath, 0777, true);
        }
        $fileName = "system-config-color-" . time() . ".xlsx";
        $objWriter->save(\App::getAlias("$filePath/$fileName"));
        return "$filePath/$fileName";
    }
}
