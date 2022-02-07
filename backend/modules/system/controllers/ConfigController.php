<?php
namespace backend\modules\system\controllers;


use backend\filters\BackendAccessRule;
use backend\models\BackendController;
use backend\modules\coupon\forms\CouponForm;
use backend\modules\service\forms\file\FileForm;
use backend\modules\system\SystemModule;
use common\entities\shop\ShopCalendar;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use common\entities\system\EventSound;
use common\entities\system\SystemConfig;
use common\entities\system\SystemSound;
use common\entities\user\UserInfo;
use common\entities\worker\WorkerInfo;
use common\entities\worker\WorkerMappingShop;
use common\helper\AmazonHelper;
use common\helper\DatetimeHelper;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Response;
use yii\web\UploadedFile;

class ConfigController extends BackendController
{
    public $layout = "layout_admin";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => BackendAccessRule::className(),
                    "module" => SystemModule::MODULE_ID
                ],
                'rules' => [
                    [
                        'actions' => [
                            'index',
                            'save',
                            'update-event',
                            'get-selected-sound',
                            'delete-sound',
                            'change-password',
                            'change-status',
                            'change-color',
                            'setting-logo-site',
                            'setting-character-site',
                            'setting-intro-site',
                            'delete-image'
                        ],
                        'allow' => true,
                        'roles' => [UserInfo::ROLE_ADMIN],
                    ],
                    [
                        'actions' => [
                            'map-schedule',
                            'get-colors',
                        ],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['post', 'get'],
                    'save' => ['post'],
                    'update-event' => ['post'],
                    'change-password' => ['post'],
                    'get-selected-sound' => ['post'],
                    'delete-sound' => ['post'],
                    'map-schedule' => ['get', 'post'],
                    'change-is-auto-accept' => ['post'],
                    'change-is-booking-sort-time' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        //todo run default
        $defaultVal = SystemConfig::defaultConfigValue();
        foreach ($defaultVal as $category => $ids) {
            foreach ($ids as $id => $val) {
                SystemConfig::getValue($category, $id);
            }
        }
        return $this->render('index', [
            'defaultVal' => $defaultVal,
        ]);
    }

    public function actionSave()
    {
        $datas = $this->request->post('datas');
        $dataSaves = [];
        foreach ($datas as $data) {
            $dataSaves[$data['category']][$data['id']] = $data['value'];
        }
        $arrs = [];
        foreach ($dataSaves as $category => $values) {
            foreach ($values as $id => $value) {
                $arrs[] = [
                    'category' => $category,
                    'id' => $id,
                    'value' => $value,
                ];
            }
        }
        foreach ($arrs as $arr) {
            $this->toSave($arr);
        }
    }

    protected function toSave($datas)
    {
        $category = $datas['category'];
        $id = $datas['id'];
        $value = $datas['value'];
        $config = SystemConfig::getConfig($category, $id);
        if ($config != null) {
            $config->value = $value;
            $config->save();
        }
    }

    public function actionUpdateEvent()
    {
        $event = $this->request->post('event');
        $sound = $this->request->post('sound');
        $eventSound = EventSound::find()
            ->where("event = :event", [
               ':event' => $event
            ])->one();
        if (!$eventSound) {
            $eventSound = new EventSound();
        }
        $eventSound->event = $event;
        $eventSound->id_sound = $sound;
        $eventSound->save();
        return true;
    }

    public function actionGetSelectedSound()
    {
        $event = $this->request->post('event');
        $eventSound = EventSound::find()
            ->where("event = :event", [
                ':event' => $event
            ])->one();
        if (!$eventSound) {
            $idSound = 0;
        } else {
            $idSound = (int)$eventSound->toArray()['id_sound'];
        }
        return $idSound;
    }

    public function actionDeleteSound()
    {
        $id = (int)$this->request->post('id');
        $name = $this->request->post('name');
        $delete = SystemSound::findOne($id);
        if ($delete->delete()) {
            EventSound::updateAll(['id_sound' => SystemSound::DEFAULT_SOUND], 'id_sound = '.$id);
            FileForm::deleteFileAudio($name);
        }
        return $name;
    }

    public function actionMapSchedule()
    {
        $dataPost = $this->request->post('date');
        $date = !empty($dataPost) ? $dataPost : date('Y-m-d');
        $query = ShopInfo::find()->where(["status" => ShopInfo::STATUS_ACTIVE])->all();
        $dbShops = array_flip(ArrayHelper::map($query, "shop_id", "shop_name"));
        $jsonShops = [
            'uguisudani' => '日暮里店',
            'kamata' => '蒲田店',
            'meguro' => '目黒店',
            'machida' => '町田店',
            'yokohama' => '横浜店',
            'kashiwa' => '柏店',
            'tachikawa' => '立川店',
            'kinshicho' => '錦糸町店',
            'shinbashi' => 'しんばし店',
            'shinjuku' =>'新宿店'
        ];
        require_once (\Yii::getAlias('@common'). '/config/simple_html_dom.php');
        $url = "http://zzzsmz0.kir.jp/UPT_EXEC/json/".$date."_yoyaku.json";
        $dataHtml = file_get_html($url);
        $dataJsons = Json::decode($dataHtml);
        if (!empty($dataJsons)) {
            foreach ($dataJsons as $value) {
                $datas = $value[0];
                $this->getDataMap($datas, $jsonShops, $dbShops, $date);
            }
        }
    }

    protected function getDataMap($datas, $jsonShops, $dbShops, $date)
    {
        $count = count($datas)/6;
        for ($i=1; $i<=$count; $i++) {
            $name = 'AREA'.$i.'-'.$date;
            $timeIn = 'IN'.$i.'-'.$date;
            $timeOut = 'OUT'.$i.'-'.$date;
            $shopName = $datas[$name];
            if (isset($jsonShops[$shopName]) && isset($dbShops[$jsonShops[$shopName]]) && $shopName != null) {
                $idShop = $dbShops[$jsonShops[$shopName]];
                $this->createSchedule($idShop, $datas[$timeIn], $datas[$timeOut], (int)$datas['girl_id'], $date);
            }
        }
    }

    protected function createSchedule($shopId, $timeIn, $timeOut, $refId, $date)
    {
        $dataMaps = WorkerMappingShop::findOne([
            'shop_id' => $shopId,
            'ref_id' => $refId
        ]);
        if ($dataMaps) {
            $workerId = $dataMaps->worker_id;
            $workeds = ShopCalendar::find()
                ->where([
                    'type' => ShopCalendar::TYPE_WORKING_DAY,
                    'worker_id' => $workerId,
                    'shop_id' => $shopId,
                    'date' => $date
                ])->exists();
            if (!$workeds) {
                $dataSaves = [
                    'shop_id' => $shopId,
                    'worker_id' => $workerId,
                    'is_work_day' => 1,
                    'work_start_hour' => (int)explode(':', $timeIn)[0],
                    'work_start_minute' => (int)explode(':', $timeIn)[1],
                    'work_end_hour' => (int)explode(':', $timeOut)[0],
                    'work_end_minute' => (int)explode(':', $timeOut)[1],
                ];
                $this->toSaveMapSchedules($dataSaves, $date);
            }
        }
    }

    protected function toSaveMapSchedules($datas, $date)
    {
        $flag = true;
        $trans = \App::$app->db->beginTransaction();
        $config = new ShopCalendar();
        $config->shop_id = $datas['shop_id'];
        $config->worker_id = $datas['worker_id'];
        $config->date = $date;
        $config->type = ShopCalendar::TYPE_HOLIDAY;
        $config->status = ShopCalendar::STATUS_ACTIVE;

        $work_start_hour = $datas['work_start_hour'];
        $work_start_minute = $datas['work_start_minute'];
        $work_end_hour = $datas['work_end_hour'];
        if ($work_end_hour != 24) {
            $work_end_minute = $datas['work_end_minute'];
        } else {
            $work_end_minute = 0;
        }
        $config->type = ShopCalendar::TYPE_WORKING_DAY;
        $config->work_start_time = "$work_start_hour:$work_start_minute";
        $config->work_end_time = "$work_end_hour:$work_end_minute";
        if (!$config->isNewRecord) {//todo validate if schedule changed
            $slots = ShopCalendarSlot::find()
                ->where([
                    "shop_id" => $config->shop_id,
                    "worker_id" => $config->worker_id,
                    "date" => $config->date,
                ])
                ->andWhere(["IN", "status", [
                    ShopCalendarSlot::STATUS_ACTIVE,
                    ShopCalendarSlot::STATUS_BOOKED,
                ]])
                ->all();
            $scheduleStartTime = DatetimeHelper::timeFormat2Seconds($config->work_start_time);
            $scheduleEndTime = DatetimeHelper::timeFormat2Seconds($config->work_end_time);
            foreach ($slots as $slot) {
                /**
                 * @var $slot ShopCalendarSlot
                 */
                $slotStartTime = DatetimeHelper::timeFormat2Seconds($slot->start_time);
                $slotEndTime = DatetimeHelper::timeFormat2Seconds($slot->end_time);
                if (
                    $slotStartTime < $scheduleStartTime ||
                    $slotStartTime > $scheduleEndTime ||
                    $slotEndTime < $scheduleStartTime ||
                    $slotEndTime > $scheduleEndTime
                ) {
                    $flag = false;
                    break;
                }
            }
        }

        if ($flag) {
            if (!$config->save()) {
                $flag = false;
            }
        }
        if ($flag) {
            $trans->commit();
            return true;
        }
        $trans->rollBack();
        return false;
    }

    public function actionChangeStatus()
    {
        $this->toSave($this->request->post());
        return true;
    }

    public function actionChangeColor()
    {
        $color = $this->request->post('color');
        $key = $this->request->post('key');
        $dataType = $this->request->post('dataType');
        $shopInfo = new ShopInfo();
        $shops = $shopInfo->getListShop();
        $shopIds = [];
        foreach ($shops as $id => $name) {
            $shopIds[] = $id;
        }
        if ($dataType == 'color-slot') {
            $config = SystemConfig::getConfig(SystemConfig::CATEGORY_COLOR, $key);
            if ($config) {
                $config->value = $color;
            } else {
                $config = new SystemConfig();
                $config->id = $key;
                $config->category = SystemConfig::CATEGORY_COLOR;
                $config->value = $color;
            }
            $config->save(false);
        } else {
            ShopConfig::setValue(ShopConfig::KEY_SHOP_COLOR, $key, $color);
        }
    }

    public function actionGetColors()
    {
        $this->response->format = Response::FORMAT_JSON;
        return SystemConfig::findAll(['category' => SystemConfig::CATEGORY_COLOR]);
    }

    public function actionDeleteImage()
    {
        $id = $this->request->post('id');
        $category = $this->request->post('category');
        $image = SystemConfig::getConfig($category,$id);
        $pathToS3 = $image->value;
        if ($image->delete()) {
            if(!empty($pathToS3)) {
                $amazonHelper = new AmazonHelper();
                $amazonHelper->deleteMatchingObjects($pathToS3);
            }
            return true;
        }
        return false;
    }

    public function actionSettingLogoSite()
    {
        $logoSite = SystemConfig::getConfig(SystemConfig::CATEGORY_DESIGN_CONFIG_SHOP_ONE,SystemConfig::DESIGN_LOGO_SITE);
        $image = $this->request->post("logo");
        if ($image != '') {
            $dateUpload = $this->dateUpload();
            list($imageBase64, $imageType) = $this->handleImage($image);
            $fileName = 'logo_' . $dateUpload . '.' . $imageType;
            $pathToS3 = \Yii::$app->params["aws.logo.path"];
            $this->uploadImageToS3($fileName, $imageBase64, $pathToS3, $imageType);
            $logoSite->value = $pathToS3 . $fileName;
        }
        if ($logoSite->save()) {
            return json_encode([
                'success' => true,
                'message' => '画像変更を完了しました！'
            ]);
        }
        return json_encode([
            'success' => false,
            'message' => $image
        ]);
    }

    public function actionSettingCharacterSite()
    {
        $characterSite = SystemConfig::getConfig(SystemConfig::CATEGORY_DESIGN_CONFIG_SHOP_ONE,SystemConfig::DESIGN_CHARACTER);
        $image = $this->request->post("character");
        if ($image != '') {
            $dateUpload = $this->dateUpload();
            list($imageBase64, $imageType) = $this->handleImage($image);
            $fileName = 'character_' . $dateUpload . '.' . $imageType;
            $pathToS3 = \Yii::$app->params["aws.logo.path"];
            $this->uploadImageToS3($fileName, $imageBase64, $pathToS3, $imageType);
            $characterSite->value = $pathToS3 . $fileName;
        }
        if ($characterSite->save()) {
            return json_encode([
                'success' => true,
                'message' => '画像変更を完了しました！'
            ]);
        }
        return json_encode([
            'success' => false,
            'message' => $image
        ]);
    }

    public function actionSettingIntroSite()
    {
        $introSite = SystemConfig::getConfig(SystemConfig::CATEGORY_DESIGN_CONFIG_SHOP_ONE,SystemConfig::DESIGN_INTRO);
        $image = $this->request->post("intro");
        if ($image != '') {
            $dateUpload = $this->dateUpload();
            list($imageBase64, $imageType) = $this->handleImage($image);
            $fileName = 'intro_' . $dateUpload . '.' . $imageType;
            $pathToS3 = \Yii::$app->params["aws.logo.path"];
            $this->uploadImageToS3($fileName, $imageBase64, $pathToS3, $imageType);
            $introSite->value = $pathToS3 . $fileName;
        }
        if ($introSite->save()) {
            return json_encode([
                'success' => true,
                'message' => '画像変更を完了しました！'
            ]);
        }
        return json_encode([
            'success' => false,
            'message' => $image
        ]);
    }

    public function dateUpload()
    {
        $date = getdate();
        $dateUpload = $date['year'] . $date['mon'] . $date['mday'] . $date['hours'] . $date['minutes'] . $date['seconds'];
        return $dateUpload;
    }

    public function handleImage($image)
    {
        $imageParts = explode(';base64,', $image);
        $imageType = explode('image/', $imageParts[0])[1];
        $imageBase64 = base64_decode($imageParts[1]);
        return array($imageBase64, $imageType);
    }

    public function uploadImageToS3($fileName, $imageBase64, $path, $typeImage)
    {
        $typeImage = 'image/' . $typeImage;
        $amazonHelper = new AmazonHelper();
        $filePathS3 = $path . $fileName;
        $amazonHelper->uploadImageBase64ToS3($imageBase64, $filePathS3, 'public-read', $typeImage);
    }
}
