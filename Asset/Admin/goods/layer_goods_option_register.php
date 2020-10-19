<script type="text/javascript">
<!--
$(document).ready(function(){
});

/**
 * 자주쓰는 옵션 등록
 */
function option_register(){
	if ($('#optionManageNm').val() == '') {
		alert('옵션 관리명을 입력해 주세요!');
		return false;
	}

	// 옵션 갯수
	var optionCnt	= $('#optionY_optionCnt').val();
	var optionName	= new Array();
	var optionValue	= new Array();
<?php
    for ($i = 0; $i < DEFAULT_LIMIT_OPTION; $i++) {
        echo '	var optionValue_'.$i.'	= new Array();'.chr(10);
    }
?>

	// 옵션값이 있는지를 체크하며, 전체 옵션값 갯수를 계산을 함
	for (var i = 0; i < optionCnt; i++) {
        if ($('#option_optionName_'+i).val() == undefined) {
            if ($('#option_optionName_layer_'+i).val() == ''){
                alert('옵션명을 작성해 주세요!');
                $.unblockUI();
                return false;
            } else {
                optionName[i]	= $('#option_optionName_layer_'+i).val();
            }
        }else if ($('#option_optionName_'+i).val() == '') {
			alert('옵션명을 작성해 주세요!');
			$.unblockUI();
			return false;
		} else {
			optionName[i]	= $('#option_optionName_'+i).val();
		}
		if ($('#option_optionCnt_'+i).val() == '') {
			alert('옵션값이 설정되지 않았습니다!');
			$.unblockUI();
			return false;
		} else {
			for (var j = 0; j < $('#option_optionCnt_'+i).val(); j++) {
				eval('optionValue_'+i)[j]	= $('#option_optionValue_'+i+'_'+j).val();
			}
			optionValue[i]	= eval('optionValue_'+i).join('<?=STR_DIVISION;?>');
		}
	}

	var parameters	= {
		mode:'option_direct_register',
		optionManageNm:$('#optionManageNm').val(),
		scmNo:'<?=$scmNo?>',
		optionDisplayFl:$('input[name=\'optionY\[optionDisplayFl\]\']:checked').val(),
		optionName:optionName.join('<?=STR_DIVISION;?>'),
		'optionValue[]':
		[
<?php
    for ($i = 0; $i < DEFAULT_LIMIT_OPTION; $i++) {
        $division    = '';
        if (($i+1) != DEFAULT_LIMIT_OPTION){
            $division    = ',';
        }
        echo 'optionValue['.$i.']'.$division.chr(10);
    }
?>
		]
	};

	$.post('goods_ps.php',parameters, function(data){
		if (data == '') {
			alert('현재의 옵션이 자주쓰는 옵션으로 등록 되었습니다.');
		} else {
			alert('오류로 인해 처리 되지 않았습니다.');
		}
	});
	$('div.bootstrap-dialog-close-button').click();
}
//-->
</script>
	<div>
		<table class="table table-cols no-title-line">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th>옵션 관리명</th>
			<td><div class="form-inline">
				<input type="text" id="optionManageNm" name="optionManageNm" value="" class="form-control width-xl" />
					<button type="button" class="btn btn-black btn-hf" onclick="option_register();">등록</button></div>
			</td>
		</tr>
		</table>
	</div>
</div>
