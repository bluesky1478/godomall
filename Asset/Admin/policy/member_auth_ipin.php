<form id="frmSetup" action="member_ps.php" method="post">
<input type="hidden" name="mode" value="member_auth_ipin" />
	<!--<div class="phead_wrap mgt0"><div class="phead">
		<h2><?php /*echo end($naviMenu->location);*/?> <span>아이핀 서비스에 필요한 항목을 설정합니다.</span></h2>
		<div class="scroll_save"><input type="submit" value="" class="save" /></div>
	</div></div>
	-->
	<div class="page-header js-affix">
		<h3><?= end($naviMenu->location); ?>
			<small></small>
		</h3>
		<input type="submit" value="저장" class="btn btn-red"/>
	</div>

	<div class="table-title gd-help-manual">
		아이핀인증 사용 설정
	</div>
	<table class="table table-cols">
		<colgroup>
			<col class="width-md" />
			<col />
		</colgroup>
		<tr>
			<th>사용 설정</th>
			<td class="form-inline">
				<label class="radio-inline">
					<input type="radio" name="useFl" value="y" <?php echo gd_isset($checked['useFl']['y']);?> />사용함
				</label>
				<label class="radio-inline">
					<input type="radio" name="useFl" value="n" <?php echo gd_isset($checked['useFl']['n']);?> />사용안함
				</label>
                <p class="notice-info">
                    서비스 신청 전인 경우 먼저 서비스를 신청하세요.
                    <a href="/service/service_info.php?menu=member_ipin_info" target="_blank" class="btn-link">서비스 자세히보기 ></a>
                </p>
			</td>
		</tr>
		<tr>
			<th>사이트 CODE</th>
			<td class="form-inline">
				<input type="text" name="siteCode" value="<?php echo gd_isset($data['siteCode']);?>" class="form-control" />
				<p class="notice-info">NICE신용평가정보와 계약후 발급 받은 &quot;사이트 CODE&quot;를 입력하세요.</p>
			</td>
		</tr>
		<tr>
			<th>사이트 Password</th>
			<td class="form-inline">
				<input type="text" name="sitePass" value="<?php echo gd_isset($data['sitePass']);?>" class="form-control" />
				<p class="notice-info">NICE신용평가정보와 계약후 발급 받은 &quot;사이트 Password&quot;를 입력하세요.</p>
			</td>
		</tr>
	</table>
</form>

<script type="text/javascript">
<!--
$(document).ready(function(){
	$(document).ready(function () {
		$('#frmSetup').validate({
			submitHandler: function (form) {
                var params = $(form).serializeArray();
                ajax_with_layer('./member_ps.php', params, function (params, textStatus, jqXHR) {
                    layer_close();
                    dialog_alert(params, '확인', {isReload: true});
                });
			}
		});
	});
});
//-->
</script>
