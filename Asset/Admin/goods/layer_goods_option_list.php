<div class="table-title">검색영역</div>
<table class="table table-cols no-title-line">
<colgroup><col class="width-md" /><col /></colgroup>
<tr>
	<th>검색어</th>
	<td>  <div class="form-inline">
		<?=gd_select_box('key','key',array('all'=>'=통합검색=','optionManageNm'=>'옵션 관리명','optionName'=>'옵션명'),null,$search['key'],null);?>
		<input type="text" name="keyword" value="<?=$search['keyword'];?>" class="form-control" />
		<input type="button" class="btn btn-hf btn-black" value="검색" onclick="layer_list_search();" /></span></div>
	</td>
</tr>
<tr>
	<th>옵션표시</th>
	<td>
		<label class="radio-inline">
			<input type="radio" name="optionDisplayFl" value="s" <?=gd_isset($checked['optionDisplayFl']['s']);?> /> 일체형
		</label>
		<label class="radio-inline">
			<input type="radio" name="optionDisplayFl" value="d" <?=gd_isset($checked['optionDisplayFl']['d']);?> /> 분리형
		</label>
	</td>
</tr>
</table>

<div style="max-height:400px;overflow-x:hidden;overflow-y:auto">
<form id="frm_layer_goods_option">
<input type="hidden" name="mode" value="apply_goods_option">
<table class="table table-rows table-fixed">
<thead>
<tr>
	<th class="width10p">선택</th>
	<th class="width10p">번호</th>
	<th class="width30p">옵션 관리명</th>
	<th class="width20p">옵션표시</th>
	<th class="width10p">옵션갯수</th>
	<th class="width30p">옵션명</th>
</tr>
</thead>
<tbody>
<?php
if (is_array($data)) {
	$arrOptionDisplay    = array('s' => '일체형', 'd' => '분리형', 'm' => '멀티형');
	foreach ($data as $key => $val) {
		$arrOptionName    = explode(STR_DIVISION,$val['optionName']);
?>
<tr>
	<td><input type="checkbox" value="<?=$val['sno'];?>" name="sno[]" data-option-display="<?=$val['optionDisplayFl']?>"></td>
	<td class="center"><?=number_format($key+1);?></td>
	<td class="center"><span class="hand" ><?=$val['optionManageNm'];?></span></td>
	<td class="center"><?=$arrOptionDisplay[$val['optionDisplayFl']];?></td>
	<td class="center number"><?=count($arrOptionName);?></td>
	<td class="center"><?=str_replace(STR_DIVISION,',',$val['optionName']);?></td>
</tr>
<?php
	}
}
?>

</tbody>
</table>
	</form>
</div>
<p class="notice-info">
"일체형/분리형" 옵션을 함께 선택한 경우 등록이 불가능합니다.<br/>
선택된 옵션의 "옵션갯수"의 총 합계가 5개를 초과한 경우 등록이 불가능합니다.
</p>
<div class="text-center" style="margin-top:10px;"><input type="button" value="확인" class="btn btn-lg btn-black js-close" /></span></div>
<script type="text/javascript">
	<!--
	$(document).ready(function(){

		$('.js-close').click(function(e){

			if ($('input[name*="sno[]"]:checked').length == 0) {
				alert('선택된 옵션이 없습니다.');
				return false;
			}

			// 옵션값 채우지 않음
			optionValueFill = false;

            if(typeof(option_reset_layer) == "undefined"){
                // 옵션 리셋
                option_reset();


                var parameters = $("#frm_layer_goods_option").serialize();
                $.post('./goods_ps.php', parameters, function (data) {
                    var getData = $.parseJSON(data);

                    var msg = [];
                    if(getData.displayFl =='') {
                        msg.push("옵션표시 유형이 다른 경우 등록이 불가능합니다.");
                    }

                    if(getData.optionName.length > 5) {
                        msg.push("선택된 옵션의 옵션개수가 5개를 초과하여 등록이 불가능합니다.");
                    }

                    if(msg.length > 0) {
                        alert(msg.join("<br>"));
                        return false;
                    } else {
                        var optionCnt = getData.optionName.length;

                        $('#optionY_optionCnt').val(optionCnt);
                        $("input[name='optionY[optionDisplayFl]'][value='"+getData.displayFl+"']").prop('checked',true);
                        option_setting(optionCnt);

                        for(var i = 0; i < optionCnt; i++) {
                            $('#option_optionName_'+i).val(getData.optionName[i]);
                            if (typeof getData.optionValue != 'undefined' && getData.optionValue != null) {
                                if (typeof getData.optionValue[i] != 'undefined') {
                                    var valueCnt = getData.optionValue[i].length;
                                    $('#option_optionCnt_' + i).val(valueCnt);
                                    option_value_conf(i, valueCnt, true);

                                    for (var j = 0; j < valueCnt; j++) {
                                        $('#option_optionValue_' + i + '_' + j).val(getData.optionValue[i][j]);
                                    }
                                }
                            }
                        }

                        option_grid();

                        $('div.bootstrap-dialog-close-button').click();
                    }
                });
            }else{
                // 옵션 리셋
                option_reset_layer();


                var parameters = $("#frm_layer_goods_option").serialize();
                $.post('./goods_ps.php', parameters, function (data) {
                    var getData = $.parseJSON(data);

                    var msg = [];
                    if(getData.displayFl =='') {
                        msg.push("옵션표시 유형이 다른 경우 등록이 불가능합니다.");
                    }

                    if(getData.optionName.length > 5) {
                        msg.push("선택된 옵션의 옵션개수가 5개를 초과하여 등록이 불가능합니다.");
                    }

                    if(msg.length > 0) {
                        alert(msg.join("<br>"));
                        return false;
                    } else {
                        var optionCnt = getData.optionName.length;

                        $('#optionY_optionCnt').val(optionCnt);
                        $("input[name='optionY[optionDisplayFl]'][value='"+getData.displayFl+"']").prop('checked',true);
                        option_setting_layer(optionCnt);

                        for(var i = 0; i < optionCnt; i++) {
                            $('#option_optionName_layer_'+i).val(getData.optionName[i]);
                            if (typeof getData.optionValue != 'undefined' && getData.optionValue != null) {
                                if (typeof getData.optionValue[i] != 'undefined') {
                                    var valueCnt = getData.optionValue[i].length;
                                    $('#option_optionCnt_' + i).val(valueCnt);
                                    option_value_conf_layer(i, valueCnt, true);

                                    for (var j = 0; j < valueCnt; j++) {
                                        $('#option_optionValue_' + i + '_' + j).val(getData.optionValue[i][j]);
                                    }
                                }
                            }
                        }

                        option_grid_layer('y');

                        $('div.bootstrap-dialog-close-button').click();
                    }
                });
            }
		});
	});

	/**
	 * 자주쓰는 옵션 검색
	 *
	 * @param string pagelink 페이지 parameters
	 */
	function layer_list_search(pagelink)
	{
		var keyStr			= $('#key').val();
		var keyword			= $('input[name=\'keyword\']').val();
		var optionDisplayFl	= $('input[name=\'optionDisplayFl\']:checked').val();

		if (typeof optionDisplayFl == 'undefined') {
			optionDisplayFl		= '';
		}

		var parameters	= {
			'key'				: keyStr,
			'keyword'			: keyword,
			'optionDisplayFl'	: optionDisplayFl,
			'scmNo'	: '<?=$scmNo?>',
			'pagelink'			: pagelink
		};

		$.get('layer_goods_option_list.php',parameters, function(data){
			$('#layerOptionListForm').html(data);
		});
	}

	//-->
</script>
