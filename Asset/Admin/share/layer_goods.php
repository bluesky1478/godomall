<div>
	<div class="mgt10"></div>
	<div>
		<form id="layer_search_goods_frm">
		<table class="table-cols no-title-line mgb10">
		<colgroup><col class="width-sm" /><col /></colgroup>
		<tr>
			<th>검색어</th>
			<td> <div class="form-inline">
				<?php echo gd_select_box('key','key',$search['combineSearch'],null,$search['key']);?>
				<input type="text" name="keyword" value="<?php echo $search['keyword'];?>" class="form-control" />
				</div>
			</td>
		</tr>
		<tr>
			<th>카테고리 선택</th>
			<td><div class="form-inline">
					<?php echo $cate->getMultiCategoryBox('layerCateGoods', gd_isset($search['cateGoods']), 'class="form-control"'); ?>
					<label class="checkbox-inline">
						<input type="checkbox" name="categoryNoneFl" value="y" <?php echo gd_isset($checked['categoryNoneFl']['y']); ?>> 미지정 상품
					</label>
				</div>
			</td>
		</tr>
			<tr>
				<th>브랜드</th>
				<td><div class="form-inline">
						<?php echo $brand->getMultiCategoryBox(null, gd_isset($search['brand']), 'class="form-control"'); ?>
						<label class="checkbox-inline"><input type="checkbox" name="brandNoneFl" value="y" <?php echo gd_isset($checked['brandNoneFl']['y']); ?>> 미지정 상품</label>
					</div>
				</td>
			</tr>
            <tr>
            <th>판매가</th>
            <td>
                <div class="form-inline">
                    <input type="text" name="goodsPrice[0]" value="<?=$search['goodsPrice'][0]; ?>" class="form-control width-sm js-number"/>이상 ~
                    <input type="text" name="goodsPrice[1]" value="<?=$search['goodsPrice'][1]; ?>" class="form-control width-sm js-number"/>이하
                </div>
            </td>
            </tr>
		<tr>
			<th>기간검색</th>
			<td>
				<div class="form-inline">
					<select name="searchDateFl" class="form-control">
						<option value="regDt" <?php echo gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
						<option value="modDt" <?php echo gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
					</select>

					<div class="input-group js-datepicker">
						<input type="text" class="form-control width-xs" name="searchDate[0]" value="<?php echo $search['searchDate'][0]; ?>" />
						<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
					</div>
					~
					<div class="input-group js-datepicker">
						<input type="text" class="form-control width-xs" name="searchDate[1]" value="<?php echo $search['searchDate'][1]; ?>" />
						<span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
					</div>
				</div>

			</td>
		</tr>
		</table>
		<p class="center"><input type="button" value="검색" class="btn btn-hf btn-black" onclick="layer_list_search();"></p>
		</form>
	</div>
</div>

<div>
	<table class="table table-rows">
	<thead>
	<tr>
		<th class="width3p"><?php
            if($optionRegister !== 'y' && $mode != 'multiSearch'){
            ?><input type="checkbox" id="allCheck" value="y" onclick="check_toggle(this.id,'layer_goods_');"/></th><?php
            }
        ?>
		<th class="width3p">번호</th>
		<th class="width3p">이미지</th>
		<th class="width10p">상품명</th>
		<th class="width5p">판매가</th>
		<th class="width5p">공급사</th>
		<th class="width3p">재고</th>
		<th class="width3p">품절상태</th>
		<th class="width3p">PC쇼핑몰<br />노출상태</th>
		<th class="width3p">모바일쇼핑몰<br />노출상태</th>
        <?php
        if($optionRegister === 'y'){
        ?><th class="width3p">옵션</th><?php
        }
        ?>
	</tr>
	</thead>
	<tbody>
<?php
    if (gd_isset($data) && is_array($data)) {
        $i = 0;
        foreach ($data as $key => $val) {

			list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);
			$goodsDisplay = $goodsDisplayMobile = '노출함';
			if ($val['goodsDisplayFl'] != 'y') $goodsDisplay = '노출안함';
			if ($val['goodsDisplayMobileFl'] != 'y') $goodsDisplayMobile = '노출안함';

			$checkboxType = 'checkbox';
			if($optionRegister === 'y' || $mode == 'multiSearch') $checkboxType = 'radio';

			$optionViewButton = '<p class="center"><input type="button" value="옵션보기" class="btn btn-hf btn-gray" onclick="show_goods_option_stock('.$val['goodsNo'].');"></p>';
			if($val['optionFl'] === 'n') $optionViewButton = '<p class="center">사용안함</p>';
?>
	<tr id="tbl_goods_<?php echo $val['goodsNo'];?>">
		<td class="center"><input type="<?=$checkboxType?>" id="layer_goods_<?php echo $val['goodsNo'];?>" name="layer_goods_<?php if($checkboxType !== 'radio'){ echo $i; }?>" value="<?php echo $val['goodsNo'];?>" /></td>
		<td class="center"><?php echo number_format($page->idx--);?></td>
		<td>
			<div class="width-2xs">
				<?php echo gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank', 'id="goodsImage_'.$val['goodsNo'].'"');?>
			</div>
		</td>
		<td>
			<a class="text-blue hand" style="word-break: break-all;" onclick="goods_register_popup('<?=$val['goodsNo']; ?>' <?php if(gd_is_provider() === true) { echo ",'1'"; } ?>);"><?php echo gd_remove_tag($val['goodsNm']);?></a>
			<input type="hidden" id="goodsNm_<?php echo $val['goodsNo'];?>" value="<?php echo gd_remove_tag($val['goodsNm']);?>" />
			<input type="hidden" id="regDt_<?php echo $val['goodsNo'];?>" value="<?php echo gd_date_format('Y-m-d', $val['regDt']); ?>" />
			<div>
				<?php

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

				// 품절 체크
				if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
					echo gd_html_image(UserFilePath::icon('goods_icon')->www() . '/' . 'icon_soldout.gif', '품절상품') . ' ';
				}

				if($val['timeSaleSno']) {
					echo "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' /> ";
				}

				?>
			</div>
		</td>
		<td id="goodsPrice_<?php echo $val['goodsNo'];?>" class="center"><?php echo number_format($val['goodsPrice']);?> 원</td>
		<td id="scmNm_<?php echo $val['goodsNo'];?>"><?php echo $val['scmNm'];?></td>
		<td id="totalStock_<?php echo $val['goodsNo'];?>" class="center"><?php echo $totalStock;?></td>
		<td id="stockTxt_<?php echo $val['goodsNo'];?>"  class="center" ><?=$stockText?></td>
		<td class="center" id="displayPc_<?php echo $val['goodsNo'];?>"><?php echo $goodsDisplay; ?></td>
		<td class="center" id="displayMobile_<?php echo $val['goodsNo'];?>"><?php echo $goodsDisplayMobile; ?></td>
        <?php
        if($optionRegister === 'y') {
            ?>
            <td><?=$optionViewButton?></td>
            <?php
        }
        ?>
	</tr>
<?php
            $i++;
        }
    } else {
?>
	<tr>
		<td class="center" colspan="8">검색을 이용해 주세요.</td>
	</tr>
<?php
    }
?>

	</tbody>
	</table>

	<div class="center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>
</div>
<div class="text-center"><input type="button" value="확인" class="btn btn-lg btn-black" onclick="select_code();" /></div>

<script type="text/javascript">
	<!--
	$(document).ready(function(){

		$('input').keydown(function(e) {
			if (e.keyCode == 13) {
				layer_list_search();
				return false
			}
		});
	});

	function layer_list_search(pagelink)
	{

		if (typeof pagelink == 'undefined') {
			pagelink		= '';
		}
		var frm = $("#layer_search_goods_frm").serializeArray();
		var cateGoods	= '';
		var brandGoods	= '';
		var parameters		= {
			'layerFormID'	: '<?php echo $layerFormID?>',
			'parentFormID'	: '<?php echo $parentFormID?>',
			'dataFormID'	: '<?php echo $dataFormID?>',
			'dataInputNm'	: '<?php echo $dataInputNm?>',
			'scmFl'	: '<?php echo $scmFl?>',
			'scmNo'	: '<?php echo $scmNo?>',
			'mode'	: '<?php echo $mode?>',
			'callFunc'	: '<?php echo $callFunc?>',
			'childRow'	: '<?php echo $childRow?>',
			'pagelink'		: pagelink
		};

		$.each(frm, function(i, field){
			if(field.name) parameters[field.name] = field.value;
		});


		for (var i = <?php echo DEFAULT_DEPTH_CATE;?>; i > 0; i--) {
			if ($('#layerCateGoods'+i).val()) {
				cateGoods	= $('#layerCateGoods'+i).val();
				break;
			}
		}
		for (var i = <?php echo DEFAULT_DEPTH_BRAND;?>; i > 0; i--) {
			if ($('#brand'+i).val()) {
				brandGoods	= $('#brand'+i).val();
				break;
			}
		}

		parameters['cateGoods[]'] = cateGoods;
		parameters['brand[]'] = brandGoods;
        parameters['optionRegister'] = '<?=$optionRegister?>';

		$.get('../share/layer_goods.php', parameters, function(data){
			$('#<?php echo $layerFormID?>').html(data);
		});
	}

	function select_code()
	{
		if ($('#<?php echo $layerFormID?> input[id*=\'layer_goods_\']:checked').length == 0) {
			alert('상품을 선택해 주세요!');
			return false;
		}

		var checkboxCnt		= $('#<?php echo $layerFormID?> input[id*=\'layer_goods_\']').length;
		var applyGoodsCnt	= 0;
		var chkGoodsCnt		= 0;
		var resultJson = {
			"mode": "<?php echo $mode?>",
			"parentFormID": "<?php echo $parentFormID?>",
			"dataFormID": "<?php echo $dataFormID?>",
			"dataInputNm": "<?php echo $dataInputNm?>",
			"childRow": "<?php echo $childRow?>",
			"info": []
		};

		$('#<?php echo $layerFormID?> input[id*=\'layer_goods_\']:checked').each(function() {
            if ('<?=$optionRegister?>' == 'y') {
                if(confirm('선택된 상품의 옵션정보를 적용하시겠습니까? 적용 시 기존 등록된 정보는 삭제되며 상품에 적용중인 옵션이 아닌 경우 복구가 불가능합니다.')){
                    location.href='goods_register_option.php?copy=1&goodsNo=' + $(this).val();
                }
                applyGoodsCnt = 0;
            }
			var goodsNo		= $(this).val();
			var goodsNm		= $('#goodsNm_'+goodsNo).val();
			var goodsImg	= $('#goodsImage_'+goodsNo).get(0).src;
			var goodsInfo		= $('#tbl_goods_'+goodsNo).html();
			var goodsPrice		= $('#goodsPrice_'+goodsNo).html();
			var scmNm		= $('#scmNm_'+goodsNo).html();
			var regDt		= $('#regDt_'+goodsNo).val();
			var totalStock		= $('#totalStock_'+goodsNo).html();
			var stockTxt		= $('#stockTxt_'+goodsNo).html();
			var displayPc		= $('#displayPc_'+goodsNo).html();
			var displayMobile		= $('#displayMobile_'+goodsNo).html();

			if ($('#<?php echo $dataFormID?>_'+goodsNo).length == 0) {

				resultJson.info.push({"goodsNo": goodsNo, "goodsNm": goodsNm, "scmNm": scmNm, "goodsImg": goodsImg, "goodsInfo": goodsInfo, "goodsPrice": goodsPrice, "regDt": regDt, "totalStock": totalStock, "stockTxt": stockTxt,"displayPc": displayPc,"displayMobile": displayMobile});

				applyGoodsCnt++;
			}
			chkGoodsCnt++;
		});

		if (applyGoodsCnt > 0) {

			<?php if($callFunc) { ?>
			<?=$callFunc?>(resultJson);
			<?php } else { ?>
			displayTemplate(resultJson);
			<?php } ?>
			if (applyGoodsCnt != chkGoodsCnt) {
				alert('선택한 '+chkGoodsCnt+'개의 상품중 '+applyGoodsCnt+'개의 상품이 추가 되었습니다.');
			}
			// 선택된 버튼 div 토글
			if (chkGoodsCnt > 0) {
				$('#' + resultJson.parentFormID).addClass('active');
			} else {
				$('#' + resultJson.parentFormID).removeClass('active');
			}
			$('div.bootstrap-dialog-close-button').click();
		} else {
			alert('동일한 상품이 이미 존재합니다.');
		}
	}
	/**
	 * 상품 기본 출력
	 * @param data
	 */
	function displayTemplate(data) {
		if (data.dataInputNm == '') {
			data.dataInputNm = 'goodsNo';
		}

		var exceptTitle = ['simple', 'recom', 'plusReview'];
		if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5') && $.inArray(data.mode, exceptTitle) === -1) {
			$('#' + data.parentFormID).prepend('<h5>선택된 상품</h5>');
		}

		var parentFormCount = $('#' + data.parentFormID+' tr').length;

		if(data.mode == 'search'){
			$.each(data.info, function (key, val) {
				var addHtml = "";
                addHtml += '<div id="' + data.dataFormID + '_' + val.goodsNo + '"  class="btn-group btn-group-xs">';
                addHtml += '<input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" />';
                addHtml += '<input type="hidden" name="' + data.dataInputNm + 'Nm[]" value="' + val.goodsNm + '" />';
                addHtml += '<button type="button" class="btn btn-gray">' + val.goodsNm + '</button>';
				addHtml += '<button type="button" class="btn btn-red" data-toggle="delete" data-target="#'+data.dataFormID+'_'+ val.goodsNo+'">삭제</button>';
                addHtml += '</div>';
				$("#" + data.parentFormID).append(addHtml);
			});
		} else if(data.mode == 'simple'){
			$.each(data.info, function (key, val) {
				var addHtml = "";
                addHtml += '<tr id="' + data.dataFormID + '_' + val.goodsNo + '">';
                addHtml += '<td class="center"><span class="number">' + (key + 1 + Number(data.childRow) + parentFormCount) + '</span><input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" /></td>';
                addHtml += '<td class="center"><a href="<?php echo URI_HOME; ?>goods/goods_view.php?goodsNo=' + val.goodsNo + '" target="_blank"><img src="' + val.goodsImg + '" align="absmiddle" width="50" alt="' + val.goodsNm + '" title="' + val.goodsNm + '" /></a></td>';
                addHtml += '<td><a href="../goods/goods_register.php?goodsNo=' + val.goodsNo + '" target="_blank">' + val.goodsNm + '</a></td>';
                addHtml += '<td  class="center"><input type="button"  data-toggle="delete"  data-target="#'+data.dataFormID+'_'+ val.goodsNo+'" value="삭제" class="btn btn-sm btn-gray"/></td>';
                addHtml += '</tr>';
				$("#" + data.parentFormID).append(addHtml);
			});
		} else if (data.mode == 'recom') {
			if ($("#" + data.parentFormID + " #tbl_recom_goods_tr_none").length > 0) $("#" + data.parentFormID + " #tbl_recom_goods_tr_none").remove();
			$.each(data.info, function (key, val) {
				var addHtml = "";
				addHtml += '<tr id="' + data.dataFormID + '_' + val.goodsNo + '" class="recom_tr">';
				addHtml += '<td class="center"><input type="checkbox" name="del[]" value="' + val.goodsNo + '"></td>';
				addHtml += '<td class="center"><span class="number"><span>' + (key + 1 + Number(data.childRow)) + '</span><input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" /></td>';
                addHtml += '<td class="center"><a href="<?php echo URI_HOME; ?>goods/goods_view.php?goodsNo=' + val.goodsNo + '" target="_blank"><img src="' + val.goodsImg + '" align="absmiddle" width="50" alt="' + val.goodsNm + '" title="' + val.goodsNm + '" /></a></td>';
                addHtml += '<td><a href="../goods/goods_register.php?goodsNo=' + val.goodsNo + '" target="_blank">' + val.goodsNm + '</a></td>';
				addHtml += '<td class="center">' + val.goodsPrice + '</td>';
				addHtml += '<td class="center">' + val.scmNm + '</td>';
				addHtml += '<td class="center">' + val.totalStock + '</td>';
				addHtml += '<td class="center">' + val.stockTxt + '</td>';
				addHtml += '<td class="center">' + val.displayPc + '</td>';
				addHtml += '<td class="center">' + val.displayMobile + '</td>';
				addHtml += '</tr>';
				$("#" + data.parentFormID).append(addHtml);
			});
		} else if(data.mode == 'plusReview') {
            $.each(data.info, function (key, val) {
                var addHtml = "";
                addHtml += '<tr id="' + data.dataFormID + '_' + val.goodsNo + '">';
                addHtml += '<td class="center"><input type="checkbox" name="exceptGoodsChk"/><input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" /></td>';
                addHtml += '<td class="center"><span class="number">' + (key + 1 + Number(data.childRow) + parentFormCount) + '</td>';
                addHtml += '<td class="center"><a href="<?php echo URI_HOME; ?>goods/goods_view.php?goodsNo=' + val.goodsNo + '" target="_blank"><img src="' + val.goodsImg + '" align="absmiddle" width="50" alt="' + val.goodsNm + '" title="' + val.goodsNm + '" /></a></td>';
                addHtml += '<td><a href="../goods/goods_register.php?goodsNo=' + val.goodsNo + '" target="_blank">' + val.goodsNm + '</a></td>';
                addHtml += '<td  class="center"><input type="button"  data-toggle="delete"  data-target="#' + data.dataFormID + '_' + val.goodsNo+'" value="삭제" class="btn btn-sm btn-gray"/></td>';
                addHtml += '</tr>';
                $("#" + data.parentFormID).append(addHtml);
            });
        } else if(data.mode == 'multiSearch') {
            $.each(data.info, function (key, val) {
		        $("#" + data.parentFormID + ' input[name='+data.dataInputNm+']').val(val.goodsNo);
                $("#" + data.parentFormID + ' input[name=goodsText]').val(val.goodsNm);
                $("#" + data.parentFormID + ' select[name=goodsKey]').val('og.goodsNm');
            });
        } else {
			$.each(data.info, function (key, val) {
				var addHtml = "";
                addHtml += '<tr id="' + data.dataFormID + '_' + val.goodsNo + '" >';
                addHtml += '<input type="hidden" name="' + data.dataInputNm + '[]" value="' + val.goodsNo + '" />';
                addHtml += val.goodsInfo;
                addHtml += '</tr>';
				$("#" + data.parentFormID).append(addHtml);
			});
		}
	}

    /**
     * 상품 옵션 보기 Ajax layer
     */
    function show_goods_option_stock(goodsNo) {
        var loadChk = $('#layerShowGoodsOptionStock').length;

        var parameters = {
            'optionRegister' : 'y',
            'layerFormID' : 'layerShowGoodsOptionStock',
            'goodsNo' : goodsNo
        };
        $.get('../share/layer_goods_option_stock.php', parameters, function (data) {
            if (loadChk == 0) {
                data = '<div id="layerShowGoodsOptionStock">' + data + '</div>';
            }
            var layerForm = data;
            layer_popup(layerForm, '옵션 재고 보기', 'wide');
        });
    }
	//-->
</script>
