<?php

use yii\db\Migration;
use common\entities\system\SystemConfig;
use common\helper\ArrayHelper;

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

        $courseData = [
            [
                "id" => "TWILIO_APP_PHONE_NUMBER",
                "category" => "TWILIO_APP",
                "value" => "(956) 420-6817",
            ],
            [
                "id" => "TWILIO_APP_SID",
                "category" => "TWILIO_APP",
                "value" => "ACd1d7c58c1db36b7da1d352e253af94fc",
            ],
            [
                "id" => "TWILIO_APP_TOKEN",
                "category" => "TWILIO_APP",
                "value" => "6c17add6de6493e2a7ecf44550b6ea68",
            ],
        ];
        foreach ($courseData as $data) {
            $model = new SystemConfig();
            $model->id = ArrayHelper::getValue($data, "id");
            $model->category = ArrayHelper::getValue($data, "category");
            $model->value = ArrayHelper::getValue($data, "value");
            if (!$model->save()) {
                var_dump($model->getErrors());
                break;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%system_config}}');
    }
}
