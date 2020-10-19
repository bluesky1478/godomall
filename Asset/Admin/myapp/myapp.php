<?php
if (!isset($menu)) {
    if ($myappConnectBrowser == 'Safari') { // 사파리 크로스 사이트 차단 처리
        echo "
            <script>
                var tmp_window = window.open(\"$adminPageUrl\", \"tmp\", \"width=10, height=10, left=-100, top=-50\");
                $(function() {
                    $('#introduce').load(function () {
                        tmp_window.close()
                    });
                });
            </script>
        ";
    }
    ?>
    <iframe id="introduce" name='introduce' src='<?=$adminPageUrl?>' frameborder='0' marginwidth='0' marginheight='0' style="width:100<?=$myappIframeWidthUnit?>; height: 100<?=$myappIframeHeightUnit?>"></iframe>
<?php }
else { ?>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location);?> <small></small></h3>
    </div>

    <iframe name='introduce' src='/share/iframe_godo_page.php?menu=<?=$menu?>' frameborder='0' marginwidth='0' marginheight='0' width='100%' height='2100'></iframe>
<?php } ?>