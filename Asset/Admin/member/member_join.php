<form id="frmSetup" action="./member_ps.php" method="post">
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>
    <div class="table-title gd-help-manual">
        가입 설정
    </div>
    <table class="table table-cols mgb15">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>가입승인 사용설정</th>
            <td>
                <div class="radio mgt0">
                    <label>
                        <input type="radio" name="appUseFl" value="n" <?= $checked['appUseFl']['n']; ?>>
                        승인 절차 없이 가입
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="appUseFl" value="y" <?= $checked['appUseFl']['y']; ?>>
                        승인 후 가입
                    </label>
                </div>
                <div class="radio mgb0">
                    <label>
                        <input type="radio" name="appUseFl" value="company" <?= $checked['appUseFl']['company']; ?>>
                        사업자회원만 승인 후 가입
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>가입연령제한 설정</th>
            <td>
                <div class="radio">
                    <label>
                        <input type="radio" name="under14Fl" value="n" <?= $checked['under14Fl']['n']; ?>>
                        제한 안함
                    </label>
                </div>
                <div class="checkbox pdl15">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="under14ConsentFl" name="under14ConsentFl" value="y" <?= $checked['under14ConsentFl']['y'];?>>
                        '만 14세 이상입니다.' 동의 항목 사용 <span class="notice-info">가입연령제한 설정 '제한 안함'사용 시 해당 설정 사용을 권장합니다.</span>
                    </label>
                </div>
                <dl class="dl-horizontal">
                    <dt class="form-inline" style="width: 220px;">
                        만 <select name="limitAge" class="form-control" style="width:120px">
                            <option value="14" <?php if ($data['limitAge'] == 14) echo 'selected' ?>>14</option>
                            <option value="19" <?php if ($data['limitAge'] == 19) echo 'selected' ?>>19</option>
                        </select> 미만인 경우
                    </dt>
                    <dd style="margin-left: 230px;">
                        <div class="radio mgt0">
                            <label>
                                <input type="radio" name="under14Fl" value="y" <?= $checked['under14Fl']['y']; ?>>
                                운영자 승인 후 가입&nbsp;
                                <a href="./member_ps.php?mode=under14Download" class="btn btn-gray btn-sm" id="btnUnder14Download">법정대리인 동의서 샘플 다운로드</a>
                            </label>
                        </div>
                        <div class="radio mgb0" style="margin-top: -6px;">
                            <label>
                                <input type="radio" name="under14Fl" value="no" <?= $checked['under14Fl']['no']; ?>>
                                가입불가
                            </label>
                        </div>
                    </dd>
                </dl>
            </td>
        </tr>
    </table>

    <div class="notice-danger notice-sm mgl15">정보통신망법에 따라 만 14세 미만의 아동은 법정대리인의 동의 확인 후 회원가입이 가능합니다.<a class="btn-link pdl5" style="cursor:pointer;" onclick="lawAlert();">[자세히보기]</a></div>
    <div class="notice-info notice-sm mgl15">
        ‘운영자 승인 후 가입’ 및 ‘가입불가’로 설정 시 본인인증서비스를 사용하거나 회원가입 항목의 ‘생일’항목을 필수로 설정해야 합니다.
        본인인증서비스 또는 생일 필수 설정이 없는 경우, 만 14세 미만 회원을 판단할 수 없으므로 ‘미승인’상태로 가입되거나, 가입이 불가하오니 주의해주시기 바랍니다.
    </div>
    <div class="notice-info notice-sm mgl15 mgb15">
        본인인증서비스: <a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link-underline">휴대폰인증 설정></a>&nbsp;<a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link-underline">아이핀인증 설정></a><br>
        생일 항목 사용 및 필수 설정: <a href="../member/member_joinitem.php" target="_blank" class="btn-link-underline">회원 가입 항목 관리></a>
    </div>
    <div class="linepd30"></div>

    <div class="table-title gd-help-manual">
        간편 로그인 기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col class=""/>
            <col/>
        </colgroup>
        <tr>
            <th>본인인증 제외설정</th>
            <td>
                <dl class="dl-horizontal">
                    <dt class="form-inline" style="width: 250px;">
                        간편 로그인으로 회원가입 시 본인인증 절차
                    </dt>
                    <dd style="margin-left: 250px;">
                        <div class="radio mgt0">
                            <label>
                                <input type="radio" name="snsMemberAuthFl" value="y" <?= $checked['snsMemberAuthFl']['y']; ?>>
                                사용함
                            </label>
                        </div>
                        <div class="radio mgb0" style="margin-top: -6px;">
                            <label>
                                <input type="radio" name="snsMemberAuthFl" value="n" <?= $checked['snsMemberAuthFl']['n']; ?>>
                                제외함
                            </label>
                        </div>
                    </dd>
                </dl>
            </td>
        </tr>
    </table>
    <div class="notice-info notice-sm mgl15">
        ‘가입연령제한 설정’이 ‘운영자 승인 후 가입’ 및 ‘가입불가’ 이고 가입 항목 중 ‘생일’이 필수가 아닌 경우, 본인확인인증서비스가 필수이므로 ‘제외함’설정이 불가합니다.
        <br />상단의 가입연령제한 설정 및 “<a href="/member/member_joinitem.php" class="btn-link-underline" target="_blank">회원>회원 관리>회원 가입 항목 관리</a>”의 설정을 확인해주시기 바랍니다.
    </div>
    <div class="notice-info notice-sm mgl15">
        원더 아이디 로그인의 경우, ‘사용함‘으로 설정하여도 본인인증 서비스가 실행되지 않습니다.
    </div>
    <div class="notice-info notice-sm mgl15 mgb15">
        본인확인 인증 서비스(휴대폰인증/아이핀인증)가 적용되어 있어야만 ‘사용함’ 설정 시 본인인증 서비스가 실행됩니다.  <a href="/policy/member_auth_cellphone.php" class="btn-link-underline" target="_blank">휴대폰인증 설정></a>&nbsp;<a href="/policy/member_auth_ipin.php" class="btn-link-underline" target="_blank">아이핀인증 설정></a>
    </div>
    <div class="linepd30"></div>

    <div class="table-title gd-help-manual">
        탈퇴/재가입 설정
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>재가입 기간제한</th>
            <td>
                <div class="radio form-inline">
                    <label>
                        <input type="radio" name="rejoinFl" value="y" <?= $checked['rejoinFl']['y']; ?>>
                        회원탈퇴/삭제 후
                        <input type="text" name="rejoin" size="4" class="input_int_m js-number" data-number="4" value="<?= $data['rejoin']; ?>">
                        일 동안 재가입 불가
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="rejoinFl" value="n" <?= $checked['rejoinFl']['n']; ?>>
                        사용안함
                    </label>
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        가입불가 회원아이디
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>가입불가 회원아이디</th>
            <td>
                <textarea name="unableid" rows="3" cols="" class="form-control"><?= $data['unableid']; ?></textarea>

                <div class="notice-info notice-sm">
                    회원가입을 제한할 아이디를 쉼표(,)로 구분하여 입력하세요.
                </div>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('input[name=rejoin]').focusout(function () {
            if (this.value < 1) {
                this.value = 1;
            }
        });

        // 재가입 기간제한
        $('input[name=\'rejoinFl\']').click(setRejoinFl);
        $('input[name=\'rejoinFl\']').each(function () {
            setRejoinFl.call(this);
        });
        
        // 만 14세 이상 동의 항목 제어
        $('input[name=\'under14Fl\']').click(setUnder14ConsentFl);
        $('input[name=\'under14Fl\']').each(function () {
            setUnder14ConsentFl.call(this);
        });

        var $frmSetup = $('#frmSetup');
        $frmSetup.validate({
            submitHandler: function (form) {
                if ($('[name=rejoinFl]:eq(0)', $frmSetup).prop('checked')) {
                    if ($('input[name="rejoin"]').val() < 1) {
                        alert('재가입 기간제한을 사용하지 않으시려면 사용하지 않음을 체크해주시기 바랍니다.');
                        return false;
                    }
                }

                var data = $(form).serializeArray();
                data.push({name: 'mode', value: 'member_join'});
                ajax_with_layer('./member_ps.php', data, function (data, textStatus, jqXHR) {
                    layer_close();
                    dialog_alert(data, '확인', {isReload: true});
                });
            }
        });
    });

    /**
     * 재가입 기간제한
     */
    function setRejoinFl() {
        if ($(this).prop('checked') === false) return;

        var thisVal = $('input[name=\'rejoinFl\']:checked').val();

        if (thisVal == 'y') {
            $('input[name=\'rejoin\']').prop('disabled', false);
        } else {
            $('input[name=\'rejoin\']').prop('disabled', true);
        }
    }

    /**
     * 만 14세 미만 동의항목 사용 설정
     */
    function setUnder14ConsentFl() {
        var thisVal = $('input[name=\'under14Fl\']:checked').val();

        if (thisVal == 'n') {
            $('input[name=\'under14ConsentFl\']').prop('disabled', false);
        }
        else {
            $('input[name=\'under14ConsentFl\']').prop('disabled', true);
        }
    }

    /**
     * 정보통신망법 안내
     */
    function lawAlert() {
        var message = '';
        message += '<b style="color: #0070c0;">제31조(법정대리인의 권리)</b><br/>';
        message += '① 정보통신서비스 제공자등이 만 14세 미만의 아동으로부터 개인정보 수집ㆍ이용ㆍ제공 등의 동의를 받으려면 그 법정대리인의 동의를 받아야 하고,' +
            ' 대통령령으로 정하는 바에 따라 법정대리인이 동의하였는지를 확인하여야 한다. ' +
            '이 경우 정보통신서비스 제공자는 그 아동에게 법정대리인의 동의를 받거나 ' +
            '법정대리인이 동의하였는지를 확인하기 위하여 필요한 법정대리인의 성명 등 최소한의 정보를 요구할 수 있다.<br/><br/>';
        message += '<b style="color: #0070c0;">제71조(벌칙)</b><br/>';
        message += '① 다음 각 호의 어느 하나에 해당하는 자는 5년 이하의 징역 또는 5천만원 이하의 벌금에 처한다.<br/>';
        message += '8. 제31조제1항(제67조에 따라 준용되는 경우를 포함한다)을 위반하여 법정대리인의 동의를 받지 아니하거나 법정대리인이 동의하였는지를 확인하지 아니하고 만 14세 미만인 아동의 개인정보를 수집한 자<br/>';

        dialog_alert(message, '정보통신망법 안내');
    }
    //-->
</script>
