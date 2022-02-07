<?php
use yii\helpers\Html;
?>
<table class="table">
    <thead>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Tên khách hàng</th>
        <th scope="col">Thái độ phục vụ</th>
        <th scope="col">Dịch vụ</th>
        <th scope="col">Kỹ thuật</th>
        <th scope="col">Chi phí</th>
        <th scope="col">Mức độ hài lòng</th>
        <th scope="col">Ghi chú</th>
        <th scope="col">Ngày đánh giá</th>
    </tr>
    </thead>
    <tbody>
    <?php if ($data): ?>
        <?php foreach ($data as $key => $value): ?>
            <tr>
                <th scope="row"><?php echo ++$key ?></th>
                <td>
                    <a href="/member/manage/view?id=<?php echo $value['user_id'] ?>"><?php echo $value['full_name'] ?></a>
                </td>
                <td style="text-align: center"><?php echo $value['behavior'] ?></td>
                <td style="text-align: center"><?php echo $value['service'] ?></td>
                <td style="text-align: center"><?php echo $value['technique'] ?></td>
                <td style="text-align: center"><?php echo $value['price'] ?></td>
                <td style="text-align: center"><?php echo $value['satisfaction'] ?></td>
                <td style="text-align: center"><?php echo $value['memo'] ?></td>
                <td style="text-align: center"><?php echo $value['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else:?>
    <tr>
        <td>Không có đánh giá nào</td>
    </tr>
    </tbody>
    <?php endif; ?>
</table>
<?php if ($data):?>
    <?php echo Html::a(
        App::t('backend.worker.label', 'Chi tiết'),
        App::$app->urlManager->createUrl(["worker/manage/rating", "WorkerRatingHistoryForm[filter_worker_id]" => $id]),
        ['class' => 'btn btn-primary',]
    );?>
<?php endif; ?>



