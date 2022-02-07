<?php

use yii\db\Migration;
use common\entities\user\UserInfo;
use yii\base\InvalidConfigException;
use common\helper\JsonHelper;

/**
 * Handles the creation of table `{{%user_info}}`.
 */
class m211021_014832_create_user_info_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        //todo create table
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%user_info}}', [
            'user_id' => $this->primaryKey(),
            'full_name' => $this->string(255)->null(),
            'username' => $this->string(200)->notNull(),
            'password' => $this->string(255)->notNull(),
            'email' => $this->string(255)->null(),
            'phone_number' => $this->string(20)->null(),
            'role' => $this->string(100)->notNull()->comment("ADMIN | USER | OPERATOR | MANAGER"),
            'status' => $this->integer(2)->defaultValue(UserInfo::STATUS_ACTIVE)->notNull(),
            'verify_email' => $this->integer()->defaultValue(UserInfo::NOT_VERIFIED),
            'verify_phone'=> $this->integer()->defaultValue(UserInfo::NOT_VERIFIED),
            'type_notification'=>$this->boolean()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);

        //todo create admin account
        $user = new UserInfo();
        $user->full_name = Yii::$app->params["user.admin.full_name"];
        $user->username = Yii::$app->params["user.admin.username"];
        $user->setPassword(Yii::$app->params["user.admin.password"]);
        $user->email = Yii::$app->params["user.admin.email"];
        $user->phone_number = Yii::$app->params["user.admin.phone_number"];
        $user->role = UserInfo::ROLE_ADMIN;
        $user->status = UserInfo::STATUS_ACTIVE;
        if (!$user->save()) {
            throw new InvalidConfigException(JsonHelper::encode($user->getErrors()));
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_info}}');
    }
}
