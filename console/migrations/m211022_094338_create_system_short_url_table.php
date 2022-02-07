<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%system_short_url}}`.
 */
class m211022_094338_create_system_short_url_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%system_short_url}}', [
            'id' => $this->string(10)->notNull(),
            'url' => $this->string(500)->notNull(),
            'description' => $this->text()->null(),
            'expired_at' => $this->dateTime()->null(),
            'total_access' => $this->integer(11)->notNull()->defaultValue(0),

            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->notNull(),
        ]);
        //todo add primary key [id]
        $this->addPrimaryKey("system_short_url_pk", "{{%system_short_url}}", [
            'id',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_short_url}}');
    }
}
