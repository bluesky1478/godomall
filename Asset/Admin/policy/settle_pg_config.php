<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmPG').validate({
            submitHandler: function (form) {
                <?php if ($data['pgAutoSetting'] === 'y' || $data['pgApprovalSetting'] === 'y') { ?>
                form.target = 'ifrmProcess';
                form.submit();
                <?php } else {?>
                alert('\'고도 > 쇼핑몰 > 부가서비스 > 전자결제(PG)\'에서 신규 신청을 하시거나,<br/>\'개별 승인 신청\'을 통해 승인 완료가 되어야만 합니다.<br/>전자결제(PG) 설정에 실패 하였습니다.');
                return false;
                <?php }?>
            },
            rules: {
            },
            messages: {
            }
        });
    });

    /**
     * 출력 여부1
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr) {
        if (modeStr == 'show') {
            $('#' + thisID).show();
        } else if (modeStr == 'hide') {
            $('#' + thisID).hide();
        }
    }

    /**
     * 무이자 기간 설정
     *
     * @param string pgName PG ID
     */
    function noInterest_peroid_conf(pgName) {
        if ($('input[name=\'noInterestFl\']:checked').val() == 'n') {
            alert('무이자결제가 설정이 되어 있어야 기간 설정이 가능합니다.');
        } else {
            var loadChk = $('#layernoInterestForm').length;
            $.post('layer_pg_nointerest_peroid.php', {'pgName': pgName}, function (data) {
                if (loadChk == 0) {
                    data = '<div id="layernoInterestForm">' + data + '</div>';
                }
                var layerForm = data;
                layer_popup(layerForm, '무이자 할부 기간 설정');
            });
        }
    }

    /**
     * checked 되었다면 특정 클래스 실행
     *
     * @param string thisOb 해당 checkBox object
     */
    function checked_bold(thisOb) {
        if ($(thisOb).is(':checked') == true) {
            $(thisOb).next('span').attr('class', 'bold');
        } else {
            $(thisOb).next('span').attr('class', 'nobold');
        }
    }

    /**
     * PG 개별 승인 신청
     */
    function pg_prefix() {
        var pgName = $('input[name=\'pgName\']').val();

        var params = {
            mode: 'pgPrefix',
            pgType: pgName
        };
        $.post('./settle_ps.php', params, function (data) {
            $('#pgPrefix').removeClass('display-none');
            $('#pgIframe').attr('src', data);
        });
    }

    /**
     * 결제수단 자동 설정 - PG 중앙화에 따른
     *
     */
    function settleKindUpdate(){
        var pgAutoSetting = $('input[name=\'pgAutoSetting\']').val();
        if (pgAutoSetting != 'y') {
            alert('자동 설정된 PG사가 아닙니다.');
        } else {
            var pgName = $('input[name=\'pgName\']').val();
            var pgId = '<?php echo $data['pgId'];?>';
            var params = {
                mode: 'pgAutoUpdate',
                pgType: pgName,
                pgId: pgId
            };

            $.post('./settle_ps.php', params, function (data) {
                var resultVal = true;
                var resultMsg = '';
                if (data == '') {
                    resultVal = false;
                    resultMsg = '결과 없음';
                }
                if (resultVal == true) {
                    var resultData = $.parseJSON(data);
                    if (resultData.result == 'ok') {
                        alert('<?php echo $pgNm;?> 정보 갱신 완료 되었습니다. 잠시후 새로고침 됩니다.');
                        setTimeout(function() {
                            parent.location.reload();
                        }, 2000);
                    } else {
                        resultVal = false;
                        resultMsg = resultData.error_msg;
                    }
                }

                if (resultVal == false) {
                    alert('<?php echo $pgNm;?> 정보 갱신에 실패하였습니다. \n서비스 신청이 완료된 상태라면 고객센터로 문의하여 주세요. \n(' + resultMsg + ') ');
                }
            });
        }
    }
    //-->
</script>

<div id="pgPrefix" style="position:absolute; z-index:1000;" class="display-none">
    <iframe id="pgIframe" frameborder="0" width="554" height="480"></iframe>
</div>

<form id="frmPG" name="frmPG" action="settle_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="pg_config"/>
    <input type="hidden" name="pgApprovalId" value="<?php echo $pgApprovalId; ?>"/>
    <input type="hidden" name="pgName" value="<?php echo $data['pgCode']; ?>"/>
    <input type="hidden" name="pgAutoSetting" value="<?php echo $data['pgAutoSetting']; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>계약된 전자결제(PG)의 설정을 하실 수 있습니다.</small>
        </h3>
        <div class="btn-group">
            <input type="submit" value="PG 정보 저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="notice-danger">아래 전자결제(PG) 업체중 계약을 맺은 한곳만 클릭한 후 정보를 입력하세요</div>
    <ul class="nav nav-tabs nav-justified mgb30" role="tablist">
        <?php
        foreach ($gPg as $key => $val) {
            if ($key === $data['pgName']) {
                $classNm = 'active';
            } else {
                $classNm = '';
            }
            ?>
            <li role="presentation" class="<?= $classNm ?>">
                <a href="settle_pg_config.php?pgMode=<?= $key ?>" role="tab"><?= $val ?></a>
            </li>
        <?php } ?>
    </ul>

    <?php include($layoutPgContent); ?>
</form>
