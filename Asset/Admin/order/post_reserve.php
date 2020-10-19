<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
</div>

<!-- 검색을 위한 form -->
<form id="frmSearchOrder" method="get">
    <div class="table-header" style="border-top:0px;">
        <div class="pull-left">
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10,20,30,40,50,60,70,80,90,100,200,300,500,]), '개 보기', $page->page['list']); ?>
            </div>
        </div>
    </div>
</form>
<!-- // 검색을 위한 form -->

<form id="frmOrderStatus" action="./post_ps.php" method="post">
    <input type="hidden" name="mode" value="reserve"/>

    <div class="table-responsive">
        <table class="table table-rows order-list">
            <thead>
            <tr>
                <th class="width3p">
                    <input type="checkbox" value="y" class="js-checkall" data-target-name="statusCheck" />
                </th>
                <th class="width3p">번호</th>
                <th class="width5p">주문일시</th>
                <th class="width10p">주문번호</th>
                <th class="width7p">주문자</th>
                <th class="width5p">상품주문번호</th>
                <th class="width-4xs"></th>
                <th>주문상품</th>
                <th class="width7p">사은품</th>
                <th class="width7p">처리상태</th>
                <th class="width5p">배송번호</th>
                <th class="width10p">송장번호</th>
                <th class="width10p">공급사</th>
                <th class="width3p">수령자</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (empty($data) === false && is_array($data)) {
                $sortNo = 1; // 번호 설정
                $totalCnt = 0; // 주문서 수량 설정
                $totalGoods = 0; // 주문서 수량 설정
                $totalPrice = 0; // 주문 총 금액 설정
                foreach ($data as $orderNo => $orderData) {
                    $rowCnt = $orderData['cnt']['goods']['all']; // 한 주문당 상품주문 수량
                    $rowChk = 0; // 한 주문당 첫번째 주문 체크용
                    $rowAddChk = 0; //
                    $totalCnt++; // 주문서 수량
                    foreach ($orderData['goods'] as $sKey => $sVal) {
                        $rowScm = 0;
                        foreach ($sVal as $dKey => $dVal) {
                            $rowDelivery = 0;
                            foreach ($dVal as $key => $val) {

                                $totalGoods++; // 상품 수량
                                if ($key === 0) {
                                    $totalPrice = $totalPrice + $val['settlePrice']; // 주문 총 금액(누적)
                                }


                                $checkBoxCd = $orderNo . INT_DIVISION . $val['sno']. INT_DIVISION .$val['invoiceNo'];


                                // 주문일괄처리 제외대상 비활성화
                                if ($isUserHandle) {
                                    $checkDisabled = ($isUserHandle && $val['userHandleFl'] != 'r' ? 'disabled="disabled"' : '');
                                } else {
                                    if (in_array($currentStatusCode, ['b', 'e', 'r'])) {
                                        // 교환/반품/환불완료일 경우 체크 불가
                                        $checkDisabled = (in_array($val['orderStatus'], ['b4', 'e5', 'r3']) === false ? '' : 'disabled="disabled"');
                                    } else {
                                        $checkDisabled = (!gd_isset($statusExcludeCd, []) || in_array($val['statusMode'], $statusExcludeCd) === false ? '' : 'disabled="disabled"');
                                    }
                                }

                                // rowspan 처리
                                $orderGoodsRowSpan = $rowChk === 0 && $rowCnt > 1 ? 'rowspan="' . $rowCnt . '"' : '';
                                $orderAddGoodsRowSpan = $val['addGoodsCnt'] > 0 ? 'rowspan="' . ($val['addGoodsCnt'] + 1) . '"' : '';
                                $orderScmRowSpan = ' rowspan="' . ($orderData['cnt']['scm'][$sKey]) . '"';
                                $orderDeliveryRowSpan = ' rowspan="' . ($orderData['cnt']['delivery'][$dKey]) . '"';
                                ?>
                                <tr class="text-center">

                                        <td <?=$orderAddGoodsRowSpan?>>
                                            <input type="checkbox" name="statusCheck[]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>
                                        </td>

                                    <td <?=$orderAddGoodsRowSpan?> class="font-num">
                                        <small><?= $page->idx--; ?></small>
                                    </td>
                                    <?php if ($rowChk === 0) { ?>
                                        <td <?= $orderGoodsRowSpan; ?> class="font-date"><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?></td>
                                    <?php } ?>
                                    <?php if ($rowChk === 0) { ?>
                                        <td <?= $orderGoodsRowSpan; ?> class="order-no">
                                            <?php if ($val['firstSaleFl'] == 'y') { ?>
                                                <img src="<?=PATH_ADMIN_GD_SHARE?>img/order/icon_firstsale.png" alt="첫주문" />
                                            <?php } ?>
                                            <a href="./order_view.php?orderNo=<?= $orderNo; ?>" title="주문번호" target="_blank" class="btn btn-link font-num"><?= $orderNo; ?></a>

                                            <?php if ($val['orderChannelFl'] == 'naverpay') { ?>
                                                <p>
                                                    <a href="./order_view.php?orderNo=<?= $orderNo; ?>" target="_blank" title="주문번호" class="font-num<?=$isUserHandle ? ' js-link-order' : ''?>" data-order-no="<?=$orderNo?>" data-is-provider="<?= $isProvider ? 'true' : 'false' ?>"><img
                                                            src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www() ?>"/> <?= $val['apiOrderNo']; ?></a>
                                                </p>
                                            <?php } else if($val['orderChannelFl'] == 'payco') { ?>
                                                <p><img src="<?= UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'payco.gif')->www() ?>"/></p>
                                            <?php } ?>
                                        </td>
                                        <td <?= $orderGoodsRowSpan; ?> class="js-member-info" data-member-no="<?= $val['memNo'] ?>" data-member-name="<?=$val['orderName']?>" data-cell-phone="<?=$val['smsCellPhone']?>">
                                            <?= $val['orderName'] ?>
                                            <p>
                                                <?php if (!$val['memNo']) { ?>
                                                    <?php if (!$val['memNoCheck']) { ?>
                                                        <span class="font-kor">(비회원)</span>
                                                    <?php } else { ?>
                                                        <span class="font-kor">(탈퇴회원)</span>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <button type="button" class="btn btn-link font-eng js-layer-crm" data-member-no="<?= $val['memNo'] ?>">(<?= $val['memId'] ?>)</button>
                                                <?php } ?>
                                            </p>
                                        </td>
                                    <?php } ?>
                                    <td><a href="./order_view.php?orderNo=<?= $orderNo; ?>" title="상품주문번호" target="_blank" class="btn font-num btn-link"><?= $val['sno'] ?></a></td>
                                    <td class="text-left">
                                        <?php if ($val['goodsType'] === 'addGoods') { ?>
                                            <?= gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                        <?php } else { ?>
                                            <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                                        <?php } ?>
                                    </td>
                                    <td class="text-left">
                                        <div class="goods_name one-line hand" title="주문 상품명" onclick="goods_register_popup('<?= $val['goodsNo']; ?>');"><?= gd_html_cut($val['goodsNm'], 46, '..'); ?></div>
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
                                    <?php if ($rowScm === 0) { ?>
                                        <td <?= $orderScmRowSpan; ?> class="font-kor">
                                            <ul class="list-unstyled mgb0">
                                                <?php
                                                if ($val['gift']) {
                                                    foreach ($val['gift'] as $gift) { ?>
                                                        <li><?=$gift['presentTitle']?> | <?=$gift['giftNm']?> | <?=$gift['giveCnt']?>개</li>
                                                    <?php } } ?>
                                            </ul>
                                        </td>
                                    <?php } ?>
                                    <?php if (in_array($currentStatusCode, $statusListCombine)) { ?>
                                        <?php if ($rowChk === 0) { ?>
                                            <td <?= $orderGoodsRowSpan; ?>>
                                                <div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div>
                                                <?php if ($val['statusMode'] == 'o') { ?>
                                                    <div class="mgt10 mgb5">
                                                        <input type="button" onclick="status_process_payment('<?= $orderNo; ?>');" value="입금확인" class="btn btn-sm btn-black"/>
                                                    </div>
                                                <?php } ?>
                                            </td>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <td <?=$orderAddGoodsRowSpan?>>
                                            <?php if ($currentStatusCode == 'r') { ?>
                                                <div class="text-muted" title="이전 상품별 주문 상태"><?= $val['beforeStatusStr']; ?> &gt;</div>
                                            <?php } ?>
                                            <div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div>
                                            <?php if (empty($val['invoiceCompanySno']) === false && empty($val['invoiceNo']) === false  && (!$val['deliveryMethodFl'] || $val['deliveryMethodFl'] === 'delivery')) { ?>
                                                <div>
                                                    <input type="button" onclick="delivery_trace('<?= $val['invoiceCompanySno']; ?>', '<?= $val['invoiceNo']; ?>');" value="배송추적" class="btn btn-sm btn-black">
                                                </div>
                                            <?php } ?>
                                        </td>
                                    <?php } ?>
                                    <?php if ($rowDelivery === 0) { ?>
                                        <td <?= $orderDeliveryRowSpan; ?> class="font-num"><?= $val['deliverySno'] ?></td>
                                    <?php } ?>

                                    <td <?= $orderAddGoodsRowSpan; ?>>
                                        <?= gd_select_box(null, 'invoiceCompanySno['.$val['statusMode'].'][]', $deliveryCom, null, $val['invoiceCompanySno'], null); ?>
                                        <?= $val['invoiceNo']; ?>
                                    </td>

                                    <?php if ($rowScm === 0) { ?>
                                        <td <?= $orderScmRowSpan; ?>><?= $val['companyNm'] ?></td>
                                    <?php } ?>
                                    <?php if ($rowChk === 0) { ?>
                                        <td <?= $orderGoodsRowSpan; ?>><?= $val['receiverName'] ?></td>
                                    <?php } ?>
                                </tr>
                                <?php
                                if ($val['addGoodsCnt'] > 0) {
                                    foreach ($val['addGoods'] as $aVal) {
                                        ?>
                                        <tr class="text-center add-goods">
                                            <td></td>
                                            <td class="text-left"><span class="label label-default" title="<?= $aVal['sno'] ?>">추가</span></td>
                                            <td class="text-left">
                                                <?= gd_html_add_goods_image($aVal['addGoodsNo'], $aVal['imageNm'], $aVal['imagePath'], $aVal['imageStorage'], 30, $aVal['goodsNm'], '_blank'); ?>
                                                <div class="goods_name one-line hand" title="추가 상품명" onclick="addgoods_register_popup('<?= $aVal['addGoodsNo']; ?>');"><?= gd_html_cut($aVal['goodsNm'], 46, '..'); ?>
                                                    <small>(<?= gd_html_cut($aVal['optionNm'], 46, '..'); ?>)</small>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $rowChk++;
                                    }
                                } else {
                                    $rowChk++;
                                }
                                $rowScm++;
                                $rowDelivery++;
                            }
                        }
                    }
                }
            } else {
                ?>
                <tr>
                    <td colspan="20" class="no-data">
                        검색된 주문이 없습니다.
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 주문을</span>
            <button type="button" class="btn btn-white js-cancel-godopost" />송장발급취소</button>
        </div>
        <div class="pull-right">
        </div>
    </div>

    <div class="text-center"><?= $page->getPage(); ?></div>



    <table class="table table-cols">
        <colgroup><col class="width-md"><col><col class="width-md"></colgroup>
        <tbody><tr>
            <th>송장번호 발급</th>
            <td>
                <div>
                    <label class="radio-inline">
                        <input type="radio" name="godoPostSendFl" value="search" checked/> 선택된 <span id="selectOrderCnt">0</span>개의 송장번호에 대해서 우체국택배에 예약합니다.
                    </label>
                </div>
                <div class="mgt5 mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="godoPostSendFl" value="all" data-count="<?=$deliveryNoneCount?>" /> 검색된 <?= number_format($page->recode['total'], 0); ?>개의 송장번호에 대해서 우체국택배에 일괄 예약합니다.
                    </label>
                </div>
            </td>
            <td>
                <input type="button" value="우체국택배 예약하기" class="btn btn-lg btn-black js-send-godopost">
            </td>
        </tr>
        </tbody></table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function(){


        // 송장취소
        $('.js-cancel-godopost').click(function () {


            if($('input[name*="statusCheck["]:checkbox:checked').length =='0') {
                alert("송장 발급 취소할 주문건을 선택해주세요.");
                return false;
            }

            dialog_confirm('선택된 주문건의 송장 발급을 취소하시겠습니까?', function (result) {
                if (result) {
                    $('#frmOrderStatus input[name=\'mode\']').val('cancel');
                    $('#frmOrderStatus').attr('action', './post_ps.php');
                    $('#frmOrderStatus').attr('target', 'ifrmProcess');
                    $('#frmOrderStatus').submit();
                }
            });


        });


        $('input[name*="statusCheck["]:checkbox,.js-checkall').click(function () {
            var chkCnt = $('input[name*="statusCheck["]:checkbox:checked').length;
            $("#selectOrderCnt").html(chkCnt);
        });

        // 예약
        $('.js-send-godopost').click(function () {

            var godoPostSendFl = $('input[name="godoPostSendFl"]:checked').val();
            var msg ="";

            if(godoPostSendFl =='search') {

                if($('input[name*="statusCheck["]:checkbox:checked').length =='0') {
                    alert("우체국택배로 예약 할 주문건을 선택해주세요.");
                    return false;
                }
                msg += "선택된 주문건";
            } else {
                msg += "검색한 주문건";

            }

            dialog_confirm(msg+'을 우체국택배로 예약하시겠습니까?', function (result) {
                if (result) {
                    $('#frmOrderStatus input[name=\'mode\']').val('reserve');
                    $('#frmOrderStatus').attr('action', './post_ps.php');
                    $('#frmOrderStatus').attr('target', 'ifrmProcess');
                    $('#frmOrderStatus').submit();
                }
            });


        });

        $('#sort, #pageNum').change(function () {
            $('#frmSearchOrder').submit();
        });
    });

    //-->
</script>


