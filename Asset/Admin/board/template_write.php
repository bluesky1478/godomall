<script type="text/javascript">
    <!--
    var mode = '<?=$req['mode']?>';

    $(document).ready(function () {
        // Form Process

        $("#frmWrite").validate({
            dialog : false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                if(mode == 'popup') {
                    $.ajax({
                        method: "POST",
                        url: "./template_ps.php",
                        data: $("#frmWrite").serialize(),
                        dataType: 'json'
                    }).success(function (result) {
                        console.log(result);
                        if (result['result'] == 'ok') {
                            $("#bdTemplateSno option",opener.document).remove();
                            if($("select[id^=bdCategoryTemplateSno]",opener.document)) $("select[id^=bdCategoryTemplateSno]",opener.document).empty();
                            var target = result.data;
                            for (var k in target){
                                if (typeof target[k] !== 'function') {
                                    $("#bdTemplateSno",opener.document).append("<option value='"+k+"'>"+target[k]+"</option>");
                                    if($("select[id^=bdCategoryTemplateSno]",opener.document)) $("select[id^=bdCategoryTemplateSno]",opener.document).append("<option value='"+k+"'>"+target[k]+"</option>");
                                }
                            }
                            $("#bdTemplateSno",opener.document).val(result.selected);
                            opener.$("#bdTemplateSno option:selected").trigger('change');
                            self.close();
                        }
                        else {
                            alert(result.msg);
                        }
                    }).error(function (e) {
                        alert(e.responseText);
                    });
                }
                else {
                    form.submit();
                }
            },
            rules: {
                subject: 'required',
                contents: {
                    required: function (textarea) {
                        var editorcontent = oEditors.getById[textarea.id].getIR();	// 에디터의 내용 가져오기.
                        editorcontent = editorcontent.replace(/<[^>]*>/gi, '').replace('&nbsp;', '');
                        return editorcontent.length === 0;
                    }
                }
            },
            messages: {
                subject: {
                    required: '제목을 입력해주세요.'
                },
                contents: {
                    required: '내용을 입력해주세요.'
                }
            }
        })
    });
    //-->
</script>
<?php if($req['mode'] == 'popup') {?>
<div class="page-header js-affix">
    <h3>게시글 양식 등록</h3>
    <button class="close" onclick="self.close();">×</button>
</div>
<?php }?>
<form id="frmWrite" action="template_ps.php" method="post">
    <input type="hidden" name="mode" value="<?=gd_isset($data['mode']) ?>"/>
    <input type="hidden" name="sno" value="<?=gd_isset($data['sno']) ?>"/>
    <table class="table table-cols" style="border-top:1px solid #e6e6e6;">
        <colgroup>
            <col style="width:10%"/>
            <col/>
        </colgroup>
        <tr>
            <th class="input_title space_r">분류</th>
            <td>
                <label class="radio-inline"><input type="radio" name="templateType" value="front" <?php if($data['templateType'] == 'front') echo 'checked'?> <?php if($req['templateType'] == 'admin') echo 'disabled';?>>쇼핑몰 게시글 양식</label>
                <label class="radio-inline"><input type="radio" name="templateType" value="admin" <?php if($data['templateType'] == 'admin') echo 'checked'?> <?php if($req['templateType'] == 'front') echo 'disabled';?>>관리자 게시글 양식</label>
            </td>
        </tr>
        <tr>
            <th class="input_title space_r require">제목</th>
            <td><input type="text" name="subject"
                       value='<?=gd_htmlspecialchars(gd_isset($data['subject'])) ?>' size="95"
                       class="form-control"/></td>
        </tr>
        <tr>
            <th class="input_title space_r require">내용</th>
            <td>
                <!-- mini editor -->
                <textarea name="contents" style="width:98%; height:200px;"
                          id="editor"><?=gd_isset($data['contents']); ?></textarea>
                <script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js"
                        charset="utf-8"></script>
                <script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js"
                        charset="utf-8"></script>
            </td>
        </tr>
    </table>
    <div class="center">
        <input type="submit" value="저장" class="btn btn-lg btn-black"/>
    </div>
</form>
