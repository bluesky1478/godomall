<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?>
    </h3>
</div>
<iframe src="../plusshop-admin/panel" width="100%" id="iframeApp"   height='100%' marginwidth='0' marginheight='0' frameborder='no' scrolling='no' ></iframe>
<script>
    jQuery(document).ready(function() {
        var idx = 0;
        // Try to change the iframe size every 2 seconds
        var refreshIntervalId  = setInterval(resize, 1000);
        function resize()
        {
            if(idx > 10){
                clearInterval(refreshIntervalId);
            }
            layer_resize_in_iframe(document.getElementById("iframeApp"));
            idx++;
        }
    });
</script>
