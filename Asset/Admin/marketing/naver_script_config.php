<form id="frmConfig" name="frmConfig" action="naver_script_config_ps.php" method="post"  target ="ifrmProcess" >
<input type="hidden" name="mode" value="naver_common_inflow_script" />
	<div class="page-header js-affix">
		<h3><?php echo end($naviMenu->location);?> </h3>
		<input type="submit" value="저장" class="btn btn-red save" />
	</div>

	<div class="table-title gd-help-manual">
		<?php echo end($naviMenu->location);?>
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col/></colgroup>
		<tr>
			<th>사용 여부</th>
			<td>
				<label class="radio-inline" title="네이버 공통유입 스크립트를 사용하시려면 &quot;사용함&quot;을 체크를 하시면 됩니다.!"><input type="radio" name="naverCommonInflowScriptFl" value="y" <?php echo gd_isset($checked['naverCommonInflowScriptFl']['y']);?> />사용함</label>
				<label class="radio-inline" title="네이버 공통유입 스크립트를 사용하지 않으려면 &quot;사용안함&quot;을 체크를 하시면 됩니다.!"><input type="radio" name="naverCommonInflowScriptFl" value="n" <?php echo gd_isset($checked['naverCommonInflowScriptFl']['n']);?> />사용안함</label>
			</td>
		</tr>
		<tr>
			<th>네이버 공통 인증키</th>
			<td>
				<div class="notice-danger mgb10">
						한번 입력하신 "네이버공통인증키"는 변경하실 수 없습니다. 최초 입력시 유의하여주시기 바랍니다.<br/>
						만일 잘못입력하였거나 변경이 필요할 시에는 고도 고객센터로 문의주시기 바랍니다.
				</div>
				<div>
					<?php if (empty($data['accountId']) === true) {?>
						<input type="text" name="accountId" value="" class="form-control width-xl" />
					<?php } else { ?>
						<span class="eng text-darkblue bold"><?php echo $data['accountId'];?></span>
						<input type="hidden" name="accountId" value="<?php echo $data['accountId'];?>" class="form-control width-xl" />
					<?php } ?>
				</div>
			</td>
		</tr>
		<tr>
			<th>White List</th>
			<td>

				<div class="notice-danger mgb10">
					네이버페이 및 마일리지서비스의 유입경로별 혜택은 네이버광고센터에 등록하신 도메인에 한해서만 적용됩니다.<br/>
					쇼핑몰이 여러개의 도메인으로 운영되는 경우 White List에 해당 도메인들을 추가하여주시기 바랍니다.
				</div>

				<div>

					<table id="addWhiteList">
						<tr id="addWhiteList0">
							<td><input type="text" name="whiteList[0]"  class="form-control width_lLarge"></td>
							<td class="center pdl5 bln"><input type="button" class="btn btn-black btn-sm" onclick="add_white_list();" value="추가" /></td>
						</tr>
						<?php if(gd_isset($data['whiteList'])) { ?>
							<?php foreach($data['whiteList'] as $k => $v) { ?>
								<tr id="addWhiteList<?=$k+1?>">
									<td class="pdt3"><input type="text" name="whiteList[<?=$k+1?>]"  class="form-control width_lLarge" value="<?=$v?>"></td>
									<td class="center pdt3 pdl5 bln"><input type="button" onclick="field_remove('addWhiteList<?=$k+1?>');" class="btn btn-gray btn-sm" value="삭제" /></td>
								</tr>
							<?php }
						} ?>
					</table>

				</div>
			</td>
		</tr>
		</table>
	</div>
</form>

<script type="text/javascript">
	<!--
	$(document).ready(function(){

	});

	//인기검색어
	function add_white_list(){
		var fieldID		= 'addWhiteList';
		var fieldNoChk	= $('#'+fieldID).find('tr:last').get(0).id.replace(fieldID,'');
		if (fieldNoChk == '') {
			var fieldNoChk	= 0;
		}
		var fieldNo		= parseInt(fieldNoChk) + 1;
		var addHtml		= '';
		addHtml	+= '<tr id="'+fieldID+fieldNo+'">';
		addHtml	+= '<td class="center pdt3"><input type="text" name="whiteList['+fieldNo+']" value="" class="form-control width_lLarge" /></td>';
		addHtml	+= '<td class="center pdt3 pdl5 bln"><input type="button" class="btn btn-gray btn-sm" onclick="field_remove(\''+fieldID+fieldNo+'\');" value="삭제" /></td>';
		addHtml	+= '</tr>';
		$('#'+fieldID).append(addHtml);
	}

	//-->
</script>
