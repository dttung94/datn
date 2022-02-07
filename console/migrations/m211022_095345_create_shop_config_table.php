<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%shop_config}}`.
 */
class m211022_095345_create_shop_config_table extends Migration
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
        $this->createTable('{{%shop_config}}', [
            'shop_id' => $this->integer(11)->notNull(),
            'key' => $this->string(100)->notNull(),
            'value' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);
        //todo add foreign key for table `user_info`
        $this->addForeignKey(
            'fk-config_shop',
            '{{%shop_config}}',
            'shop_id',
            '{{%shop_info}}',
            'shop_id'
        );
        //todo add primary key [user_id, key]
        $this->addPrimaryKey("shop_config_pk", "{{%shop_config}}", [
            'shop_id',
            'key'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%shop_config}}');
    }
}
