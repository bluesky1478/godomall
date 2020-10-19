<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
    </div>
</div>

<div class="panel pd10">
    <p><b style="font-size: 15px;">이미지호스팅 일괄전환이란?</b></p>
    <p>
        오픈마켓에 입점한 운영자는 반드시 이미지호스팅을 사용해야 합니다.<br/>
        내 상점에 등록한 상품수가 많을 경우 하나하나 이미지호스팅으로 수정하는 시간이 많이 걸리게 됩니다.<br/>
        아래 기능은 내 쇼핑몰에 올려진 상품설명이미지를 이미지호스팅으로 빠르게 전환해주는 기능입니다.<br/>
        이 기능을 사용하려면 이미지호스팅이 신청되어 있어야 합니다.
        <a href="http://hosting.godo.co.kr/imghosting/imghosting_info.php" class="btn btn-gray btn-sm" target="_blank">서비스 자세히 보기</a>
    </p>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th rowspan="2" class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="goodsNo"></th>
            <th rowspan="2" class="width5p">번호</th>
            <th rowspan="2" class="width10p">상품코드</th>
            <th rowspan="2" class="width-2xs">이미지</th>
            <th rowspan="2" class="width20p">상품명</th>
            <th rowspan="2" class="width10p">전환이 필요한<br/>이미지갯수</th>
            <th rowspan="2" class="width10p">공급사</th>
            <th colspan="2" class="width10p">노출상태</th>
            <th colspan="2" class="width10p">판매상태</th>
            <th rowspan="2" class="width5p">재고</th>
            <th rowspan="2" class="width10p">판매가</th>
            <th rowspan="2" class="width10p">등록일 / 수정일</th>
        </tr>
        <tr>
            <th class="width5p center">PC</th>
            <th class="width5p center">모바일</th>
            <th class="width5p center">PC</th>
            <th class="width5p center">모바일</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            $arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');
            $arrGoodsSell = array('y' => '판매함', 'n' => '판매안함');
            $arrGoodsTax = array('t' => '과세', 'f' => '면세');
            $arrGoodsApply = array('a' => '승인요청','y'   => '승인완료','r'  => '반려','n'  => '철회',);
            $arrDeliveryFree = array('one' => '해당 상품만', 'goods' => '상품별 배송', 'all' => '모두 무료');
            foreach ($data as $key => $val) {

                list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                //$val['applyFl'] = ($val['applyFl'] == '') ? '' : $val['applyFl'];
                if ($val['applyFl'] !='y') {
                    $displayText = $arrGoodsApply[$val['applyFl']];
                    $displayMobileText = $arrGoodsApply[$val['applyFl']];
                    $sellText = $arrGoodsApply[$val['applyFl']];
                    $sellMobileText = $arrGoodsApply[$val['applyFl']];
                } else {
                    $displayText = $arrGoodsDisplay[$val['goodsDisplayFl']];
                    $displayMobileText = $arrGoodsDisplay[$val['goodsDisplayMobileFl']];
                    $sellText = $arrGoodsSell[$val['goodsSellFl']];
                    $sellMobileText = $arrGoodsSell[$val['goodsSellMobileFl']];
                }

                $imageHostingReplace = \App::load('\\Component\\Goods\\ImageHostingReplace');
                $aImageCount = $imageHostingReplace->getImageReplaceCount($val['goodsDescription']);
                ?>
                <tr>
                    <td class="center"><input type="checkbox" name="goodsNo[]" value="<?=$val['goodsNo']; ?>" <?php if ($aImageCount['in'] == 0) { echo "disabled = 'true'"; }  ?> /></td>
                    <td class="center number"><?=number_format($page->idx--); ?></td>
                    <td class="center number"><?=$val['goodsNo']; ?></td>
                    <td class="width-2xs center">
                            <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                    </td>
                    <td>
                        <div onclick="goods_register_popup('<?=$val['goodsNo']; ?>' <?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"
                             class="hand"><?=$val['goodsNm']; ?></div>
                        <div
                            class="notice-ref notice-sm"><?=Globals::get('gDelivery.' . $val['deliveryFl']); ?><?php if ($val['deliveryFl'] == 'free') {
                                echo '(' . $arrDeliveryFree[$val['deliveryFree']] . ')';
                            } ?></div>
                        <div>
                            <?php
                            // 기간 제한용 아이콘
                            if (empty($val['goodsIconCdPeriod']) === false && is_array($val['goodsIconCdPeriod']) === true) {
                                foreach ($val['goodsIconCdPeriod'] as $iKey => $iVal) {
                                    echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                }
                            }
                            // 상품 아이콘
                            if (empty($val['goodsIconCd']) === false && is_array($val['goodsIconCd']) === true) {
                                foreach ($val['goodsIconCd'] as $iKey => $iVal) {
                                    echo gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                }
                            }
                            // 품절 체크
                            if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
                                echo gd_html_image(UserFilePath::icon('goods_icon')->www() . '/' . 'icon_soldout.gif', '품절상품');
                            }

                            if($val['timeSaleSno']) {
                                echo "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' />";
                            }
                            ?>

                        </div>
                    </td>
                    <td class="center lmenu" id="in_<?=$val['goodsNo']; ?>"><span style="font-weight: bold; font-size: 19px; color: #0070c0;"><?=number_format($aImageCount['in'])?></span></td>
                    <td class="center lmenu"><?=$val['scmNm']; ?></td>
                    <td class="center lmenu"><?=$displayText; ?></td>
                    <td class="center lmenu"><?=$displayMobileText; ?></td>
                    <td class="center lmenu"><?=$sellText; ?></td>
                    <td class="center lmenu"><?=$sellMobileText; ?></td>
                    <td class="center number"><?=$totalStock; ?></td>
                    <td class="center">
                        <div><span class="font-num"><?=gd_currency_display($val['goodsPrice']); ?></span></div>
                    </td>                    
                    <td class="center date">
                        <?=gd_date_format('Y-m-d', $val['regDt']); ?>
                        <?php if ($val['modDt']) { echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);} ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="12">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-check-replace">전환하기</button>
        </div>
        <div class="pull-right">
            <!--button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGoods" data-target-list-form="frmList" data-target-list-sno="goodsNo" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>">엑셀다운로드</button-->
        </div>
    </div>
</form>
<div class="text-center"><?=$page->getPage(); ?></div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('button.js-check-replace').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            var loadChk = $('#layerImageHostingReplaceForm').length;

            $.get('layer_goods_image_hosting_replace.php', {}, function (data) {
                if (loadChk == 0) {
                    data = '<div id="layerImageHostingReplaceForm">' + data + '</div>';
                }
                var layerForm = data;
                layer_ui(layerForm, '이미지호스팅 전환하기');
            });
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });

    });

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
