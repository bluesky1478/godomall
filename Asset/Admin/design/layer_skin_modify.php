<script type="text/javascript">
    <!--
    $(document).ready(function(){
        $("#frmSkin").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            dialog: false
        });
    });
    //-->
</script>
<form id="frmSkin" name="frmSkin" action="design_skin_list_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="modifySkin" />
    <input type="hidden" name="skinName" value="<?php echo $skinInfo['skin_name']; ?>" />
    <input type="hidden" name="skinCode" value="<?php echo $skinInfo['skin_code']; ?>" />
    <input type="hidden" name="skinType" value="<?php echo $skinType;?>" />
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>스킨코드</th>
            <td colspan="3"><?php echo $skinInfo['skin_code']; ?></td>
        </tr>
        <tr>
            <th>스킨명</th>
            <td colspan="3">
                <input type="text" name="skinName" value="<?php echo $skinInfo['skin_name']; ?>" placeholder="스킨명을 입력하세요." maxlength="20" class="form-control width90p"/>
            </td>
        </tr>
        <tr>
            <th>썸네일 이미지</th>
            <td colspan="3">
                <input type="file" name="thumbnails" class="form-control width90p"/>
                <div class="notice-info mgt5">jpg, jpeg, png, gif만 등록 가능하며, 기본 이미지는 150x150 pixel 입니다.</div>
                <div class="notice-info mgt5">이미지명은 되도록이면 영문으로 올려주세요.</div>
            </td>
        </tr>
        <tr>
            <th>적용가능 솔루션</th>
            <td><?php echo $skinInfo['apply_solution']; ?></td>
            <th>스킨 언어</th>
            <td><?php echo $skinInfo['skin_language']; ?></td>
        </tr>
        <tr>
            <th>배포일자</th>
            <td><?php echo $skinInfo['skin_date']; ?></td>
            <th>스킨버전</th>
            <td><?php echo $skinInfo['skin_version']; ?></td>
        </tr>
        <tr>
            <th>라이센스</th>
            <td colspan="3"><?php echo $skinInfo['skin_license']; ?></td>
        </tr>
        <tr>
            <th>제작자</th>
            <td><?php echo $skinInfo['author']; ?></td>
            <th>제작사</th>
            <td><?php echo $skinInfo['company']; ?></td>
        </tr>
    </table>
    <div class="text-center js-copy-btn">
        <a class="btn btn-white btn-lg js-layer-close">취소</a>
        <input type="submit" value="확인" class="btn btn-red btn-lg" />
    </div>
</form>

