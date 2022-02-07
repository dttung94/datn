<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_token}}`.
 */
class m211022_091055_create_user_token_table extends Migration
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
        $this->createTable('{{%user_token}}', [
            'token' => $this->string(200)->notNull(),
            'type' => $this->string(100)->notNull(),
            'user_id' => $this->integer(11)->notNull(),
            'expire_date' => $this->dateTime()->notNull(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);
        //todo add foreign key for table `user_info`
        $this->addForeignKey(
            'fk-token_user',
            '{{%user_token}}',
            'user_id',
            '{{%user_info}}',
            'user_id'
        );
        //todo add primary key [token, user_id, type]
        $this->addPrimaryKey("user_token_pk", "{{%user_token}}", [
            'token',
            'user_id',
            'type',
        ]);

    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{$user_token}}');
    }
}
