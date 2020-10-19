<div class="well well-sm">
    선택된 상품의 재고를 환원 하시겠습니까?
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th class="width3p" rowspan="2">번호</th>
        <th class="width5p" rowspan="2">사유</th>
        <th class="width5p" rowspan="2">상품<br/>주문번호</th>
        <th class="width-3xs" rowspan="2">이미지</th>
        <th>주문상품</th>
        <th class="width5p" rowspan="2">수량</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($data['goods']) === true) {
        $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
        $settlePrice = 0;// 결제금액
        $totalSettlePrice = 0; // 전체 결제금액 (결제금액 + 배송비)
        foreach ($data['goods'] as $sKey => $sVal) {
            $rowScm = 0;
            foreach ($sVal as $dKey => $dVal) {
                $rowDelivery = 0;
                foreach ($dVal as $key => $val) {
                    $statusMode = substr($val['orderStatus'], 0, 1);

                    // rowspan 처리
                    $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                    $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';
                    $orderDeliveryRowSpan = ' rowspan="' . ($data['cnt']['delivery'][$dKey]) . '"';
                    ?>
                    <tr id="statusCheck_<?= $statusMode ?>_<?= $key ?>" class="text-center">
                        <td <?= $orderAddGoodsRowSpan ?>><?= $sortNo ?></td>
                        <td <?= $orderAddGoodsRowSpan ?>><?= $val['handleReason'] ?></td>
                        <td <?= $orderAddGoodsRowSpan ?>><?= $val['sno'] ?></td>
                        <td>
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } else { ?>
                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } ?>
                        </td>
                        <td class="text-left">
                            <div class="goods_name hand text-primary" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo'] ?>');">
                                <?= $val['goodsNm'] ?>
                            </div>
                            <div class="info">
                                <?php
                                // 상품 코드
                                if (empty($val['goodsCd']) === false) {
                                    echo '<div class="font-kor" title="상품코드">[' . $val['goodsCd'] . ']</div>';
                                }

                                // 옵션 처리
                                if (empty($val['optionInfo']) === false) {
                                    foreach ($val['optionInfo'] as $oKey => $oVal) {
                                        echo '<dl class="dl-horizontal" title="옵션명">';
                                        echo '<dt>' . $oVal['optionName'] . ' :</dt>';
                                        echo '<dd>' . $oVal['optionValue'] . '</dd>';
                                        echo '</dl>';
                                    }
                                }

                                // 텍스트 옵션 처리
                                if (empty($val['optionTextInfo']) === false) {
                                    foreach ($val['optionTextInfo'] as $oKey => $oVal) {
                                        echo '<ul class="list-unstyled" title="텍스트 옵션명">';
                                        echo '<li>' . $oVal['optionName'] . ' :</li>';
                                        echo '<li>' . $oVal['optionValue'] . ' ';
                                        if ($oVal['optionTextPrice'] > 0) {
                                            echo '<span>(추가금 ' . gd_currency_display($oVal['optionTextPrice']) . ')</span>';
                                        }
                                        echo '</li>';
                                        echo '</ul>';
                                    }
                                }
                                ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="returnStock[]" value="<?= $val['handleSno'] ?>">
                            <?= number_format($val['goodsCnt']) ?>
                        </td>
                    </tr>
                    <?php
                    if ($val['addGoodsCnt'] > 0) {
                        foreach ($val['addGoods'] as $aVal) {
                            ?>
                            <tr class="text-center add-goods">
                                <td class="text-center">
                                    <span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span></td>
                                <td class="text-left">
                                    <?= gd_html_add_goods_image($aVal['addGoodsNo'], $aVal['imageNm'], $aVal['imagePath'], $aVal['imageStorage'], 30, $aVal['goodsNm'], '_blank'); ?>
                                    <div class="goods_name one-line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo'] ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..') ?>
                                        <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..') ?>)</small>
                                    </div>
                                </td>
                                <td class="goods_cnt"><?= number_format($aVal['goodsCnt']) ?></td>
                            </tr>
                            <?php
                        }
                    }
                    $sortNo--;
                    $rowDelivery++;
                    $rowScm++;
                }
            }
        }
    }
    ?>
    </tbody>
</table>

<div class="text-center">
    <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
    <button type="button" class="btn btn-lg btn-black js-stock-cancel">적용안함</button>
    <button type="button" class="btn btn-lg btn-black js-stock-apply">적용</button>
</div>

<script type="text/javascript">
    $(function () {
        // 적용 할 경우
        $('.js-stock-apply').bind('click', function (e) {
            $('input[name="returnStock[]"]').each(function (idx) {
                // 환불접수 건만 처리
                var handleSno = $(this).val();
                if (handleSno > 0) {
                    $('#frmRefund input[name="refund[' + handleSno + '][returnStock]"]').val((e.canceled ? 'n' : 'y'));
                }
            });

            // 조건에 따라 레이어 닫기
            if (_.isUndefined(e.closed) || e.closed) {
                layer_close();
            }
        });

        // 적용안 할 경우
        $('.js-stock-cancel').bind('click', function (e) {
            $('.js-stock-apply').trigger({
                type: 'click',
                canceled: true,
                closed: true
            });
        });

        // 재고 초기화
        $('.js-stock-apply').trigger({
            type: 'click',
            canceled: true,
            closed: false
        });
    });
</script>
