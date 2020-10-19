<div>
    <table class="table table-rows table-fixed">
        <thead>
        <tr>
            <th class="width10p">번호</th>
            <th class="width90p"><?= $getData['title']; ?></th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($getData['data']) && is_array($getData['data'])) {
            $i = 1;
            foreach ($getData['data'] as $key => $val) {
                ?>
                <tr>
                    <td class="center"><?= $i++; ?></td>
                    <td><a href="../goods/goods_register.php?goodsNo=<?= $val['no']; ?>" target="_blank"><?= $val['name']; ?></a></td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
</div>
