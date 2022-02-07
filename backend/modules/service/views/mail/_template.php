<?php
use backend\modules\service\forms\mail\MailTemplateSearchForm;

/**
 * @var $this \backend\models\BackendView
 * @var $model MailTemplateSearchForm
 */
$this->subTitle = App::t("backend.service_mail.title", "メールテンプレート");
?>
<table class="table table-striped table-advance table-hover">
    <thead>
    </thead>
    <tbody>
    <?php foreach (MailTemplateSearchForm::getListTemplates() as $key => $data) { ?>
        <tr class="unread" data-key="<?php echo $key; ?>" data-click-url=<?php echo App::$app->urlManager->createUrl([
            "service/mail",
            "type" => "template-update",
            "key" => $key,
        ]) ?>>
            <td class="view-message">
                <?php echo $key; ?>
            </td>
            <td class="view-message">
                <?php echo $data["title"]; ?>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
