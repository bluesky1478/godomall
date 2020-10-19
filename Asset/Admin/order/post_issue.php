<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
</div>

<!-- 검색을 위한 form -->
<form id="frmSearchOrder" method="get">
    <input type="hidden" name="detailSearch" value="<?= $search['detailSearch']; ?>"/>
    <input type="hidden" name="mode" value=""/>
    <div class="table-title "><?=end($naviMenu->location)?></div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <?php if (!isset($isProvider) && $isProvider != true) { ?>
                <tr>
                    <th>발급방법</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="deliveryListFl" value="o" <?= gd_isset($checked['deliveryListFl']['o']) ?>/> 주문별 송장발급
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="deliveryListFl" value="g" <?= gd_isset($checked['deliveryListFl']['g']) ?>/> 상품별 송장발급
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>공급사 구분</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="all" <?= gd_isset($checked['scmFl']['all']); ?>/>전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="0" <?= gd_isset($checked['scmFl']['0']); ?>/>본사
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="1" class="js-layer-register" <?= gd_isset($checked['scmFl']['1']); ?> data-type="scm" data-mode="checkbox"/> 공급사
                        </label>
                        <input type="button" value="공급사 선택" class="btn btn-sm btn-gray js-layer-register" data-type="scm" data-mode="search"/>

                        <div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == '1' && !empty($search['scmNo']) ? 'active' : ''?>">
                            <h5>선택된 공급사 : </h5>
                            <?php if ($search['scmFl'] == '1') { ?>
                                <?php foreach ($search['scmNo'] as $k => $v) { ?>
                                    <div id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                        <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                        <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                        <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control input-sm'); ?>
                        <?= gd_select_box('searchKind', 'searchKind', $search['searchKindArray'], null, $search['searchKind'], null, null, 'form-control '); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control width300"/>
                        <div class="notice-danger notice-search-kind">
                            “검색어 부분포함“으로 검색 시 검색량에 따라 로딩 속도가 느릴 수 있으며, 검색 중 페이지가 멈출 수 있습니다.<br/>
                            안전한 검색을 위해서 “검색어 전체일치＂로 검색할 것을 권고 드립니다.
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>발급상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceFl" value="" class="js-not-checkall" data-target-name="invoiceFl" <?= gd_isset($checked['invoiceFl']['']) ?>/> 전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceFl" value="y" <?= gd_isset($checked['invoiceFl']['y']) ?>/> 발급
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceFl" value="n" <?= gd_isset($checked['invoiceFl']['n']) ?>/> 미발급
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceFl" value="e" <?= gd_isset($checked['invoiceFl']['e']) ?>/> 연락처오류
                    </label>
                </td>
                <th>예약상태</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceReserveFl" value="" class="js-not-checkall" data-target-name="invoiceReserveFl" <?= gd_isset($checked['invoiceReserveFl']['']) ?>/> 전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceReserveFl" value="n" <?= gd_isset($checked['invoiceReserveFl']['n']) ?>/> 예약 전
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="invoiceReserveFl" value="y" <?= gd_isset($checked['invoiceReserveFl']['y']) ?>/> 예약 후
                    </label>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('treatDateFl', 'treatDateFl', $search['combineTreatDate'], null, $search['treatDateFl'], null, null, 'form-control input-sm'); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>

                        <?= gd_search_date(gd_isset($search['periodFl'], 6), 'treatDate[]', false) ?>
                    </div>
                </td>
            </tr>
            <?php if (empty($statusSearchableRange) === false) { ?>
                <tr>
                    <th>주문상태</th>
                    <td colspan="3">
                        <dl class="dl-horizontal dl-checkbox">
                            <dt>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="orderStatus[]" value="" class="js-not-checkall" data-target-name="orderStatus[]" <?= gd_isset($checked['orderStatus']['']) ?>/> 전체
                                </label>
                            </dt>
                            <dd>
                                <?php $chk = 0;
                                foreach ($statusSearchableRange as $key => $val) { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="orderStatus[]" value="<?= $key ?>" <?= gd_isset($checked['orderStatus'][$key]) ?> /> <?= $val ?>
                                    </label>
                                    <?php $chk++;
                                    if ($chk % 8 == 0) {
                                        echo '<br/>';
                                    }
                                } ?>
                            </dd>
                        </dl>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
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
    <input type="hidden" name="mode" value="issue"/>
    <input type="hidden" name="whereDetail" value="" />

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

                                if($search['deliveryListFl'] =='o') {
                                    $checkBoxCd = $orderNo;
                                } else {
                                    $checkBoxCd = $orderNo . INT_DIVISION . $val['sno'];
                                }

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

                                    <?php if($search['deliveryListFl'] =='o') { ?>
                                    <?php if ($rowChk === 0) { ?>
                                        <td  <?= $orderGoodsRowSpan; ?>>
                                            <input type="checkbox" name="statusCheck[]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>


                                        </td>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <td <?=$orderAddGoodsRowSpan?>>
                                            <input type="checkbox" name="statusCheck[]" <?= $checkDisabled ?> value="<?= $checkBoxCd; ?>"/>
                                        </td>
                                    <?php } ?>
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

    <div class="text-center"><?= $page->getPage(); ?></div>

    <table class="table table-cols">
        <colgroup><col class="width-md"><col><col class="width-md"></colgroup>
        <tbody><tr>
            <th>송장번호 발급</th>
            <td>
              <div>
                  <label class="radio-inline">
                      <input type="radio" name="godoPostSendFl" value="search" checked/> 선택된 <span id="selectOrderCnt">0</span>개의 주문에 대해서 새로운 우체국택배 송장번호를 발급합니다.
                  </label>
              </div>
                <div class="mgt5 mgb5">
                    <label class="radio-inline">
                    <input type="radio" name="godoPostSendFl" value="all" data-count="<?=$deliveryNoneCount?>" /> 검색된 <span class="js-post-order-cnt"></span>개의 주문 중, 송장번호 미발급 주문 (<?= number_format($deliveryNoneCount, 0); ?>)개에  송장번호를 일괄 발급합니다.
                    </label>
                </div>
            </td>
            <td>
                <input type="button" value="송장번호 발급받기" class="btn btn-lg btn-black js-send-godopost">
            </td>
        </tr>
        </tbody></table>
</form>


<script type="text/javascript">
    <!--
    $(document).ready(function(){


        $('input[name*="statusCheck["]:checkbox,.js-checkall').click(function () {
            var chkCnt = $('input[name*="statusCheck["]:checkbox:checked').length;
            $("#selectOrderCnt").html(chkCnt);
        });

        // 삭제
        $('.js-send-godopost').click(function () {

            var godoPostSendFl = $('input[name="godoPostSendFl"]:checked').val();
            var msg ="";

            if(godoPostSendFl =='search') {

                if($('input[name*="statusCheck["]:checkbox:checked').length =='0') {
                    alert("송장번호를 발급 할 주문건을 선택해주세요.");
                    return false;
                }
                msg += "선택된 주문건";
            } else {
                var count = $('input[name="godoPostSendFl"]:checked').data('count');
                if(count > 0 ) {
                    msg += "검색한 주문건";
                } else {
                    alert("검색된 주문 중 송장번호 미발급 주문건이 존재하지 않습니다.");
                    return false;
                }

            }

            var deliveryListFl = $('input[name="deliveryListFl"]:checked').val();

            if(deliveryListFl =='o')  msg = msg+'의 주문별 송장번호를 발급하시겠습니까?';
            else     msg = msg+'의 상품별 송장번호를 발급하시겠습니까?';

            dialog_confirm(msg, function (result) {
                if (result) {
                    $('#frmOrderStatus input[name=\'mode\']').val('issue');
                    $('#frmOrderStatus input[name=\'whereDetail\']').val( $('#frmSearchOrder').serialize());
                    $('#frmOrderStatus').attr('action', './post_ps.php');
                    $('#frmOrderStatus').attr('target', 'ifrmProcess');
                    $('#frmOrderStatus').submit();
                }
            });


        });

        $('#sort, #pageNum').change(function () {
            $('#frmSearchOrder').submit();
        });

        $(".js-post-order-cnt").html($('input[name="statusCheck[]"]').length);

        //검색어 변경 될 때 placeHolder 교체 및 검색 종류 변환 및 검색 종류 변환
        set_searchKind_display();
        set_searchKind_placeholder();
        set_keyword_placeholder();
        $('#frmSearchOrder #key').change(function (e) {
            set_searchKind_display();
            set_searchKind_default();
            set_searchKind_placeholder();
            set_keyword_placeholder();
        });

        $('#frmSearchOrder #searchKind').change(function (e) {
            set_searchKind_placeholder();
            set_keyword_placeholder();
        });
    });

    var equalSearch = [
        'o.orderNo','og.goodsNo','og.goodsCd','og.goodsModelNo','og.makerNm','oi.orderName',
        'oi.receiverName','o.bankSender','m.nickNm','sm.companyNm','pu.purchaseNm'
    ];

    var fullLikeSearch = [
        'og.invoiceNo','og.goodsNm','oi.orderCellPhone',
        'oi.orderPhone','oi.receiverPhone','oi.receiverCellPhone'
    ];

    var endLikeSearch = [
        'oi.orderEmail','m.memId'
    ];

    var changeSearchKind = [
        'oi.orderName', 'oi.receiverName', 'o.bankSender', 'm.nickNm'
    ];

    function set_keyword_placeholder() {
        var equalSearchPlaceHolder = ' 전체를 정확히 입력하세요.';
        var fullLikeSearhPlaceHolder = '에 포함된 내용을 입력하세요.';
        var endLikeSearchPlaceHolder = ' 전체 또는 앞 내용을 입력하세요.';
        var changedPlaceHolder = '';
        var keyword = $('input[name=keyword]');
        var keyOptionText = $('#frmSearchOrder #key option:selected').text();
        var keyOptionVal = $('#frmSearchOrder #key option:selected').val();

        if ($.inArray(keyOptionVal, equalSearch) !== -1) {
            changedPlaceHolder = keyOptionText + equalSearchPlaceHolder;
        } else if ($.inArray(keyOptionVal, fullLikeSearch) !== -1) {
            changedPlaceHolder = keyOptionText + fullLikeSearhPlaceHolder;
        } else if ($.inArray(keyOptionVal, endLikeSearch) !== -1) {
            changedPlaceHolder = keyOptionText + endLikeSearchPlaceHolder;
        }

        keyword.attr("placeholder", changedPlaceHolder);
    }

    function set_searchKind_display() {
        var keyValue =  $('#frmSearchOrder #key option:selected').val();
        var searchKind = $('#frmSearchOrder #searchKind');
        var noticeDiv = $('.notice-search-kind');

        if ($.inArray(keyValue, changeSearchKind) == -1) {
            searchKind.attr('disabled', 'true');
            searchKind.css('display', 'none');
            noticeDiv.css('display', 'none');
        } else {
            searchKind.removeAttr('disabled');
            searchKind.css('display', '');
            noticeDiv.css('display', '');
        }
    }

    function set_searchKind_placeholder() {
        var keyOptionVal =  $('#frmSearchOrder #key option:selected').val();
        var searchKindValue = $('#frmSearchOrder #searchKind option:selected').val();
        var isInArrayFullLikeSearch = $.inArray(keyOptionVal, fullLikeSearch);
        var isInArrayEqualSearch = $.inArray(keyOptionVal, equalSearch);
        if ($.inArray(keyOptionVal, changeSearchKind) !== -1) {
            if (searchKindValue == 'equalSearch') {
                if (isInArrayEqualSearch == -1) {
                    equalSearch.push(keyOptionVal);
                }
                if (isInArrayFullLikeSearch !== -1) {
                    fullLikeSearch.splice(isInArrayFullLikeSearch, 1);
                }
            }

            if (searchKindValue == 'fullLikeSearch') {
                if (isInArrayFullLikeSearch == -1) {
                    fullLikeSearch.push(keyOptionVal);
                }
                if (isInArrayEqualSearch !== -1) {
                    equalSearch.splice(isInArrayEqualSearch, 1);
                }
            }
        }
    }

    function set_searchKind_default() {
        $('#frmSearchOrder #searchKind').val('equalSearch').attr('selected', 'selected');
    }
    //-->
</script>


