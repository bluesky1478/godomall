<table class="table table-rows">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th class="center">회원등급</th>
        <th class="center">할인금액</th>
    </tr>
    <?php foreach ($data['goodsDiscountGroupMemberInfo']['groupSno'] as $key => $value) { ?>
        <tr>
            <td class="center"><?php echo $groupList[$value]; ?></td>
            <td class="center">
                <?php
                if ($data['goodsDiscountGroupMemberInfo']['goodsDiscountUnit'][$key] == 'percent') {
                    echo '구매금액의 ' . $data['goodsDiscountGroupMemberInfo']['goodsDiscount'][$key] . '%';
                } else {
                    echo '구매수량별 ' . gd_money_format($data['goodsDiscountGroupMemberInfo']['goodsDiscount'][$key]) . gd_currency_default();
                }
                ?>
            </td>
        </tr>
    <?php } ?>
</table>