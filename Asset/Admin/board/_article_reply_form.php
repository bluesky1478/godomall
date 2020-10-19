<div id="divWrite" style="padding:0px 5px 0px 5px;">

    <div class="table-title gd-help-manual">답글</div>

    <form name="frmWrite" id="frmWrite" action="article_ps.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="bdId" value="<?=$bdWrite['cfg']['bdId'] ?>">
        <input type="hidden" name="sno" value="<?=$req['sno'] ?>">
        <input type="hidden" name="mode" value="<?=$req['mode'] ?>">
        <input type="hidden" name="writerNm" value="<?=$bdWrite['data']['writerNm'] ?>">
        <input type="hidden" name="goodsNo" value="<?=$bdWrite['data']['goodsNo'] ?>">

        <?php if ($bdWrite['cfg']['bdCategoryFl'] == 'y') { ?>
            <input type="hidden" name="category" value="<?=$bdParent['data']['category']; ?>"/>
        <?php } ?>

        <table class="table table-cols">
            <colgroup>
                <col width="100px"/>
                <col width="550px"/>
            </colgroup>
            <tr>
                <th>게시글 양식</th>
                <td>
                    <select id="replyTemplate" class="form-control">
                        <option value=""> = 선택안함 =</option>
                        <?php
                        if (empty($bdWrite['replyTemplate']) === false) {
                            for ($i = 0; $i < count($bdWrite['replyTemplate']); $i++) {
                                ?>
                                <option
                                    value="<?=$bdWrite['replyTemplate'][$i]['sno'] ?>"><?=($bdWrite['replyTemplate'][$i]['subject']) ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th>작성자</th>
                <td>
                    <?=gd_isset($bdWrite['data']['writer']) ?>
                </td>
            </tr>
            <tr>
                <th>작성타입</th>
                <td>
                    <select name="replyMode" onchange="changeReply(this.value)" class="form-control">
                        <option value="reply">답글</option>
                        <?php
                        if ($bdWrite['cfg']['bdMemoFl'] == 'y') {
                            ?>
                            <option value="memo">댓글</option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr name="trOnlyReply">
                <th>제목</th>
                <td>
                    <input type="text" name="subject" value="<?=gd_isset($bdWrite['data']['subject']) ?>"
                           class="form-control"/>
                </td>
            </tr>

            <?php
            if ($bdWrite['cfg']['bdUploadFl'] == 'y') {
                ?>
                <tr name="trOnlyReply">
                    <td colspan="2">
                        <table id="tblUpload" class="table table-cols">
                            <?php
                            if (gd_isset($bdWrite['data']['upfilesCnt'], 0) > 0) {
                                for ($i = 0;
                                     $i < $bdWrite['data']['upfilesCnt'];
                                     $i++) {
                                    ?>
                                    <tr name="<?=($i + 1) ?>">
                                        <td><?=($i + 1) ?></td>
                                        <td class="form-group">
                                            <input type="file" name="upfiles[]"/><br>
                                            <input type="checkbox" name="delFile[<?=$i ?>]" value="y"/>
                                            Delete Uploaded File
                                            .. <?=$bdWrite['data']['uploadFileNm'][$i] ?>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                            <?php if (gd_isset($bdWrite['data']['upfilesCnt'], 0) < 12) { ?>
                                <tr>
                                    <td><?=$bdWrite['data']['upfilesCnt'] + 1 ?></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="file" name="upfiles[]">
                                            <span class="input-group-btn">
                                                 <a class="btn btn-red addUploadBtn">+</a>
                                             </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>

                        <div class="snote width100p">
                            - 파일은 최대 12개까지 다중업로드가 지원됩니다<br>
                            <?php if ($bdWrite['cfg']['bdStrMaxSize'] != '') echo '<div>- 파일 업로드 최대 사이즈는 ' . $bdWrite['cfg']['bdStrMaxSize'] . '입니다</div>'; ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
            <?php
            if ($bdWrite['cfg']['bdLinkFl'] == 'y') {
                ?>
                <tr name="trOnlyReply">
                    <th>링크</th>
                    <td>
                        <input type="text" name="urlLink"
                               value="<?=gd_isset($bdWrite['data']['urlLink']) ?>"
                               class="form-control">
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <td colspan="2" name="trOnlyReply">
                    <?php

                    switch ($bdWrite['cfg']['bdSecretFl']) {
                        case '1' : {
                            // 비밀글 설정 - 작성시 기본 비밀글
                            echo '<input type="checkbox" name="isSecret" is="w_isSecret" value="y" ';
                            if ($req['mode'] != 'modify' || gd_isset($bdWrite['data']['isSecret']) == 'y') echo 'checked="checked"';
                            echo ' /> <label for="w_isSecret">비밀글</label>';
                            break;
                        }
                        case '2' : {
                            // 비밀글 설정 - 작성시 무조건 일반글
                            echo '<input type="hidden" name="isSecret" value="n" />';
                            break;
                        }
                        case '3' : {
                            // 비밀글 설정 - 작성시 무조건 비밀글
                            echo '<input type="hidden" name="isSecret" value="y" /> 해당글은 비밀글로만 작성이 됩니다.';
                            break;
                        }
                        default : {
                            echo '<input type="checkbox" name="isSecret" is="w_isSecret" value="y" ';
                            if (gd_isset($bdWrite['data']['isSecret']) == 'y') echo 'checked="checked"';
                            echo '/> <label for="w_isSecret">비밀글</label>';
                            break;
                        }
                    }
                    ?>

                    <textarea name="contents" style="width:100%; height:250px;" id="editor"
                              label="내용"><?=gd_isset($bdWrite['data']['contents']); ?></textarea>
                </td>
            </tr>
            <tr name="trOnlyMemo">
                <td colspan="2">
                        <textarea name="memo" style="width:100%; height:250px;" class="form-control"
                                  label="내용"><?=gd_isset($bdWrite['data']['contents']); ?></textarea>

                </td>
            </tr>
        </table>
        <div class="text-center">
            <button type="submit" class="btn btn-red btn-lg">저장</button>
        </div>
    </form>
</div>
<?php
if ($bdWrite['cfg']['bdMemoFl'] == 'y') {
?>
<div class="memo">
    <form id="frmMemo" name="frmMemo" action="memo_ps.php" method="post">
        <input type="hidden" name="bdId" value="<?=$bdWrite['cfg']['bdId'] ?>"/>
        <input type="hidden" name="bdParentSno" value="<?=$bdParent['data']['sno'] ?>"/>
        <input type="hidden" name="sno"/>
        <input type="hidden" name="mode"/>
        <input type="hidden" name="memo"/>
    </form>
    <?php
    }
    ?>
</div>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
