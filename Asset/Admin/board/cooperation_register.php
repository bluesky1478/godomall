<form name="frmCoop" action="../board/cooperation_ps.php" method="post">
	<input type="hidden" name="mode" value="modify" />
	<input type="hidden" name="sno" value="<?=gd_isset($data['sno'])?>" />

	<div class="page-header js-affix">
		<h3>문의답변 <small>문의에 대해 답변합니다.</small></h3>
		<input type="submit" value="저장" class="btn btn-red" onclick="if (check_smart_authority() === false) return false;" />
	</div>

	<div class="table-title gd-help-manual">문의</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-sm" /><col class="width-xl" /><col class="width-sm" /><col/></colgroup>
		<tr>
			<th class="input_title r_space require">문의분야 설정</th>
			<td>
				<select name="itemCd" class="form-control">
				<option value="">== 전체 ==</option>
				<?php if (isset($field['itemCd']) && is_array($field['itemCd'])) { foreach($field['itemCd'] as $k => $v) {?>
				<option value="<?php echo $k;?>" <?php echo gd_isset($selected['itemCd'][$k]);?>><?php echo $v;?></option>
				<?php } }?>
				</select>
			</td>
			<th>접수일</th>
			<td class="input_area date"><?php echo $data['regDt'];?></td>
		</tr>
		<tr>
			<th class="input_title r_space require">고객명</th>
			<td class="input_area" colspan="3">
				<span title="이름을 입력해주세요!"><input type="text" name="name" value="<?php echo gd_isset($data['name']);?>" class="form-control width-xs" /></span>
				<span id="name_msg" class="input_error_msg"></span>
			</td>
		</tr>
		<tr>
			<th>이메일</th>
			<td class="input_area" colspan="3">
				<span title="이메일을 입력해주세요!"><input type="text" name="email[]" value="<?php echo gd_isset($data['email'][0]);?>" class="form-control width-xs"/></span> @
				<span title="이메일을 입력해주세요!"><input type="text" id="email" name="email[]" value="<?php echo gd_isset($data['email'][1]);?>" class="form-control width-xs"/></span>
				<?php echo gd_select_box('email_domain','email_domain',$field['email'],null,$data['email'][1]);?>
				<span class="font-eng"><?php echo implode('@',$data['email']);?></span>
				<span id="emailMsg" class="input_error_msg"></span>
			</td>
		</tr>
		<tr>
			<th>문의제목</th>
			<td class="input_area" colspan="3"><?php echo $data['title'];?></td>
		</tr>
		<tr>
			<th>문의내용</th>
			<td class="input_area" colspan="3" style="padding:0 0 0 10px;"><div style="height:205px; overflow-y:scroll;"><?php echo nl2br($data['content']);?></div></td>
		</tr>
		</table>
	</div>

	<div class="table-title gd-help-manual">답변</div>
	<div>
		<table class="table table-cols">
		<colgroup><col class="width-sm" /><col/></colgroup>
		<tr>
			<th>답변내용</th>
			<td>
				<span title="답변내용을 작성해 주세요!">
				<textarea name="reply" rows="15" cols="" class="form-control width90p"><?php echo gd_isset($data['reply']);?></textarea>
				</span>
				<span id="replyMsg" class="input_error_msg"></span>
			</td>
		</tr>
		<tr>
			<th>답변일</th>
			<td>
				<span title="답변일을 입력해주세요!"><input type="text" name="replayDt" value="<?php echo gd_isset($data['replyDt']);?>" class="form-control width-xs" /></span>
				<span id="replayDt_msg" class="input_error_msg"></span>
			</td>
		</tr>
		<tr>
			<th>메일전송</th>
			<td>
				<strong><?php echo (str_replace('-', '', substr($data['mailDt'],8)) > 0 ? '<span class="font-date">' . $data['mailDt'] . '</span>' : '<span class="bold">미발송</span>')?></strong>
				&nbsp;&nbsp;
				<span class="button"><a href="../board/cooperation_ps.php?mode=mailsend&sno=<?php echo $data['sno']?>" target="ifrmProcess">수동으로 전송하기</a></span>
				<div style="padding-top:10px">
					<label><input type="checkbox" name="mailFl" value="y"/> 답변을 등록할 때 고객에게 답변메일을 동시에 전송하시겠습니까?</label>
				</div>
			</td>
		</tr>
		</table>
	</div>

	<div class="text-center">
		<input type="submit" value="저장" class="btn btn-red"/>
	</div>
</form>



<script type="text/javascript">
<!--
$(document).ready(function(){
	// 이메일 도메인 대입
	$('#email_domain').each(function () {put_email_domain('email');});
	$('#email_domain').change(function () {put_email_domain('email');});

	// 폼
	$('form[name=frmCoop]').formProcess('msg',[
		{'before':function(fObj){
			if ($('input[name=mode]',fObj).val() == 'modify' && $('input[name=sno]',fObj).val() == ''){
				alert('수정할 정보가 비어있습니다.');
				return false;
			}
			return true;
		}}
		, {'inputName':'itemCd','name':'분야','required':'required','msgId':'itemCd_msg'}
		, {'inputName':'name','name':'고객명','required':'required','msgId':'name_msg'}
	],'alert',false);
});
//-->
</script>
