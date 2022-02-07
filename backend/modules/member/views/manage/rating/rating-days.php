<table class="table">
    <thead-light>
    <tr>
        <th scope="col">#</th>
        <th scope="col">Ngày đánh giá</th>
        <th scope="col">Thái độ phục vụ</th>
        <th scope="col">Dịch vụ</th>
        <th scope="col">Kỹ thuật</th>
        <th scope="col">Giá cả</th>
        <th scope="col">Mức độ hài lòng</th>
        <th scope="col">Ghi chú</th>
    </tr>
    </thead-light>
    <tbody>
    <?php if ($data): ?>
        <?php foreach ($data as $key => $value): ?>
            <tr>
                <th scope="row"><?php echo ++$key ?></th>
                <td><?php echo $value['created_at'] ?></td>
                <td><?php echo $value['behavior'] ?></td>
                <td><?php echo $value['service'] ?></td>
                <td><?php echo $value['technique'] ?></td>
                <td><?php echo $value['price'] ?></td>
                <td><?php echo $value['satisfaction'] ?></td>
                <td><?php echo $value['memo'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else:?>
    <tr>
        <td>Không có đánh giá nào</td>
    </tr>
    </tbody>
    <?php endif; ?>
</table>