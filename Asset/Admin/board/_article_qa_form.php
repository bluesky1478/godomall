<script>
    var bdId = '<?=$req['bdId']?>';
    var replyStatusComplete='<?=$replyStatusComplete?>';
    $(document).ready(function(){
        $('#frmWrite').find('[name=queryString]').val(getUrlVars());

        $("#frmWrite").validate({
            submitHandler: function (form) {
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },
        });

        $('#bdTemplateSno').bind('change',function(){
            if($(this).val() == '' || $(this).val() == 0){
                return false;
            }

            var editorcontent = oEditors.getById['editor'].getIR();	// 에디터의 내용 가져오기.
            editorcontent = editorcontent.replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
            if (editorcontent.length > 0) {
                if (!confirm('본문이 삭제되고 템플릿내용이 삽입됩니다. 진행하시겠습니까?')) {
                    return false;
                }
            }

            $.ajax({
                method: "POST",
                url: "./template_ps.php",
                data: {mode : 'getData',sno : $(this).val()},
                dataType: 'json'
            }).success(function (result) {
                console.log(result);
                var contents = result['contents'];
                oEditors.getById["editor"].exec("SET_CONTENTS", [contents]);
            }).error(function (e) {
                alert(e.responseText);
            });
        })

        $('.js-template-register').bind('click',function(){
            window.open('template_write.php?mode=popup&templateType=admin','template','width=850,height=600');
        })
    })
</script>
<form method="post" id="frmWrite" action="article_ps.php">
<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <?php if($req['popupMode'] !='yes') { // CRM 팝업모드가 아닐 경우 ?>
        <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
        <?php } ?>
        <input type="submit" value="저장하기" class="btn btn-red"/>
    </div>
</div>
<?php
include '_article_detail.php';
?>
    <div class="table-title gd-help-manual">게시글 답변하기</div>
    <input type="hidden" name="bdId" value="<?=$req['bdId']?>" >
    <input type="hidden" name="mode" value="replyQa"/>
    <input type="hidden" name="queryString" value=""/>
    <input type="hidden" name="sno" value="<?=$req['sno']?>"/>
    <table class="table table-cols">
        <col class="width-md"/>
        <col/>
        <tr>
            <th>답변 작성자</th>
            <td>
                <?= $writer['writer'] ?>
            </td>
        </tr>
        <tr>
            <th>답변 상태</th>
            <td>
                <select name="replyStatus" class="form-control">
                    <?php
                    foreach ($listReplyStatus as $key => $val) { ?>
                        <option
                            value="<?= $key ?>" <?php if ($bdView['data']['replyStatus'] == $key) echo 'selected' ?>><?= $val ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <?php if(gd_is_provider() === false) {?>
            <tr>
                <th>게시글 양식</th>
                <td class="form-inline">
                    <?= gd_select_box('bdTemplateSno', 'bdTemplateSno', $templateList); ?>
                    <input type="button" value="게시글 양식 등록" class="btn btn-black js-template-register">
                </td>
            </tr>
        <?php }?>
        <tr>
            <th>답변 제목</th>
            <td>
                <input type="text" class="form-control" name="answerSubject" value="<?= gd_isset($bdView['data']['answerSubject']) ?>"/>
            </td>
        </tr>
        <tr>
            <th>답변 내용</th>
            <td>
                <textarea class="form-control" name="answerContents" id="editor" style="width:98%; height:450px;" label="내용"><?= gd_isset($bdView['data']['answerContents']); ?></textarea>
            </td>
        </tr>
    </table>
</form>
<?php if($req['popupMode'] !='yes') { // CRM 팝업모드가 아닐 경우 ?>
<div class="text-center">
    <button class="btn btn-white" type="button" onclick="btnList('<?=$req['bdId']?>')" >목록가기</button>
</div>
<?php } ?>
<style>
    textarea {
        width: 85% !important;;
        height: 110px !important;;
    }
</style>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js?ss=<?= date('YmdHis') ?>" charset="utf-8"></script>
