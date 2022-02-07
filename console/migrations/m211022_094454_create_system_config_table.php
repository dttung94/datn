<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%system_config}}`.
 */
class m211022_094454_create_system_config_table extends Migration
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
        $this->createTable('{{%system_config}}', [
            'id' => $this->string(200)->notNull(),
            'category' => $this->string(200)->notNull(),
            'value' => $this->text()->null(),
            'modified_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        //todo add primary key [id, language]
        $this->addPrimaryKey("language_translate_pk", "{{%system_config}}", [
            'id',
            'category'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_config}}');
    }
}
