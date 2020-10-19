<table class="table table-rows">
    <thead>
    <tr>
        <th class="width3p">번호</th>
        <th class="width5p">처리상태</th>
        <th class="width8p">공급사</th>
        <th class="width-3xs">이미지</th>
        <th>상품</th>
        <th class="width5p"><?=$data['handleModeStr']?>수량</th>
        <th class="width8p"><?=$data['handleModeStr']?>사유</th>
        <th class="width10p">상세사유</th>
        <?php if (in_array($data['statusMode'], ['b', 'r'])) { ?>
        <th class="width8p">환불수단</th>
        <th class="width-2xl">환불계좌</th>
        <?php } ?>
        <?php if ($data['statusMode'] !== 'c') { ?>
        <th class="width5p">수정</th>
        <?php } ?>
    </tr>
    </thead>
    <tbody>
    <?php
    if (isset($data['goods']) === true) {
        $sortNo = $data['cnt']['goods']['goods'];// 번호 설정
        $settlePrice = 0;// 상품가격
        foreach ($data['goods'] as $sKey => $sVal) {
            $rowScm = 0;
            foreach ($sVal as $dKey => $dVal) {
                $rowDelivery = 0;
                foreach ($dVal as $key => $val) {
                    // 주문상태 모드
                    $statusMode = substr($val['orderStatus'], 0, 1);

                    // rowspan 처리
                    $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                    $orderScmRowSpan = ' rowspan="' . ($data['cnt']['scm'][$sKey]) . '"';
                    $orderDeliveryRowSpan = ' rowspan="' . ($data['cnt']['delivery'][$dKey]) . '"';
                    ?>
                    <tr class="text-center">
                        <td <?= $orderAddGoodsRowSpan; ?>><?= $sortNo ?></td>
                        <td <?= $orderAddGoodsRowSpan; ?> class="center">
                            <?php if ($val['beforeStatusStr'] && $statusMode == 'r') { ?>
                                <div class="text-muted" title="이전 상품별 주문 상태"><?= $val['beforeStatusStr']; ?> &gt;</div>
                            <?php } ?>
                            <p><?= $val['orderStatusStr']; ?></p>
                        </td>
                        <?php if ($rowScm == 0) { ?>
                            <td <?=$orderScmRowSpan?> class="border-left text-center"><?= $val['companyNm']; ?></td>
                        <?php } ?>
                        <td>
                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                                <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } else { ?>
                                <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            <?php } ?>
                        </td>
                        <td class="text-left">
                            <?php if ($data['orderChannelFl'] == 'naverpay') { ?>
                                <p class="mgt5"><img src="<?=UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www()?>" /> <?= $val['apiOrderGoodsNo']; ?></p>
                            <?php } ?>

                            <?php if ($val['goodsType'] === 'addGoods') { ?>
                            <span class="label label-default" title="<?= $val['sno'] ?>">추가</span>
                                <a href="javascript:void();" class="one-line bold mgb5" title="추가상품명"
                                   onclick="addgoods_register_popup('<?= $val['goodsNo']; ?>', <?= $isProvider ? 'true' : 'false' ?>);"><?= gd_html_cut($val['goodsNm'], 56, '..'); ?></a>
                            <?php } else { ?>
                                <a href="javascript:void()" class="one-line" title="상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>');"><?= gd_html_cut($val['goodsNm'], 56, '..'); ?></a>
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
                        <td <?= $orderAddGoodsRowSpan; ?> class="text-center">
                            <div class="toggleHandleReason">
                                <span class="off"><?= $val['handleReason']; ?></span>
                                <span class="on" style="display:none;">
                                <?php
                                if ($data['statusMode'] === 'b') {
                                    echo gd_select_box('claimHandleReason', 'claim[handleReason][' . $val['handleSno'] . ']', $backReason, null, $val['handleReason'], null);
                                } else {
                                    echo gd_select_box('claimHandleReason', 'claim[handleReason][' . $val['handleSno'] . ']', $refundReason, null, $val['handleReason'], null);
                                }
                                ?>
                                </span>
                            </div>
                        </td>
                        <td <?= $orderAddGoodsRowSpan; ?> class="text-center">
                            <input type="hidden" name="claim[handleSno][<?=$val['handleSno']?>]" value="<?=$val['handleSno']?>">
                            <div class="toggleHandleDetailReason">
                                <span class="off"><?= $val['handleDetailReason']; ?></span>
                                <span class="on" style="display:none;"><input type="text" name="claim[handleDetailReason][<?=$val['handleSno']?>]" value="<?= $val['handleDetailReason']; ?>"></span>
                            </div>
                        </td>
                        <?php if (in_array($data['statusMode'], ['b', 'r'])) { ?>
                        <td <?= $orderAddGoodsRowSpan; ?> class="text-center">
                            <div class="toggleRefundMethod">
                                <span class="off"><?= $val['refundMethod']; ?></span>
                                <span class="on" style="display:none;">
                                <?= gd_select_box('claimRefundMethod', 'claim[refundMethod][' . $val['handleSno'] . ']', $refundMethod, null, $val['refundMethod'], null,$claimElementDisabled); ?>
                                </span>
                            </div>
                        </td>
                        <td <?= $orderAddGoodsRowSpan; ?> class="text-center nowrap">
                            <div class="toggleRefundBank">
                                <span class="off">
                                    <?= $val['refundBankName']; ?>
                                    <?= $val['refundAccountNumber']; ?>
                                    <?= $val['refundDepositor']; ?>
                                </span>
                                <span class="on form-inline" style="display:none;">
                                    <?= gd_select_box('claimRefundBankName', 'claim[refundBankName][' . $val['handleSno'] . ']', $bankNm, null, $val['refundBankName'], '= 은행 선택 =',$claimElementDisabled); ?>
                                    <input type="text" name="claim[refundAccountNumber][<?=$val['handleSno']?>]" value="<?= $val['refundAccountNumber']; ?>" class="form-control width-md" placeholder="계좌번호" <?=$claimElementDisabled?>/>
                                    <input type="text" name="claim[refundDepositor][<?=$val['handleSno']?>]" value="<?= $val['refundDepositor']; ?>" placeholder="예금주" class="form-control width-2xs" <?=$claimElementDisabled?>/>
                                </span>
                            </div>
                        </td>
                        <?php } ?>
                        <?php if ($data['statusMode'] !== 'c') { ?>
                        <td <?= $orderAddGoodsRowSpan; ?> class="text-center">
                            <?php if ($val['handleCompleteFl'] === 'n') { ?>
                                <button type="button" class="btn btn-sm btn-gray js-claim-modify js-claim-act-modify">수정</button>
                                <button type="button" class="btn btn-sm btn-gray js-claim-modify js-claim-act-complete display-none">완료</button>
                            <?php } ?>
                        </td>
                        <?php } ?>
                    </tr>
                    <?php
                    if ($val['addGoodsCnt'] > 0) {
                        foreach ($val['addGoods'] as $aVal) {
                            ?>
                            <tr id="addStatusCheck_<?= $statusMode; ?>_<?= $val['sno']; ?>" class="text-center add-goods">
                                <td class="text-center"><span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span></td>
                                <td class="text-left">
                                    <?= gd_html_add_goods_image($aVal['addGoodsNo'], $aVal['imageNm'], $aVal['imagePath'], $aVal['imageStorage'], 30, $aVal['goodsNm'], '_blank'); ?>
                                    <a href="javascript:void()" class="one-line" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo']; ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..'); ?>
                                        <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..'); ?>)</small>
                                    </a>
                                </td>
                                <td class="goods_cnt"><?= number_format($aVal['goodsCnt']); ?></td>
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
    } else {
        ?>
        <tr>
            <td class="no-data" colspan="25"><?=$data['incTitle']?>가 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<script type="text/javascript">
    <!--
    $(function(){
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
                var refundMethodObj = selfClosest.find("#claimRefundMethod option:selected");
                var refundBankNameObj = selfClosest.find("#claimRefundBankName option:selected");
                var refundAccountNumberObj = selfClosest.find("input[name*='claim[refundAccountNumber]']");
                var refundDepositorObj = selfClosest.find("input[name*='claim[refundDepositor]']");

                //완료클릭시
                $.post('../order/order_ps.php', {
                    mode : 'update_order_handle_reason',
                    handleSno : hadleSnoObj.val(),
                    handleReason : handleReasonObj.val(),
                    handleDetailReason : handleDetailReasonObj.val(),
                    refundMethod : refundMethodObj.val(),
                    refundBankName : refundBankNameObj.val(),
                    refundAccountNumber : refundAccountNumberObj.val(),
                    refundDepositor : refundDepositorObj.val()
                }, function (data) {
                    if(parseInt(data) === 1){
                        alert("수정되었습니다.");
                        selfClosest.find(".js-claim-act-modify").removeClass("display-none");

                        selfClosest.find('.toggleHandleReason').find('.off').html(handleReasonObj.text());
                        selfClosest.find('.toggleHandleDetailReason').find('.off').html(handleDetailReasonObj.val());
                        selfClosest.find('.toggleRefundMethod').find('.off').html(refundMethodObj.text());

                        var refundInfo = refundBankNameObj.text() + ' ' + refundAccountNumberObj.val() + ' ' + refundDepositorObj.val();
                        selfClosest.find('.toggleRefundBank').find('.off').html(refundInfo);
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
