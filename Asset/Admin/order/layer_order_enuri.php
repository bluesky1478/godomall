<form name="enuriForm" id="enuriForm" action="../order/order_change_ps.php" method="post" target="ifrmProcess">
<input type="hidden" name="mode" value="set_enuri_order_view" />
<input type="hidden" name="orderNo" value="<?=$orderNo?>" />
    <table class="table table-rows mgb5">
        <thead>
        <tr>
            <th>공급사</th>
            <th>상품 주문번호</th>
            <th>이미지</th>
            <th style="width: 200px;">주문상품</th>
            <th>수량</th>
            <th>상품금액</th>
            <th>총 상품금액</th>
            <th>상품 결제금액</th>
            <th style="width: 130px;">운영자 추가할인 금액</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data['goods']) === false) {
            $totalGoodsSettlePrice = $totalGoodsSumPrice = $goodsSumPrice = $totalGoodsCnt = $totalEnuriSumPrice = 0;
            foreach ($data['goods'] as $sKey => $sVal) {
                $rowScm = 0;
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        $statusMode = substr($val['orderStatus'], 0, 1);
                        $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']);

                        $settlePrice = $val['settlePrice'] + $val['divisionGoodsDeliveryUseDeposit'] + $val['divisionGoodsDeliveryUseMileage'] + $val['enuri'];
                        $orderScmRowSpan = ' rowspan="' . ($orderData['cnt']['scm'][$sKey]) . '"';
                        $totalGoodsCnt += $val['goodsCnt'];
                        $goodsSumPrice += $goodsPrice;
                        $totalGoodsSumPrice += ($goodsPrice*$val['goodsCnt']);
                        $totalGoodsSettlePrice += $settlePrice;
                        $totalEnuriSumPrice += $val['enuri'];
        ?>
                        <tr>
                            <?php if ($rowScm === 0) { ?>
                            <td class="text-center" <?= $orderScmRowSpan; ?>><?= $val['companyNm'] ?></td>
                            <?php } ?>
                            <td class="text-center">
                                <input type="hidden" name="orderGoodsSno[<?= $val['sno'] ?>]" value="<?= $val['sno'] ?>" />
                                <?= $val['sno'] ?>
                            </td>
                            <td class="text-center">
                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                    <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                <?php } else { ?>
                                    <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                <?php } ?>
                            </td>
                            <td style="word-break: break-all;">
                                <?php if($statusMode === 'e'){ ?>
                                    <span class="label label-danger">교환취소</span><br />
                                <?php } else if ($statusMode === 'z'){ ?>
                                    <span class="label label-primary">교환추가</span><br />
                                <?php } ?>

                                <?php if ($val['goodsType'] === 'addGoods') { ?>
                                    <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                    <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                       onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 24, '..'); ?></a>
                                <?php } else { ?>
                                    <a href="javascript:void();" class="one-line bold mgb5" title="상품명"
                                       onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 24, '..'); ?></a>
                                <?php } ?>
                                <?php
                                // 옵션 처리
                                if (empty($val['optionInfo']) === false) {
                                    echo '<div class="option_info" title="상품 옵션">';
                                    foreach ($val['optionInfo'] as $option) {
                                        $tmpOption[] = $option['optionName'] . ':' . $option['optionValue'];
                                    }
                                    echo implode(', ', $tmpOption);
                                    echo '</div>';
                                    unset($tmpOption);
                                }

                                // 텍스트 옵션 처리
                                if (empty($val['optionTextInfo']) === false) {
                                    echo '<div class="option_info" title="텍스트 옵션">';
                                    foreach ($val['optionTextInfo'] as $option) {
                                        $tmpOption[] = $option['optionName'] . ':' . $option['optionValue'];
                                    }
                                    echo implode(', ', $tmpOption);
                                    echo '</div>';
                                    unset($tmpOption);}
                                ?>
                            </td>
                            <td class="text-center"><?= number_format($val['goodsCnt']); ?></td>
                            <td class="text-center"><?= gd_currency_display($goodsPrice); ?></td>
                            <td class="text-center"><?= gd_currency_display($goodsPrice*$val['goodsCnt']); ?></td>
                            <td class="text-center"><?= gd_currency_display($settlePrice); ?></td>
                            <td>
                                <input type="text" name="enuri[<?= $val['sno'] ?>]" class="form-control input-small js-number" value="<?=$val['enuri']?>" data-price="<?= $settlePrice; ?>" />
                            </td>
                        </tr>
        <?php
                    }
                }
                $rowScm++;
            }
        }
        ?>
        </tbody>
        <?php if (empty($data['goods']) === false) { ?>
            <tfoot>
            <tr class="text-center">
                <th colspan="3"></th>
                <th class="text-center">합계</th>
                <th class="text-center"><?= number_format($totalGoodsCnt); ?></th>
                <th class="text-center"><?= gd_currency_display($goodsSumPrice); ?></th>
                <th class="text-center"><?= gd_currency_display($totalGoodsSumPrice); ?></th>
                <th class="text-center"><?= gd_currency_display($totalGoodsSettlePrice); ?></th>
                <th>
                    <input type="text" name="totalEnuriSumPrice" class="form-control input-small js-number" value="<?=$totalEnuriSumPrice?>" disabled="disabled" />
                </th>
            </tr>
            </tfoot>
        <?php } ?>
    </table>

    <div class="mgb20 notice-info">운영자 추가 할인 금액은 입력된 금액만큼 상품 결제금액에서 차감되며, 결제정보의 총 할인금액에서 확인할 수 있습니다.</div>

    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="submit" class="btn btn-lg btn-black">확인</button>
    </div>
</form>

<script>
    $(document).ready(function () {
        $("input[name*='enuri']").change(function(){
            var sumEnuri = 0;
            $("input[name*='enuri']").each(function(){
                if($(this).val() === ''){
                    $(this).val(0);
                }
                if(Number($(this).val()) > Number($(this).attr('data-price'))){
                    $(this).val($(this).attr('data-price'));
                    alert('상품결제금액보다 클 수 없습니다.');
                }
                sumEnuri += parseInt($(this).val());
            });
            $("input[name='totalEnuriSumPrice']").val(sumEnuri);
        });

        //취소버튼
        $('.js-layer-close').click(function(){
            layer_close();
        });
    });
</script>
