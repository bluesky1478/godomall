<form id="frmCreate" name="frmCreate" action="select_sales_system_ps.php" method="post">
    <input type="hidden" name="mode" value="saveProcess"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>통계수집방식</th>
            <td>
                <div class="radio">
                    <label title="주기적 데이터 수집" class="radio-inline">
                        <input type="radio" name="salesProcess" value="periodic" <?= gd_isset($checked['periodic']); ?> />
                        주기적 데이터 수집 (웹 스케줄러 방식) <span class="text-blue"><?= gd_isset($tuningInfo)?></span>
                    </label>
                </div>
                <div class="radio">
                    <label title="실시간 데이터 수집" class="radio-inline <?=gd_isset($realTimeDisabled['textColor'])?>">
                        <input type="radio" name="salesProcess" value="realTime" <?= gd_isset($checked['realTime']); ?> <?= gd_isset($realTimeDisabled['radioBox']); ?> />
                        실시간 데이터 수집 (프로시저 방식)
                    </label>
                </div>
            </td>
        </tr>
    </table>
    <div class="notice-info">
        통계 파일 튜닝 상점은 실시간 데이터 수집 (프로시저 방식)을 사용하는 경우, 데이터 수집이 정상 작동되지 않아 주기적 데이터 수집 (웹 스케줄러 방식) 으로만 이용 가능합니다.
    </div>
    <div class="notice-danger mgt10">
        튜닝으로 인한 통계 데이터 유실 시 복구가 불가하며, 튜닝으로 인한 데이터 유실은 전적으로 이용자에게 책임이 있습니다.
    </div>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="button" class="btn btn-lg btn-black select-sales-ps">확인</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.select-sales-ps').click(function () {
            BootstrapDialog.show({
                title: '<h2>확인</h2>',
                message: '통계 수집 방식 변경 시 매출 분석에 대한 수집 방식이 <span class=\'text-orange-red\'>일괄 변경</span>됩니다.<br/>변경 하시겠습니까?',
                buttons: [{
                        id: 'btn-close',
                        label: '취소',
                        action: function(){
                            layer_close();
                        }
                    },
                    {
                        id: 'btn-cancel',
                        label: '확인',
                        cssClass: 'btn-warning',
                        action: function() {
                            $.post('../statistics/select_sales_system_ps.php', $('#frmCreate').serializeArray(), function (data) {
                                if (data.result === 'fail') {
                                    BootstrapDialog.show({
                                        title: '경고',
                                        message: data.message,
                                        buttons: [{
                                            label: '확인',
                                            cssClass: 'btn-black',
                                            action: function() {
                                                layer_close();
                                            }
                                        }]
                                    });
                                    return false;
                                } else {
                                    BootstrapDialog.show({
                                        title: '확인',
                                        message: data.message,
                                        onshown: function (dialog) {
                                            setTimeout(function () {
                                                layer_close();
                                            }, 2000);
                                        },
                                    });
                                    return true;
                                }
                            });
                        }
                }]
            });
        });

        // 툴팁 데이터
        var tooltipData = <?php echo $tooltipData;?>;
        $('#frmCreate .table.table-cols th').each(function(idx){
            var titleName = $(this).text().trim().replace(/ /gi, '').replace(/\n/gi, '');
            for (var i in tooltipData) {
                if (tooltipData[i].attribute == titleName) {
                    $(this).append('<button type="button" class="btn btn-xs js-layer-tooltip" title="' + tooltipData[i].content + '" data-placement="right" data-width="' + tooltipData[i].cntWidth + '" onclick="tooltip(this)"><span title="" class="icon-tooltip"></span></button>');
                }
            }
        });

        // 툴팁 제거 이벤트
        $(document).on('click', '.tooltip.in .tooltip-close', function () {
            $('.js-layer-tooltip[aria-describedby=' + $(this).parent().attr('id') + ']').trigger('click');
        });

        $('button.close').click(function(){
            $('.tooltip.in .tooltip-close').trigger('click');
        });

        $('.js-layer-close').click(function(){
            $('.tooltip.in .tooltip-close').trigger('click');
        });
    });

    function tooltip(e) {
        if ($(e).attr('aria-describedby')) {
            $(e).tooltip('destroy');
        } else {
            var option = {
                trigger: 'click',
                container: '#content',
                html: true,
                template: '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div><button class="tooltip-close">close</button></div>',
            };
            $(e).on('shown.bs.tooltip', function () {
                $(".tooltip.in").css({
                    width: $(this).data('width'),
                    maxWidth: "none",
                });
            });
            $(e).tooltip(option).tooltip('show');
        }
    }
    //-->
</script>
