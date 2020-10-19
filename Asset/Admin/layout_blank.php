<?php
/**
 * 컨텐츠전용 레이아웃
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
?>
<?php include UserFilePath::adminSkin('head.php');?>
<body class="<?php echo $adminBodyClass; ?> layout-blank menu-no-border">

<div id="content" class="row">
    <div class="col-xs-12">
        <?php include($layoutContent);?>

        <?php include($layoutHelp); ?>
    </div>
</div>

<div id="panel_popupPanel"></div>

<iframe name="ifrmProcess" src="/blank.php" width="100%" height="200" class="<?=App::isDevelopment() === true ? 'display-block' : 'display-none'?>"></iframe>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        adminPanelApiAjax('<?php echo $manualData['menuCode'];?>', '<?php echo $manualData['menuKey'];?>', '<?php echo $manualData['menuFile'];?>');
        <?= gd_isset($menuAccessAuth); ?>
        <?= $functionAuth; ?>
    });
    //-->
</script>
</body>
</html>
