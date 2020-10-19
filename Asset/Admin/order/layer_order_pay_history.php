<table class="table table-rows">
    <thead>
    <tr>
        <th>일자</th>
        <th>구분</th>
        <th>상품 판매금액</th>
        <th>배송비</th>
        <th>할인금액</th>
        <th>부가결제금액</th>
        <th>결제금액</th>
    </tr>
    </thead>
    <?php
    if (empty($payHistory) === false) {
        foreach ($payHistory as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?php echo gd_date_format('Y-m-d H:i', $val['regDt']); ?></td>
                <td>
                    <?php
                    switch ($val['type']) {
                        case 'fs'://최초결제
                            echo '최초결제';
                            break;
                        case 'pc'://부분취소
                            echo '부분취소';
                            break;
                        case 'ac'://전체취소
                            echo '전체취소';
                            break;
                        case 'pr'://부분환불
                            echo '부분환불';
                            break;
                        case 'ar'://전체환불
                            echo '전체환불';
                            break;
                    }
                    ?>
                </td>
                <td><?=gd_currency_display($val['goodsPrice'])?></td>
                <td><?=gd_currency_display($val['deliveryCharge'])?></td>
                <td><?=gd_currency_display($val['dcPrice'])?></td>
                <td><?=gd_currency_display($val['addPrice'])?></td>
                <td><?=gd_currency_display($val['settlePrice'])?></td>
            </tr>
            <?php
        }
    }
    ?>
</table>
<div class="text-center">
    <button type="button" class="btn btn-lg btn-black js-layer-close">닫기</button>
</div>
