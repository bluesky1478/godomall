<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="상품 등록" class="btn btn-red-line js-register"/>
    </div>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <input type="hidden" name="modDtUse" value=""/>
    <div class="table-action" style="margin:0;">
        <div class="pull-left">
            <button type="button" class="btn btn-black js-check-sale">상품 노출/판매 수정</button>
            <?php if(gd_is_provider() === false) {?>
                <button type="button" class="btn btn-black js-check-populate">인기상품노출수정</button>
            <?php }?>
        </div>
        <div class="pull-right">
            <button type="button" class="js-layer-register btn btn-sm btn-black" style="height: 27px !important;" data-type="goods_grid_config" data-goods-grid-mode="<?=$goodsAdminGridMode?>">조회항목설정</button>
        </div>
        <div class="pull-left" style="width:100%; padding-top: 5px;">
            <?php if(gd_is_provider() === false) {?>
            <button type="button" class="btn btn-white js-check-maindisplay">메인상품진열</button>
            <?php }?>
            <button type="button" class="btn btn-white js-check-group">분류관리</button>
            <button type="button" class="btn btn-white js-check-moddt">수정일변경</button>
            <button type="button" class="btn btn-white js-check-soldout">품절처리</button>
            <button type="button" class="btn btn-white js-check-copy">선택 복사</button>
            <button type="button" class="btn btn-white js-check-delete">선택 삭제</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-rows">
            <thead>
                <tr>
                    <!-- 상품리스트 그리드 항목 시작-->
                    <?php
                    if (count($goodsGridConfigList) > 0) {
                        foreach($goodsGridConfigList as $gridKey => $gridName){
                            $addClass = '';
                            if($gridKey === 'display') continue;
                            if($gridKey === 'goodsNm') {
                                $addClass = " style='min-width: 300px !important;' ";
                            }
                            if( $gridKey === 'goodsDisplayFl' || $gridKey ==='goodsSellFl') {
                                $addClass = " style='min-width: 120px !important;' ";
                            }
                            if($gridKey === 'check') {
                                echo "<th><input type='checkbox' value='y' class='js-checkall' data-target-name='goodsNo'/></th>";
                            }
                            else {
                                echo "<th ".$addClass.">".$gridName."</th>";
                            }
                        }
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                $arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');
                $arrGoodsSell = array('y' => '판매함', 'n' => '판매안함');
                $arrGoodsTax = array('t' => '과세', 'f' => '면세');
                $arrGoodsApply = array('a'    => '승인요청','y'   => '승인완료','r'  => '반려','n'  => '철회',);
                $arrDeliveryFree = array('one' => '해당 상품만', 'goods' => '상품별 배송', 'all' => '모두 무료');

                $goodsConfig = (gd_policy('goods.display')); // 상품 설정 config 불러오기
                $goodsConfig['goodsModDtTypeList'] = gd_isset($goodsConfig['goodsModDtTypeList'], 'y');
                $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');

                foreach ($data as $key => $val) {
                    list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);


                    if($val['applyFl'] !='y') {
                        $displayText = $arrGoodsApply[$val['applyFl']];
                        $sellText = $arrGoodsApply[$val['applyFl']];
                        $sellMobileText = $displayMobileText = "";
                    } else {
                        $displayText = "PC | " . $arrGoodsDisplay[$val['goodsDisplayFl']];
                        $displayMobileText = "모바일 | " . $arrGoodsDisplay[$val['goodsDisplayMobileFl']];
                        $sellText = "PC | " . $arrGoodsSell[$val['goodsSellFl']];
                        $sellMobileText = "모바일 | " . $arrGoodsSell[$val['goodsSellMobileFl']];
                    }
                    // 과세여부
                    if($val['taxFreeFl'] == 't') {
                        $displayTaxText = $val['taxPercent'];
                    } else {
                        $displayTaxText = $arrGoodsTax[$val['taxFreeFl']];
                    }


                    ?>
                    <tr>
                        <!-- 주문리스트 그리드 항목 시작-->
                        <?php
                        if (count($goodsGridConfigList) > 0) {
                            foreach($goodsGridConfigList as $gridKey => $gridName){
                                if($gridKey === 'check'){ ?>
                                    <!--선택-->
                                    <td class="center"><input type="checkbox" name="goodsNo[<?=$val['goodsNo']; ?>]" value="<?=$val['goodsNo']; ?>" <?php if($val['applyFl'] !='y') { echo "disabled = 'true'"; }  ?> /></td>
                                <?php }
                                if($gridKey === 'no'){ ?>
                                    <!--번호-->
                                    <td class="center number"><?=number_format($page->idx--); ?></td>
                                <?php }
                                if($gridKey === 'goodsNo'){ ?>
                                    <!--상품코드번호-->
                                    <td class="center number"><?=$val['goodsNo']; ?></td>
                                <?php }
                                if($gridKey === 'goodsCd'){ ?>
                                    <!--자체상품코드번호-->
                                    <td class="center number text-nowrap"><?=$val['goodsCd']; ?></td>
                                <?php }
                                if($gridKey === 'goodsImage'){ ?>
                                    <!--상품이미지-->
                                    <td class="width-2xs center">
                                        <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                                    </td>
                                <?php }
                                if($gridKey === 'goodsNm'){ ?>
                                    <!--상품명-->
                                    <td>
                                        <div><a class="text-blue hand" onclick="goods_register_popup('<?=$val['goodsNo']; ?>' <?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"><?=$val['goodsNm']; ?></a>
                                        </div>
                                        <div class="notice-ref notice-sm"><?=Globals::get('gDelivery.' . $val['deliveryFl']); ?><?php if ($val['deliveryFl'] == 'free') {
                                                echo '(' . $arrDeliveryFree[$val['deliveryFree']] . ')';
                                            } ?></div>

                                            <div>
                                                <?php
                                                if($goodsGridConfigList['display']['icon'] === 'y') {
                                                    // 상품 혜택 아이콘
                                                    if (empty($val['goodsBenefitIconCd']) === false && is_array($val['goodsBenefitIconCd']) === true) {
                                                        foreach ($val['goodsBenefitIconCd'] as $iKey => $iVal) {
                                                            echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                                        }
                                                    }

                                                    // 상품 아이콘
                                                    if (empty($val['goodsIconCd']) === false && is_array($val['goodsIconCd']) === true) {
                                                        foreach ($val['goodsIconCd'] as $iKey => $iVal) {
                                                            echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                                        }
                                                    }

                                                    // 기간 제한용 아이콘
                                                    if (empty($val['goodsIconStartYmd']) === false && empty($val['goodsIconEndYmd']) === false && empty($val['goodsIconCdPeriod']) === false && strtotime($val['goodsIconStartYmd']) <= time() && strtotime($val['goodsIconEndYmd']) >= time()) {
                                                        foreach ($val['goodsIconCdPeriod'] as $iKey => $iVal) {
                                                            echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                                        }
                                                    }
                                                }
                                                // 품절 체크
                                                if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
                                                    echo gd_html_image(UserFilePath::icon('goods_icon')->www() . '/' . 'icon_soldout.gif', '품절상품') . ' ';
                                                }

                                                if($val['timeSaleSno']) {
                                                    echo "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' /> ";
                                                }

                                                ?>
                                            </div>
                                            <!--아이콘-->
                                        <?php
                                        if($goodsGridConfigList['display']['color'] === 'y'){
                                            if (is_array($val['goodsColor'])) {
                                                ?>
                                                [ 대표색상
                                                <?php
                                                foreach (array_unique($val['goodsColor']) as $k => $v) {
                                                    ?>
                                                    <div id='goodsColor_<?= $v ?>' class="btn-group btn-group-xs">
                                                        <span class='btn js-popover' style='background:#<?= $v ?>;border:1px solid #efefef' data-html="true" data-content="<?=$v?>" data-placement="bottom">&nbsp;&nbsp;&nbsp;</span>
                                                    </div>
                                                    <?php
                                                }
                                                ?>]
                                                <?php
                                            }
                                        }
                                        ?>
                                    </td>
                                <?php }
                                if($gridKey === 'goodsNmUs'){ ?>
                                    <!--상품명(영문몰)-->
                                    <td class="center text-nowrap"><?=$val['goodsNmUs']; ?></td>
                                <?php }
                                if($gridKey === 'goodsNmCn'){ ?>
                                    <!--상품명(중문몰)-->
                                    <td class="center text-nowrap"><?=$val['goodsNmCn']; ?></td>
                                <?php }
                                if($gridKey === 'goodsNmJs'){ ?>
                                    <!--상품명(일문몰)-->
                                    <td class="center text-nowrap"><?=$val['goodsNmJs']; ?></td>
                                <?php }
                                if($gridKey === 'option'){ ?>
                                    <!--옵션-->
                                    <td class="center text-nowrap">
                                        <?php
                                        if($val['optionFl'] == 'n') {
                                            echo "사용안함";
                                        } else if($val['optionFl'] == 'y') {
                                            foreach($val['optionInfo'] as $okey => $oValue) { ?>
                                                <?=$val['optionName'][$okey];?> : <?=$oValue;?><br/>
                                            <?php }?>
                                            <button type="button" class="js-layer-grid-option btn btn-sm btn-black" style="height: 20px !important;" data-type="goods_option" data-goods-option="<?=$val['goodsNo']?>">옵션재고보기</button>
                                        <?php }?>
                                    </td>
                                <?php }
                                if($gridKey === 'optionText') { ?>
                                    <!--텍스트옵션-->
                                    <td class="center text-nowrap">
                                        <?php
                                        if ($val['optionTextFl'] == 'n') {
                                            echo "사용안함";
                                        } else if ($val['optionTextFl'] == 'y') {
                                            ?>
                                            <?= implode('<br/>', $val['optionTextInfo']); ?>
                                        <?php }?>
                                    </td>
                                <?php }
                                if($gridKey === 'goodsPrice'){ ?>
                                    <!--판매가-->
                                    <td class="center text-nowrap">
                                        <div><span class="font-num"><?=gd_currency_display($val['goodsPrice']); ?></span></div>
                                    </td>
                                <?php }
                                if($gridKey === 'fixedPrice'){ ?>
                                    <!--정가-->
                                    <td class="center text-nowrap">
                                        <div><span class="font-num"><?=gd_currency_display($val['fixedPrice']); ?></span></div>
                                    </td>
                                <?php }
                                if($gridKey === 'costPrice'){ ?>
                                    <!--매입가-->
                                    <td class="center text-nowrap">
                                        <div><span class="font-num"><?=gd_currency_display($val['costPrice']); ?></span></div>
                                    </td>
                                <?php }
                                if($gridKey === 'supplyPrice'){ ?>
                                    <!--공급가-->
                                    <td class="center text-nowrap">
                                        <div><span class="font-num"><?=gd_currency_display($val['supplyPrice']); ?></span></div>
                                    </td>
                                <?php }
                                if($gridKey === 'commission'){ ?>
                                    <!--수수료율-->
                                    <td class="center"><?=$val['commission']; ?></td>
                                <?php }
                                if($gridKey === 'taxFreeFl'){ ?>
                                    <!--과세면세-->
                                    <td class="center"><?=$displayTaxText; ?></td>
                                <?php }
                                if($gridKey === 'goodsDiscountFl'){ ?>
                                    <!--상품할인금액-->
                                    <td class="center"><?=($val['goodsDiscountFl'] == 'y' || $val['goodsBenefitSetFl'] == 'y') ? "사용함" : "사용안함"; ?></td>
                                <?php }
                                if($gridKey === 'mileageFl'){ ?>
                                    <!--마일리지-->
                                    <td class="center"><?=($val['mileageFl'] == 'c') ? "통합설정" : "개별설정"; ?></td>
                                <?php }
                                if($gridKey === 'payLimit'){ ?>
                                    <!--결제수단-->
                                    <td class="center"><?=$val['payLimitIcon']; ?></td>
                                <?php }
                                if($gridKey === 'purchaseNo'){ ?>
                                    <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) { ?>
                                        <!--매입처-->
                                        <td class="center lmenu  text-nowrap"><?=$val['purchaseNm']; ?></td>
                                    <?php } ?>
                                <?php }
                                if($gridKey === 'scmNo'){ ?>
                                    <!--공급사-->
                                    <td class="center text-nowrap"><?=$val['scmNm']; ?></td>
                                <?php }
                                if($gridKey === 'brandCd'){ ?>
                                    <!--브랜드-->
                                    <td class="center text-nowrap"><?=$val['brandNm']; ?></td>
                                <?php }
                                if($gridKey === 'makerNm'){ ?>
                                    <!--제조사-->
                                    <td class="center text-nowrap"><?=$val['makerNm']; ?></td>
                                <?php }
                                if($gridKey === 'originNm'){ ?>
                                    <!--원산지-->
                                    <td class="center"><?=$val['originNm']; ?></td>
                                <?php }
                                if($gridKey === 'goodsModelNo'){ ?>
                                    <!--모델명-->
                                    <td class="center text-nowrap"><?=$val['goodsModelNo']; ?></td>
                                <?php }
                                if($gridKey === 'goodsDisplayFl'){ ?>
                                    <!--노출상태-->
                                    <td class="center"><?=$displayText; ?><br/> <?=$displayMobileText; ?></td>
                                <?php }
                                if($gridKey === 'goodsSellFl'){ ?>
                                    <!--판매상태-->
                                    <td class="center"><?=$sellText; ?><br/> <?=$sellMobileText; ?></td>
                                <?php }
                                if($gridKey === 'soldOutFl'){ ?>
                                    <!--품절상태-->
                                    <td class="center"><?=$stockText; ?></td>
                                <?php }
                                if($gridKey === 'stockFl'){ ?>
                                    <!--재고-->
                                    <td class="center"><?=$totalStock; ?></td>
                                <?php }
                                if($gridKey === 'orderGoodsCnt'){ ?>
                                    <!--주문상품수-->
                                    <td class="center"><?=$val['orderGoodsCnt']; ?></td>
                                <?php }
                                if($gridKey === 'hitCnt'){ ?>
                                    <!--조회수-->
                                    <td class="center"><?=$val['hitCnt']; ?></td>
                                <?php }
                                if($gridKey === 'orderRate'){ ?>
                                    <!--구매율-->
                                    <td class="center"><?=$val['orderRate']; ?></td>
                                <?php }
                                if($gridKey === 'cartCnt'){ ?>
                                    <!--장바구니 수-->
                                    <td class="center"><?=$val['cartCnt']; ?></td>
                                <?php }
                                if($gridKey === 'wishCnt'){ ?>
                                    <!--관심상품 수-->
                                    <td class="center"><?=$val['wishCnt']; ?></td>
                                <?php }
                                if($gridKey === 'reviewCnt'){ ?>
                                    <!--후기 수-->
                                    <td class="center"><?=$val['reviewCnt']; ?></td>
                                <?php }
                                if($gridKey === 'deliverySno'){ ?>
                                    <!--배송비조건-->
                                    <td class="center text-nowrap"><?=$val['deliveryNm'];?></td>
                                <?php }
                                if($gridKey === 'memo'){ ?>
                                    <!--메모-->
                                    <td class="center"><button type="button" class="js-layer-goods-memo btn btn-sm btn-<?= $val['memo'] != '' ? 'gray js-html-popover' : 'white' ?>" style="height: 27px !important;" title="관리자메모"  data-placement="left" data-content="<?=nl2br($val['memo'])?>" data-goods-memo="<?=$val['goodsNo']?>">보기</button></td>
                                <?php }
                                if($gridKey === 'regDt'){ ?>
                                <!--등록일 수정일-->
                                    <td class="center date">
                                        <?=gd_date_format('Y-m-d', $val['regDt']); ?>
                                        <?php if ($val['modDt']) { echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);} ?>
                                    </td>
                                <?php
                                }
                            }
                        }
                        ?>
                        <td class="center padlr10"><a href="./goods_register.php?goodsNo=<?=$val['goodsNo']; ?>&page=<?=$page->page[now]?>" class="btn btn-white btn-sm">수정</a></td>
                    </tr>
                    <?php
                    // 노출항목 설정이 존재한 경우
                    include $goodsListGridAddDisplay;// 노출항목 추가설정 레이어
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="<?=count($goodsGridConfigList)+1?>">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-black js-check-sale">상품 노출/판매 수정</button>
            <?php if(gd_is_provider() === false) {?>
                <button type="button" class="btn btn-black js-check-populate">인기상품노출수정</button>
            <?php }?>
        </div>
        <div class="pull-left" style="width:100%; padding-top: 5px;">
            <?php if(gd_is_provider() === false) {?>
            <button type="button" class="btn btn-white js-check-maindisplay">메인상품진열</button>
            <?php }?>
            <button type="button" class="btn btn-white js-check-group">분류관리</button>
            <button type="button" class="btn btn-white js-check-moddt">수정일변경</button>
            <button type="button" class="btn btn-white js-check-soldout">품절처리</button>
            <button type="button" class="btn btn-white js-check-copy">선택 복사</button>
            <button type="button" class="btn btn-white js-check-delete">선택 삭제</button>
            <div class="pull-right">
                <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGoods" data-target-list-form="frmList" data-target-list-sno="goodsNo" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>">엑셀다운로드</button>
            </div>
        </div>
    </div>
</form>
<div class="text-center"><?=$page->getPage();?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 삭제
        $('button.js-check-delete').click(function () {

            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 삭제하시겠습니까?\n삭제 된 상품은 [삭제상품 리스트]에서 확인 가능합니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete_state');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        $('button.js-check-copy').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }
            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 복사하시겠습니까?', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('copy');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        $('button.js-check-soldout').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 상품을 품절처리 하시겠습니까?', function (result) {
                if (result) {
                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeList'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result) {
                        if (result) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $('#frmList input[name=\'mode\']').val('soldout');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('action', './goods_ps.php');
                        $('#frmList').submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeList'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $('#frmList input[name=\'mode\']').val('soldout');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('action', './goods_ps.php');
                        $('#frmList').submit();
                    <?php } ?>
                }
            });

        });

        // 노출 설정
        $('button.js-check-sale').click(function () {

            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            var childNm = 'goods_sale';
            var addParam = {
                mode: 'simple',
                layerTitle: '노출 및 판매상태 설정',
                layerFormID: childNm + "Layer",
                parentFormID: childNm + "Row",
                dataFormID: childNm + "Id",
                dataInputNm: childNm
            };
            layer_add_info(childNm, addParam);
        });


        // 등록
        $('.js-register').click(function () {
            location.href = './goods_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        <?php if(gd_is_provider() === false) {?>
        $('button.js-check-populate').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return false;
            }else if (chkCnt > 100) {
                alert('인기상품노출수정은 1회 100개까지만 수정할 수 있습니다.');
                return false;
            }else{
                display_populate_popup(<?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);
            }

        });
        <?php } ?>

        // 메인상품진열 설정
        <?php if(gd_is_provider() === false) {?>
        $('button.js-check-maindisplay').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return false;
            }else if (chkCnt > 100) {
                alert('메인상품진열은 1회 100개까지만 수정할 수 있습니다.');
                return false;
            }else{
                display_main_popup(<?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);
            }

        });
        <?php } ?>

        // 분류관리 설정
        $('button.js-check-group').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return false;
            }else if (chkCnt > 100) {
                alert('분류관리는 1회 100개까지만 수정할 수 있습니다.');
                return false;
            }else{
                category_popup(<?php if(gd_is_provider() === true) { echo "1"; } ?>);
            }

        });

        // 수정일 변경
        $('button.js-check-moddt').click(function(){
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            console.log(chkCnt);

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            var childNm = 'goods_moddt';
            var addParam = {
                mode: 'simple',
                layerTitle: '상품 수정일 변경',
                layerFormID: childNm + "Layer",
                parentFormID: childNm + "Row",
                dataFormID: childNm + "Id",
                dataInputNm: childNm
            };
            layer_add_info(childNm, addParam);
        });

    });

    <?php if(gd_is_provider() === false) {?>
    /**
     * 인기상품노출수정 등록/수정 팝업창
     *
     * @author seonghu
     */
    function display_populate_popup(isProvider, page) {
        url = '/share/popup_populate_list.php?popupMode=yes';

        if (page) url += page;

        win = popup({
            url: url,
            width: 1000,
            height: 800,
            resizable: 'yes'
        });
    }
    <?php } ?>

    <?php if(gd_is_provider() === false) {?>
    /**
     * 메인상품진열 등록/수정 팝업창
     *
     * @author sueun
     */
    function display_main_popup(isProvider, page) {
        var url = '/share/popup_display_main_list.php?popupMode=yes';

        if (page) url += page;

        win = popup({
            url: url,
            width: 1000,
            height: 800,
            resizable: 'yes',
            scrollbars: 'yes'
        });
    }
    <?php } ?>

    /**
     * 분류관리 팝업창
     *
     * @author sueun
     */
    function category_popup(isProvider) {
        if(isProvider) var url = '/provider/share/popup_display_main_group.php?popupMode=yes';
        else var url = '/share/popup_display_main_group.php?popupMode=yes';

        win = popup({
            url: url,
            width: 1000,
            height: 800,
            resizable: 'yes',
            scrollbars : 'yes',
        });
    }

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>
