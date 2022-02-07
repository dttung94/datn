<?php

use yii\db\Migration;

/**
 * Class m211022_091131_create_worker_mapping_shop
 */
class m211022_095031_create_worker_mapping_shop extends Migration
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
        $this->createTable('{{%worker_mapping_shop}}', [
            'worker_id' => $this->integer(11)->notNull(),
            'shop_id' => $this->integer(11)->notNull(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);
        //todo add foreign key for table `worker_info`
        $this->addForeignKey(
            'fk-worker_info',
            '{{%worker_mapping_shop}}',
            'worker_id',
            '{{%worker_info}}',
            'worker_id'
        );
        //todo add foreign key for table `shop_info`
        $this->addForeignKey(
            'fk-shop_id',
            '{{%worker_mapping_shop}}',
            'shop_id',
            '{{%shop_info}}',
            'shop_id'
        );
        //todo add primary key [worker_id, shop_id]
        $this->addPrimaryKey("worker_mapping_shop_pk", "{{%worker_mapping_shop}}", [
            'worker_id',
            'shop_id'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%worker_mapping_shop}}');
    }
}
