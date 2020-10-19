<div class="table-title position-relative">
    <?php include($layoutTitleBar); ?>
</div>
<div class="form-inline" style="padding:20px 20px 0px; background:#E8E8E8; border-top: 2px solid #5b5b5b;">
    최근 저장 내역(최대 10개) 보기
    <select id="slt_history" class="form-control input-sm">
<?php
    if (empty($designHistory) === false) {
        foreach($designHistory as $keyFile => $fVal) {
            echo '<option value="'.$fVal['path'].'">'.$fVal['date'].' 저장내용</option>';
        }
    }
    else {
            echo '<option value="">최근 저장내용이 없습니다.</option>';
    }
?>
    </select>
    <button type="button" onclick="get_design_history();" class="btn btn-gray btn-sm">저장내용 확인</button>
</div>

<!-- design editor tool : start -->
<?php
if (gd_isset($popupMode) === 'yes') {
    $editorLine = 30;
} else {
    $editorLine = 40;
}
?>

<script>DCTM.write('content', '100%', '<?php echo $editorLine;?>', ' required label="HTML 소스"', '<?php echo '/' . $getPageID;?>', '<?php echo $skinType;?>');</script>
<!-- design editor tool : end -->
