<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%service_sms}}`.
 */
class m211022_093744_create_service_sms_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        //todo create table
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%service_sms}}', [
            'sms_id' => $this->primaryKey(),
            "content" => $this->text()->notNull(),
            "params" => $this->text()->null(),
            "to" => $this->string(100)->notNull(),
            "tag" => $this->text()->null(),
            "result" => $this->text()->null(),
            "status" => $this->integer(2)->notNull(),
            "created_at" => $this->dateTime()->notNull(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%service_sms}}');
    }
}
