<form method="post" name="frmShortUrlRegist" id="frmShortUrlRegist" action="./short_url_ps.php" target="ifrmProcess">
	<input type="hidden" name="mode" value="registShortUrl">

    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>원본 URL</th>
            <td>
                <input type="text" name="longUrl" value="" class="form-control">
            </td>
        </tr>
        <tr>
            <th>URL 설명</th>
            <td>
                <input type="text" name="description" value="" maxlength="20" class="form-control">
            </td>
        </tr>
    </table>

	<div class="text-center mgt10">
		<button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
		<button type="submit" class="btn btn-lg btn-black">등록</button>
	</div>
</form>

<script type="text/javascript">
	<!--
	$(document).ready(function(){
		// 폼 체크 후 전송
		$('#frmShortUrlRegist').validate({
            dialog: false,
			rules: {
                longUrl: 'required'
			},
			messages: {
                longUrl: '원본 URL을 입력해주세요.'
			},
			submitHandler: function(form) {
				form.target = 'ifrmProcess';
				form.submit();
			}
		});

        // space 입력되지 않도록 처리
        $('input[name="longUrl"]').keyup(function(e){
            $(this).val($.trim($(this).val()));
        });

        $('input[maxlength]').maxlength({
            showOnReady: true,
            alwaysShow: true
        });
	});
	//-->
</script>
