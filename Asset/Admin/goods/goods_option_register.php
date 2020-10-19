<script type="text/javascript">
<!--
$(document).ready(function(){

	$.validator.addMethod(
			'optionCheck', function (value, element) {

				// 옵션 개수 체크
				var optionCnt	= $('#optionCnt').val();
				if (optionCnt < 1) {
					//$.warnUI('옵션 개수 체크', '옵션 개수를 선택해 주세요.!');
					$.validator.messages.optionCheck =  '옵션 개수를 선택해 주세요.!';
					return false;
				}
				var fieldCnt	= $('#option tbody tr').length;

				for (var i = 0; i < fieldCnt; i++) {
					if ($('#option_optionName_'+i).val() == '') {
						//$.warnUI('옵션명 체크', '옵션명을 넣어주세요.!');
						$.validator.messages.optionCheck =  '옵션명을 넣어주세요.!';
						return false;
					}
				}
				return true;


			},'');

		$("#frmOption").validate({
			submitHandler: function (form) {
				form.target='ifrmProcess';
				form.submit();
			},
			// onclick: false, // <-- add this option
			rules: {
				optionManageNm: {
					required: true
				},
				mode: {
					optionCheck: true
				}
			},
			messages: {
				optionManageNm: {
					required: "옵션관리명을 입력햊쉐요."
				}
			}
		});


	<?php if ($data['optionCnt'] > 0) {?>fill_option();<?php }?>
	$('input[name*=\'optionCnt\']').number_only();
});
<?php if ($data['mode'] == 'option_modify') {?>
/**
 * 옵션 정보 채우기
 */
function fill_option() {
	option_setting(<?=$data['optionCnt'];?>);
<?php
    for ($i = 0; $i < $data['optionCnt']; $i++) {
        $optionKey        = 'optionValue'.($i+1);
        $optionValue    = explode(STR_DIVISION,gd_isset($data[$optionKey]));
        $optionCnt        = count($optionValue);
        echo "	$('#option_optionName_".$i."').val('".gd_htmlspecialchars_slashes($data['optionName'][$i],'add')."');".chr(10);
        if (is_array($optionValue)) {
            $j    = 0;
            foreach ($optionValue as $key => $val) {
        		if($j > 0 ) echo "	option_value_conf(".$i.", true);".chr(10);
                echo "	$('#option_optionValue_".$i."_".$j." input:text').val('".gd_htmlspecialchars_slashes($val,'add')."');".chr(10);
                $j++;
            }
        }
    }
?>
}
<?php }?>

/**
 * 옵션 세팅 - 옵션명 설정 및 추가 정보
 *
 * @param string thisCnt 옵션 개수
 */
function option_setting(thisCnt) {
	var fieldCnt	= $('#option tbody tr').length;
	var fieldChk	= parseInt(thisCnt - fieldCnt);


	var addHtml		= '';
	if (fieldChk > 0) {
		for (var i = fieldCnt; i < thisCnt; i++) {
			addHtml	+= '<tr>';
			addHtml	+= '<td><input type="text" id="option_optionName_'+i+'" name="optionName[]" maxlength="30" value="" class="form-control width-xl" /></td>';
			addHtml	+= '<td id="optionValue'+i+'"><div class="form-inline"  id="option_optionValue_'+i+'_'+0+'" ><input type="text" name="optionValue['+i+'][]" maxlength="100" value="" class="form-control width-md" /> <input type="button" class="btn btn-white btn-icon-plus btn-xs" onclick="option_value_conf('+i+')" value="추가" /></div></td>';
			addHtml	+= '</tr>';
		}
	}else if (fieldChk < 0) {
		for (var j = thisCnt; j < fieldCnt; j++) {
			$('#option tbody tr:last').remove();
		}
	}

	$('#option tbody').append(addHtml);
	if($('#option tbody tr').length > 0) $('#option thead').show();
	else $('#option thead').hide();

	$('input[name*=\'optionCnt\']').number_only(4,100,100);

}

/**
 * 옵션값 설정 - 옵션값 ,색상표, 아이콘 등
 *
 * @param string loc 옵션 순서 (1-5)
 * @param string thisCnt 옵션값 개수
 * @param string loadChk 옵션값 갯수 제한 체크 여부 (기본 false)
 */
function option_value_conf(loc, loadChk) {

	if(option_total_check(loc)) {
		var fieldID		= 'optionValue'+loc;
		var fieldCnt	= $('#'+fieldID).find('div[id*=\'option_optionValue_'+loc+'\']').length;

		var addHtml		= '';
		addHtml	+= '<div class="form-inline"  id="option_optionValue_'+loc+'_'+fieldCnt+'" style="margin-top:10px"><input type="text"  name="optionValue['+loc+'][]" maxlength="100" value="" class="form-control width-md" /> <input type="button" class="btn btn-white btn-icon-minus  btn-xs" onclick="remove_option_value('+loc+','+fieldCnt+')" value="삭제" /></div>';


		$('#'+fieldID).append(addHtml);
	}
}

function option_total_check(loc) {

	var optionTotalCnt = $("#optionCnt").val();
	var totalOption = 1;
	for(var i = 0; i < optionTotalCnt; i++ ) {

		var tmp = $("div[id*='option_optionValue_"+i+"']").length;

		if(loc == i) tmp += 1;

		totalOption = totalOption*tmp;
	}

	if(totalOption > 1000) {
		alert("옵션의 조합은 1000개 이하로 가능합니다.");
		return false;
	} else {
		return true;
	}
}


/**
* 옵션값 삭제
*/
function remove_option_value(loc,fieldCnt) {

	$('#option_optionValue_'+loc+'_'+fieldCnt).remove();
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
<form id="frmOption" name="frmOption" action="./goods_ps.php" method="post">
<input type="hidden" name="mode" value="<?=$data['mode'];?>" />
<input type="hidden" name="sno" value="<?=$data['sno'];?>" />

	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location); ?></h3>
		<div class="btn-group">
			<input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./goods_option_list.php');" />
			<input type="submit" value="저장" class="btn btn-red" />

		</div>
	</div>

	<div class="table-title gd-help-manual">
		<?=end($naviMenu->location); ?>
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
        <?php if(gd_use_provider() === true) { ?>
			<?php if(gd_is_provider()) { ?>
				<input type="hidden" name="scmNo" value="<?=$data['scmNo']?>">
			<?php } else { ?>
		<tr>
			<th class="input_title r_space ">공급사 구분</th>
			<td>
				<label class="radio-inline"><input type="radio" name="scmFl"
							  value="n" <?=gd_isset($checked['scmFl']['n']); ?>    onclick="$('#scmLayer').html('')";/>본사</label>
				<label class="radio-inline"><input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?>
							  onclick="layer_register('scm', 'radio',true)"/>공급사</label>
				<label> <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm', 'radio',true)">공급사 선택</button></label>
				<div id="scmLayer" class="selected-btn-group <?=$data['scmNoNm'] && $data['scmNo'] != DEFAULT_CODE_SCMNO  ? 'active' : ''?>">
					<?php if ($data['scmNo']) { ?>
						<h5>선택된 공급사 : </h5>
						<span id="info_scm_<?= $data['scmNo'] ?>" class="btn-group btn-group-xs">
						<input type="hidden" name="scmNo" value="<?= $data['scmNo'] ?>"/>
							<?php if($data['scmNo'] != DEFAULT_CODE_SCMNO) { ?>
								<span class="btn"><?= $data['scmNoNm'] ?></span>
								<button type="button" class="btn btn-white btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $data['scmNo'] ?>">삭제</button>
							<?php }?>
						</span>
					<?php } ?>
				</div>
			</td>
		</tr>
			<?php } ?>
        <?php } ?>
		<tr>
			<th class="require">옵션 관리 명</th>
			<td>
				<label title="옵션 관리 명을 넣어주세요. 태그는 &quot;사용할 수 없습니다!&quot;"><input type="text" name="optionManageNm" value="<?=$data['optionManageNm'];?>"  class="form-control js-maxlength width-lg" maxlength="30" /></label>
			</td>
		</tr>
		<tr>
			<th>옵션 노출 방식</th>
			<td>
				<label class="radio-inline"><input type="radio" name="optionDisplayFl" value="s" <?=gd_isset($checked['optionDisplayFl']['s']);?> />일체형</label>
				<label class="radio-inline"><input type="radio" name="optionDisplayFl" value="d" <?=gd_isset($checked['optionDisplayFl']['d']);?> />분리형</label>
			</td>
		</tr>
		<tr>
			<th class="require">옵션 개수</th>
			<td> <div class="form-inline">
				<?=gd_select_box('optionCnt','optionCnt',gd_array_change_key_value(range(1, DEFAULT_LIMIT_OPTION)),'개',$data['optionCnt'],'=옵션 개수=','onchange="option_setting(this.value);"');?></div>
			</td>
		</tr>
		<tr>
			<th class="require">자주쓰는 옵션 등록</th>
			<td>

					<table  class="table-cols" id="option" >
						<colgroup><col class="width-xl" /><col class="width-xl"  /></colgroup >
						<thead class="display-none">
							<tr><th>옵션명</th><th>옵션값</th></tr>
						</thead>
						<tbody>

						</tbody>
					</table>


			</td>
		</tr>
		</table>
	</div>

</form>
