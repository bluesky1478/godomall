<script type="text/javascript">
<!--
$(document).ready(function(){
    // 스킨 복사 하기
    $("#frmSkin").validate({
        submitHandler: function (form) {
            $('.js-copy-btn').html('복사중입니다. 잠시만 기다려 주세요.');
            form.target = 'ifrmProcess';
            form.submit();
        },
        dialog: false,
        rules: {
            copySkinCode: "required"
        },
        messages: {
            copySkinCode: {
                required: '스킨코드를 입력해 주세요.'
            }
        }
    });
});
//-->
</script>

<form id="frmSkin" name="frmSkin" action="design_skin_list_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="copySkin" />
    <input type="hidden" name="skinName" value="<?php echo $skinName;?>" />
    <input type="hidden" name="skinType" value="<?php echo $skinType;?>" />
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th class="require">스킨코드</th>
            <td>
                <input type="text" name="copySkinCode" value="<?php echo $skinName;?>_C" maxlength="20" placeholder="스킨코드를 입력하세요. (영문, 숫자, _ 만 입력)" class="form-control width90p" style="ime-mode:disabled;"/>
                <div class="notice-info mgt5">스킨코드는 영문, 숫자, _ 만 입력 하세요. (특수문자, 공백, 한글 입력 금지)</div>
            </td>
        </tr>
        <tr>
            <th>스킨명</th>
            <td><input type="text" name="copySkinName" placeholder="스킨명을 입력하세요." class="form-control width90p" maxlength="20"/></td>
        </tr>
    </table>
    <div class="text-center js-copy-btn">
        <input type="submit" value="스킨 복사하기" class="btn btn-red" />
    </div>
</form>

