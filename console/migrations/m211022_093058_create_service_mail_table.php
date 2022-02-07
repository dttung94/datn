<?php

use yii\db\Migration;
use common\entities\user\UserInfo;

/**
 * Handles the creation of table `{{%service_mail}}`.
 */
class m211022_093058_create_service_mail_table extends Migration
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
        $this->createTable('{{%service_mail}}', [
            'mail_id' => $this->primaryKey(),
            "subject" => $this->string(500)->notNull(),
            "content" => $this->text()->notNull(),
            "from_email" => $this->string(300)->notNull(),
            "from_name" => $this->string(300)->notNull(),
            "to" => $this->text()->notNull(),
            "params" => $this->text()->null(),
            "result" => $this->text()->null(),
            "status" => $this->integer(2)->notNull(),
            "mail_type" => $this->tinyInteger()->null(),
            'role' => $this->string(20)->defaultValue(UserInfo::ROLE_ADMIN),
            "created_at" => $this->dateTime()->notNull(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%service_mail}}');
    }
}
