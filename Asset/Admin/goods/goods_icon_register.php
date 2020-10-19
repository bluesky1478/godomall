<script type="text/javascript">
<!--
$(document).ready(function(){

	$("#frmIcon").validate({
		submitHandler: function (form) {
			form.target='ifrmProcess';
			form.submit();
		},
		// onclick: false, // <-- add this option
		rules: {
			iconNm: 'required'
		},
		messages: {
			iconNm: {
				required: '아이콘이름을 입력하세요.'
			}
		}
	});

});
//-->
</script>
<form id="frmIcon" name="frmIcon" action="./goods_ps.php" method="post" enctype="multipart/form-data">
<input type="hidden" name="mode" value="<?=$data['mode'];?>" />
<input type="hidden" name="sno" value="<?=$data['sno'];?>" />
<input type="hidden" name="iconCd" value="<?=$data['iconCd'];?>" />
<input type="hidden" name="iconImageTemp" value="<?=$data['iconImage'];?>" />

	<div class="page-header js-affix">
		<h3><?=end($naviMenu->location); ?></h3>
		<div class="btn-group">
			<input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./goods_icon_list.php');" />
			<input type="submit" value="저장" class="btn btn-red" />

		</div>
	</div>

	<div class="table-title gd-help-manual">
		<?=end($naviMenu->location); ?>
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th class="require">아이콘 이름 </th>
			<td>
				<label title="아이콘 이름을 넣어주세요. 태그는 &quot;사용할 수 없습니다!&quot;"><input type="text" name="iconNm" value="<?=$data['iconNm'];?>" class="form-control width-lg js-maxlength"  maxlength="30" /></label>
			</td>
		</tr>
		<tr>
			<th >아이콘 코드</th>
			<td><?php if ($data['mode'] == "icon_register") { echo "아이콘 등록시 자동 생성됩니다."; } else { echo $data['iconCd']; }?></td>
		</tr>
		<tr>
			<th >기간제한 상태 </th>
			<td>
				<label class="radio-inline" title="해당 아이콘에 기간제한을 사용하지 않을 경우에는 &quot;무제한용&quot;을 선택하세요!"><input type="radio" name="iconPeriodFl" value="n" <?=gd_isset($checked['iconPeriodFl']['n']);?> />무제한용</label>
				<label class="radio-inline" title="해당 아이콘에 기간제한을 사용할 경우에는 &quot;기간제한용&quot;을 선택하세요!"><input type="radio" name="iconPeriodFl" value="y" <?=gd_isset($checked['iconPeriodFl']['y']);?> />기간제한용</label>
			</td>
		</tr>
		<tr>
			<th >사용상태 </th>
			<td>
				<label  class="radio-inline" title="해당 아이콘 사용할 경우에는 &quot;사용&quot;을 선택하세요!"><input type="radio" name="iconUseFl" value="y" <?=gd_isset($checked['iconUseFl']['y']);?> />사용함</label>
				<label  class="radio-inline" title="해당 아이콘 사용하지 않을 경우에는 &quot;사용안함&quot;을 선택하세요!"><input type="radio" name="iconUseFl" value="n" <?=gd_isset($checked['iconUseFl']['n']);?> />사용안함</label>
			</td>
		</tr>
		</table>
	</div>

	<div class="table-title gd-help-manual">
		아이콘 이미지 설정
	</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-md" /><col /></colgroup>
		<tr>
			<th>저장 경로</th>
			<td><?=UserFilePath::icon('goods_icon')->www();?></td>
		</tr>
		<tr>
			<th class="require">아이콘 이미지</th>
			<td>
				<div class="form-inline">
				<div style="padding:10px;border:1px solid #AEAEAE;float:left;text-align:center;display:table-cell; vertical-align:middle;margin-right:10px;">
					<?php if (empty($data['iconImage']) === false) { echo gd_html_image(UserFilePath::icon('goods_icon', $data['iconImage'])->www(), $data['iconNm']); }?>
				</div>
				<div style="float:left;">
					<input type="file" name="iconImage" value="" class="form-control" />
					<div style="padding-top:5px;"><?php if (empty($data['iconImage']) === false) { echo gd_htmlspecialchars_slashes($data['iconImage'],'add'); }?></div>
				</div>
				</div>
				<div style="clear:both;padding-bottom:10px;"></div>
				<div class="notice-info">아이콘 이미지 사이즈는 작게 해서 올려 주세요. 해당 이미지 크기 그대로 출력이 됩니다.</div>
			</td>
		</tr>
		</table>
	</div>

</form>
