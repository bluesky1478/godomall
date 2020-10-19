<?php
/**
 * 레이어용 레이아웃
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
?>
<div id="layer-wrap">
	<?php include($layoutContent);?>
</div>

<?php if (empty($menuAccessAuth) === false) {?>
    <script type="text/javascript">
        $(document).ready(function () {
            <?= gd_isset($menuAccessAuth); ?>
        });
    </script>
<?php }?>

<?php if (empty($tooltipData) === false) {?>
<script type="text/javascript">
    $(document).ready(function () {
        // 툴팁 데이터
        var tooltipData = <?php echo $tooltipData;?>;
        var sectionEle = null;
        $('#layer-wrap .table.table-rows thead th').each(function(idx){
            if ($(this).closest('table').siblings('.table-title').length) {
                sectionEle = $(this).closest('table').prevAll('.table-title:first');
            } else if ($(this).closest('table').parent('div').siblings('.table-title').length) {
                sectionEle = $(this).closest('table').parent('div').prevAll('.table-title:first');
            } else {
                // 주문상세는 이곳을 탐
                sectionEle = $(this).closest('table').parent('div').parent('div').parent('div').parent('div').parent('div').prevAll('.table-title:first');
            }

            if (typeof sectionEle[0] !== "undefined") {
                var sectionTitle = $(sectionEle[0]).find('span:eq(0)').html().replace(/\(?<\/?[^*]+>/gi, '').trim().replace(/ /gi, '').replace(/\n/gi, '');
                var titleName = $(this).text().trim().replace(/ /gi, '').replace(/\n/gi, '');
                for (var i in tooltipData) {
                    if (tooltipData[i].title == sectionTitle) {
                        if (tooltipData[i].attribute == titleName) {
                            $(this).append('<button type="button" class="btn btn-xs js-tooltip" title="' + tooltipData[i].content + '" data-placement="right" data-width="' + tooltipData[i].cntWidth + '"><span class="icon-tooltip"></span></button>');
                        }
                    }
                }
            }
        });

        if ($('.js-tooltip').length > 0) {
            // 툴팁 초기화
            $(document).on({
                'click': function() {
                    if ($(this).attr('aria-describedby')) {
                        $(this).tooltip('destroy');
                    } else {
                        var option = {
                            trigger: 'click',
                            container: '#content',
                            html: true,
                            template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div><button class="tooltip-close">close</button></div>',
                        };
                        $(this).on('shown.bs.tooltip', function () {
                            $(".tooltip.in").css({
                                width: $(this).data('width'),
                                maxWidth: "none",
                            });
                        });
                        $(this).tooltip(option).tooltip('show');
                    }
                }
            }, '.js-tooltip');
            $(document).on('click', '.tooltip.in .tooltip-close', function () {
                $('.js-tooltip[aria-describedby=' + $(this).parent().attr('id') + ']').trigger('click');
            });
        }

        // 레이어 생성/제거시에 툴팁 제거
        $(document).on('shown.bs.modal hidden.bs.modal', '.modal', function () {
            if ($('.tooltip.in').length) {
                $('.tooltip.in').tooltip('destroy');
            }
        });
    });
</script>
<?php }?>
