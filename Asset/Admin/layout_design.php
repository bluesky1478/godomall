<?php
/**
 * 디자인 관리용 레이아웃
 * @author Shin Donggyu <artherot@godo.co.kr>
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
?>
<?php include UserFilePath::adminSkin('head.php');?>
<body class="<?php echo $adminBodyClass; ?> layout-design">

<div id="container-wrap" class="container-fluid">
	<div id="container" class="row">
		<div id="header" class="col-xs-12">
			<?php include($layoutHeader); ?>
            <div id="panel_popupPanel"></div>
        </div>
        <div id="content-wrap">
            <div id="menu">
				<?php include($layoutMenu); ?>
            </div>
            <div id="content" class="row">
				<!-- 상단 타이틀 바 -->
				<?php include($layoutTitleBar);?>
				<!-- //상단 타이틀 바 -->

                <div class="col-xs-12">
					<?php include($layoutContent); ?>

					<?php include($layoutHelp); ?>
                </div>
            </div>
        </div>
        <div id="footer" class="col-xs-12">
			<?php include($layoutFooter); ?>
        </div>
    </div>
</div>


<iframe name="ifrmProcess" src="/blank.php" width="100%" height="200" class="<?=App::isDevelopment() === true ? 'display-block' : 'display-none'?>"></iframe>
<script type="text/javascript">
	$(function() {
        adminPanelApiAjax('<?php echo $manualData['menuCode'];?>', '<?php echo $manualData['menuKey'];?>', '<?php echo $manualData['menuFile'];?>');
    });
</script>
</body>
</html>
