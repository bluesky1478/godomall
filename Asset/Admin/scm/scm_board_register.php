<script type="text/javascript">
    <!--
    var MAX_UPLOAD_COUNT = <?=$maxUploadCount?>;
    $(document).ready(function () {
        $("#frmWrite").validate({
            submitHandler: function (form) {
                if ($('input[name="scmFl"]:checked').val() == 'y') {
                    if ($('input[name="scmNo[]"]').length === 0) {
                        alert('대상을 선택해주세요.')
                        return false;
                    }
                }

                oEditors.getById["editor"].exec("UPDATE_CONTENTS_FIELD", []);	// 에디터의 내용이 textarea에 적용됩니다.
                form.target = 'ifrmProcess';
                form.submit();
            },
            // onclick: false, // <-- add this option
            rules: {
                scmFl: 'required',
                subject: 'required',
                contents: {
                    required: function (textarea) {
                        var editorcontent = oEditors.getById[textarea.id].getIR();	// 에디터의 내용 가져오기.
                        editorcontent = editorcontent.replace(/<(?!img).*?>/ig, '').replace('&nbsp;', '');  //이미지테그제외한 테그제거
                        return editorcontent === 0;
                    }
                }
            },
            messages: {
                scmFl: {
                    required: '대상을 체크해주세요.',
                },
                subject: {
                    required: '제목을 입력해주세요.'
                },
                contents: {
                    required: '내용을 입력해주세요'
                },
            }
        });

        $('body').on('click', '.addUploadBtn', function () {
            if ($('input[name="uploadFiles[]"]').length >= 5) {
                alert("파일은 최대 "+MAX_UPLOAD_COUNT+"개까지 업로드를 지원합니다");
                return;
            }
            var addUploadBox = _.template(
                $("script.template").html()
            );
            $(this).closest('ul').append(addUploadBox);
            init_file_style();
        });

        $('body').on('click', '.minusUploadBtn', function () {
            $(this).closest('li').remove();
        })

    });

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }
    //-->
</script>
<script type="text/template" class="template">
    <li class="form-inline mgb5">
        <input type="file" name="uploadFiles[]">
        <a class="btn btn-white btn-icon-minus minusUploadBtn btn-sm">삭제</a>
    </li>
</script>
<form id="frmWrite" action="scm_board_ps.php" method="post" enctype="multipart/form-data">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./scm_board_list.php');" />
            <input type="submit" class="btn btn-red" value="저장"/>
        </div>
    </div>
    <div>
        <div class="table-title">게시글 <?=$mode?></div>

        <?php if ($req['mode'] == 'modify' || $req['mode'] == 'reply') { ?>
            <input type="hidden" name="sno" value="<?= $req['sno'] ?>"/>
        <?php } ?>
        <input type="hidden" name="mode" value="<?= $req['mode'] ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>

            <?php if (!gd_is_provider() && $req['mode'] != 'reply') { ?>
                <tr>
                    <th>대상</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="all" <?= gd_isset($data['checked']['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="n" <?= gd_isset($data['checked']['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="scmFl" value="y" <?= gd_isset($data['checked']['scmFl']['y']); ?> onclick="layer_register('scm','checkbox')"/>공급사
                        </label>
                        <label>
                            <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                        </label>

                        <div id="scmLayer" class="width100p">
                            <?php if ($data['scmFl'] == 'y') {
                                //TODO:수정
                                if ($data['scmBoardGroup']) {
                                    foreach ($data['scmBoardGroup'] as $k => $v) { ?>
                                        <span id="info_scm_<?= $v['scmNo'] ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="scmNo[]" value="<?= $v['scmNo'] ?>"/>
                                <input type="hidden" name="scmNoNm[]" value="<?= $v['companyNm'] ?>"/>
                                <button type="button" class="btn btn-default" name="scmNoNm"><?= $v['companyNm'] ?></button>
                                <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#info_scm_<?= $v['scmNo'] ?>">삭제</button>
                                </span>
                                    <?php }
                                }
                            } ?>
                        </div>

                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th class="require">제목</th>
                <td class="form-inline"><input type="text" name="subject" value="<?= gd_isset($data['subject']) ?>" class="form-control width70p">
                    <?php if (!gd_is_provider() && !$data['groupThread']) { ?>
                        <label class="checkbox-inline"><input type="checkbox" name="isNotice" value="y" <?= $data['checked']['isNotice']['y'] ?>/>공지사항</label>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <th>분류</th>
                <td><?= gd_select_box('category', 'category', $category, null, gd_isset($data['category']), '=전체='); ?></td>
            </tr>
            <tr>
                <th>작성자</th>
                <td><?= $data['writer'] ?></td>
            </tr>
            <tr>
                <th>파일첨부</th>
                <td>
                    <ul class="pdl0">
                        <?php
                        if (gd_isset($data['upfilesCnt'], 0) > 0) {
                            for ($i = 0; $i < $data['upfilesCnt']; $i++) {
                                ?>
                                <li class="form-inline mgb5">
                                    <input type="file" name="uploadFiles[]"/><br>
                                    <input type="checkbox" name="delFile[<?= $i ?>]" value="y"/>
                                    Delete Uploaded File
                                    .. <?= $data['uploadFileNm'][$i] ?>
                                </li>
                                <?php
                            }
                        }
                        ?>
                        <?php if (gd_isset($data['upfilesCnt'], 0) < 12) { ?>
                            <li class="form-inline mgb5">
                                <input type="file" name="uploadFiles[]">
                                <a class="btn btn-white btn-icon-plus addUploadBtn btn-sm">추가</a>
                            </li>
                        <?php } ?>
                    </ul>

                    <div class="notice-info">
                        파일은 최대 <?=$maxUploadCount?>개까지 업로드를 지원합니다.<br>
                        파일 업로드 최대 사이즈는 <?=$maxUploadSize?>MB 입니다.
                    </div>
                </td>
            </tr>
            <tr>
                <th class="require">내용</th>
                <td>
                    <textarea name="contents" id="editor" rows="10" style="width:98%; height:412px; "><?= gd_isset($data['contents']); ?></textarea>
                </td>
            </tr>
        </table>

    </div>
</form>
<script type="text/javascript">
   // var editorPath = '<?=PATH_ADMIN_GD_SHARE ?>script/smart';
</script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js?ss=<?= date('YmdHis') ?>" charset="utf-8"></script>
