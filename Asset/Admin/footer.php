<?php
/**
 * 관리자 하단
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 * @author    Shin Donggyu <artherot@godo.co.kr>
 */
if (isset($layoutLayerNotice)) {
    include($layoutLayerNotice);
}
if (isset($layoutLayerManager)) {
    include($layoutLayerManager);
}
if (isset($layoutLegalRequirements)) {
    include($layoutLegalRequirements);
}
?>

<div class="footer">
    <div class="copyright">
        &copy; NHN <a href="https://www.godo.co.kr" target="_blank">godo
            <span>:</span>
        </a> Corp All Rights Reserved. (ver : <span class="version"><?= Globals::get('gLicense.version'); ?></span>)
    </div>
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        <?= gd_isset($menuAccessAuth); ?>
        <?= gd_isset($functionAuth); ?>

        $.ajax('../base/main_setting_ps.php', {
            method: "post",
            data: {mode: 'orderPresentationNew'},
            global_complete: false,
            beforeSend: function () {
                $('.js-oder-count-new').addClass('display-none')
            },
            success: function () {
                if (arguments[0].success === 'OK') {
                    var result = arguments[0].result;
                    if (result === true) {
                        $('.js-oder-count-new').removeClass('display-none');
                    }
                }
            }
        });
    });
    //-->
</script>
