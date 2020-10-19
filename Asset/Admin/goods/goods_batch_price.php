<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?> </h3>
    <div class="btn-group">
        <input type="button" value="저장" class="btn btn-red" id="batchSubmit"/>
    </div>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmBatchPrice" name="frmBatchPrice" action="./goods_ps.php"  target="ifrmProcess" method="post">
<input type="hidden" name="mode" value="batch_price" />
<input type="hidden" name="isPrice" value="" />
<input type="hidden" name="modDtUse" value="" />
<?php
    foreach ($batchAll as $key => $val) {
        echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
    }
?>

<div  class="table-responsive">
    <table class="table table-rows  table-fixed">
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
        <th class="width-lg center">정가</th>
        <th class="width-lg center">매입가</th>
        <th class="width-lg center">판매가</th>
        <th class="width-xs center">옵션</th>
    </tr>
    <tr style="background: #f9f9f9;">
        <td class="width-xs left pd10" colspan="8" ><span class="select-goods">선택한 상품</span><button type="button" class="btn btn-black btn-18 mgl20" onclick="set_price()" >가격 일괄적용</button> </td>
        <td class="width-md center"><div class="form-inline"><?=gd_currency_symbol();?>
                <input type="text" name="setFixedPrice"  value=""  class="form-control width-sm" />
                <?=gd_currency_string();?></div></td>
        <td class="width-md center"><div class="form-inline"> <?=gd_currency_symbol();?>
                <input type="text" name="setCostPrice"  value=""  class="form-control width-sm" />
                <?=gd_currency_string();?></div></td>
        <td class="width-md center"><div class="form-inline"><?=gd_currency_symbol();?>
                <input type="text" name="setGoodsPrice"  value=""  class="form-control width-sm" >
                <?=gd_currency_string();?></div></td>
        <td></td>
    </tr>
    </thead>
    <tbody>

<?php
if (gd_isset($data) && count($data) > 0 ) {
        $arrGoodsDisplay = ['y' => '노출함', 'n' => '노출안함'];
        $arrGoodsSell = ['y' => '판매함', 'n' => '판매안함'];

        // --- 상품  설정 config 불러오기
        $goodsConfig = (gd_policy('goods.display'));
        $goodsConfig['goodsModDtTypeAll'] = gd_isset($goodsConfig['goodsModDtTypeAll'], 'y');
        $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');

        foreach ($data as $key => $val) {
            // 상품 재고
            if ($val['stockFl'] == 'n') {
                $stockCnt = '∞';
            } else {
                $stockCnt = number_format($val['stockCnt']);
            }
            ?>
            <tr>
                <td class="center number">
                    <input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo']; ?>"/></td>
                <td class="center"><?=number_format($page->idx--); ?></td>
                <td class="center number"><?=$val['goodsNo']; ?></td>
                <td class="center">
                    <div class="width-2xs">
                        <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                    </div>
                </td>
                <td>
                    <a href="./goods_register.php?goodsNo=<?=$val['goodsNo']; ?>" target="_blank" class="btn-blue"><span class="emphasis_text"><?=$val['goodsNm']; ?></span></a>
                </td>
                <td class="center"><?= $val['scmNm'] ?></td>
                <td class="center lmenu"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                <td class="center lmenu"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                <td class="center number">
                    <div class="form-inline"><?=gd_currency_symbol(); ?>
                        <input type="text" name="fixedPrice[<?= $val['goodsNo']?>]" value="<?=gd_money_format($val['fixedPrice'], false); ?>" class="form-control width-sm js-type-price"/>
                        <?=gd_currency_string(); ?></div>
                </td>
                <td class="center number">
                    <div class="form-inline"> <?=gd_currency_symbol(); ?>
                        <input type="text" name="costPrice[<?= $val['goodsNo']?>]" value="<?=gd_money_format($val['costPrice'], false); ?>" class="form-control width-sm js-type-price"/>
                        <?=gd_currency_string(); ?></div>
                </td>
                <td class="center number">
                    <div class="form-inline"><?=gd_currency_symbol(); ?>
                        <input type="text" name="goodsPrice[<?= $val['goodsNo']?>]" value="<?=gd_money_format($val['goodsPrice'], false); ?>" class="form-control width-sm js-type-price">
                        <?=gd_currency_string(); ?></div>
                </td>
                <td class="center">
                    <a onclick="goods_register_popup('<?=$val['goodsNo']; ?>' <?php if(gd_is_provider() === true) { echo ",'1'";  } else { echo ",''"; } ?>,'#gd_goods_price');"class="btn btn-white btn-xs">수정</a>
                </td>
            </tr>
            <?php

        }
    } else {
?>
        <tr><td class="no-data" colspan="12">검색된 정보가 없습니다.</td></tr>
    <?php } ?>
    </tbody>
    </table>


</div>
    <div class="center"><?=$page->getPage();?></div>
<div class="mgt10"></div>
<?php
    $arrPriceType    = array('goodsPrice' => '판매가', 'fixedPrice' => '정가', 'costPrice' => '매입가');
    $arrMarkType    = array('w' => '원', 'p' => '%');
    $arrPlusMinus    = array('m' => '할인', 'p' => '할증');
    $arrRoundUnit    = array(1 => 1, 10 => 10, 100 => 100, 1000 => 1000);
    $arrRoundType    = array('up' => '올림', 'half' => '반올림', 'down' => '내림');
?>
<div>

    <table class="table table-cols">
    <colgroup><col class="width-md" /><col /></colgroup>
    <tr>
        <th>가격조건설정</th>
        <td>
            <div class="form-inline  mgb10">
                <label class="checkbox-inline"><input type="checkbox" id="batchAll" name="batchAll" value="y" />
                검색된 상품 전체(<?=number_format($page->recode['total']);?>개 상품)를 수정합니다.</label>
                <p class="notice-danger mgt5 ">상품수가 많은 경우 비권장합니다. 가능하면 한 페이지씩 선택하여 수정하세요.</p>
            </div>
            <div class="form-inline">
                <?=gd_select_box('confPrice','confPrice',$arrPriceType,null,'goodsPrice',null);?>
				<span>에서</span>&nbsp;
                <input type="text" id="pricePercent" name="pricePercent" value="" class="form-control"" />
                <?=gd_select_box('markType','markType',$arrMarkType,null,'w',null);?>을 &nbsp;
                <?=gd_select_box('plusMinus','plusMinus',$arrPlusMinus,null,'m',null);?>된 가격으로 &nbsp;
                <?=gd_select_box('targetPrice','targetPrice',$arrPriceType,null,'goodsPrice',null);?>을 일괄적으로 수정합니다. (절사기준 : <?=gd_trunc_display('goods');?>)
            </div>
        </td>
    </tr>
    </table>


</div>
</form>

<div id="lay_goods_price" style="display:none">
    <table class="table table-cols table-fixed">
        <colgroup><col class="width-sm" /><col /><col class="width-sm" /></colgroup>
        <thead>
        <tr><th>상품코드</th><th>상품명</th><th>변경 후 가격</th></tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

<script type="text/javascript">
<!--
$(document).ready(function(){

    //양수만 입력
    $("input.js-type-price").bind('keyup', function () {
        $(this).val($(this).val().replace(/[^0-9.]*/gi, ''));
    });


    $( "#batchSubmit" ).click(function() {

        var msg = '';
        var type = '';

        //input 가격 변경
        if (($('#pricePercent').val() == '' || parseInt($('#pricePercent').val()) < 0) && $('#batchAll:checked').length == 0) {

            if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                return false;
            }

            msg += "선택된 상품을 수정하시겠습니까?\n\n";
            msg += "[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.";

            type='select';

        }
        //하단 조건 가격 변경
        else
        {
            if ($('#batchAll:checked').length == 0) {
                if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                    $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                    return false;
                }

                msg += '선택된 상품을 ';
            } else {
                msg += '검색된 전체 상품을 ';
            }

            if ($('#pricePercent').val() == '' || parseInt($('#pricePercent').val()) < 0) {
                $.warnUI('금액 체크', '변경할 금액(퍼센트)을 숫자(양의 정수 및 소수)로 작성해 주세요!');
                return false;
            }


            msg += $('#confPrice option:selected').text() + '의 ';
            msg += $('#pricePercent').val() + $('#markType option:selected').text() + '을(를) ';
            msg += $('#plusMinus option:selected').text() + '된 가격으로, \n';
            msg += $('#targetPrice option:selected').text() + '를 ';
            msg += $('#roundUnit option:selected').text() + '원 단위로 ';
            msg += $('#roundType option:selected').text();
            msg += '하여 일괄적으로 수정하시겠습니까?\n\n';
            msg += '[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';

            type='all';
        }


        dialog_confirm(msg, function (result) {
            if (result) {

                if (type =='select') {

                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                        if (result2) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $('input[name="isPrice"]').val('');
                        $( "#frmBatchPrice").submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                    //상품 수정일 변경 범위설정 체크
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                    $('input[name="modDtUse"]').val('y');
                    <?php } else { ?>
                    $('input[name="modDtUse"]').val('n');
                    <?php } ?>
                    $('input[name="isPrice"]').val('');
                    $( "#frmBatchPrice").submit();
                    <?php } ?>

                } else {

                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                        dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                            if (result2) {
                                $('input[name="modDtUse"]').val('y');
                            } else {
                                $('input[name="modDtUse"]').val('n');
                            }
                            $('input[name="isPrice"]').val('y');
                            var parameters = $("#frmBatchPrice").serialize();

                            $.post('./goods_ps.php', parameters, function (data) {
                                if (data.match(/<!doctype html>/g) != null) {
                                    $("body").append(data);
                                    return;
                                }

                                var getData = $.parseJSON(data);

                                if(getData.cnt > 0) {
                                    $("#lay_goods_price table tbody").html('');

                                    $.each(getData.info, function (key, val) {

                                        var addHtml = "";

                                        addHtml += '<tr>';
                                        addHtml += '<td>'+val.goodsNo+'</td>';
                                        addHtml += '<td>'+val.goodsNm+'</td>';
                                        addHtml += '<td><?=gd_currency_symbol(); ?>'+val.price+' <?=gd_currency_string(); ?></td>';
                                        addHtml += '</tr>';


                                        $("#lay_goods_price table tbody").append(addHtml);


                                    });

                                    BootstrapDialog.show({
                                        title: '상품 가격 설정',
                                        message: $("#lay_goods_price table").clone(),
                                        closable: true,
                                        buttons: [{
                                            label: '제외 하고 수정',
                                            cssClass: 'btn-red',
                                            action: function(dialogItself){

                                                $('input[name="isPrice"]').val('');
                                                $( "#frmBatchPrice").submit();

                                                dialogItself.close();

                                            }
                                        }, {
                                            label: '취소',
                                            action: function(dialogItself){
                                                dialogItself.close();
                                            }
                                        }]
                                    });

                                } else {
                                    $('input[name="isPrice"]').val('');
                                    $( "#frmBatchPrice").submit();
                                }

                            });
                        }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $('input[name="isPrice"]').val('y');
                        var parameters = $("#frmBatchPrice").serialize();

                        $.post('./goods_ps.php', parameters, function (data) {
                            if (data.match(/<!doctype html>/g) != null) {
                                $("body").append(data);
                                return;
                            }

                            var getData = $.parseJSON(data);

                            if(getData.cnt > 0) {
                                $("#lay_goods_price table tbody").html('');

                                $.each(getData.info, function (key, val) {

                                    var addHtml = "";

                                    addHtml += '<tr>';
                                    addHtml += '<td>'+val.goodsNo+'</td>';
                                    addHtml += '<td>'+val.goodsNm+'</td>';
                                    addHtml += '<td><?=gd_currency_symbol(); ?>'+val.price+' <?=gd_currency_string(); ?></td>';
                                    addHtml += '</tr>';


                                    $("#lay_goods_price table tbody").append(addHtml);


                                });

                                BootstrapDialog.show({
                                    title: '상품 가격 설정',
                                    message: $("#lay_goods_price table").clone(),
                                    closable: true,
                                    buttons: [{
                                        label: '제외 하고 수정',
                                        cssClass: 'btn-red',
                                        action: function(dialogItself){

                                            $('input[name="isPrice"]').val('');
                                            $( "#frmBatchPrice").submit();

                                            dialogItself.close();

                                        }
                                    }, {
                                        label: '취소',
                                        action: function(dialogItself){
                                            dialogItself.close();
                                        }
                                    }]
                                });

                            } else {
                                $('input[name="isPrice"]').val('');
                                $( "#frmBatchPrice").submit();
                            }

                        });
                    <?php } ?>

                }
            } else {
                return false;
            }
        });

    });




    $('#pricePercent').number_only();

    $('select[name=\'pageNum\']').change(function () {
        $('#frmSearchGoods').submit();
    });

    $('select[name=\'sort\']').change(function () {
        $('#frmSearchGoods').submit();
    });

    $( ".js-search-toggle" ).click(function() {
        onlyDisplayArea();
    });
    onlyDisplayArea();
});

    function onlyDisplayArea()
    {
        if($('input[name=detailSearch]').val() == 'y'){
            $('.js-search-detail .js-search-price').show();
        }
        else {
            $('.js-search-detail .js-search-price').hide();
        }
    }

    function resetEventSearchCondition()
    {
        $("input[name='event_text']").val('');
        $("input[name='eventThemeSno']").val('');
        $("#eventGroupSelectArea").addClass("display-none");
        $("#eventSearchResetArea").addClass("display-none");
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

/*
 * 혜택
 */
function check_price()
{
    $('input[name="isPrice"]').val('y');
    var parameters = $("#frmBatchPrice").serialize();

    $.post('./goods_ps.php', parameters, function (data) {
        alert(data);
        //var getData = $.parseJSON(data);

        $('input[name="isPrice"]').val('');
    });

}
function set_price()
{

    var cnt = $('input[name="arrGoodsNo[]"]:checked').length;

    if(cnt > 0 )
    {

        var fixedPrice = $('input[name="setFixedPrice"]').val();
        var costPrice  = $('input[name="setCostPrice"]').val();
        var goodsPrice  = $('input[name="setGoodsPrice"]').val();


        if(fixedPrice!='' || costPrice !='' || goodsPrice !='')
        {
            $('input[name="arrGoodsNo[]"]').each(function (i) {
                if (this.checked) {
                    if(fixedPrice) $('#frmBatchPrice input[name*="fixedPrice["]').eq(i).val(fixedPrice);
                    if(costPrice) $('#frmBatchPrice input[name*="costPrice["]').eq(i).val(costPrice);
                    if(goodsPrice) $('#frmBatchPrice input[name*="goodsPrice["]').eq(i).val(goodsPrice);
                }
            });

        }
        else
        {
            $.warnUI('항목 체크', "일괄적용 할 “정가, 매입가, 판매가”를 입력해 주세요.");
            return false;
        }

    }
    else
    {
        $.warnUI('항목 체크', "선택된 상품이 없습니다.");
        return false;
    }

}

//-->
</script>
