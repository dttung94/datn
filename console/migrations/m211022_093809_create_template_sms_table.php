<?php

use yii\db\Migration;
use common\entities\service\TemplateSms;
use common\helper\ArrayHelper;

/**
 * Handles the creation of table `{{%template_sms}}`.
 */
class m211022_093809_create_template_sms_table extends Migration
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
        $this->createTable('{{%template_sms}}', [
            'template_id' => $this->primaryKey(),
            "type" => $this->string(100)->notNull(),
            "content" => $this->text()->notNull(),
            "status" => $this->integer(2)->notNull(),
            "created_at" => $this->dateTime()->notNull(),
            "modified_at" => $this->dateTime()->null(),
        ], $tableOptions);

        //todo init template data
        $templateSmsDatas = [
            [
                "type" => "MEMBER_REGISTER_VERIFY_PHONE_NUMBER",
                "content" => "Đây là hệ thống đặt lịch chuỗi salon tóc nam Tuấn Dũng. 
                            Nhấn vào URL để hoàn tất đăng ký: {verify_url} 
                            Thời hạn đường dẫn là 1 giờ 
                            * Bạn không thể trả lời tin nhắn này.",
            ],
            [
                "type" => "MEMBER_FORGOT_PASSWORD_REQUEST",
                "content" => "■ Đặt lại mật khẩu ■
                            Vui lòng nhấp vào URL bên dưới và đặt lại mật khẩu của bạn. Vui lòng sử dụng ít nhất 6 ký tự cho mật khẩu:{quên_password_url} 
                            * Bạn không thể trả lời tin này.",
            ],
            [
                "type" => "BOOKING_ONLINE_ACCEPT",
                "content" => "■ Đặt lịch đã hoàn tất ■
                            Bạn đã đặt lịch lúc {booking_date} {booking_time} với nhân viên {worker_name}, thời lượng khoảng {course_id} {course_time} phút. Phí là [{cost} VNĐ]. Nếu có vấn đề gì thì bạn có thể liên hệ với cửa hàng qua số điện thoại {phone_number}.
                            Trong trường hợp bạn muốn hủy lịch, bạn có thể quay lại màn hình đặt lịch và hủy ở phần lịch sử phía trên bên phải của màn hình đặt lịch. 
                            * Bạn không thể trả lời tin nhắn này.",
            ],
            [
                "type" => "BOOKING_ONLINE_UPDATE",
                "content" => "■ Điều chỉnh đặt lịch đã hoàn tất ■
                            Thành công thay đổi yêu cầu tại {shop_name} với nhân viên {worker_name}
                            * Bạn không thể trả lời tin nhắn này.",
            ],
            [
                "type" => "BOOKING_ONLINE_REJECT",
                "content" => '■ Đặt lịch không thành công ■ 
                        Xin lỗi quý khách vì không thể phục vụ quý khách theo yêu cầu với nhân viên {worker_name} tại cửa hàng {shop_name}.
                        Quý khách vui lòng đặt lịch vào thời điểm khác hoặc có thể liên hệ với cửa hàng theo số {phone_number}
                        * Bạn không thể trả lời tin nhắn này.'
            ],
            [
                "type" => "BOOKING_REMOVE_SMS",
                "content" => '■ Hủy yêu cầu từ phía cửa hàng ■ 
                            Cửa hàng {shop_name}. Đặt lịch {booking_date} {booking_time}  với dịch vụ {course_id} và thời gian {course_time} phút của bạn đã bị hủy do có trục trặc phía cửa hàng. 
                            Chúng tôi xin gửi lời xin lỗi tới bạn. Nếu bạn muốn đặt lịch lại, vui lòng gọi cho chúng tôi hoặc đặt lịch trực tuyến lại.
                            *Bạn không thể trả lời tin nhắn này'
            ],
            [
                "type" => "BOOKING_FREE_SMS",
                "content" => "Quý khách hàng đã đặt trước tại cửa hàng {shop_name} chúng tôi lúc {booking_time}. 
                            Vui lòng đến cửa hàng tại địa chỉ {shop_address} trước 10 phút để chúng tôi có thể sắp xếp và phục vụ quý khách.
                            Xin cảm ơn.
                            * Bạn không thể trả lời tin nhắn này.
                            ",
            ],
            [
                "type" => "WORKER_WORK_BREAK",
                "content" => "Thông báo từ salon tóc {shop_name}. 
                            Tôi rất tiếc nhưng nhân viên {worker_name} không thể phục vụ bạn nữa do có việc đột xuất từ cửa hàng. Chúng tôi xin lỗi vì sự bất tiện này, nhưng chúng tôi rất mong được dời lịch cho bạn. Bạn có thể liên hệ cửa hàng thông qua số điện thoại {phone_number}.
                             * Bạn không thể trả lời tin nhắn này.",
            ],
            [
                "type" => "BOOKING_ONLINE_AUTO_REJECT",
                "content" => "Từ salon tóc {shop_name}. Chúng tôi có vấn đề với việc đặt chỗ của bạn. 
                        Xin lỗi đã làm phiền bạn, nhưng vui lòng gọi cho cửa hàng qua số điện thoại {phone_number} 
                        * Bạn không thể trả lời tin nhắn này.",
            ],

        ];
        foreach ($templateSmsDatas as $data) {
            $model = new TemplateSms();
            $model->type = ArrayHelper::getValue($data, "type");
            $model->content = ArrayHelper::getValue($data, "content");
            $model->status = TemplateSms::STATUS_ACTIVE;
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
        $this->dropTable('{{%template_sms}}');
    }
}
