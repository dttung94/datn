<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%worker_info}}`.
 */
class m211022_091115_create_worker_info_table extends Migration
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
        $this->createTable('{{%worker_info}}', [
            'worker_id' => $this->primaryKey(),
            'worker_name' => $this->string(350)->notNull(),
//            'avatar' => $this->integer()->null(),
            'avatar_url' => $this->string()->null()->defaultValue(null),
            'description' => $this->text()->null(),
//            'ref_id' => $this->string(300)->null(),
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
        $this->dropTable('{{%worker_info}}');
    }
}
