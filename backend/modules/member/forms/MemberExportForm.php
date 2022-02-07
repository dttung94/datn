<?php
namespace backend\modules\member\forms;


use backend\modules\coupon\forms\CouponForm;
use backend\modules\member\controllers\ManageController;
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
class MemberExportForm extends Model
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
        $objPHPExcel->getActiveSheet()->mergeCells('A' . $rowCount . ':E' . $rowCount);
        $this->_setStyleValue($objPHPExcel, "A" . $rowCount, \Yii::t('backend.worker.title', "会員管理", []), [
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
        $rowCount = 2;
        $headers = ['名前','電話番号', 'メール', '好みタイプ', '利用店舗', 'コメント', 'クーポン', 'クーポン総発行料',
            '最終クーポン発行日', 'タグ', '最終予約', '総予約数','利用合計時間(時間)', '利用合計金額(千円)', 'ステータス', '登録日', '紹介者'];
        $statuses = [
            MemberForm::STATUS_CONFIRMING => '番号認証途中',
            MemberForm::STATUS_VERIFYING => '承認待ち',
            MemberForm::STATUS_SHOP_BLACK_LIST => '店舗BL/承認',
            MemberForm::STATUS_WORKER_BLACK_LIST => 'NGリスト',
            MemberForm::STATUS_ACTIVE => 'アクティブ'
        ];
        foreach ($headers as $key => $header) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($this->_getColName($key))->setWidth(20);
            $this->_setStyleValue($objPHPExcel, $this->_getColName($key) . $rowCount, \App::t("backend.worker.title", $header), [
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
        $memberPage = 1;
        $memberForm = new MemberForm();
        $members = $memberForm->searchExport($memberPage);
        $members = ManageController::fillUserData($members);
        while (!empty($members)) {
            foreach ($members as $member) {
                $this->_setStyleValue($objPHPExcel, $this->_getColName(0).$rowCount, $member['full_name'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(1).$rowCount, $member['phone_number'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(2).$rowCount, $member['email'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(3).$rowCount, $member['hobbies'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(4).$rowCount, $member['used_shops'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(5).$rowCount, $member['note'], $formats);
                $arrayPrint = '';
                if (!empty($member['coupons'])) {
                    foreach ($member['coupons'] as $index => $memberCoupon) {
                        if ($index + 1 != count($member['coupons'])) {
                            $arrayPrint .= $memberCoupon->coupon_code . ', ';
                        } else {
                            $arrayPrint .= $memberCoupon->coupon_code;
                        }
                    }
                }
                $this->_setStyleValue($objPHPExcel, $this->_getColName(6).$rowCount, $arrayPrint, $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(7).$rowCount, $member['total_coupon_yield'] == 0 ? '0' : $member['total_coupon_yield'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(8).$rowCount, $member['last_coupon_released'] == 0 ? '' : $member['last_coupon_released'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(9).$rowCount, $member['tag'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(10).$rowCount, $member['last_booking'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(11).$rowCount, $member['total_booking'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(12).$rowCount, round($member['total_time']/60, 2), $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(13).$rowCount, $member['total_money']/1000, $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(14).$rowCount, $statuses[$member['status']], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(15).$rowCount, $member['created_at'], $formats);
                $this->_setStyleValue($objPHPExcel, $this->_getColName(16).$rowCount, $member['referrer_full_name'], $formats);
                $rowCount++;
            }
            $memberPage++;
            $members = $memberForm->searchExport($memberPage);
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