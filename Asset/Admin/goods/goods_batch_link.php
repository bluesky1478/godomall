<div class="page-header js-affix">
	<h3><?=end($naviMenu->location); ?> </h3>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmBatchLink" name="frmBatchLink" action="./goods_ps.php"  target="ifrmProcess" method="post">
<input type="hidden" name="mode" value="" />
<input type="hidden" name="modDtUse" value="" />

	<?php
	foreach ($batchAll as $key => $val) {
		echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
	}
	?>
<div class="goods-list">
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
                <th class="width-xs center">브랜드</th>
                <th class="width-xs center">노출상태</th>
                <th class="width-xs center">판매상태</th>
                <th class="width-xs center">품절여부</th>
                <th class="width-xs center">재고</th>
                <th class="width-md center">판매가</th>
                <th class="width-md center">마일리지</th>
                <th class="width-xs center">상품할인</th>
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
                    $goodsDiscount = '사용안함';
                    if ($val['goodsDiscountFl'] == 'y' || $val['goodsBenefitSetFl'] == 'y') {
                        if (gd_isset($val['goodsDiscountGroup'], 'all') == 'all') {
                            $goodsDiscount = '사용함<br />(전체회원)';
                        } else if (gd_isset($val['goodsDiscountGroup'], 'all') == 'member') {
                            $goodsDiscount = '사용함<br />(회원전용)';
                        } else if (gd_isset($val['goodsDiscountGroup'], 'all') == 'group') {
                            $goodsDiscount = '사용함<br />(특정회원등급)';
                        }
                    }


                    if ($val['mileageFl'] == 'g') {
                        if ($val['mileageGoodsUnit'] == 'mileage') $mileageGoods = gd_money_format($val['mileageGoods']) . $conf['mileageBasic']['unit'];
                        else $mileageGoods = (int)$val['mileageGoods'] . '%';
                    } else $mileageGoods = $conf['mileage']['goods'] . '%';


                    list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);


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
                        <td class="center"><?= $val['brandNm'] ?></td>
                        <td class="center lmenu"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                        <td class="center lmenu"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                        <td class="center lmenu"><?=$stockText?></td>
                        <td class="center lmenu"><?=$totalStock?></td>
                        <td class="center number">
                            <div class="form-inline"><?=gd_currency_symbol(); ?><?=gd_money_format($val['goodsPrice']); ?><?=gd_currency_string(); ?></div>
                        </td>
                        <td class="center"><?= $mileageGoods ?></td>
                        <td class="center"><?= $goodsDiscount ?></td>
                    </tr>


                    <?php
                }
            }  else {

                ?>
                <tr><td class="no-data" colspan="11">검색된 정보가 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <div class="center"><?=$page->getPage('#');?></div>
</div>
<div class="mgt10"></div>
<div>
    <label class="checkbox-inline"><input type="checkbox" id="batchAll" name="batchAll" value="y" /> 검색된 상품 전체(<?=number_format($page->recode['total']);?>개 상품)를 수정합니다.</label>
    <p class="notice-danger mgt5">상품수가 많은 경우 비권장합니다. 가능하면 한 페이지씩 선택하여 수정하세요.</p>

	<table class="table table-cols">
	<colgroup><col class="width-md" /><col /></colgroup>
	<tr>
        <th>카테고리<br />연결/이동/복사</th>
        <td>
            <div class="form-inline">
                <input class="btn btn-gray btn-sm" type="button" value="카테고리 선택" onclick="layer_register('category');" />
                <input class="btn btn-white btn-sm" type="button" value="연결" onclick="batch_process('link_category');" />
                <input class="btn btn-white btn-sm" type="button" value="이동" onclick="batch_process('move_category');" />
                <input class="btn btn-white btn-sm" type="button" value="복사" onclick="batch_process('copy_category');" />
            </div>
            <div class="pdt5">
                <div class="notice-info">상품 연결/이동/복사를 원하지 않는 카테고리는 '삭제'버튼을 이용하여 삭제할 수 있습니다.<br>등록하신 카테고리 중 체크된 카테고리가 대표 카테고리로 설정됩니다.</div>
            </div>
            <table id="presentCategoryTable" class="table table-rows table-fixed" style="display: none;margin-bottom:0px;">
                <thead>
                <tr>
                    <th class="width-2xs center">대표 설정</th>
                    <th class="width-2xl center">카테고리 명</th>
                    <th class="width-xs center">카테고리 코드</th>
                    <th class="width-2xs center">삭제</th>
                </tr>
                </thead>
                <tbody id="presentCategory"></tbody>
                <tfoot>
                <tr>
                    <td colspan="4"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#presentCategory').html('');" /></td>
                </tr>
                </tfoot>
            </table>
		</td>
	</tr>
	<tr>
		<th>브랜드 교체</th>
        <td>
            <div class="form-inline">
                <input type="button" value="브랜드 선택" class="btn btn-gray btn-sm" onclick="layer_register('brand');" />
                <input type="button" value="교체" class="btn btn-white btn-sm" onclick="batch_process('link_brand');" />
            </div>
            <div id="presentBrand"></div>
        </td>
    </tr>
        <tr>
		<th>
            <select name="unlinkType">
                <option value="all">전체 해제</option>
                <option value="category">카테고리 부분 해제</option>
                <option value="brand">브랜드 부분 해제</option>
            </select>
        </th>
		<td class="js-unlink">
            <div class="form-inline js-unlink-all">
			<input class="btn btn-red  btn-sm" type="button" value="카테고리 전체 해제" onclick="batch_process('unlink_category');" />
			<input class="btn btn-red btn-sm" type="button" value="브랜드 전체 해제" onclick="batch_process('unlink_brand');" />
                <div class="pdt5">
                    <div class="notice-info">카테고리 전체 해제 : 상품에 연결된 모든 카테고리 정보를 전체 삭제 합니다.<br/>
                        브랜드 전체 해제 : 상품에 연결된 브랜드 정보를 전체 삭제 합니다.
                    </div>
                </div>
			</div>


            <div class="form-inline js-unlink-category" style="display:none">
                <input class="btn btn-gray btn-sm" type="button" value="카테고리 선택" onclick="layer_register('category','Part');" />
                <input class="btn btn-red-box btn-sm" type="button" value="카테고리 부분 해제" onclick="batch_process('unlink_category_part');" />
                <div class="pdt5">
                    <div class="notice-info">상품에 연결된 카테고리 중 선택된 카테고리 정보만 부분 삭제 합니다.<br/>
                        해제를 원하지 않는 카테고리는 ‘삭제’ 버튼을 이용하여 삭제할 수 있습니다.
                    </div>
                </div>
                <table id="presentCategoryPartTable" class="table table-rows table-fixed" style="display: none;margin-bottom:0px;">
                    <thead>
                    <tr>
                        <th class="width-2xl  center">카테고리 명</th>
                        <th class="width-xs center">카테고리 코드</th>
                        <th class="width-2xs center">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="presentCategoryPart"></tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#presentCategoryPart').html('');" /></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

            <div class="form-inline js-unlink-brand" style="display:none">
                <input type="button" value="브랜드 선택" class="btn btn-gray btn-sm" onclick="layer_register('brand','Part');" />
                <input class="btn btn-red-box btn-sm" type="button" value="브랜드 부분 해제" onclick="batch_process('unlink_brand_part');" />
                <div class="pdt5">
                    <div class="notice-info">상품에 연결된 브랜드를 선택된 브랜드 정보만 부분 삭제 합니다.<br/>
                        해제를 원하지 않는 브랜드는 ‘삭제’ 버튼을 이용하여 삭제할 수 있습니다.
                    </div>
                </div>
                <table id="presentBrandPartTable" class="table table-rows table-fixed" style="display: none;margin-bottom:0px;">
                    <thead>
                    <tr>
                        <th class="width-2xl  center">브랜드 명</th>
                        <th class="width-xs center">브랜드 코드</th>
                        <th class="width-2xs center">삭제</th>
                    </tr>
                    </thead>
                    <tbody id="presentBrandPart"></tbody>
                    <tfoot>
                    <tr>
                        <td colspan="3"><input type="button" class="btn btn-sm btn-gray" value="전체삭제" onclick="$('#presentBrandPart').html('');" /></td>
                    </tr>
                    </tfoot>
                </table>
            </div>

		</td>
	</tr>
	<tr>
		<th>상품 삭제</th>
		<td>
			<input class="btn btn-red-box btn-sm" type="button" value="삭제" onclick="batch_process('delete_goods');" />
		</td>
	</tr>
	</table>


</div>
</form>

<script type="text/javascript">
<!--
$(document).ready(function(){

	$('select[name=\'pageNum\']').change(function () {
		$('#frmSearchGoods').submit();
	});

	$('select[name=\'sort\']').change(function () {
		$('#frmSearchGoods').submit();
	});

    $('#batchAll').click(function() {
        if (<?=$page->recode['total'];?> >= 500 && $(this).prop('checked') === true) {
            $('#batchAll').prop('checked', false);
            dialog_confirm('서버 부하 등 안정적인 서비스를 위해서 500개 이상 상품의 일괄 수정은 비권장합니다.<br />검색된 상품 전체를 수정하시겠습니까?', function (result) {
                if (result) {
                    $('#batchAll').prop('checked', true);
                } else {
                    $('#batchAll').prop('checked', false);
                }
            });
        }
    });

    $(document).on('click', '.goods-list .pagination li a', function() {
        $(this).removeAttr('href');
        get_goods_list($(this).data('page'));
    });

    $('select[name=\'unlinkType\']').change(function () {
        $(".js-unlink .form-inline").hide();
        $(".js-unlink .js-unlink-"+$(this).val()).show();
    });

    var categoryRepresent = "";
    $(document).on('click', 'input:radio[name="categoryRepresent"]', function() {
        if ($(this).prop('checked') === true && categoryRepresent == $(this).val()) {
            $(this).prop('checked', false);
            categoryRepresent = "";
        } else {
            categoryRepresent = $(this).val();
        }
    });
});

/**
 * 모드별 처리
 *
 * @param string modeStr 처리 모드
 */
function batch_process(modeStr)
{

	$('input[name=\'mode\']').val('batch_'+modeStr);

    var modeStr = $('input[name=\'mode\']').val();

    var strCateNm = '';
    var strCateCnt = 0;

	var msg = '';
	var msgPre = '';
    var msgSuf = '';

	if ($('#batchAll:checked').length == 0) {
		if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
			$.warnUI('항목 체크', '선택된 항목이 없습니다.');
			return false;
		}
        msgPre += '선택된 상품';
	} else {
        msgPre += '검색된 전체 상품';
	}

    if (modeStr == 'batch_link_category' || modeStr == 'batch_move_category' || modeStr == 'batch_copy_category' || modeStr == 'batch_unlink_category_part' || modeStr == 'batch_unlink_brand_part') {

	    if(modeStr == 'batch_unlink_category_part') {
	        var targetTable = "presentCategoryPartTable";
        }  else if(modeStr == 'batch_unlink_brand_part') {
            var targetTable = "presentBrandPartTable";
        } else {
            var targetTable = "presentCategoryTable";
        }

        if ($('#'+targetTable+' td[name="cateNm"]').length) {
            $('#'+targetTable+' td[name="cateNm"]').each(function(){
                if (strCateCnt === 0) {
                    strCateNm = $(this).text();
                }
                strCateCnt++;
            });
        }

        if (strCateCnt === 0 ) {
            if(modeStr == 'batch_unlink_brand_part') {
                $.warnUI('브랜드 체크', '브랜드를 선택해 주세요!');
            } else {
                $.warnUI('카테고리 체크', '카테고리를 선택해 주세요!');
            }
			return false;
        } else if (strCateCnt > 1) {
            msgSuf = ' 외 ' + (strCateCnt - 1) + '건의 ';
        }

        if (modeStr == 'batch_link_category') {
            msgPre += '에 "' + strCateNm + '"' + msgSuf + ' 카테고리를 추가 연결 하시겠습니까?<br />';
        } else if (modeStr == 'batch_move_category') {
            msgPre += '을 "' + strCateNm + '"' + msgSuf + ' 카테고리로 이동 하시겠습니까?<br />이동을 하시면 기존 연결된 카테고리는 삭제 됩니다.<br />';
        } else if (modeStr == 'batch_copy_category') {
            msgPre += '을 "' + strCateNm + '"' + msgSuf + ' 카테고리에 복사 하시겠습니까?<br />';
        }
    }

	if (modeStr == 'batch_link_brand') {
        if ($('input:hidden[name="brandCodeNm"]').length) {
            msgPre += '의 브랜드를 "' + $('input:hidden[name="brandCodeNm"]').val() + '"(으)로 변경 하시겠습니까?<br />';
        } else {
			$.warnUI('브랜드 체크', '브랜드를 선택해 주세요!');
			return false;
		}
	}

	if (modeStr == 'batch_unlink_category') {
        msgPre += '의 모든 카테고리를 연결 해제 하시겠습니까?<br />해제를 하시면 사용자 페이지에 상품이 노출 안될 수 있습니다.<br />';
	}

    if (modeStr == 'batch_unlink_category_part') {
        msgPre += '에 "' + strCateNm + '"' + msgSuf + ' 카테고리를 연결 해제 하시겠습니까?<br />해제를 하시면 사용자 페이지에 상품이 노출 안될 수 있습니다.<br />';
    }

	if (modeStr == 'batch_unlink_brand') {
        msgPre += '의 브랜드를 연결 해제 하시겠습니까?<br />';
	}

    if (modeStr == 'batch_unlink_brand_part') {
        msgPre += '에 "' + strCateNm + '"' + msgSuf + ' 브랜드를 연결 해제 하시겠습니까?<br />';
    }

	if (modeStr == 'batch_delete_goods') {
        msgPre += '을 일괄 삭제 처리 하시겠습니까?<br />';
	}

	msg += msgPre;
	msg += '[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';

	dialog_confirm(msg, function (result) {
		if (result) {
            //상품수정일 변경 확인 팝업
            <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
            dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                if (result2) {
                    $('input[name="modDtUse"]').val('y');
                } else {
                    $('input[name="modDtUse"]').val('n');
                }
                $('#frmBatchLink').submit();
                // 선택된 상품 체크 해제
                if ($('.js-checkall').prop('checked') === true) {
                    $('.js-checkall').trigger('click');
                } else {
                    $('input:checkbox[name="arrGoodsNo[]"]').each(function () {
                        if ($(this).prop('checked') === true) {
                            $(this).prop('checked', false);
                        }
                    });
                }
            }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
            <?php } else { ?>
                //상품 수정일 변경 범위설정 체크
                <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                    $('input[name="modDtUse"]').val('y');
                <?php } else { ?>
                    $('input[name="modDtUse"]').val('n');
                <?php } ?>
                $('#frmBatchLink').submit();
                // 선택된 상품 체크 해제
                if ($('.js-checkall').prop('checked') === true) {
                    $('.js-checkall').trigger('click');
                } else {
                    $('input:checkbox[name="arrGoodsNo[]"]').each(function () {
                        if ($(this).prop('checked') === true) {
                            $(this).prop('checked', false);
                        }
                    });
                }
            <?php } ?>
		}
	});
}

/**
 * 카테고리/브랜드 선택 레이어 호출
 */
function layer_register(typeStr, modeStr) {
    if (typeStr === 'category' || typeStr === 'brand' && (typeof modeStr == 'undefined' || modeStr == 'Part')) {
        var typeStrId = typeStr.substr(0, 1).toUpperCase() + typeStr.substr(1);
        if (typeof modeStr == 'undefined') {
            modeStr = "";
        } else {
            typeStrId = typeStrId+modeStr;
        }

        var layerFormID = 'addPresentForm';
        var parentFormID = 'present' + typeStrId;
        var dataFormID = 'id' + typeStrId;

        if (typeStr === 'category') {
            var layerTitle = '카테고리 선택';
            var dataInputNm = typeStr + modeStr;
        } else if (typeStr === 'brand') {
            var layerTitle = '브랜드 선택';
            if(modeStr) {
                var dataInputNm = typeStr + modeStr;
            } else {
                var dataInputNm = typeStr + 'Code';
            }
        }

        $('#'+parentFormID+'Table').show();
    }

    if (typeStr === 'scm') {
        $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
    }

    var addParam = {
        "layerFormID": layerFormID,
        "parentFormID": parentFormID,
        "dataFormID": dataFormID,
        "dataInputNm": dataInputNm,
        "layerTitle": layerTitle,
    };

    if (typeStr === 'category' || typeStr === 'brand') {
        if (typeStr === 'brand' && modeStr != 'Part') {
            if(modeStr == 'radio-search') { // 미지정 상품을 위해 조건 추가
                addParam['mode'] = 'radio-search';
            } else {
                addParam['mode'] = 'radio';
            }
        }
        else{
            addParam['callFunc'] = 'set_category_select';
        }
    } else if(typeStr === 'goods_benefit') { // 혜택관리 레이어 조건 추가
        if(modeStr == 'layer_search_page') {
            addParam['mode'] = 'layer_search_page';
        }
    }

    layer_add_info(typeStr, addParam);
}

/**
 * 카테고리 선택
 */
function set_category_select(data) {
    $.each(data.info, function (key, val) {
        var addHtml = "";
        var complied = _.template($('#categoryBatchTemplate').html());
        addHtml += complied({
            cateNm: val.cateNm,
            cateCd: val.cateCd,
            dataFormID: data.dataFormID,
            dataInputNm: data.dataInputNm
        });

        $("#" + data.parentFormID).append(addHtml);
    });
    if(data.dataInputNm =="categoryPart" || data.dataInputNm =="brandPart") {
        $("#" + data.parentFormID + " tr td:nth-child(2)").hide();
    }
}

/**
 * 상품리스트 페이징 ajax
 */
function get_goods_list(page) {
    var params = $("#frmSearchGoods").serialize() + '&page=' + page;
    $.ajax({
        method: "get",
        url: '../goods/_batch_link_goods_list.php',
        data: params,
        success: function (data) {
            $('.goods-list').html(data);
        },
        error: function (data) {
            alert(data.message);
        }
    });
}
//-->
</script>

<script type="text/html" id="categoryBatchTemplate">
    <tr id="<%=dataFormID%>_<%=cateCd%>">
        <input type="hidden" name="<%=dataInputNm%>Code[]" value="<%=cateCd%>">
        <td class="center"><input type="radio" name="<%=dataInputNm%>Represent" value="<%=cateCd%>"></td>
        <td name="cateNm"><%=cateNm%></td>
        <td class="center" name="cateCd"><%=cateCd%></td>
        <td class="center"><input type="button" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#<%=dataFormID%>_<%=cateCd%>" value="삭제"/></td>
    </tr>
</script>

