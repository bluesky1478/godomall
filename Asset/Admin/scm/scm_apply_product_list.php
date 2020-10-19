<script type="text/javascript">
    <!--
    $(document).ready(function () {

        // 삭제
        $('button.checkDelete').click(function () {

            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if (chkCnt == 0) {
                alert('선택된 상품이 없습니다.');
                return;
            }
            if (confirm('선택한 ' + chkCnt + '개 상품을  정말로 삭제하시겠습니까?\n삭제 된 상품은 [삭제상품 리스트]에서 확인 가능합니다.')) {
                $('#frmList input[name=\'mode\']').val('delete_state');
                $('#frmList').attr('method', 'post');
                $('#frmList').attr('action', '../goods/goods_ps.php');
                $('#frmList').submit();
            }
        });

        $('button.checkApply').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if(chkCnt > 0) {
                var addMsg = "";
                $('#frmList input[name*="goodsNo["]:checkbox:checked').each(function () {
                    console.log($(this).data('apply-fl'));
                    if($(this).data('apply-fl') !='a') {
                        addMsg = "승인 요청 상품 외에 상태 변경 불가능합니다.<br/>";
                        $(this).prop("checked",false);
                        chkCnt--;
                    }
                });
            }

            if (chkCnt == 0) {
                if(addMsg) {
                    alert(addMsg);
                } else {
                    alert('선택된 상품이 없습니다.');
                }
                return;
            }

            dialog_confirm(addMsg+' 선택한 ' + chkCnt + '개 상품을  정말로 승인하시겠습니까?', function (result) {
                if (result) {
                    //상품수정일 변경 확인 팝업
                    <?php
                    $goodsConfig = (gd_policy('goods.display')); //상품  설정 config 불러오기
                    $goodsConfig['goodsModDtTypeScm'] = gd_isset($goodsConfig['goodsModDtTypeScm'], 'y');
                    $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');
                    if ($goodsConfig['goodsModDtTypeScm'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                        if (result2) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $('#frmList input[name=\'mode\']').val('apply');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('action', '../goods/goods_ps.php');
                        $('#frmList').submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeScm'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $('#frmList input[name=\'mode\']').val('apply');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('action', '../goods/goods_ps.php');
                        $('#frmList').submit();
                    <?php } ?>
                }
            });

        });

        $('button.checkReject').click(function () {
            var chkCnt = $('input[name*="goodsNo"]:checked').length;

            if(chkCnt > 0) {
                var addMsg = "";
                $('#frmList input[name*="goodsNo["]:checkbox:checked').each(function () {
                    console.log($(this).data('apply-fl'));
                    if($(this).data('apply-fl') !='a') {
                        addMsg = "승인 요청 상품 외에 상태 변경 불가능합니다.<br/>";
                        $(this).prop("checked",false);
                        chkCnt--;
                    }
                });
            }

            if (chkCnt == 0) {
                if(addMsg) {
                    alert(addMsg);
                } else {
                    alert('선택된 상품이 없습니다.');
                }
                return;
            }

            var data = '<div class="text-center">'+addMsg+'선택한 ' + chkCnt + '개 상품을  정말로 반려 하시겠습니까?<div>';

            layer_popup(data+$("#lay_reject").html(), '선택상품 반려');
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

    /**
     * 정보 보기
     *
     * @param string modeStr 레이어창 종류
     * @param string typeStr 레이어창 타입
     * @param string sno 사은품 증정 sno
     */
    function layer_info_view(goodsNo)
    {
        var loadChk	= $('#viewInfoForm').length;
        var title = "변경 이력";

        $.post('./layer_goods_apply_info.php',{ goodsNo : goodsNo }, function(data){
            if (loadChk == 0) {
                data = '<div id="viewInfoForm">'+data+'</div>';
            }
            var layerForm = data;

            BootstrapDialog.show({
                title:title,
                size : BootstrapDialog.SIZE_WIDE,
                message: $(layerForm),
                closable: true
            });

        });

    }


    //-->
</script>

<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
    <input type="hidden" name="delFl" value="<?php echo $search['delFl']; ?>"/>
    <input type="hidden" name="searchDateFl" value="applyDt"/>
    <input type="hidden" name="searchFl" value="y"/>

    <div class="table-title">
        상품 승인 검색
    </div>
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
                <th>공급사 구분</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?php echo gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="n" <?php echo gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="y" <?php echo gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/>공급사
                    </label>
                    <label>
                        <button type="button" class="btn btn-gray btn-sm" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                    </label>

                    <div id="scmLayer" class="selected-btn-group width100p <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
                        <?php if ($search['scmFl'] == 'y') {
                            foreach ($search['scmNo'] as $k => $v) { ?>
                                <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>
                                </span>
                            <?php }
                        } ?>
                    </div>
                </td>
            </tr>
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
                        <?= gd_search_date($search['searchPeriod'],'searchDate[]') ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>승인구분</th>
                <td>
                    <?php foreach($search['applyTypeList'] as $k => $v) { ?>
                        <label class="radio-inline">
                            <input type="radio" name="applyType" value="<?=$k?>" <?php echo gd_isset($checked['applyType'][$k]); ?> /><?=$v?>
                        </label>
                    <?php } ?>
                </td>
                <th>승인상태</th>
                <td>
                    <?php foreach($search['applyFlList'] as $k => $v) { ?>
                        <label class="radio-inline">
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
                        &nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="categoryNoneFl" value="y" <?php echo gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>브랜드</th>
                <td>
                    <label><input type="text" name="brandCdNm" value="<?php echo $search['brandCdNm']; ?>" class="form-control"/> </label>
                    <label><input type="button" class="btn btn-gray btn-sm" value="브랜드검색" onclick="layer_register('brand', 'radio')"/></label>
                    &nbsp;&nbsp;<label class="checkbox-inline"><input type="checkbox" name="brandNoneFl" value="y" <?php echo gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품</label>
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

    <div class="text-center table-btn">
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
        <input type="hidden" name="modDtUse" value="" />
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width5p center"><input type="checkbox" class="js-checkall" data-target-name="goodsNo"></th>
                <th class="width5p">번호</th>
                <th class="width10p">승인상태</th>
                <th class="width10p">승인구분</th>
                <th class="width10p">공급사</th>
                <th class="width-2xs">이미지</th>
                <th class="width40p">상품명</th>
                <th class="width10p">판매가</th>
                <th class="width5p">재고</th>
                <th class="width10p">승인요청일</th>
                <th class="width5p">변경이력</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                $arrGoodsDisplay = array('y' => '출력', 'n' => '미출력');
                $arrGoodsSell = array('y' => '판매', 'n' => '판매중지');
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
                        <td class="center"><input type="checkbox" name="goodsNo[<?php echo $val['goodsNo']; ?>]" value="<?php echo $val['goodsNo']; ?>"  data-apply-fl="<?=$val['applyFl']?>"/></td>
                        <td class="center number"><?php echo number_format($page->idx--); ?></td>
                        <td class="center number"><?php echo $search['applyFlList'][$val['applyFl']]; ?> <?php if($val['applyFl'] =='r') { ?> <input type="button" value="사유" onclick="layer_apply_msg('<?=$val['applyMsg']?>')"> <?php } ?> </td>
                        <td class="center number"><?php echo $search['applyTypeList'][$val['applyType']]; ?><input type="hidden" name="applyType[<?php echo $val['goodsNo']; ?>]" value="<?php echo $val['applyType']; ?>"/></td>
                        <td class="center lmenu"><?php echo $val['scmNm']; ?>
                        <td>
                            <div class="width-2xs">
                                <?php echo gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            </div>
                        </td>
                        <td>
                            <div onclick="goods_register_popup('<?php echo $val['goodsNo']; ?>');"
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
                        <td class="center"><input type="button" value="보기" class="btn btn-white btn-xs" onclick="layer_info_view('<?=$val['goodsNo']?>')"></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="10">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>

        <div class="table-action">
            <div class="pull-left">
                <button type="button" class="btn btn-white btn-xs checkApply">선택상품 승인</button>
                <button type="button" class="btn btn-white btn-xs checkReject">선택상품 반려</button>
                <button type="button" class="btn btn-white btn-xs checkDelete">선택상품 삭제</button>
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

