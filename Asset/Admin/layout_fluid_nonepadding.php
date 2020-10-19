<?php
/**
 * 메뉴가 없는 레이아웃 (컨텐츠 풀 사이즈)
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */

include UserFilePath::adminSkin('head.php');
?>
<body class="<?php echo $adminBodyClass; ?> layout-fluid menu-no-border breadcrumb-no-header">

<div id="container-wrap" class="container-fluid pd0">
    <div id="container" class="row">
        <div id="header" class="col-xs-12">
            <?php include($layoutHeader); ?>
            <div id="panel_popupPanel"></div>
        </div>
        <div id="content-wrap">
            <div class="container-fluid pd0">
                <div id="contents">
                    <div class="col-xs-12 pd0">
                        <?php include($layoutContent); ?>

                        <?php include($layoutHelp); ?>
                    </div>
                </div>
            </div>
        </div>
        <div id="footer" class="col-xs-12">
            <?php include($layoutFooter); ?>
        </div>
    </div>
</div>


<div id="panel_noticePanel"></div>

<iframe name="ifrmProcess" src="/blank.php" width="100%" height="200" class="<?=App::isDevelopment() === true ? 'display-block' : 'display-none'?>"></iframe>
<script type="text/javascript">
    $(function() {
        adminPanelApiAjax('<?php echo $manualData['menuCode'];?>', '<?php echo $manualData['menuKey'];?>', '<?php echo $manualData['menuFile'];?>');
    });
</script>
</body>
</html>
