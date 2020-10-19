<table class="table table-rows">
    <thead>
    <tr>
        <th class="width5p">교환상태</th>
        <th class="width5p">교환일</th>
        <th class="width8p">처리상태</th>
        <th class="width8p">공급사</th>
        <th class="width-3xs">이미지</th>
        <th>상품</th>
        <th class="width5p"><?=$data['handleModeStr']?>수량</th>
        <th class="width8p"><?=$data['handleModeStr']?>사유</th>
        <th class="width10p">상세사유</th>
        <th class="width5p">수정</th>
        <th class="width8p">차액</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($data['goods']) === true) {
        $settlePrice = 0;// 상품가격
        foreach ($data['goods'] as $handleGroupCd => $handleModeArr) {
            $rowHandle = 0;
            foreach ($handleModeArr as $statusModeKey => $statusModeArr) {
                $rowPart = 0;
                foreach ($statusModeArr as $key => $val) {
                    // 주문상태 모드
                    $statusMode = substr($val['orderStatus'], 0, 1);
                    $orderPartRowSpan = ' rowspan="' . count($data['goods'][$handleGroupCd][$statusModeKey]) . '"';
                    ?>
                    <tr class="text-center">
                        <?php if ($rowPart === 0) { ?>
                            <td style="background-color: #F6F6F6;" <?= $orderPartRowSpan ?>>
                                <span class="label label-danger" title="<?= $val['sno'] ?>">교환취소</span>
                            </td>
                            <td class="center" <?= $orderPartRowSpan ?>>
                                <?= $val['handleRegDt']; ?>
                            </td>
                        <?php } ?>
                        <td class="center"><?= $val['orderStatusStr']; ?></td>
                        <td class="border-left text-center"><?= $val['companyNm']; ?></td>
                        <td>
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } else { ?>
                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } ?>
                        </td>
                        <td class="text-left">
                            <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
                                <p class="mgt5"><img
                                            src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderGoodsNo']; ?>
                                </p>
                            <?php } ?>

                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 56, '..'); ?></a>
                            <?php } else { ?>
                                <a href="javascript:void()" class="one-line" title="상품명"
                                   onclick="goods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 56, '..'); ?></a>
                            <?php } ?>

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
                            <strong><?= number_format($val['goodsCnt']); ?></strong>
                        </td>
                        <td class="text-center">
                            <div class="toggleHandleReason">
                                <span class="off"><?= $val['handleReason']; ?></span>
                                <span class="on" style="display:none;">
                                <?=gd_select_box('claimHandleReason', 'claim[handleReason][' . $val['handleSno'] . ']', $refundReason, null, $val['handleReason'], null)?>
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <input type="hidden" name="claim[handleSno][<?= $val['handleSno'] ?>]" value="<?= $val['handleSno'] ?>">
                            <div class="toggleHandleDetailReason">
                                <span class="off"><?= $val['handleDetailReason']; ?></span>
                                <span class="on" style="display:none;">
                                    <input type="text" name="claim[handleDetailReason][<?= $val['handleSno'] ?>]" value="<?= $val['handleDetailReason']; ?>" />
                                </span>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php if ($val['handleCompleteFl'] === 'n' && $statusMode === 'e') { ?>
                                <button type="button" class="btn btn-sm btn-gray js-claim-modify js-claim-act-modify">수정</button>
                                <button type="button" class="btn btn-sm btn-gray js-claim-modify js-claim-act-complete display-none">완료</button>
                            <?php } ?>
                        </td>
                        <?php if($rowHandle === 0){ ?>
                        <td class="text-center" <?=$orderPartRowSpan?>>
                            <!-- 차액 -->
                            <strong><?=((float)$val['exchangeHandle']['ehDifferencePrice']) ? gd_currency_display($val['exchangeHandle']['ehDifferencePrice']*-1) : gd_currency_display(0)?></strong>
                            <?php if((float)$val['exchangeHandle']['ehDifferencePrice']){ ?>
                                <p>
                                    <button type="button" data-exchange-handleSno="<?=$val['exchangeHandle']['sno']?>" class="btn btn-sm btn-white mgt5 js-exchange-price-detail">상세보기</button>
                                </p>
                            <?php } ?>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php
                    $rowHandle++;
                    $rowPart++;
                }
            }
        }
    } else {
        ?>
        <tr>
            <td class="no-data" colspan="10"><?=$data['incTitle']?>가 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<script type="text/javascript">
    <!--
    $(function(){
        $(document).on("click", ".js-exchange-price-detail", function () {
            var exchnageHandleSno = $(this).attr('data-exchange-handleSno');
            $.post('layer_order_exchange_price_detail.php', {
                orderNo: '<?= gd_isset($data['orderNo']);?>',
                handleSno: exchnageHandleSno
            }, function (data) {
                layer_popup(data, '차액 상세정보', 'wide');
            });
        });

        $('.js-claim-modify').click(function(e){
            if ($('#frmOrder').data('lock-write') == '1') {
                alert('주문 상세정보의 쓰기 권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.');
                return false;
            }

            var self = $(this);
            var selfClosest = self.closest('tr');
            var toggleEle = [
                selfClosest.find('.toggleHandleReason'),
                selfClosest.find('.toggleHandleDetailReason'),
                selfClosest.find('.toggleRefundMethod'),
                selfClosest.find('.toggleRefundBank')
            ];

            if(self.hasClass('js-claim-act-modify')){
                //수정클릭시
                self.addClass("display-none");
                selfClosest.find(".js-claim-act-complete").removeClass("display-none");
            }
            else {
                var hadleSnoObj = selfClosest.find("input[name*='claim[handleSno]']");
                var handleReasonObj = selfClosest.find("#claimHandleReason option:selected");
                var handleDetailReasonObj = selfClosest.find("input[name*='claim[handleDetailReason]']");

                //완료클릭시
                $.post('../order/order_ps.php', {
                    mode : 'update_order_handle_reason',
                    handleSno : hadleSnoObj.val(),
                    handleReason : handleReasonObj.val(),
                    handleDetailReason : handleDetailReasonObj.val()
                }, function (data) {
                    if(parseInt(data) === 1){
                        alert("수정되었습니다.");
                        selfClosest.find(".js-claim-act-modify").removeClass("display-none");

                        selfClosest.find('.toggleHandleReason').find('.off').html(handleReasonObj.text());
                        selfClosest.find('.toggleHandleDetailReason').find('.off').html(handleDetailReasonObj.val());
                    }
                    else {
                        alert("실패하였습니다.");
                    }
                    self.addClass("display-none");
                    selfClosest.find(".js-claim-act-modify").removeClass("display-none");
                });
            }

            $.each(toggleEle, function(idx, obj){
                if (obj.find('.off').css('display') != 'none') {
                    obj.find('.off').hide();
                    obj.find('.on').show();
                } else {
                    obj.find('.off').show();
                    obj.find('.on').hide();
                }
            });
        });
    });
    //-->
</script>
