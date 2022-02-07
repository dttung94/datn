<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_config}}`.
 */
class m211206_101500_create_user_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%user_config}}', [
            'user_id' => $this->integer(11)->notNull(),
            'key' => $this->string(100)->notNull(),
            'value' => $this->text()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);
        //todo add foreign key for table `user_info`
        $this->addForeignKey(
            'fk-config_user',
            '{{%user_config}}',
            'user_id',
            '{{%user_info}}',
            'user_id'
        );
        //todo add primary key [user_id, key]
        $this->addPrimaryKey("user_config_pk", "{{%user_config}}", [
            'user_id',
            'key'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%user_config}}');
    }
}
