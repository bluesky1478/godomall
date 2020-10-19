<!-- 주의! 본사, 공급사 별도 수정 필요 -->
<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>정산요청번호</th>
                <td colspan="3"><?= $scmAdjustData['scmAdjustCode']; ?></td>
            </tr>
            <tr>
                <th>공급사</th>
                <td colspan="3"><?= $convertGetData[0]['scm']['name']; ?></td>
            </tr>
            <tr>
                <th>요청타입</th>
                <td><?= $convertGetData[0]['scmAdjustType']; ?></td>
                <th>정산타입</th>
                <td><?= $convertGetData[0]['scmAdjustKind']; ?></td>
            </tr>
            <tr>
                <th>정산요청금액</th>
                <td><?= gd_currency_symbol() . ' ' . gd_money_format($scmAdjustData['scmAdjustPrice']) . ' ' . gd_currency_string(); ?></td>
                <th>처리상태</th>
                <td><?= $convertGetData[0]['scmAdjustState']; ?></td>
            </tr>
            <tr>
                <th>요청일</th>
                <td><?= $scmAdjustData['regDt']; ?></td>
                <th>처리일</th>
                <td><?= $scmAdjustData['modDt']; ?></td>
            </tr>
        </table>
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th class="width5p">주문일시</th>
        <th class="width10p">주문번호</th>
        <th class="width7p">주문자</th>
        <th class="width5p">배송번호</th>
        <th class="width7p">배송비</th>
        <th class="width7p">수수료</th>
        <th class="width7p">배송수수료</th>
        <th class="width7p">정산금액</th>
        <th class="width7p">처리상태</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false && is_array($data)) {
        foreach ($data as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?></td>
                <td>
                    <a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="주문번호" target="_blank" class="btn btn-link"><?= $val['orderNo']; ?></a>
                </td>
                <td>
                    <?= $val['orderName'] ?>
                    <p>
                        <?php if (!$val['memNo']) { ?>
                            (비회원)
                        <?php } else { ?>
                            (<?= $val['memId'] ?>)
                        <?php } ?>
                    </p>
                </td>
                <td class="border-left font-num"><?= $val['deliverySno'] ?></td>
                <td><?= gd_currency_display($val['deliveryCharge']); ?></td>
                <td><?= $val['commission']; ?>%</td>
                <td><?= gd_currency_symbol() ?><?= gd_money_format($val['deliveryAdjustCommission']); ?></span><?= gd_currency_string() ?></td>
                <td><?= gd_currency_symbol() ?><?= gd_money_format($val['deliveryAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                <td>
                    <div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div>
                </td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="20" class="no-data">
                검색된 배송비가 없습니다.
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<table class="table table-rows">
    <thead>
    <tr>
        <th class="width10p">처리일자</th>
        <th class="width10p">처리자</th>
        <th class="width10p">처리상태</th>
        <th>내용</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($dataLog) === false && is_array($dataLog)) {
        foreach ($dataLog as $keyLog => $valLog) {
            ?>
            <tr class="text-center">
                <td><?= str_replace(' ', '<br>', $valLog['regDt']); ?></td>
                <td><?= $valLog['managerNm'] ?> / <?= $valLog['managerId'] ?></td>
                <td><?= $convertGetLogData[$keyLog]['scmAdjustState'] ?></td>
                <td class="text-left"><?= $valLog['scmAdjustMemo'] ?></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="4" class="no-data">
                로그가 없습니다.
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
