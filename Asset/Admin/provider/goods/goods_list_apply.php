<script type="text/javascript">
    <!--
    $(document).ready(function () {


        $('button.checkWithdraw').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;
            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 상품을  정말로 승인 철회 하시겠습니까?', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('applyWithdraw');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './goods_ps.php');
                    $('#frmList').submit();
                }
            });

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

    /**
     * 반려사유
     */
    function layer_apply_msg(msg) {

        layer_popup("<div>"+msg+"</div>", '반려 사유');
    }


    /**
     * 반려
     */
    function sumbit_reject() {

        $('#frmList input[name=\'applyMsg\']').val($(".bootstrap-dialog-body input[name='applyMsg']").val());


        $('#frmList input[name=\'mode\']').val('applyReject');
        $('#frmList').attr('method', 'post');
        $('#frmList').attr('action', '../goods/goods_ps.php');
        $('#frmList').submit();

    }


    //-->
</script>

<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>



<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="delFl" value="<?php echo $search['delFl']; ?>"/>
    <input type="hidden" name="searchFl" value="y"/>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>승인요청일</th>
                <td colspan="3">
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][0]; ?>" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][1]; ?>" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod']) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>승인구분</th>
                <td>
                    <?php foreach($search['applyTypeList'] as $k => $v) { ?>
                        <label>
                            <input type="radio" name="applyType" value="<?=$k?>" <?php echo gd_isset($checked['applyType'][$k]); ?> /><?=$v?>
                        </label>
                    <?php } ?>
                </td>
                <th>승인상태</th>
                <td>
                    <?php foreach($search['applyFlList'] as $k => $v) { ?>
                        <label>
                            <input type="radio" name="applyFl" value="<?=$k?>" <?php echo gd_isset($checked['applyFl'][$k]); ?>  /><?=$v?>
                        </label>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>카테고리</th>
                <td class="contents" colspan="3">
                    <div class="form-inline">
                        <?php echo $cate->getMultiCategoryBox(null, $search['cateGoods']); ?>
                        &nbsp;&nbsp;<input type="checkbox" name="categoryNoneFl" value="y" <?php echo gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품
                    </div>
                </td>
            </tr>
            <tr>
                <th>브랜드</th>
                <td>
                    <label><input type="text" name="brandCdNm" value="<?php echo $search['brandCdNm']; ?>" class="form-control"/> </label>
                    <label><input type="button" value="브랜드검색" class="btn btn-sm btn-default"   onclick="layer_register('brand', 'radio')"/></label>
                    &nbsp;&nbsp;<input type="checkbox" name="brandNoneFl" value="y" <?php echo gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품
                    <div id="brandLayer" class="width100p">
                        <?php if ($search['brandCd']) { ?>
                            <span id="idbrand<?= $search['brandCd'] ?>" class="pull-left">
                        <input type="hidden" name="brandCd" value="<?= $search['brandCd'] ?>"/>
                        </span>
                        <?php } ?>
                    </div>
                </td>
                <th>판매가</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="goodsPrice[]" value="<?php echo $search['goodsPrice'][0]; ?>" class="form-control width-sm js-number"/> ~
                        <input type="text" name="goodsPrice[]" value="<?php echo $search['goodsPrice'][1]; ?>" class="form-control width-sm js-number"/>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?php echo number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?php echo number_format($page->recode['amount']);?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>

</form>



<div>
    <form id="frmList" action="" method="get" target="ifrmProcess">
        <input type="hidden" name="mode" value="">
        <input type="hidden" name="applyMsg" value="">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="goodsNo"></th>
                <th class="width2p">번호</th>
                <th class="width5p">승인상태</th>
                <th class="width10p">승인구분</th>
                <th class="width-2xs">이미지</th>
                <th >상품명</th>
                <th class="width10p">판매가</th>
                <th class="width5p">재고</th>
                <th class="width10p">승인요청일</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                $arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');
                $arrGoodsSell = array('y' => '판매함', 'n' => '판매안함');
                $arrGoodsTax = array('t' => '과세', 'f' => '비과세');
                $arrDeliveryFree = array('one' => '해당 상품만', 'goods' => '상품별 배송', 'all' => '모두 무료');
                foreach ($data as $key => $val) {
                    // 상품 재고
                    if ($val['stockFl'] == 'n') {
                        $totalStock = '∞';
                    } else {
                        $totalStock = number_format($val['totalStock']);
                    }
                    ?>
                    <tr <?php if($val['applyFl'] =='a') { echo "style=background:#efefef"; } ?>>
                        <td class="center"><input type="checkbox" name="goodsNo[<?php echo $val['goodsNo']; ?>]" value="<?php echo $val['goodsNo']; ?>"  <?php if($val['applyFl'] !='a') { echo "disabled='disabled'"; } ?> /></td>
                        <td class="center number"><?php echo number_format($page->idx--); ?></td>
                        <td class="center number"><?php echo $search['applyFlList'][$val['applyFl']]; ?> <?php if($val['applyFl'] =='r') { ?> <input type="button" class="btn btn-gray btn-xs " onclick="layer_apply_msg('<?=$val['applyMsg']?>')" value="사유"> <?php } ?> </td>
                        <td class="center number"><?php echo $search['applyTypeList'][$val['applyType']]; ?></td>
                        <td>
                            <div class="width-2xs">
                                <?php echo gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            </div>
                        </td>
                        <td>
                            <div onclick="goods_register_popup('<?php echo $val['goodsNo']; ?>'<?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"
                                 class="hand"><?php echo $val['goodsNm']; ?></div>
                            <div
                                class="notice-ref notice-sm"><?php echo Globals::get('gDelivery.' . $val['deliveryFl']); ?><?php if ($val['deliveryFl'] == 'free') {
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
                                    echo gd_html_icon('soldout') . ' ';
                                }
                                ?>
                            </div>
                        </td>
                        <td class="center">
                            <div><span class="font-num"><?php echo gd_currency_display($val['goodsPrice']); ?></span></div>
                        </td>
                        <td class="center number"><?php echo $totalStock; ?></td>
                        <td class="center date">
                            <?php echo gd_date_format('Y-m-d', $val['applyDt']); ?>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="9">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white checkWithdraw">선택상품 철회</button>
            </div>
            <div class="pull-right">
                <!-- <button type="button" class="btn btn-white btn-icon-excel">엑셀다운로드</button> -->
            </div>
        </div>


    </form>
    <div class="center"><?php echo $page->getPage(); ?></div>


    <div class="display-none"  id="lay_reject">
        <table class="table table-cols">
            <tbody>

            <tr>
                <th>반려사유</th>
                <td><label><input type="text" name="applyMsg" class="form-control"/></label>
                </td>
            </tr>
            </tbody>
        </table>
        <div><button class="btn  btn-default checkReStoreConfirm" type="button" onclick="sumbit_reject();">확인</button></div>
    </div>

</div>

