<form id="frmSms080Reject" name="frmSms080Reject" method="post" action="sms080_ps.php">
    <input type="hidden" name="mode" value="savePolicy"/>
    <input type="hidden" name="status" value="<?= gd_isset($policy['status'], '') ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <div class="btn-group">
            <input type="submit" value="설정 저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        080 수신거부 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>사용여부</th>
            <td class="form-inline">
                <?php if ($policy['status'] == '') { ?>
                    <div><a href="https://www.godo.co.kr/echost/power/add/convenience/sms-refusal-intro.gd" target="_blank"><b>[080 수신거부 사용신청]</b></a>을 먼저 해주시기 바랍니다.</div>
                <?php } elseif ($policy['status'] == 'A') { ?>
                    <div>
                        신규등록 (등록일 : <?= $policy['date'] ?>)
                        <div class="mgt10 notice-danger">080 수신거부 사용을 위해 고도몰 홈페이지 > 마이고도 > 쇼핑몰관리에서 결제하여 주시기 바랍니다.</div>
                    </div>
                <?php } elseif ($policy['status'] == 'P') { ?>
                    <div>결제완료 (결제일 : <?= $policy['date'] ?>)
                        <div class="mgt10 notice-danger">결제가 완료되었으며, 개통대기 중입니다.</div>
                    </div>
                <?php } elseif ($policy['status'] == 'O' || $policy['status'] == 'T') { ?>
                    <div>
                        <label class="radio-inline">
                            <input type="radio" name="use" value="y" <?= $checked['use']['y'] ?> <?= $policy['status'] == 'T' ? 'disabled' : '' ?>/>
                            사용
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="use" value="n" <?= $checked['use']['n'] ?> <?= $policy['status'] == 'T' ? 'disabled' : '' ?>/>
                            사용안함
                        </label>
                    </div>
                <?php } ?>
            </td>
        </tr>
        <?php if ($policy['status'] == 'O' || $policy['status'] == 'T') { ?>
            <tr>
                <th>수신거부 번호</th>
                <td class="form-inline">
                    <label>
                        <input type="text" name="rejectNumber" disabled class="form-control width-md" value="<?= $policy['rejectNumber'] ?>">
                    </label>
                </td>
            </tr>
            <tr>
                <th>신청정보</th>
                <td class="form-inline">
                    <?php if ($policy['status'] == 'O') { ?>
                        <div>개통완료(개통일 : <?= $policy['date'] ?>)
                            <div class="notice-danger"><b>080 수신거부의 사용기간이 만료되면 서비스가 해지되며 기존에 등록된 080 수신거부 번호가 모두 삭제됩니다.</b><br/>반드시 사용기간 만료 전에 서비스를 연장하여 주시기 바랍니다.</div>
                        </div>
                    <?php } elseif ($policy['status'] == 'T') { ?>
                        <div>해지상태(해지일 : <?= $policy['date'] ?>)
                            <div class="notice-danger">해지 전 SMS 자동발송 시 수신거부 번호를 삽입하였다면, 해당 문구를 제거하여 주시기 바랍니다.  <a href="../member/sms_auto.php" target="_blank" class="text-blue">회원 > 자동 SMS 설정</a></div>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 폼 체크
        $.validator.addMethod('status', function (val) {
            return val === 'O';

        }, "개통상태가 아닙니다.");
        $('#frmSms080Reject').validate({
            rules: {
                status: {
                    status: true
                }
            }, submitHandler: function () {
                $.ajax('../member/sms080_ps.php', {
                    data: {
                        mode: 'savePolicy',
                        use: $(':radio[name=use]:checked').val()
                    },
                    success: function () {
                        BootstrapDialog.closeAll();
                        alert(arguments[0]);
                    },
                    error: function () {
                        BootstrapDialog.closeAll();
                        alert(arguments[0]);
                    }
                });
            }
        });
    });
    //-->
</script>
