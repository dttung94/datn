<?php
namespace backend\widgets;

use backend\modules\member\forms\MemberForm;
use common\entities\calendar\CouponBusinessHistory;
use common\entities\calendar\Rating;
use common\entities\forum\Comment;
use common\entities\forum\Post;
use common\entities\service\TemplateMail;
use common\entities\shop\ShopCalendarSlot;
use common\entities\shop\ShopInfo;
use common\entities\system\SystemConfig;
use common\entities\system\SystemData;
use common\entities\user\UserConfig;
use common\entities\user\UserInfo;
use common\helper\ArrayHelper;
use common\models\UserIdentity;
use yii\bootstrap\Widget;
use App;
use yii\db\Query;
use yii\helpers\Json;
use yii\bootstrap\Html;

/**
 * Class SideMenuWidget
 */
class SideMenuWidget extends Widget
{
    public function init()
    {
        $module = \Yii::$app->controller->module->id;
        $controller = \Yii::$app->controller->id;
        $action = \Yii::$app->controller->action->id;
//        $couponBusiness = CouponBusinessHistory::find()->orderBy(['created_at' => SORT_DESC])->one();
//        if($couponBusiness != null) {
//            $createdCouponBusiness = strtotime(date('Y-m-d', strtotime($couponBusiness->created_at)));
//        }
//        else {
//            $createdCouponBusiness = strtotime(date('Y-m-d'));
//        }
        $now = strtotime(date('Y-m-d'));
//        $dateDiff = floor(abs($now - $createdCouponBusiness) / (60*60*24));
//        $dateNotificationCouponBusiness = SystemConfig::getValue(SystemConfig::CONFIG_COUPON_BUSINESS, SystemConfig::DAYS_TO_REMIND);
        /**
         * @var $user UserIdentity
         */
        $user = App::$app->user->identity;
        $menus = [];
        //todo show dashboard menu
//        $menuDashboard = [
//            [
//                "label" => App::t("backend.menu.primary", "統計"),
//                "icon" => "icon-home",
//                "class" => "start",
//                "url" => App::$app->urlManager->createUrl(["site/index"]),
//                'is_selected' => ($controller == "site" && $action == 'index'),
//            ],
//            [
//                "label" => App::t("backend.menu.primary", "SMS・メール"),
//                "icon" => "icon-home",
//                "class" => "start",
//                "url" => App::$app->urlManager->createUrl(["site/statistics-sms"]),
//                'is_selected' => ($controller == "site" && $action == 'statistics-sms'),
//            ],
//            [
//                "label" => App::t("backend.menu.primary", "利用率"),
//                "icon" => "icon-home",
//                "class" => "start",
//                "url" => App::$app->urlManager->createUrl(["site/statistics-usage-rate"]),
//                'is_selected' => ($controller == "site" && $action == 'statistics-usage-rate'),
//            ],
//
//        ];
        $menus[] = [
            "label" => App::t("backend.menu.primary", "Thống kê"),
            "icon" => "icon-home",
            "class" => "start",
            "url" => App::$app->urlManager->createUrl(["site/index"]),
            'is_selected' => ($controller == 'site' && $action == 'index'),
        ];
        //todo show calendar menu
        $menuShops = [];
        $query = ShopInfo::find()
            ->where([
                "status" => ShopInfo::STATUS_ACTIVE,
            ]);
        if ($user->role == UserInfo::ROLE_MANAGER || $user->role == UserIdentity::ROLE_OPERATOR) {
            $shopIds = Json::decode(UserConfig::getValue(UserConfig::KEY_MANAGE_SHOP_IDS, \App::$app->user->id, "[]"));
            $query->andWhere(["IN", "shop_id", $shopIds]);
        }
        foreach ($query->all() as $shop) {
            /**
             * @var $shop ShopInfo
             */
            $menuShops[] = [
                "label" => $shop->shop_name,
                "icon" => "fa fa-calendar",
                "url" => App::$app->urlManager->createUrl(["calendar/schedule/config", "shop_id" => $shop->shop_id]),
                'is_selected' => ($module == "calendar" && $controller == 'schedule' && App::$app->request->get("shop_id") == $shop->shop_id),
            ];
        }

        $menuCalendar = [
            "Dịch vụ đặt chỗ",
            [
                "label" => App::t("backend.menu.primary", "Quản lý đặt chỗ"),
                "icon" => "fa fa-cart-plus",
                "url" => App::$app->urlManager->createUrl([
                    "calendar/booking"
                ]),
                'is_selected' => ($module == "calendar" && $controller == 'booking'),
            ],
            [
                "label" => App::t("backend.menu.primary", "Ca làm việc"),
                "icon" => "icon-calendar",
                "url" => "javascript:;",
                'is_selected' => ($module == "calendar" && $controller == 'schedule'),
                "subs" => $menuShops,
            ],
            [
                "label" => App::t("backend.menu.primary", "Đánh giá xếp hạng"),
                "icon" => "fa fa-list-ol",
                "url" => App::$app->urlManager->createUrl([
                    "calendar/rating/ranking",
                ]),
                'is_selected' => ($module == "calendar" && $controller == 'rating'),

            ]
        ];
        $menus = ArrayHelper::merge($menus, $menuCalendar);
        //todo show money menu
//        $subMenuMoney = [
//            [
//                "label" => App::t("backend.menu.primary", "手動クーポン"),
//                "icon" => "fa fa-money",
//                "url" => App::$app->urlManager->createUrl(["coupon/manage"]),
//            ],
//            [
//                "label" => App::t("backend.menu.primary", "自動クーポン"),
//                "icon" => "fa fa-money",
//                "url" => App::$app->urlManager->createUrl(["coupon/manage/coupon-wait-confirm"]),
//            ],
//        ];
//        if ($user->role == UserIdentity::ROLE_MANAGER || $user->role == UserIdentity::ROLE_ADMIN) {
//            $subMenuMoney[] = [
//                "label" => App::t("backend.menu.primary", "インポート"),
//                "icon" => "fa fa-upload",
//                "url" => App::$app->urlManager->createUrl(["coupon/import/index"]),
//            ];
//            $subMenuMoney[] = [
//                "label" => App::t("backend.menu.primary", "クーポン削除"),
//                "icon" => "fa fa-trash",
//                "url" => App::$app->urlManager->createUrl(["coupon/manage/coupon-can-delete"]),
//            ];
//            $subMenuMoney[] = [
//                "label" => App::t("backend.menu.primary", "一斉送信"),
//                "icon" => "fa fa-plus",
//                "url" => App::$app->urlManager->createUrl(["coupon/manage/create-coupon-private"]),
//            ];
//        }
//        if ($user->role == UserIdentity::ROLE_ADMIN) {
//            $subMenuMoney[] = [
//                "label" => App::t("backend.menu.primary", "営業クーポン"),
//                "icon" => "fa fa-plus",
//                "url" => App::$app->urlManager->createUrl(["coupon/manage/create-coupon-business"]),
//                'notification' => Html::tag("span", "", [
//                    "class" => "badge badge-danger pull-right notice-coupon-business",
////                    "ng-show" => "$dateDiff >= $dateNotificationCouponBusiness",
//                    "ng-bind" => "'!'",
////                    "title" => "$dateDiff 日までまだ営業クーポンを作成しません。",
//                ]),
//            ];
//        }
//        $menuMoney = [
//            "クーポン",
//            [
//                "label" => App::t("backend.menu.primary", "クーポン管理"),
//                "icon" => "fa fa-tag",
//                "url" => 'javascript:;',
//                'is_selected' => ($module == "coupon"),
//                "subs" => $subMenuMoney,
//                'notification' => Html::tag("span", "", [
//                    "class" => "badge badge-danger pull-right notice-coupon-business",
////                    "ng-show" => "$dateDiff >= $dateNotificationCouponBusiness",
//                    "ng-bind" => "'!'",
////                    "title" => "$dateDiff 日までまだ営業クーポンを作成しません。",
//                ]),
//            ],
//        ];
//        $menus = ArrayHelper::merge($menus, $menuMoney);
        //todo show business menu
        $menuBusiness = [
            "Nội bộ",
            [
                "label" => App::t("backend.menu.primary", "Quản lý nhân viên"),
                "icon" => "fa fa-users",
                "url" => App::$app->urlManager->createUrl(["worker/manage"]),
                'is_selected' => ($module == "worker" && ($controller == 'manage' || $controller == "import" || $controller == "widget")),
            ],
        ];
        if ($user->role == UserIdentity::ROLE_MANAGER || $user->role == UserIdentity::ROLE_ADMIN) {
            $menuBusiness[] = [
                "label" => App::t("backend.menu.primary", "Quản lý Salon"),
                "icon" => "fa fa-folder-open",
                "url" => App::$app->urlManager->createUrl(["shop/manage"]),
                'is_selected' => ($module == "shop" && $controller == 'manage'),
            ];
        }
        $menuBusiness[] = [
            "label" => App::t("backend.menu.primary", "Quản lý thành viên"),
            "icon" => "fa fa-user-md",
            "url" => App::$app->urlManager->createUrl(["member/manage"]),
            'is_selected' => ($module == "member" && $controller == 'manage' && $action == "index"),
//            'notification' => Html::tag("span", "", [
//                "class" => "badge badge-danger pull-right notice-total-member-waiting-confirm",
//                "ng-show" => "totalMemberWaiting > 0",
//                "ng-bind" => "totalMemberWaiting",
//                "ng-init" => "totalMemberWaiting = " . UserInfo::find()->where([
//                        "status" => UserInfo::STATUS_CONFIRMING,
//                        "role" => UserInfo::ROLE_USER,
//                    ])->count(),
//            ]),
        ];
//        $countComment = Comment::find()->innerJoin(Post::tableName(), Post::tableName() . '.id = ' . Comment::tableName() . '.post_id')->where([
//            Comment::tableName() . ".status" => Comment::PENDING,
//            Comment::tableName() . '.del_flg' => Comment::STATUS_ACTIVE,
//            Post::tableName() . '.status' => Post::APPROVE,
//            Post::tableName() . '.del_flg' => Post::STATUS_ACTIVE,
//        ])->count();
//        $countPost = Post::find()->where([
//            "status" => Post::PENDING,
//            'del_flg' => Post::STATUS_ACTIVE
//        ])->count();
//        if ($user->role == UserIdentity::ROLE_ADMIN || $user->role == UserIdentity::ROLE_OPERATOR || $user->role == UserIdentity::ROLE_MANAGER) {
//            $subMenuForumTemplate = [
//                [
//                    "label" => App::t("backend.menu.primary", "書き込み管理"),
//                    "icon" => "fa fa-pencil-square-o",
//                    "url" => App::$app->urlManager->createUrl(["forum/manage"]),
//                    'is_selected' => ($module == "forum" && $controller == 'manage'),
//                    'notification' => Html::tag("span", "", [
//                        "class" => "badge badge-danger pull-right notice-total-member-waiting-confirm",
//                        "ng-show" => "totalPostWaiting > 0",
//                        "ng-bind" => "totalPostWaiting",
//                        "ng-init" => "totalPostWaiting = " ,
//                    ]),
//                ],
//                [
//                    "label" => App::t("backend.menu.primary", "コメント管理"),
//                    "icon" => "fa fa-comments",
//                    "url" => App::$app->urlManager->createUrl(["forum/comment"]),
//                    'is_selected' => ($module == "forum" && $controller == 'comment'),
//                    'notification' => Html::tag("span", "", [
//                        "class" => "badge badge-danger pull-right notice-total-member-waiting-confirm",
//                        "ng-show" => "totalCommentWaiting > 0",
//                        "ng-bind" => "totalCommentWaiting",
//                        "ng-init" => "totalCommentWaiting = ",
//                    ]),
//                ],
//            ];
//            $menus = ArrayHelper::merge($menus, [
//                'フォーラム',
//                [
//                    "label" => App::t("backend.menu.primary", "フォーラム"),
//                    "icon" => "fa fa-pencil-square-o",
//                    "url" => App::$app->urlManager->createUrl(["service/mail/template"]),
//                    'is_selected' => ($module == 'forum' && ($controller == 'comment' || $controller == 'manage')),
//                    'subs' => $subMenuForumTemplate,
//                    'notification' => Html::tag("span", "", [
//                        "class" => "badge badge-danger pull-right notice-total-member-waiting-confirm",
//                        "ng-show" => "totalWaiting > 0",
//                        "ng-bind" => "totalWaiting",
//                        "ng-init" => "totalWaiting = " ,
//                    ]),
//                ],
//            ]);
//        }

//        if ($user->role == UserIdentity::ROLE_ADMIN) {
//            $menuBusiness[] = [
//                "label" => App::t("backend.menu.primary", "メモ閲覧"),
//                "icon" => "fa fa-star",
//                "url" => App::$app->urlManager->createUrl(["rating/manage"]),
//                'is_selected' => ($module == "rating" && $controller == 'manage'),
//            ];
//        }
//
//        if ($user->role == UserIdentity::ROLE_ADMIN || $user->role == UserIdentity::ROLE_MANAGER) {
//            $menuBusiness[] = [
//                "label" => App::t("backend.menu.primary", "紹介プログラム"),
//                "icon" => "fa fa-user-plus",
//                "url" => App::$app->urlManager->createUrl(["refer/manage"]),
//                'is_selected' => ($module == "refer" && $controller == 'manage'),
//            ];
//        }
//
//        if ($user->role == UserIdentity::ROLE_ADMIN) {
//            $menuBusiness[] = [
//                "label" => App::t("backend.menu.primary", "好みの女の子の集計"),
//                "icon" => "fa fa fa-heart",
//                "url" => App::$app->urlManager->createUrl(["member/manage/get-total-user-hobbies"]),
//                'is_selected' => ($module == "member" && $controller == 'manage' && $action == 'get-total-user-hobbies'),
//            ];
//        }

        $menus = ArrayHelper::merge($menus, $menuBusiness);
        if ($user->role == UserInfo::ROLE_ADMIN) {
            $dataMenus = [
                "Quản lý dịch vụ",

//                "label" => App::t("backend.menu.primary", "料金管理"),
//                "icon" => "fa fa-money",
//                "url" => "javascript:;",
//                'is_selected' => ($module == "data" && $controller == 'price'),
//                "subs" => [
                [
                    "label" => App::t("backend.menu.primary", "Dịch vụ & phí"),
                    "icon" => "fa fa-money",
                    "url" => App::$app->urlManager->createUrl(["data/price/course"]),
                    'is_selected' => ($module == "data" && $controller == 'price' && $action == 'course'),
                ],
//                    [
//                        "label" => App::t("backend.menu.primary", "指名料金 延長料金"),
//                        "icon" => "fa fa-money",
//                        "url" => App::$app->urlManager->createUrl(["data/price/fee"]),
//                        'is_selected' => ($module == "data" && $controller == 'price' && $action == 'fee'),
//                    ],
//                ],

            ];
//        if ($user->role == UserIdentity::ROLE_ADMIN) {
//            $dataKey = App::$app->request->get("cat");
//            foreach (Rating::listCategories() as $key => $text) {
//                $dataMenus[] = [
//                    "label" => $text,
//                    "icon" => "fa fa fa-database",
//                    "url" => App::$app->urlManager->createUrl([
//                        "data/common",
//                        "cat" => $key
//                    ]),
//                    'is_selected' => ($module == "data" && $controller == 'common' && $dataKey == $key),
//                ];
//            }
//        }
            $menus = ArrayHelper::merge($menus, $dataMenus);
        }

        if ($user->role == UserIdentity::ROLE_ADMIN) {
            $menus = ArrayHelper::merge($menus, [
                'Email',
                [
                    "label" => App::t("backend.menu.primary", "Lịch sử email"),
                    "icon" => "fa fa-envelope-o",
                    "url" => App::$app->urlManager->createUrl(["service/mail/index"]),
                    'is_selected' => ($module == "service" && $controller == 'mail' && $action == 'index'),
                ]
            ]);
        }

        //todo show system menu
        if ($user->role == UserIdentity::ROLE_ADMIN) {
            $menus = ArrayHelper::merge($menus, [
                "SMS",
                [
                    "label" => App::t("backend.menu.primary", "Quản lý SMS"),
                    "icon" => "fa fa-mobile",
                    "url" => App::$app->urlManager->createUrl(["service/sms/template"]),
                    'is_selected' => ($module == "service" && $controller == 'sms' && $action == 'template'),
                ],
                [
                    "label" => App::t("backend.menu.primary", "Lịch sử SMS"),
                    "icon" => "fa fa-mobile",
                    "url" => App::$app->urlManager->createUrl(["service/sms/index"]),
                    'is_selected' => ($module == "service" && $controller == 'sms' && $action == 'index'),
                ],
                "Hệ thống",
                [
                    "label" => App::t("backend.menu.primary", "Quản lý nhân sự"),
                    "icon" => "fa fa-users",
                    "url" => App::$app->urlManager->createUrl(["system/manager"]),
                    'is_selected' => ($module == "system" && $controller == 'manager'),
                ],
                [
                    "label" => App::t("backend.menu.primary", "Cài đặt hệ thống"),
                    "icon" => "fa fa-cogs",
                    "url" => App::$app->urlManager->createUrl(["system/config"]),
                    'is_selected' => ($module == "system" && $controller == 'config'),
                ]
            ]);
        }

//        if ($user->role == UserIdentity::ROLE_MANAGER) {
//            $menus = ArrayHelper::merge($menus, [
//                "SMS",
//                [
//                    "label" => App::t("backend.menu.primary", "SMSテンプレート"),
//                    "icon" => "fa fa-mobile",
//                    "url" => App::$app->urlManager->createUrl(["service/sms/template"]),
//                    'is_selected' => ($module == "service" && $controller == 'sms' && $action == 'template'),
//                ],
//                [
//                    "label" => App::t("backend.menu.primary", "SMS履歴"),
//                    "icon" => "fa fa-mobile",
//                    "url" => App::$app->urlManager->createUrl(["service/sms/index"]),
//                    'is_selected' => ($module == "service" && $controller == 'sms' && $action == 'index'),
//                ],
//                "システム",
//                [
//                    "label" => App::t("backend.menu.primary", "ログ"),
//                    "icon" => "fa fa-terminal",
//                    "url" => App::$app->urlManager->createUrl(["system/user-log"]),
//                    'is_selected' => ($module == "system" && $controller == 'user-log'),
//                ]
//            ]);
//        }
        echo $this->render("side-menu/index", [
            "menus" => $menus,
        ]);
    }
}
