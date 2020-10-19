<?php
/**
 * 주문리스트내 검색 폼 레이아웃
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */

//주문번호별/상품주문번호별 탭 이동 시 검색결과개수 초기화
parse_str($queryString,$arrQueryString);
unset($arrQueryString['__total']);
unset($arrQueryString['__amount']);
unset($arrQueryString['__totalPrice']);
$queryString = http_build_query($arrQueryString);
?>

<!-- 검색을 위한 form -->
<form id="frmSearchOrder" method="get" class="js-form-enter-submit">
    <div class="table-title <?=isset($currentUserHandleMode) ? '' : 'gd-help-manual'?>">
        CJ대한통운 예약하기
    </div>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm">
                <col>
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>발급방법</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="view" value="order" <?= gd_isset($checked['view']['order']); ?>/>주문(배송지)별 송장발급
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="view" value="orderGoods" <?= gd_isset($checked['view']['orderGoods']); ?>/>상품별 송장발급
                    </label>
                </td>
            </tr>

            <?php if(gd_use_provider() === true) { ?>
                <?php if (!isset($isProvider) && $isProvider != true) { ?>
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
                                <?php if ($search['scmFl'] == '1' && empty($search['scmNo']) === false) { ?>
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
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control '); ?>
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
                <th>예약상태</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="reservationStatus" value="all" <?= gd_isset($checked['reservationStatus']['all']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="reservationStatus" value="ready" <?= gd_isset($checked['reservationStatus']['ready']); ?>/>예약전
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="reservationStatus" value="complete" <?= gd_isset($checked['reservationStatus']['complete']); ?>/>완료
                    </label>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('treatDateFl', 'treatDateFl', $search['combineTreatDate'], null, $search['treatDateFl'], null, null, 'form-control '); ?>
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

                        <?= gd_search_date(gd_isset($search['searchPeriod'], 6), 'treatDate[]', false) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>주문상태</th>
                <td colspan="3">
                    <div class="dl-horizontal dl-checkbox">
                        <div style="display: inline-block; margin: 0 10px 0 0;">
                            <label class="checkbox-inline">
                                <input type="radio" name="orderStatusType" value="all"  <?= gd_isset($checked['orderStatusType']['all']) ?>/> 전체
                            </label>
                        </div>
                        <div>
                            <label class="checkbox-inline">
                                <input type="radio" name="orderStatusType" value="possibleReservation" <?= gd_isset($checked['orderStatusType']['possibleReservation']) ?> />예약가능상태
                            </label>(
                            <label>
                                <input type="checkbox" name="orderStatus[]" value="p" <?= gd_isset($checked['orderStatus']['p']) ?> />결제완료
                            </label>
                            <label >
                                <input type="checkbox" name="orderStatus[]" value="g" <?= gd_isset($checked['orderStatus']['g']) ?> />상품준비중
                            </label>
                            )
                            <label >
                                <input type="radio" name="orderStatusType" value="delivery" <?= gd_isset($checked['orderStatusType']['delivery']) ?> />배송
                            </label>
                            <label >
                                <input type="radio" name="orderStatusType" value="exchange" <?= gd_isset($checked['orderStatusType']['exchange']) ?> />교환
                            </label>
                            <label >
                                <input type="radio" name="orderStatusType" value="refund" <?= gd_isset($checked['orderStatusType']['refund']) ?> />환불
                            </label>
                        </div>
                        <div class="notice-info">예약가능 상태 외 택배 예약이 완료된 주문 건만 검색됩니다.</div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>배송방식</th>
                <td class="form-inline">
                    <label class="radio-inline">
                        <input type="radio" name="deliveryMethodFl" value="delivery" <?= gd_isset($checked['deliveryMethodFl']['delivery']); ?>/>택배만
                    </label>
                </td>
                <th>배송정보</th>
                <td class="form-inline">
                    <label>
                        <input type="checkbox" name="withPacket" value="y" <?= gd_isset($checked['withPacket']['y']); ?>/>묶음배송
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>



    <div class="table-header <?=$tableHeaderClass?>">

        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10,20,30,40,50,60,70,80,90,100,200,300,500,]), '개 보기', $page->page['list']); ?>

            </div>
        </div>
    </div>
    <input type="hidden" name="searchFl" value="y">
    <input type="hidden" name="applyPath" value="<?=gd_php_self()?>?view=<?=$search['view']?>">
</form>
<!-- // 검색을 위한 form -->

<!-- 프린트 출력을 위한 form -->
<form id="frmOrderPrint" name="frmOrderPrint" action="" method="post" class="display-none">
    <input type="hidden" name="orderPrintCode" value=""/>
    <input type="hidden" name="orderPrintMode" value=""/>
</form>
<!-- // 프린트 출력을 위한 form -->
