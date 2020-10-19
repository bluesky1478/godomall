<style>
#divGoodsCate div { border:solid 1px #CCCCCC; margin:2px; padding:2px;}
#divGoodsBrand div { border:solid 1px #CCCCCC; margin:2px; padding:2px;}
</style>
<script>
<!--

/**
 * 구매 상품 범위 등록 / 예외 등록 Ajax layer
 *
 * @param string typeStr 타입
 */
function layer_register(typeStr)
{
	var layerFormID		= 'addEventForm';
 	var parentFormID	= 'goodsNoForm';
	var dataFormID		= 'idGoodsNo';
	var dataInputNm		= 'goodsNo';
	var layerTitle		= '이벤트에 진열할 상품 설정';

	layer_add_info(typeStr, layerFormID, parentFormID, dataFormID, dataInputNm, layerTitle, 'y', get_category);
}

function get_category() {
	var goodsNo = $("#goodsNoForm input[name='goodsNo[]']");
	if(goodsNo.length > 0) {
		var arrParam = {};
		arrParam['mode'] = 'getGoodsCate';
		for(var i = 0; i < goodsNo.length; i++) {
			arrParam["goodsNo[" + i + "]"] = goodsNo.eq(i).val();
		}
		$.post("event_ps.php", arrParam, set_category);
	}
	else {
		$("#divGoodsCate >div").remove();
		$("#divGoodsBrand >div").remove();
	}
}

function set_category(data) {
	var res = eval(data);
	if (res[0]) {
		for(var key in res[0]) {
			if ($("#cate_" + key).length) {
				$("#cate_" + key).addClass('exists');
				continue;
			}
			<?php
                if ($mode == 'regist') {
            ?>
			var adHtml = "<div id='cate_" + key + "' class='exists'> <input type='checkbox' name='cateCd[]' value='" + key + "'>" + res[0][key] + "</div>";
			<?php
                }
                else {
            ?>
			var adHtml = "<div id='cate_" + key + "' class='exists'> <span class=\"button small blue\"><a onclick='copyTxt(\"?cateCd=" + key + "&sno=<?php echo $sno?>\")'>주소복사</a></span> <label><input type='checkbox' name='cateCd[]' value='" + key + "'> " + res[0][key] + "</label></div>";
			<?php
                }
            ?>
			$("#divGoodsCate").append(adHtml);
		}
		$("#divGoodsCate >div:not(.exists)").remove();
		$("#divGoodsCate >div.exists").removeClass('exists');
	}
	else {
		$("#divGoodsCate >div").remove();
	}

	if (res[1]) {
		for(var key in res[1]) {
			if ($("#brand_" + key).length) {
				$("#brand_" + key).addClass('exists');
				continue;
			}
			<?php
                if ($mode == 'regist') {
            ?>
			var adHtml = "<div id='brand_" + key + "' class='exists'> <input type='checkbox' name='brandCd[]' value='" + key + "'>" + res[1][key] + "</div>";
			<?php
                }
                else {
            ?>
			var adHtml = "<div id='brand_" + key + "' class='exists'> <span class=\"button small blue\"><a onclick='copyTxt(\"?brandCd=" + key + "&sno=<?php echo $sno?>\")'>주소복사</a></span> <label><input type='checkbox' name='brandCd[]' value='" + key + "'> " + res[1][key] + "</label></div>";
			<?php
                }
            ?>
			$("#divGoodsBrand").append(adHtml);
		}
		$("#divGoodsBrand >div:not(.exists)").remove();
		$("#divGoodsBrand >div.exists").removeClass('exists');
	}
	else {
		$("#divGoodsBrand >div").remove();
	}
}

function goods_remove(id) {
	field_remove(id);
	get_category();
}

$(document).ready(function() {
	add_data_sortable('goodsNoForm');		// 상품 이동 소트
	$('#divGoodsCate').sortable().disableSelection().css('cursor', 'pointer');		// 상품 이동 소트
	$('#divGoodsBrand').sortable().disableSelection().css('cursor', 'pointer');		// 상품 이동 소트

	$('input.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });
	$("[class*=input_int]").number_only();
	$("label:has(:radio) img").click(function() {
		$(this).parent().find(":radio").click();
	});
	$(":radio[name=display]").click(function() {
		if ($(this).val() == 'list') {
			$("input[name=perLine]").val("1").prop("readonly", true);
		}
		else {
			$("input[name=perLine]").prop("readonly", false);
		}
	});

	$("#frmReg").formProcess("alert", [
		{'before': function(fObj) {
			if (fObj.find("input[name='goodsNo[]']").length == 0) {
				alert("이벤트 상품을 선정해주세요.");
				return false;
			}

			return true;
		}}
		,{inputName: "subject", name: "제목", required:true}
		,{inputName: "startDt", name: "이벤트시작일", required:true}
		,{inputName: "endDt", name: "이벤트종료일", required:true}
		,{inputName: "perPage", name: "상품출력수", required:true}
		,{inputName: "perLine", name: "라인당상품수", required:true}
	]);
});
//-->
</script>

<form id="frmReg" method="post" action="event_ps.php">
<input type="hidden" name="mode" value="<?php echo gd_isset($mode)?>">
<input type="hidden" name="sno" value="<?php echo gd_isset($sno)?>">

	<div class="page-header js-affix">
		<h3>이벤트 <?php echo (($mode != 'modify')? '생성' : '수정')?> <small>이벤트페이지를 직접 디자인하고 이벤트상품들을 선정하실 수 있습니다.</small></h3>
		<input type="submit" value="저장" class="btn btn-red" onclick="if (check_smart_authority() === false) return false;" />
	</div>

	<table class="table table-cols">
	<col class="width-md"><col />
	<tr>
		<th class="input_title r_space require">이벤트제목</th>
		<td><input type="text" name="subject" value="<?php echo gd_isset($data['subject'])?>" class="form-control"></td>
	</tr>
	<tr>
		<th class="input_title r_space require">이벤트기간 <span class="tip"><span>기간을 입력하면 종료일 자정까지 효력이 발휘됩니다</span></span></th>
		<td>
			<input type="text" name="startDt" value="<?php echo gd_isset($data['startDt'])?>" class="input_date datepicker"> -
			<input type="text" name="endDt" value="<?php echo gd_isset($data['endDt'])?>" class="input_date datepicker">
		</td>
	</tr>
	<tr>
		<th class="input_title r_space require">이벤트내용<br>디자인 & HTML입력</th>
		<td>
			<!-- mini editor -->
			<textarea name="contents" style="width:98%; height:300px;" type="editor" required="required" label="답변"><?php echo gd_isset($data['contents']);?></textarea>
			<script type="text/javascript" src="<?php echo PATH_ADMIN_GD_SHARE;?>script/meditor/mini_editor.js"></script>
			<script type="text/javascript">mini_editor("<?php echo PATH_ADMIN_GD_SHARE;?>script/meditor/")</script>
		</td>
	</tr>
	<tr>
		<th class="input_title r_space require">이벤트상품 선정</th>
		<td>
			<span class="button black small"><a onclick="layer_register('goods');" />상품 선택하기</a></span>
			<span class="button black small"><a href="../goods/goods_batch_price.php" target="_blank" />빠른 가격수정</a></span>
			<span class="button black small"><a href="../goods/goods_batch_mileage.php" target="_blank" />빠른 마일리지수정</a></span>
			<div id="goodsNoForm" class="width100p">
			<?php
				if (gd_array_is_empty($data['goods']) === false) {
					foreach ($data['goods'] as $key => $val) {
						echo '<span id="idGoodsNo_'.$val['goodsNo'].'" class="pull-left selected_goods">'.chr(10);
						echo '<input type="hidden" name="goodsNo[]" value="'.$val['goodsNo'].'" />'.chr(10);
						echo '<span class="outline">'.gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank').'</span>'.chr(10);
						echo '<span class="button gray small"><input type="button" onclick="goods_remove(\'idGoodsNo_'.$val['goodsNo'].'\');" value="삭제" /></span>'.chr(10);
						echo '</span>'.chr(10);
					}
				}
			?>
			</div>
		</td>
	</tr>
	<tr>
		<th>상품 디스플레이 유형</th>
		<td>
			<ul>
				<li class="pull-left">
					<label><input type="radio" name="display" value="gallery" style="vertical-align:top" <?php echo gd_isset($checked['display']['gallery'])?>><img src="<?php echo PATH_ADMIN_GD_SHARE?>/img/event/goodalign_style_01.gif"></label>
				</li>
				<li class="pull-left">
					<label><input type="radio" name="display" value="list" style="vertical-align:top" <?php echo gd_isset($checked['display']['list'])?>><img src="<?php echo PATH_ADMIN_GD_SHARE?>/img/event/goodalign_style_02.gif"></label>
				</li>
				<li class="pull-left">
					<label><input type="radio" name="display" value="group" style="vertical-align:top" <?php echo gd_isset($checked['display']['group'])?>><img src="<?php echo PATH_ADMIN_GD_SHARE?>/img/event/goodalign_style_03.gif"></label>
				</li>
			</ul>
		</td>
	</tr>
	<tr>
		<th class="input_title r_space require">라인당 상품수</th>
		<td><input type="text" name="perLine" value="<?php echo gd_isset($data['perLine'])?>" class="input_int_m"> 개</td>
	</tr>
	<tr>
		<th>
			현재 선정된 상품의<br />카테고리 노출하기
			<span class="tip"><span>우측 분류는 위에서 선정한 상품들의 카테고리입니다. 이벤트페이지에 카테고리를 보여주려면 체크하세요.</span></span>
		</th>
		<td>
			<div id="divGoodsCate">
			<?php
			if (gd_array_is_empty($data['goodsCate']) === false) {
				foreach($data['goodsCate'] as $key => $val) {
			?>
				<div id='cate_<?php echo $key?>'>
				<?php
					if ($mode == 'modify') {
				?>
				<span class="button small blue"><a class="js-clipboard" data-clipboard-text="?cateCd=<?php echo $key?>&sno=<?php echo $sno?>">주소복사</a></span>
				<?php
					}
				?>
				<label><input type='checkbox' name='cateCd[]' value='<?php echo $key?>' <?php echo gd_isset($checked['cateCd'][$key])?> /><?php echo $val?></label>
				</div>
			<?php
				}
			}
			?>
			</div>
		</td>
	</tr>
	<tr>
		<th>
			현재 선정된 상품의<br>브랜드 노출하기
			<span class="tip"><span>우측 브랜드는 위에서 선정한 상품들의 브랜드입니다. 이벤트페이지에 브랜드를 보여주려면 체크하세요.</span></span>
		</th>
		<td>
			<div id="divGoodsBrand">
			<?php
			if (gd_array_is_empty($data['goodsBrand']) === false) {
				foreach($data['goodsBrand'] as $key => $val) {
			?>
				<div id='brand_<?php echo $key?>'>
				<?php
					if ($mode == 'modify') {
				?>
				<span class="button small blue"><a class="js-clipboard" data-clipboard-text="?brandCd=<?php echo $key?>&sno=<?php echo $sno?>">주소복사</a></span>
				<?php
					}
				?>
				<label><input type='checkbox' name='brandCd[]' value='<?php echo $key?>' <?php echo gd_isset($checked['brandCd'][$key])?> /><?php echo $val?></label>
				</div>
			<?php
				}
			}
			?>
			</div>
		</td>
	</tr>
	</table>

	<div class="table-title gd-help-manual">상품 출력 정보</div>
	<table class="table table-cols">
	<col class="width-md"><col /><col class="width-md"><col />
		<th>품절상품 출력</th>
		<td>
			<label><input type="radio" name="soldOutFl" value="y" <?php echo gd_isset($checked['soldOutFl']['y']);?>/>출력함</label>
			<label><input type="radio" name="soldOutFl" value="n" <?php echo gd_isset($checked['soldOutFl']['n']);?>/>출력안함</label>
		</td>
		<th>브랜드 출력</th>
		<td>
			<label><input type="radio" name="brandFl" value="y" <?php echo gd_isset($checked['brandFl']['y']);?>/>출력함</label>
			<label><input type="radio" name="brandFl" value="n" <?php echo gd_isset($checked['brandFl']['n']);?>/>출력안함</label>
		</td>
	</tr>
	<tr>
		<th>옵션 출력</th>
		<td class="input_area" colspan="3">
			<label><input type="radio" name="optionFl" value="y" <?php echo gd_isset($checked['optionFl']['y']);?>/>출력함</label>
			<label><input type="radio" name="optionFl" value="n" <?php echo gd_isset($checked['optionFl']['n']);?>/>출력안함</label>
		</td>
	</tr>
	</table>

	<div class="text-center">
		<input type="submit" value="저장" class="btn btn-red"/>
	</div>
</form>
