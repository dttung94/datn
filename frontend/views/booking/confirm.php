<?php
use common\helper\HtmlHelper;
use frontend\assets\MemberAsset;

?>

<div>
    <div class="login-form" style="text-align: center">
        <?php
            echo HtmlHelper::a(App::t("frontend.shop.button", "取り消す", []), ["booking-reject", 'id' => $id, 'accessToken' => $accessToken, 'showSlot' => true], [
                "class" => "btn btn-danger"
            ])
        ?>
        <?php
            echo HtmlHelper::a(App::t("frontend.shop.button", "同意する", []), ["booking-accept", 'id' => $id, 'accessToken' => $accessToken], [
                "class" => "btn btn-primary"
            ])
        ?>
    </div>
</div>
