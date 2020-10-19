<form id="frmFreeSsl" name="frmFreeSsl" action="ssl_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="checkFreeSsl"/>
</form>
<form id="frmSslSetting" name="frmSslSetting" action="ssl_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="insertSslConfig"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>
    <table class="table table-cols">
        <thead>
        <tr>
            <th class="width-md">보안서버 구분</th>
            <th class="width-md">도메인 정보</th>
            <th class="width-md">연결 상점</th>
            <th class="width-md">사용여부</th>
            <th class="width-md">서비스 상태</th>
            <th class="width-md">상세설정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($position == 'pc') {
        ?>
        <tr class="center">
            <th class="width-md">무료 보안서버<br/><button type="button" id="requestFreeSsl" class="btn btn-sm btn-gray" style="display: inline-block;">무료SSL설치요청</button></th>
            <th class="width-md"><?= $sslData['free']['domain']; ?></th>
            <td class="width-md"><?= $sslData['free']['mall']['mallName']; ?></td>
            <td class="width-md"><?= $sslData['free']['use']['useString']; ?></td>
            <td class="width-md"><?= $sslData['free']['status']['statusString']; ?></td>
            <td class="width-md">
                <button type="button" class="btn btn-white btn-sm js-setting" data-domain="<?= $sslData['free']['domain']; ?>" data-no="<?= $sslData['free']['ssl']['sslConfigNo']; ?>" data-type="free">설정</button>
            </td>
        </tr>
        <?php
        }
        $payCount = count($sslData['godo']['forward']);
        ?>
        <tr class="center">
            <th class="width-md" rowspan="<?= $payCount + 1; ?>">유료 보안서버<br/><button type="button" id="requestPaySsl" class="btn btn-sm btn-gray" style="display: inline-block;">유료SSL설치요청</button></th>
            <?php
            if ($sslData['godo']['shop']['domain']) {
                ?>
                <th class="width-md">[대표]<?= $sslData['godo']['shop']['domain']; ?></th>
                <td class="width-md"><?= $sslData['godo']['shop']['mall']['mallName']; ?></td>
                <td class="width-md"><?= $sslData['godo']['shop']['use']['useString']; ?></td>
                <td class="width-md"><?= $sslData['godo']['shop']['status']['statusString']; ?></td>
                <td class="width-md">
                    <button type="button" class="btn btn-white btn-sm js-setting" data-domain="<?= $sslData['godo']['shop']['domain']; ?>" data-no="<?= $sslData['godo']['shop']['ssl']['sslConfigNo']; ?>" data-type="godo">설정
                    </button>
                </td>
                <?php
            } else {
                ?>
                <th class="width-md" colspan="5">연결된 도메인이 없습니다.</th>
                <?php
            }
            ?>
        </tr>
        <?php
        if ($payCount > 0) {
            foreach ($sslData['godo']['forward'] as $key => $val) {
            ?>
            <tr class="center">
                <th class="width-md"><?= $val['domain']; ?></th>
                <td class="width-md"><?= $val['mall']['mallName']; ?></td>
                <td class="width-md"><?= $val['use']['useString']; ?></td>
                <td class="width-md"><?= $val['status']['statusString']; ?></td>
                <td class="width-md">
                    <button type="button" class="btn btn-white btn-sm js-setting" data-domain="<?= $val['domain']; ?>" data-no="<?= $val['ssl']['sslConfigNo']; ?>" data-type="godo">설정</button>
                </td>
            </tr>
            <?php
            }
        }
        ?>
        </tbody>
    </table>
</form>
<?php
foreach ($infoMsg as $key => $val) {
    foreach ($val as $style => $text) {
        echo '<div class="' . $style . '">' . $text . '</div>';
    }
}
?>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        let doubleSubmitFlag = false;
        function doubleSubmitCheck() {
            if (doubleSubmitFlag) {
                return doubleSubmitFlag;
            } else {
                doubleSubmitFlag = true;
                return false;
            }
        }
        $('#requestFreeSsl').click(function () {
            document.frmFreeSsl.submit();
        });
        $('#requestPaySsl').click(function () {
            window.open('https://hosting.godo.co.kr/valueadd/ssl_service.php?iframe=yes', '_blank');
            return false;
        });
        $('.js-setting').click(function (e) {
            e.preventDefault();
            if ($(this).data('type') === 'free') {
                if ("<?= $sslData['free']['status']['status']; ?>" !== 'used') {
                    $(this).data('no', '');
                }
            }
            if ($(this).data('no') > 0) {
                if(doubleSubmitCheck()) return;
                params = {
                    position: '<?= $position; ?>',
                    domain: $(this).data('domain'),
                    configNo: $(this).data('no'),
                    type: $(this).data('type'),
                };
                $.get('../policy/layer_ssl_setting.php', params, function (data) {
                    BootstrapDialog.show({
                        size: BootstrapDialog.SIZE_WIDE,
                        title: '보안서버 설정',
                        message: $(data),
                        closable: true,
                    });
                    doubleSubmitFlag = false;
                });
            } else {
                if ($(this).data('type') === 'free') {
                    alert('무료 보안서버가 설치완료 된 후 설정할 수 있습니다.');
                } else {
                    alert('보안서버를 구매하신 후 설정할 수 있습니다.');
                }
            }
            return false;
        });
    });
    //-->
</script>