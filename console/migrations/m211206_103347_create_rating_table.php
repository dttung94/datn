<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%rating}}`.
 */
class m211206_103347_create_rating_table extends Migration
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

        $this->createTable('{{%rating}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(11)->notNull(),
            'worker_id' => $this->integer(11)->notNull(),
            'booking_id' => $this->integer(11)->notNull(),
            'behavior' => $this->tinyInteger()->notNull()->comment("Thái độ phục vụ"),
            'technique' => $this->tinyInteger()->notNull()->comment("Tay nghề nhân viên"),
            'service' => $this->tinyInteger()->notNull()->comment("Dịch vụ"),
            'price' => $this->tinyInteger()->notNull()->comment("Giá cả"),
            'satisfaction' => $this->tinyInteger()->notNull()->comment("Mức độ hài lòng"),
            'memo' => $this->string('255')->null(),
            'created_at' => $this->dateTime()->notNull(),
        ], $tableOptions);

        $this->addForeignKey(
            'fk-rating-user-info',
            '{{%rating}}',
            'user_id',
            '{{%user_info}}',
            'user_id'
        );

        $this->addForeignKey(
            'fk-rating-worker-info',
            '{{%rating}}',
            'worker_id',
            '{{%worker_info}}',
            'worker_id'
        );

        $this->addForeignKey(
            'fk-rating-booking-info',
            '{{%rating}}',
            'booking_id',
            '{{%booking_info}}',
            'booking_id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%rating}}');
    }
}
