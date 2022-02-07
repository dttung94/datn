<?php
namespace app\backend\modules\system\widgets;

use app\backend\models\BackendWidget;
use app\backend\modules\system\forms\log\SystemLogSearchForm;
use app\common\entities\system\SystemLog;
use app\common\util\UtilDate;
use yii\helpers\ArrayHelper;

/**
 * Date: 10/26/15
 * Time: 9:08 AM
 */
class AdminLogWidget extends BackendWidget
{
    public $tabs = null;

    public function run()
    {
        if ($this->tabs == null || empty($this->tabs)) {
            $this->tabs = [
                [
                    "title" => "API Error Log",
                    "search" => [
                        "level" => SystemLogSearchForm::LEVEL_ERROR,
                        "keyword" => UtilDate::now("Y-m-d"),
                    ]
                ],
                [
                    "title" => "API Log",
                    "search" => [
                        "level" => SystemLogSearchForm::LEVEL_WARNING,
                        "category" => "APIApplication",
                        "keyword" => UtilDate::now("Y-m-d"),
                    ]
                ],
                [
                    "title" => "API DBQuery Log",
                    "search" => [
                        "category" => 'yii\db\Command::query',
                        "keyword" => UtilDate::now("Y-m-d"),
                    ]
                ],
                [
                    "title" => "API DBExecute Log",
                    "search" => [
                        "category" => 'yii\db\Command::execute',
                        "keyword" => UtilDate::now("Y-m-d"),
                    ]
                ],
//        [
//            "title" => "API UserLogin Log",
//            "search" => [
//                "category" => 'yii\web\User::logi',
//                  "keyword" => UtilDate::now("Y-m-d"),
//            ]
//        ]
            ];
        }
        foreach ($this->tabs as $index => $tab) {
            $form = new SystemLogSearchForm();
            $form->load(ArrayHelper::getValue($tab, "search"), "");
            $this->tabs[$index]['model'] = $form;
        }
        echo $this->render("log/index", [
            "tabs" => $this->tabs,
        ]);
    }
}