<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
?>
<form id="formSetup" name="formSetup" action="../policy/base_guide_info_ps.php" method="post">
    <input type="hidden" name="mode" value="<?= $mode; ?>"/>
    <input type="hidden" name="mallSno" value="<?= $mallSno; ?>"/>
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input id="btnSubmit" type="submit" value="저장" class="btn btn-red"/>
    </div>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($mallList as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-mall-sno="<?php echo $mall['sno']?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./base_guide_info.php?mallSno=<?php echo $mall['sno']; ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="table-title gd-help-manual">
        이용안내
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>내용입력</th>
            <td>
                <textarea id="baseGuideContent" name="baseGuideContent" rows="12" class="form-control"><?= $baseGuideContent; ?></textarea>
                <p class="notice-info mgt5">
                    쇼핑몰명은 치환코드{rc_mallNm}로 제공되며, 입력 시 기본정보 설정에 등록된 “쇼핑몰명”이 자동으로 표시됩니다.<br/>
                    등록한 내용은 쇼핑몰 하단의 [이용안내] 화면에 표시됩니다.
                </p>
            </td>
        </tr>
        </tbody>
    </table>


    <div class="table-title gd-help-manual">
        탈퇴안내
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>내용입력</th>
            <td>
                <textarea id="hackOutGuideContent" name="hackOutGuideContent" rows="12" class="form-control"><?= $hackOutGuideContent; ?></textarea>
                <p class="notice-info mgt5">
                    쇼핑몰명은 치환코드{rc_mallNm}로 제공되며, 입력 시 기본정보 설정에 등록된 “쇼핑몰명”이 자동으로 표시됩니다.<br/>
                    등록한 내용은 쇼핑몰 하단의 [이용안내] 화면에 표시됩니다.
                </p>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#btnSubmit').click(function () {
            $('#formSetup').validate({
                submitHandler: function (form) {
                    var params = $(form).serializeArray();
                    post_with_reload('../policy/base_guide_info_ps.php', params);
                }
            });
        });
    });
    //-->
</script>
