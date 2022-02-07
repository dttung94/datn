<?php

use yii\db\Migration;
use common\entities\shop\ShopCalendarSlot;

/**
 * Handles the creation of table `{{%shop_calendar_slot}}`.
 */
class m211022_095340_create_shop_calendar_slot_table extends Migration
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
        $this->createTable('{{%shop_calendar_slot}}', [
            'slot_id' => $this->primaryKey(),
            'shop_id' => $this->integer(11)->notNull(),
            'worker_id' => $this->integer(11)->notNull(),
            'date' => $this->date()->notNull(),
            'start_time' => $this->string(100)->notNull(),
            'end_time' => $this->string(100)->notNull(),
            'duration_minute' => $this->integer()->notNull(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);
        //todo add foreign key for table `shop_info`
        $this->addForeignKey(
            'fk-calendar_slot_shop',
            '{{%shop_calendar_slot}}',
            'shop_id',
            '{{%shop_info}}',
            'shop_id'
        );
        //todo add foreign key for table `worker_info`
        $this->addForeignKey(
            'fk-calendar_slot_worker',
            '{{%shop_calendar_slot}}',
            'worker_id',
            '{{%worker_info}}',
            'worker_id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%shop_calendar_slot}}');
    }
}
