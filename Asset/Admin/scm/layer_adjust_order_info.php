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

<div class="table-responsive statistics-board mgt0">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width7p">주문일시</th>
            <th class="width5p">주문번호</th>
            <th class="width5p">주문자</th>
            <th class="width5p">상품주문번호</th>
            <th>주문상품</th>
            <th class="width3p">수량</th>
            <th class="width7p">금액</th>
            <th class="width5p">수수료</th>
            <th class="width5p">판매수수료</th>
            <th class="width7p">정산금액</th>
            <th class="width7p">총 정산금액</th>
            <th class="width5p">처리상태</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $key => $val) {
                $goodsPrice = $val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']); // 상품 주문 금액
                if ($val['addGoodsCnt'] > 0) {
                    $rowSpan = 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"';
                } else {
                    $rowSpan = '';
                }
                ?>
                <tr class="text-center">
                    <td <?= $rowSpan; ?>><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?></td>
                    <td <?= $rowSpan; ?>>
                        <a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="주문번호" target="_blank" class="btn btn-link"><?= $val['orderNo']; ?></a>
                    </td>
                    <td <?= $rowSpan; ?>><?= $val['orderName'] ?>
                        <p>
                            <?php if (!$val['memNo']) { ?>
                                (비회원)
                            <?php } else { ?>
                                (<?= $val['memId'] ?>)
                            <?php } ?>
                        </p>
                    </td>
                    <td <?= $rowSpan; ?>>
                        <a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="상품주문번호" target="_blank" class="btn btn-link"><?= $val['sno'] ?></a>
                    </td>
                    <td class="text-left">
                        <div class="goods_name one_line hand" title="주문 상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>',<?= gd_is_provider() == true ? 'true' : 'false' ?>);"><?= gd_html_cut(gd_remove_only_tag(stripslashes($val['goodsNm'])), 46, '..'); ?></div>
                        <?php
                        // 옵션 처리
                        if (empty($val['optionInfo']) === false) {
                            echo '<div class="option_info" title="상품 옵션">';
                            foreach ($val['optionInfo'] as $option) {
                                echo $option[0] . ':', $option[1] . ', ';
                            }
                            echo '</div>' . chr(10);

                        }

                        // 텍스트 옵션 처리
                        if (empty($val['optionTextInfo']) === false) {
                            echo '<div class="option_info" title="텍스트 옵션">';
                            foreach ($val['optionTextInfo'] as $option) {
                                echo $option[0] . ':', $option[1] . ', ';
                            }
                            echo '</div>' . chr(10);
                        }
                        ?>
                    </td>
                    <td class="goods_cnt"><?= number_format($val['goodsCnt']); ?></td>
                    <td><?= gd_currency_display($goodsPrice); ?></td>
                    <td><?= $val['commission']; ?>%</td>
                    <td><?= gd_currency_symbol() ?><?= gd_money_format($val['goodsAdjustCommission']); ?></span><?= gd_currency_string() ?></td>
                    <td><?= gd_currency_symbol() ?><?= gd_money_format($val['goodsAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                    <td <?= $rowSpan; ?>><?= gd_currency_symbol() ?><?= gd_money_format($val['totalAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                    <td <?= $rowSpan; ?>>
                        <div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div>
                    </td>
                </tr>
                <?php
                if ($val['addGoodsCnt'] > 0) {
                    foreach ($val['addGoods'] as $aVal) {
                        ?>
                        <tr class="text-center add-goods">
                            <td class="text-left">
                                <span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span>
                                    <span class="goods_name one_line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo']; ?>');"><?= gd_html_cut(gd_remove_only_tag(stripslashes($aVal['goodsNm'])), 46, '..'); ?>
                                        <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..'); ?>)</small>
                                    </span>
                            </td>
                            <td class="goods_cnt"><?= number_format($aVal['goodsCnt']); ?></td>
                            <td><?= gd_currency_display($aVal['goodsPrice'] * $aVal['goodsCnt']); ?></td>
                            <td><?= $aVal['commission']; ?>%</td>
                            <td><?= gd_currency_symbol() ?><?= gd_money_format($aVal['addGoodsAdjustCommission']); ?></span><?= gd_currency_string() ?></td>
                            <td><?= gd_currency_symbol() ?><?= gd_money_format($aVal['addGoodsAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                        </tr>
                        <?php
                    }
                }
            }
        } else {
            ?>
            <tr>
                <td colspan="12" class="no-data">
                    검색된 주문이 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

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
