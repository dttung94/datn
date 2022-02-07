<?php
namespace backend\modules\calendar\forms\shop;

use common\entities\shop\ShopConfig;
use common\entities\shop\ShopInfo;
use App;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use yii\helpers\Json;

/**
 * Class ShopConfigForm
 * @package backend\modules\calendar\forms\shop
 */
class ShopConfigForm extends ShopInfo
{
    public static function setShopConfigs($shopConfigs)
    {
        $trans = \App::$app->db->beginTransaction();
        $hasError = false;
        foreach ($shopConfigs as $shop_id => $value) {
            $shop = self::findOne($shop_id);
            if ($shop) {
                ShopConfig::setValue(ShopConfig::KEY_SHOP_ALLOW_FREE_BOOKING, $shop->shop_id, $value);
            } else {
                $hasError = true;
            }
        }
        if (!$hasError) {
            $trans->commit();
            return true;
        } else {
            $trans->rollBack();
        }
        return false;
    }

    public function search()
    {
        $users = App::$app->user->identity;
        $datas = UserConfig::find()
            ->where([
                'user_id' => $users->user_id,
                'key' => UserConfig::KEY_MANAGE_SHOP_IDS
            ])->one();
        $shopIds = Json::decode($datas->value);
        $query = parent::search();
        $query->where([
            "status" => self::STATUS_ACTIVE,
        ]);
        if ($users->role != UserInfo::ROLE_ADMIN) {
            $query = $query->andWhere(['in', 'shop_id', $shopIds]);
        }
        return $query->all();
    }

    public function toArray(array $fields = [], array $expand = ["isAllowFreeBooking"], $recursive = true)
    {
        return parent::toArray($fields, $expand, $recursive);
    }
}