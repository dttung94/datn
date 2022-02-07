<?php

use yii\db\Migration;
use common\entities\calendar\CourseInfo;
use common\helper\ArrayHelper;

/**
 * Handles the creation of table `{{%course_info}}`.
 */
class m211022_095320_create_course_info_table extends Migration
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
        $this->createTable('{{%course_info}}', [
            'course_id' => $this->primaryKey(11),
            'course_name' => $this->string(255)->notNull(),
            'description' => $this->text()->null(),
            'price' => $this->double()->notNull(),
            'status' => $this->integer(2)->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'modified_at' => $this->dateTime()->null(),
        ], $tableOptions);

        //todo init course data
        $courseData = [
            [
                "name" => "Cắt tóc & tạo kiểu",
                "price" => "80000",
            ],
            [
                "name" => "Uốn tóc",
                "price" => "200000",
            ],
            [
                "name" => "Nhuộm tóc",
                "price" => "200000",
            ],
            [
                "name" => "Tẩy & nhuộm tóc",
                "price" => "250000",
            ],
            [
                "name" => "Combo cắt + gội + uốn/nhuộm",
                "price" => '300000'
            ],
        ];
        foreach ($courseData as $data) {
            $model = new CourseInfo();
            $model->course_name = ArrayHelper::getValue($data, "name");
            $model->price = ArrayHelper::getValue($data, "price");
            $model->status = CourseInfo::STATUS_ACTIVE;
            if (!$model->save()) {
                var_dump($model->getErrors());
                break;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('course_info');
    }
}
