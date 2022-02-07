<?php
echo $content;
if (!empty($workers)) {
    echo '<table>';
    foreach ($workers as $worker) {
        $workerIn = $worker['in'];
        $workerOut = $worker['out'];
        if ($workerIn != '') {
            $minuteIn = intval(explode(':', $workerIn)[1]) > 0 ? explode(':', $workerIn)[1] . '分' : '';
        }
        if ($workerOut != '') {
            $minuteOut = intval(explode(':', $workerOut)[1]) > 0 ? explode(':', $workerOut)[1] . '分' : '';
        }
        if ($worker['in2'] != '') {
            $minuteIn2 = intval(explode(':', $worker['in2'])[1]) > 0 ? explode(":", $worker['in2'])[1] . '分' : '';
        }
        if ($worker['out2'] != '') {
            $minuteOut2 = intval(explode(':', $worker['out2'])[1]) > 0 ? explode(":", $worker['out2'])[1] . '分' : '';
        }

        if (isset($worker['worker_name']) && $worker['shop_name'] != '') {
            echo '<tr><td colspan="2"><p> ・ ' .$worker['worker_name'] . 'ちゃん</p></td></tr>';
        }
        if ($worker['shop_name'] != '' && $workerIn != '' && $workerOut != '') {
            echo '<tr><td>（<b>' . $worker['shop_name'] . '</b>）' .explode('-', $worker['date'])[1] . '月' . explode('-', $worker['date'])[2] . '日（' . App::$app->formatter->asDayOfWeek(strtotime($worker['date'])) . '）' . explode(':',$workerIn)[0] . '時' . $minuteIn . '〜' .
                explode(':', $workerOut)[0] . '時' . $minuteOut;
            if ($worker['shop_name'] != $worker['shop_name_2']) {
                echo '</td><td><span style="padding-left: 2rem">Tel: ' .$worker['phone_number_1'] . '</span></td>';
            } else {
                echo '、';
            }
        }

        if ($worker['shop_name_2'] != '' && $worker['in2'] != '' && $worker['out2'] != '') {
            if ($worker['shop_name'] == $worker['shop_name_2']) {
                echo explode(':',$worker["in2"])[0] . '時' . $minuteIn2 . '〜' . explode(':', $worker['out2'])[0] . '時' . $minuteOut2 . '<td><span style="padding-left: 2rem">Tel: ' .$worker['phone_number_2'] . '</span></td></tr>';
            } else {
                echo '<td>' . '（<b>' . $worker['shop_name_2'] . '</b>）' .explode('-', $worker['date'])[1] . '月' . explode('-', $worker['date'])[2] . '日（' . App::$app->formatter->asDayOfWeek(strtotime($worker['date'])) . '）' . explode(':',$worker["in2"])[0] . '時' . $minuteIn2 . '〜' .
                    explode(':', $worker['out2'])[0] . '時' . $minuteOut2 . '</td><td><span style="padding-left: 2rem">Tel: ' .$worker['phone_number_2'] . '</span></td></tr>';
            }
        }
        echo '</tr>';
    }
    echo '</table>';

    echo '<br /> お客様のご予定の参考にお願いします。ご予約は前日予約をご利用ください。<br />
予定が変更されることもありますのでご了承ください。<br /><br /> ご不明点などあれば、お電話いただくか、予約システムのフォーラムに書き込んでいただければと思います。<br />
いつもご利用ありがとうございます。世界のあんぷり亭　店主 <br /><br><a href="'.\Yii::$app->params["site.frontend"].'/profile/delete-email'.'">受信を解除したい場合はこちら</a>';
}
