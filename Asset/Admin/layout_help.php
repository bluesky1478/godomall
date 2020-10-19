<?php
/**
 * 하단 안내 화면
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
$widthClass = '';
if (empty($helpData) === false) {
    if(gd_php_self() == '/design/design_skin_list.php' || gd_php_self() == '/mobile/design_skin_list.php' ) $widthClass = 'design-list-width';
?>
<div class="information <?=$widthClass?>">
    <h4>안내</h4>
    <div class="content">
        <?php echo $helpData[0]['content'];?>
    </div>
</div>
<?php
}
?>
