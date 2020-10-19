<?php
/**
 * iframeProcess 포함되지 않은 layout
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
?>
<?php include UserFilePath::adminSkin('head.php');?>
<body class="<?php echo $adminBodyClass; ?> layout-no-iframe menu-no-border">
<?php include($layoutContent);?>

<?php include($layoutHelp); ?>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        <?= gd_isset($menuAccessAuth); ?>
        <?= gd_isset($functionAuth); ?>
    });
    //-->
</script>
</body>
</html>
