<form id="frmGoodsInfo" name="frmGoodsInfo" action="goods_ps.php" method="post">
    <input type="hidden" name="mode" value="goods_info"/>
    <input type="hidden" name="informCd" value="<?php echo gd_isset($data['informCd']); ?>"/>
    <input type="hidden" name="modeFl" valye="y"/>
    <input type="hidden" name="scmFl" value="n"/>
    <input type="hidden" name="mallSno" value="<?php echo $mallSno; ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location) . ' (해외몰 전용)'; ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('<?=$adminList;?>');" />
            <input type="submit" value="저장" class="btn btn-red">
        </div>
    </div>

    <?php if ($mallCnt >= 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./goods_detail_info_global_register.php?mallSno=<?php echo $mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="table-title gd-help-manual">
        상품 상세 이용안내 내용
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="input_title r_space require">배송안내 제목</th>
            <td>
                <input type="text" name="informNm[002]" value="<?php echo $data['informNm']['002']?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require">배송안내 내용</th>
            <td>
                <textarea name="content[002]" rows="26" style="width:100%; height:400px;" id="editor002" class="form-control"><?php echo $data['content']['002']?></textarea>
            </td>
        </tr>
    </table>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="input_title r_space require">AS안내 제목</th>
            <td>
                <input type="text" name="informNm[003]" value="<?php echo $data['informNm']['003']?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require">AS안내 내용</th>
            <td>
                <textarea name="content[003]" rows="26" style="width:100%; height:400px;" id="editor003" class="form-control"><?php echo $data['content']['003']?></textarea>
            </td>
        </tr>
    </table>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="input_title r_space require">환불안내 제목</th>
            <td>
                <input type="text" name="informNm[004]" value="<?php echo $data['informNm']['004']?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require">환불안내 내용</th>
            <td>
                <textarea name="content[004]" rows="26" style="width:100%; height:400px;" id="editor004" class="form-control"><?php echo $data['content']['004']?></textarea>
            </td>
        </tr>
    </table>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th class="input_title r_space require">교환안내 제목</th>
            <td>
                <input type="text" name="informNm[005]" value="<?php echo $data['informNm']['005']?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="input_title r_space require">교환안내 내용</th>
            <td>
                <textarea name="content[005]" rows="26" style="width:100%; height:400px;" id="editor005" class="form-control"><?php echo $data['content']['005']?></textarea>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/HuskyEZCreator.js" charset="utf-8"></script>
<script type="text/javascript" src="<?= PATH_ADMIN_GD_SHARE ?>script/smart/js/editorLoad.js" charset="utf-8"></script>
<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $("#frmGoodsInfo").validate({
            submitHandler: function (form) {
                oEditors.getById["editor002"].exec("UPDATE_CONTENTS_FIELD", []);
                oEditors.getById["editor003"].exec("UPDATE_CONTENTS_FIELD", []);
                oEditors.getById["editor004"].exec("UPDATE_CONTENTS_FIELD", []);
                oEditors.getById["editor005"].exec("UPDATE_CONTENTS_FIELD", []);
                form.target = 'ifrmProcess';
                form.submit();
            }
        });
        editorLoad('editor002');
        editorLoad('editor003');
        editorLoad('editor004');
        editorLoad('editor005');
    });

    var editorLoad = function(editorId) {
        nhn.husky.EZCreator.createInIFrame({
            oAppRef: oEditors,
            elPlaceHolder: editorId,
            sSkinURI: "<?=PATH_ADMIN_GD_SHARE?>script/smart/SmartEditor2Skin.html",
            htParams: {
                bUseToolbar: true,
                bUseVerticalResizer: true,
                bUseModeChanger: true,
            },
            fOnAppLoad: function () {
            },
            fCreator: "createSEditor2"
        });
    }
    //-->
</script>
