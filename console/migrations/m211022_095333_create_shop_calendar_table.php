<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%shop_calendar}}`.
 */
class m211022_095333_create_shop_calendar_table extends Migration
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
        $this->createTable('{{%shop_calendar}}', [
            'shop_id' => $this->integer(11)->notNull(),
            'worker_id' => $this->integer(11)->notNull(),
            'date' => $this->date()->notNull(),
            'type' => $this->string(100)->notNull()->comment("WORK_DAY | HOLIDAY"),
            'work_start_time' => $this->string(100)->null(),
            'work_end_time' => $this->string(100)->null(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);
        //todo add foreign key for table `shop_info`
        $this->addForeignKey(
            'fk-calendar_shop',
            '{{%shop_calendar}}',
            'shop_id',
            '{{%shop_info}}',
            'shop_id'
        );
        //todo add foreign key for table `worker_info`
        $this->addForeignKey(
            'fk-calendar_worker',
            '{{%shop_calendar}}',
            'worker_id',
            '{{%worker_info}}',
            'worker_id'
        );
        //todo add primary key [shop_id, worker_id, date]
        $this->addPrimaryKey("shop_calendar_pk", "{{%shop_calendar}}", [
            'shop_id',
            'worker_id',
            'date',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%shop_calendar}}');
    }
}
