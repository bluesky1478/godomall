<!-- //@formatter:off -->
<form id="frmSetup" action="./member_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="member_sleep"/>

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small>휴면회원의 가입 조건을 정합니다.</small>
        </h3>
        <input type="button" value="저장" class="btn btn-red btn-save"/>
    </div>

    <div class="table-title gd-help-manual">
        휴면회원 정책
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>일반회원 전환방법</th>
            <td class="form-inline">
                <div class="mgb15">
                    <label class="radio-inline">
                        <input type="radio" name="wakeType" value="normal" <?= $checked['wakeType']['normal']; ?> />
                        로그인 후 본인인증단계 없이 일반회원으로 전환
                    </label>
                </div>
                <div class="mgt10 mgb10">
                    <label class="radio-inline">
                        <input type="radio" name="wakeType" value="info" <?= $checked['wakeType']['info']; ?> />
                        회원정보에 등록되어있는 정보 입력 후 일반회원으로 전환
                    </label>

                    <div class="pd5 pdl15 pdb20">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="checkPhone" value="y" <?= $checked['checkPhone']['y']; ?> />
                            휴대폰번호
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="checkEmail" value="y" <?= $checked['checkEmail']['y']; ?> />
                            이메일
                        </label>
                    </div>
                </div>
                <div class="mgt10">
                    <label class="radio-inline">
                        <input type="radio" name="wakeType" value="auth" <?= $checked['wakeType']['auth']; ?> />
                        본인인증 이후 일반회원으로 전환
                    </label>

                    <div class="pd5 pdl15">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="authSms" value="y" <?= $checked['authSms']['y']; ?> />
                            등록된 휴대폰으로 인증번호 SMS수신
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="authEmail" value="y" <?= $checked['authEmail']['y']; ?> />
                            등록된 이메일로 인증번호 수신
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="authIpin" value="y" <?= $checked['authIpin']['y'];
                            echo $disabled['ipinUseFl']['n']; ?> />
                            아이핀본인인증
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="authRealName" value="y" <?= $checked['authRealName']['y'];
                            echo $disabled['phoneUseFl']['n']; ?> />
                            휴대폰본인인증
                        </label>
                    </div>
                    <div class="pd5 pdl15">
                        <span class="notice-info">* SMS는 잔여포인트가 있어야 발송됩니다.
                            <a href="../member/sms_charge.php" class="btn btn-xs btn-gray mgl10">SMS포인트 충전하기</a>
                        </span>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <th>회원등급 초기화 설정</th>
            <td class="form-inline">
                <div class="mgt10">
                    <label class="radio-inline">
                        <input type="radio" name="initMemberGroup" value="y" <?= $checked['initMemberGroup']['y']; ?>/>
                        휴면회원 해제 시 기본회원으로 등급변경
                    </label>
                </div>
                <div class="mgt10 mgb10">
                    <label class="radio-inline">
                        <input type="radio" name="initMemberGroup" value="n" <?= $checked['initMemberGroup']['n']; ?>/>
                        사용안함
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>마일리지 소멸 설정</th>
            <td class="form-inline">
                <div class="mgt10">
                    <label class="radio-inline">
                        <input type="radio" name="initMileage" value="wake" <?= $checked['initMileage']['wake']; ?>/>
                        휴면회원 해제 시 유효기간이 지난 마일리지 소멸
                    </label>
                    <div class="notice-info">마일리지의 유효기간은 지급 당시의 <a target="_blank" class="btn-link-underline" href="../member/member_mileage_basic.php">회원 > 마일리지 / 예치금관리 > 마일리지 기본 설정</a>을 따르며,<br/>마일리지 소멸 시 자동안내(SMS, 이메일)는 발송되지 않습니다.</div>
                </div>
                <div class="mgt10 mgb10">
                    <label class="radio-inline">
                        <input type="radio" name="initMileage" value="sleep" <?= $checked['initMileage']['sleep']; ?>/>
                        휴면회원 전환 시 보유 마일리지 초기화
                    </label>
                    <div class="notice-danger">해당 설정 시 휴면회원의 마일리지 처리방침에 대한 별도 안내를 이용약관 및 공지사항 등을 통해 사전에 고지할 것을 권장합니다.</div>
                </div>
            </td>
        </tr>
    </table>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    <!--
    var member_sleep = (function ($) {
        var validate, form;
        return {
            init: function () {
                form = $('#frmSetup');
            }, save: function () {
                var $mgt10 = $('.mgt10');
                var $radio = $(':radio:checked');
                if ($radio.val() == 'info' && $mgt10.eq(0).find(':checkbox:checked').length < 1) {
                    alert('휴면회원 해제 시 입력할 정보의 종류를 선택해주세요.');
                    return false;
                }
                if ($radio.val() == 'auth' && $mgt10.eq(1).find(':checkbox:checked').length < 1) {
                    alert('휴면회원 해제 시 인증에 사용될 수단을 선택해주세요.');
                    return false;
                }
                validate = $('#frmSetup').validate();
                form.submit();
            }, eventWakeType: function ($target) {
                if ($target.val() === 'auth') {
                    var phoneUseFl = '<?= $data['phoneUseFl']; ?>';
                    var ipinUseFl = '<?= $data['ipinUseFl']; ?>';
                    var checkbox = $target.closest('div').find(':checkbox');
                    checkbox.each(function (index, element) {
                            var $element = $(element);
                            var name = $element.attr('name');
                            if (name === 'authRealName' && phoneUseFl !== 'y') {
                                return true;
                            } else if (name === 'authIpin' && ipinUseFl !== 'y') {
                                return true;
                            }
                            $element.prop('disabled', false);
                        }
                    );
                } else {
                    $target.closest('div').find(':checkbox').prop('disabled', false);
                }
                $target.closest('div').siblings().find(':checkbox').prop({
                    "disabled": true,
                    "checked": false
                });
            }
        }
    })($);

    $(document).ready(function () {
        member_sleep.init();
        $('.btn-save').click(member_sleep.save);
        $(':radio[name=wakeType]').on('change', function (e) {
            member_sleep.eventWakeType($(e.target));
        });
        member_sleep.eventWakeType($(':radio:checked'));
    });
    //-->
</script>
