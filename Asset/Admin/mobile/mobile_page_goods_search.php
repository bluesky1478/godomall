<script type="text/javascript">
<!--
$(document).ready(function(){
    $("#frmPageConfig").validate({
        submitHandler: function (form) {
            form.target = 'ifrmProcess';
            form.submit();
            return false;
        },
        rules: {
        },
        messages: {
        }
    });
});
//-->
</script>

<form id="frmPageConfig" name="frmPageConfig" action="./mobile_ps.php" method="post">
<input type="hidden" name="mode" value="mobile_page_config" />
<input type="hidden" name="pageCode" value="<?php echo $pageCode;?>" />
    <div class="phead_wrap mgt0"><div class="phead">
        <h2><?php echo end($naviMenu->location);?> <span>모바일샵 <?php echo end($naviMenu->location);?>의 출력 항목을 설정 합니다.</span></h2>
        <div class="scroll_save"><input type="submit" value="" class="save" /></div>
    </div></div>

    <div class="table-title gd-help-manual">
        모바일샵 <?php echo end($naviMenu->location);?> 출력 항목 설정
    </div>
    <div>
        <table class="table table-cols">
        <colgroup><col class="width-sm" /><col class="width-xl" /><col class="width-sm" /><col/></colgroup>
        <tr>
<?php
    foreach ($arrFields as $key => $val) {
        if ($key != 0 && $key % 2 == 0) echo '</tr><tr>';
?>
            <th><?php echo $val['titleNm'];?> 출력 <?php if (empty($val['desc']) === false) {?><span class="tip"><span><?php echo $val['desc'];?></span></span><?php }?></th>
            <td class="font-eng">
                <label><input type="radio" name="<?php echo $val['CodeNm'];?>" value="y" <?php echo gd_isset($checked[$val['CodeNm']]['y']);?>/>출력</label>
                <label><input type="radio" name="<?php echo $val['CodeNm'];?>" value="n" <?php echo gd_isset($checked[$val['CodeNm']]['n']);?>/>미출력</label>
            </td>
<?php
    }

    if (count($arrFields)%2 == 1)  {
?>
            <th></th>
            <td class="font-eng"></td>
<?php
    }
?>
        </tr>
        </table>
    </div>

    <div class="text-center">
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>
</form>
