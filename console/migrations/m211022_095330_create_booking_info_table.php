<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%booking_info}}`.
 */
class m211022_095330_create_booking_info_table extends Migration
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
        $this->createTable('{{%booking_info}}', [
            'booking_id' => $this->primaryKey(),
            'member_id' => $this->integer(11)->null()->comment("ID of member user_id in user_info"),
            'course_id' => $this->integer(2)->notNull(),
            'slot_id' => $this->integer(11)->notNull()->comment("ID of CalendarSlot"),

            "cost" => $this->double()->notNull(),

            'comment' => $this->text()->null()->comment("Comment of Customer"),
            'note' => $this->text()->null()->comment("Note of Manager"),

            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);

        //todo add foreign key for table `course_info`
        $this->addForeignKey(
            'fk-booking-course_info',
            '{{%booking_info}}',
            'course_id',
            '{{%course_info}}',
            'course_id'
        );

        //todo add foreign key for table `user_info` [MEMBER]
        $this->addForeignKey(
            'fk-booking-member_id',
            '{{%booking_info}}',
            'member_id',
            '{{%user_info}}',
            'user_id'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%booking_info}}');
    }
}
