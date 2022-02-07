<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%shop_info}}`.
 */
class m211022_094444_create_shop_info_table extends Migration
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
        $this->createTable('{{%shop_info}}', [
            'shop_id' => $this->primaryKey(),
            'shop_name' => $this->string(350)->notNull(),
            'shop_desc' => $this->text()->null(),
            'shop_address' => $this->text()->null(),
        //ko dung is_auto_create (tao cp tu dong)
            'phone_number' => $this->string(20)->null(),
            'shop_email' => $this->string(500)->null(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%shop_info}}');
    }
}
