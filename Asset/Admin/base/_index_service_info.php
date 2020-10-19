<div class="row main-layout main-header reform mgt-33">
    <div class="wrap">
        <div class="layout-left">
            <h1>
                <a href="../policy/base_info.php"><?= $svcInfos['mallNm']; ?></a> <a href="../policy/base_info.php">
                    <small><?= $svcInfos['mallDomain']; ?></small>
                </a>
            </h1>
        </div>
        <?php if (gd_is_provider() === false) { ?>
            <div class="layout-right text-right">
                <ul class="mall-info-cnt list-inline">
                    <li class="disk-space">
                        <span class="disk-space-check disk-storage"></span>
                        <button type="button" class="btn btn-gray btn-xs btn_react_disk">갱신</button>
                        <button type="button" onclick="gotoGodomall('disk');" class="btn btn-gray btn-xs">추가</button>
                    </li>
                    <li class="sms-cnt pdlr30">
                        <strong>SMS</strong>
                        <strong class="text-danger"><?= number_format($smsPoint, 1); ?> 포인트</strong>
                        <a href="../member/sms_charge.php" class="btn btn-gray btn-xs">충전</a>
                    </li>
                </ul>
            </div>
        <?php } ?>
    </div>
</div>
<?php if (gd_is_provider() === false) { ?>
    <script type="text/javascript">
        $(document).ready(function () {
            // 계정용량
            $('.disk-space-check').disk_space();

            $('.btn_react_disk').click(function () {
                $('.disk-space-check').disk_space('react');
            }).css('cursor', 'pointer');
        });

        /*
         * 계정용량
         */
        $.fn.disk_space = function (mode) {
            var obj = $(this);
            obj.html('<img src="<?php echo PATH_ADMIN_GD_SHARE;?>img/icon_loading2.gif" alt="로딩중" width="34" class="middle" />');
            $.post('<?php echo URI_ADMIN;?>base/disk_space.php', {'mode': mode}, function (data) {
                if (data == '') {
                    obj.empty();
                    return;
                }
                var diskData = data;
                var diskHtml = [];
                if (diskData.supplyDisk == '') {
                    diskHtml.push('<strong class="pdr15">' + diskData.diskTitle + '</strong><span class="si_disk"><strong class="text-danger">' + diskData.usedDisk + '</strong></span>');
                } else if (typeof diskData.usedPer !== 'undefined') {
                    diskHtml.push('<span class="storage-title">' + diskData.diskTitle + '</span>');
                    diskHtml.push('<span class="disk-progressbar"><span class="disk-guage" style="width:' + diskData.usedPer + '%;"></span></span>');
                    diskHtml.push('<span class="storage-val"><span class="c-gdred"><b>' + diskData.usedDisk + ' </b> </span> <span class="bar"> / </span> <span>' + diskData.supplyDisk + '</span></span>');
                    if (diskData.usedPer == '100') {
                        var message = '쇼핑몰 제공용량이 초과되었습니다. <span class="c-gdred">초과 상태로 3일 경과 시, 전체 관리자 로그인이 차단</span>됩니다.<br /><br />- 초과시점 : <span class="c-gdred">' + diskData.fullDate + '</span><br />- 잔여용량 확보 방법 안내<br />1. 불필요한 용량 정리<br />2. 디스크 용량 추가';
                        var btnText = {};
                        btnText.cancelLabel = '자세히보기';
                        btnText.confirmLabel = '확인';
                        dialog_confirm(message, function(result) {
                            if (result) {
                                layer_close();
                            } else {
                                window.open('https://www.godo.co.kr/customer/faq-list.gd?refcd=52#753', '_blank');
                            }
                        }, '경고', btnText);
                    }
                }
                obj.html(diskHtml.join(''));
            });
        };
    </script>
<?php } ?>
