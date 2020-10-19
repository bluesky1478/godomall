<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $('input[name*=\'goodsPrice\']').number_only();

        delivery_switch('<?=$search['deliveryFl'];?>');

        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 삭제하시겠습니까?<br/>삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_ps.php');
                    $('#frmList').submit();
                }
            });
        });

        //복구선택
        $('button.checkReStore').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            var data = '<div class="text-center">선택한 ' + chkCnt + '개 상품을  정말로 복구 하시겠습니까?<div>';

            layer_popup(data+$("#lay_reStore").html(), '선택상품 복구');
        });

        $('select[name=\'pageNum\']').change(function(){
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function(){
            $('#frmSearchGoods').submit();
        });

    });


    //복구
    function sumbit_restore() {

        $('#frmList input[name=\'goodsDisplayFl\']').val($(".bootstrap-dialog-body input:radio[name='chkGoodsDisplayFl']:checked").val());
        $('#frmList input[name=\'goodsSellFl\']').val($(".bootstrap-dialog-body input:radio[name='chkGoodsSellFl']:checked").val());

        $('#frmList input[name=\'mode\']').val('goods_restore');
        $('#frmList').attr('method', 'post');
        $('#frmList').attr('action', './goods_ps.php');
        $('#frmList').submit();

    }

    /**
     * 배송 정책 종류 선택
     *
     * @param string thisID 종류 ID
     */
    function delivery_switch(thisID) {
        if (thisID == 'free') {
            $('#deliveryConf_free').show();
        } else {
            $('#deliveryConf_free').hide();
        }
    }

    /*
     **
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


<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?> </h3>
</div>

<?php include($goodsSearchFrm); ?>
<div style="width: 100%;overflow: hidden;background: #f6f6f6;">
    <div class="pull-right" style="margin-bottom : 10px;">
        <button type="button" class="js-layer-register btn btn-sm btn-black" style="height: 27px !important;" data-type="goods_grid_config" data-goods-grid-mode="<?=$goodsAdminGridMode?>">조회항목설정</button>
    </div>
</div>
<div>
    <form id="frmList" action="" method="get" target="ifrmProcess" >
        <input type="hidden" name="mode" value="">
        <input type="hidden" name="goodsDisplayFl" value="">
        <input type="hidden" name="goodsSellFl" value="">
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
                                if($gridKey === 'goodsDisplayFl' || $gridKey ==='goodsSellFl') {
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
                    $arrDeliveryFree = array('one' => '해당 상품만', 'goods' => '상품별 배송', 'all' => '모두 무료');
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
                                            <td class="center lmenu text-nowrap"><?=$val['purchaseNm']; ?></td>
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
                                        <td class="center"><?=$displayText; ?><br/> <?=$displayMobileText;?></td>
                                    <?php }
                                    if($gridKey === 'goodsSellFl'){ ?>
                                        <!--판매상태-->
                                        <td class="center"><?=$sellText;?><br/> <?=$sellMobileText; ?></td>
                                    <?php }
                                    if($gridKey === 'soldOutFl'){ ?>
                                        <!--품절상태-->
                                        <td class="center"><?=$stockText;?></td>
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
                                    if($gridKey === 'delDt'){ ?>
                                    <!--등록일 수정일-->
                                        <td class="center date">
                                            <?=gd_date_format('Y-m-d', $val['regDt']); ?>
                                            <?php if ($val['delDt']) { echo " / <br/>" . gd_date_format('Y-m-d', $val['delDt']);} ?>
                                        </td>
                                    <?php
                                    }
                                }
                            }
                            ?>
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
                <button type="button" class="btn btn-white checkReStore">상품복구</button>
                <button type="button" class="btn btn-white checkDelete">완전삭제</button>
            </div>
            <div class="pull-right">
                <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGoods" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>" data-target-list-form="frmList" data-target-list-sno="goodsNo">엑셀다운로드</button>
            </div>
        </div>
    </form>

    <div class="center"><?=$page->getPage(); ?></div>



    <div class="display-none"  id="lay_reStore">
        <table class="table table-cols">
            <tbody>
            <tr>
                <th>노출상태</th>
                <td><label><input type="radio" name="chkGoodsDisplayFl"  value="y"  checked='checked' />노출함</label>
                    <label><input type="radio" name="chkGoodsDisplayFl"  value="n"  />노출안함</label>
                </td>
            </tr>
            <tr>
                <th>판매상태</th>
                <td><label><input type="radio" name="chkGoodsSellFl"  value="y"  checked='checked'/>판매함</label>
                    <label><input type="radio" name="chkGoodsSellFl"  value="n" />판매안함</label>
                </td>
            </tr>
            </tbody>
        </table>
        <div><button class="btn  btn-default checkReStoreConfirm" type="button" onclick="sumbit_restore();">확인</button></div>
    </div>

</div>
