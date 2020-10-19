<?php
// 내용  설정
$currentSkinInfo = '';
if (is_array($mallData) === true) {
    $currentSkinInfo .= '<span class="design-mall-info"><span class="flag flag-16 flag-' . $mallData['domainFl'] . '"></span> ' . $mallData['mallName'] . '</span>';
}
$currentSkinInfo .= '작업중인 스킨은 <strong>' . $skinWorkName . ' (' .$currentWorkSkin.')</strong> 스킨입니다.';
$currentSkinInfo .= ' <a href="design_skin_list.php" target="_blank" class="btn btn-gray btn-sm">작업스킨 변경</a>';
?>
<div class="mgb10"><?php echo $currentSkinInfo;?></div>
