<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%template_mail}}`.
 */
class m211022_093756_create_template_mail_table extends Migration
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
        $this->createTable('{{%template_mail}}', [
            'template_id' => $this->primaryKey(),
            "type" => $this->string(100)->notNull(),
            "title" => $this->text()->notNull(),
            "content" => $this->text()->notNull(),
            "status" => $this->integer(2)->notNull(),
            "created_at" => $this->dateTime()->notNull(),
            "modified_at" => $this->dateTime()->null(),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%template_mail}}');
    }
}
