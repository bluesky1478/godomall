<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?> </h3>
    <div class="btn-group">
        <input type="button" value="저장" class="btn btn-red" id="batchSubmit"/>
    </div>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmBatchIcon" name="frmBatchIcon" action="./goods_ps.php"  method="post"  target="ifrmProcess">
    <input type="hidden" name="mode" value="batch_icon" />
    <input type="hidden" name="modDtUse" value="" />
    <?php
    foreach ($batchAll as $key => $val) {
        echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
    }
    ?>
    <div class="table-responsive">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width-2xs center"><input type="checkbox" class="js-checkall" data-target-name="arrGoodsNo[]"></th>
                <th class="width-2xs center">번호</th>
                <th class="width-xs center">상품코드</th>
                <th class="width-xs">이미지</th>
                <th class="width-lg center">상품명</th>
                <th class="width-xs center">공급사</th>
                <th class="width-xs center">노출상태</th>
                <th class="width-xs center">판매상태</th>
                <th class="width-md center">판매가</th>
                <th class="width-xs center">대표색상</th>

                <?php
                foreach ($icon as $key => $val) {
                    if ($val['iconPeriodFl'] == 'y') {
                        echo '<th class="width-3xs center">'.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'</th>';
                    }
                }
                ?>

                <?php
                foreach ($icon as $key => $val) {
                    if ($val['iconPeriodFl'] == 'n') {
                        echo '<th class="width-3xs center">'.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'</th>';
                    }
                }
                ?>

            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && count($data) > 0 ) {
                $arrGoodsDisplay = ['y' => '노출함', 'n' => '노출안함'];
                $arrGoodsSell = ['y' => '판매함', 'n' => '판매안함'];
                $goodsConfig = (gd_policy('goods.display')); //상품  설정 config 불러오기
                $goodsConfig['goodsModDtTypeAll'] = gd_isset($goodsConfig['goodsModDtTypeAll'], 'y');
                $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');

                foreach ($data as $key => $val) {
                    if($val['goodsColor']) $val['goodsColor'] = explode(STR_DIVISION, $val['goodsColor']);

                    ?>
                    <tr>
                        <td class="center number">
                            <input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo']; ?>"/>
                        </td>
                        <td class="center"><?=number_format($page->idx--); ?></td>
                        <td class="center number"><?=$val['goodsNo']; ?></td>
                        <td class="center">
                            <div class="width-2xs">
                                <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            </div>
                        </td>
                        <td>
                            <a href="./goods_register.php?goodsNo=<?=$val['goodsNo']; ?>" target="_blank"><span class="emphasis_text"><?=$val['goodsNm']; ?></span></a>

                        </td>
                        <td class="center"><?= $val['scmNm'] ?></td>
                        <td class="center lmenu"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                        <td class="center lmenu"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                        <td class="center number">
                            <div class="form-inline"><?=gd_currency_symbol(); ?><?=gd_money_format($val['goodsPrice']); ?><?=gd_currency_string(); ?></div>
                        </td>
                        <td class="center">
                            <?php if(is_array($val['goodsColor'])) {
                                foreach(array_unique($val['goodsColor']) as $k => $v) {
                                        if (!in_array($v,$goodsColorList) ) {
                                            continue;
                                        }
                                    ?>

                                    <div class='btn-group btn-group-xs'>
                                        <button type='button' class='btn btn-default js-popover' data-html='true' data-content='<?=array_flip($goodsColorList)[$v]?>' data-placement='bottom' style='background:#<?=$v?>;'>&nbsp;&nbsp;&nbsp;</button>
                                    </div>

                                <?php } } ?>

                        </td>
                        <?php
                        foreach ($icon as $k => $v) {
                            if ($v['iconPeriodFl'] == 'y') { ?>
                                <td class="width-3xs center"><input type="checkbox" name="goodsIconCdPeriod[<?=$val['goodsNo']?>][]" value="<?=$v['iconCd']?>" <?php if($val['goodsIconCdPeriod'][$v['iconCd']]) { echo "checked='checked'"; } ?>></td>
                            <?php }
                        }
                        ?>
                        <?php
                        foreach ($icon as $k => $v) {
                            if ($v['iconPeriodFl'] == 'n') { ?>
                              <td class="width-3xs center"><input type="checkbox" name="goodsIconCd[<?=$val['goodsNo']?>][]" value="<?=$v['iconCd']?>" <?php if($val['goodsIconCd'][$v['iconCd']]) { echo "checked='checked'"; } ?>></td>
                            <?php }
                        }
                        ?>
                    </tr>
                    <?php
                }
            }  else {

                ?>
                <tr><td class="no-data" colspan="<?=(10+count($icon))?>">검색된 정보가 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="center"><?=$page->getPage();?></div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th class="center">
                    <select name="type" onchange="view_terms(this.value)" class="form-control">
                        <option value="icon">아이콘</option>
                        <option value="color">대표색상</option>
                    </select><br/>
                    조건설정
                </th>
                <td id="display_set">
                    <label class="checkbox-inline"><input type="checkbox" id="batchAll" name="batchAll" value="y" />검색된 상품 전체(<?=number_format($page->recode['total']);?>개 상품)를 수정합니다.</label>
                    <p class="notice-danger mgt5">상품수가 많은 경우 비권장합니다. 가능하면 한 페이지씩 선택하여 수정하세요.</p>
                    <input type="hidden" name="termsFl" value="n" >
                    <div id="tbl_set_icon">
                    <table class="table table-cols mgt5">
                        <colgroup><col class="width-md" /><col/></colgroup>
                        <tr>
                            <th>기간제한용</th>
                            <td>

                                <div class="form-inline">시작일 / 종료일
                                    <label title="아이콘 기간 제한용 시작일을 선택/작성(yyyy-mm-dd)해 주세요!">
                                        <div class="form-inline">
                                            <div class="input-group js-datetimepicker">
                                                <input type="text" name="icon[goodsIconStartYmd]" class="form-control width-sm" value=""  placeholder="수기입력 가능">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                                            </div>
                                        </div>
                                    </label>
                                    ~
                                    <label title="아이콘 기간 제한용 유효일자 종료일을 선택/작성(yyyy-mm-dd)해 주세요!">
                                        <div class="form-inline">
                                            <div class="input-group js-datetimepicker endDatetime">
                                                <input type="text" name="icon[goodsIconEndYmd]" class="form-control width-sm" value=""  placeholder="수기입력 가능">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                                            </div>
                                        </div>
                                    </label>
                                </div>

                                <?php
                                foreach ($icon as $key => $val) {
                                    if ($val['iconPeriodFl'] == 'y') {
                                        echo '<label class="nobr checkbox-inline"><input type="checkbox" name="icon[goodsIconCdPeriod][]" value="'.$val['iconCd'].'"  /> '.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'&nbsp;&nbsp;&nbsp;</label>'.chr(10);
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th>무제한용 </th>
                            <td>
                                <?php
                                foreach ($icon as $key => $val) {
                                    if ($val['iconPeriodFl'] == 'n') {
                                        echo '<label class="nobr checkbox-inline"><input type="checkbox" name="icon[goodsIconCd][]" value="'.$val['iconCd'].'"  /> '.gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']).'</label>'.chr(10);
                                    }
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                        <?php if (gd_is_provider() === false) { ?>
                        <p class="notice-info">
                            아이콘은 <a href="/goods/goods_icon_list.php" target="_blank">[상품관리&gt;상품 아이콘 관리]</a>에서 추가할 수 있습니다.
                        </p>
                        <?php } ?>
                    </div>
                    <div id="tbl_set_color" class="display-none">
                    <table class="table table-cols">
                        <tbody>
                        <tr><td>  <div class="form-inline">대표색상 : <label class="radio-inline"><input type="radio" name="colorType" value="add">추가</label><label class="radio-inline"><input type="radio" name="colorType" value="update">변경</label><label class="radio-inline"><input type="radio" name="colorType" value="del">전체삭제</label></td></tr>
                        <tr>
                            <td >
                                <?php foreach($goodsColorList as $k => $v) { ?>
                                    <button type="button" class="btn btn-default btn-xs js-popover" data-html="true" data-color="<?=$v?>" data-content="<?=$k?>" data-placement="bottom" style="background:#<?=$v?>;border:1px solid #efefef;" onclick="selectColor(this,'#frmBatchIcon','goodsColor_')">&nbsp;&nbsp;&nbsp;</button>
                                <?php } ?>
                                <br/>선택색상 : <span id="selectColorLayer"></span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                        <p class="notice-info">
                            대표색상은 상품 검색시에 사용되며 <a href='/policy/base_code_list.php?categoryGroupCd=05' target="_blank" class="btn-link">기본설정>기본 정책>코드 관리</a>에서 추가할 수 있습니다.
                        </p>
                        </div>
                </td>
            </tr>
        </table>


    </div>
</form>

<script type="text/javascript">
    <!--

    $(document).ready(function(){


        if($('input[name=detailSearch]').val() !='y')
        {
            $('.js-search-detail').show();
            $('.js-search-detail tr').hide();
            $('.js-search-detail .js-search-icon').show();
        }


        $( ".js-search-toggle" ).click(function() {

            var detailSearch = $('input[name=detailSearch]').val();

            if(detailSearch == 'y')
            {
                $('.js-search-detail tr').show();
            }
            else
            {
                $('.js-search-detail').show();
                $('.js-search-detail tr').hide();
                $('.js-search-detail .js-search-icon').show();
            }

        });


        $( "#batchSubmit" ).click(function() {

            var msg = "";

            var type =  $('select[name="type"]').val();

            var goodsIconStartYmd = $('#frmBatchIcon input[name="icon[goodsIconStartYmd]"]').val();
            var goodsIconEndYmd = $('#frmBatchIcon input[name="icon[goodsIconEndYmd]"]').val();
            var goodsIconCdPeriod = $('#frmBatchIcon input[name="icon[goodsIconCdPeriod][]"]:checked').length;
            var goodsIconCd = $('#frmBatchIcon input[name="icon[goodsIconCd][]"]:checked').length;

            var colorType = $('#frmBatchIcon input[name="colorType"]:checked').length;
            var goodsColor = $('#frmBatchIcon input[name="goodsColor[]"]').length;

            if(goodsIconStartYmd == '' &&  goodsIconEndYmd == '' &&  goodsIconCdPeriod == '0' && goodsIconCd =='0' &&  colorType == '0' &&  goodsColor == '0')
            {
                if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                    $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                    return false;
                }

                $('#frmBatchIcon input[name="termsFl"]').val('n');

                msg += "선택된 상품을 수정하시겠습니까?\n";
                msg += "[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.";

                $('#frmBatchIcon input[name="termsFl"]').val('n');
            }
            else
            {
                if ($('#batchAll:checked').length == 0) {
                    if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                        $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                        return false;
                    }

                    msg += '선택된 상품의';
                } else {
                    msg += '검색된 전체 상품의';
                }


                if(type =='icon')
                {

                    msg += ' 아이콘을 ';

                    if((goodsIconStartYmd || goodsIconEndYmd || goodsIconCdPeriod > 0) && goodsIconCd > 0)
                    {
                        msg += '기간제한용,';
                    } else if(goodsIconStartYmd || goodsIconEndYmd || goodsIconCdPeriod > 0) {
                        msg += '기간제한용 아이콘 설정 정보로';
                    }


                    if(goodsIconCd > 0)
                    {
                        msg += '무제한용 아이콘 설정 정보로';
                    }

                    msg += '\n일괄 수정하시겠습니까?\n\n';

                }

                if(type =='color')
                {
                    msg += ' 대표색상을 ';

                    if(colorType =='0')
                    {
                        $.warnUI('항목 체크', '대표 색상 변경 타입을 선택해주세요.');
                        return false;
                    }

                    if($('#frmBatchIcon input[name="colorType"]:checked').val() != 'del' && goodsColor =='0')
                    {
                        $.warnUI('항목 체크', '대표색상을 선택해주세요.');
                        return false;
                    }

                    var colorType = $('#frmBatchIcon input[name="colorType"]:checked').val();

                    if(colorType =='add') {
                        msg += ' 추가 하시겠습니까?\n\n';
                    } else if (colorType =='update') {
                        msg += ' 변경 하시겠습니까?\n\n';
                    } else if (colorType =='del') {
                        msg += ' 전체삭제 하시겠습니까?\n\n';;
                    }
                }

                msg += '[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';

                $('#frmBatchIcon input[name="termsFl"]').val('y');
            }

            dialog_confirm(msg, function (result) {
                if (result) {
                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                        if (goodsColor == 0 && goodsIconCd > 0 || goodsColor == 0 && goodsIconCd == 0) {
                            //상품 수정일 변경 범위설정 체크
                            <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                            <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                            <?php } ?>
                            $( "#frmBatchIcon").submit();
                        } else {
                            dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                                if (result2) {
                                    $('input[name="modDtUse"]').val('y');
                                } else {
                                    $('input[name="modDtUse"]').val('n');
                                }
                                $( "#frmBatchIcon").submit();
                            }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                        }
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $( "#frmBatchIcon").submit();
                    <?php } ?>
                }
            });

        });



        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });


    });



    function view_terms(id)
    {
        $("#display_set div[id*='tbl_set_']").hide();
        $("#tbl_set_"+id).show();
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
