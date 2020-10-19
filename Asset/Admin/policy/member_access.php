<form id="frmSetup" action="member_ps.php" method="post">
<input type="hidden" name="mode" value="member_access" />
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <input type="submit" value="설정 저장" class="btn btn-red"/>
    </div>
    <div class="table-title gd-help-manual">
        인트로페이지 사용 설정
    </div>
    <table class="table table-cols mgb15">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>사용설정</th>
            <td class="form-inline">
                <div class="mgt5 mgb5">
                    <label class="checkbox-inline"><input type="checkbox" name="introFrontUseFl" value="y" <?php echo gd_isset($checked['introFrontUseFl']['y']);?> data-target="js-intro-front" /> PC 인트로페이지</label>
                    <label class="checkbox-inline"><input type="checkbox" name="introMobileUseFl" value="y" <?php echo gd_isset($checked['introMobileUseFl']['y']);?>data-target="js-intro-mobile"   /> 모바일 인트로페이지</label>
                </div>
            </td>
        </tr>
        <tr class="js-intro-front" <?php if($data['introFrontUseFl'] =='n') { echo "style='display:none'"; } ?>>
            <th>PC 쇼핑몰<br/>접속 권한</th>
            <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="introFrontAccess" value="free" <?php echo gd_isset($checked['introFrontAccess']['free']);?> />제한없음</label>
                    <label class="radio-inline"><input type="radio" name="introFrontAccess" value="adult" <?php echo gd_isset($checked['introFrontAccess']['adult']);?> onclick="checkIntroAccess();" />성인만 가능</label>
                    <label class="radio-inline"><input type="radio" name="introFrontAccess" value="member" <?php echo gd_isset($checked['introFrontAccess']['member']);?> />회원만 가능</label>
                    <label class="radio-inline"><input type="radio" name="introFrontAccess" value="walkout" <?php echo gd_isset($checked['introFrontAccess']['walkout']);?> />접속불가</label>
                <div class="mgt5 notice-info">
                    본인확인 인증 서비스(아이핀인증/휴대폰인증) 사용 시 성인인증 인트로(성인만 가능)를 사용할 수 있습니다.<br/>
                    본인확인 인증 서비스 사용상태 : <span class="bold"><?php echo (gd_isset($adultIdentifyFl) === 'y' ? '사용중' : '미사용');?></span>
                </div>
                <?php if($gGlobal['isUse']) { ?>
                <div class="mgt5 notice-info">
                    "성인 인증" 인트로는 해외몰에는 본인확인 인증서비스 불가로 사용이 불가하여, 해외몰 접속시에는 "회원 전용" 인트로가 적용되므로 유의바랍니다.
                </div>
                <?php } ?>
            </td>
        </tr>
        <tr class="js-intro-mobile" <?php if($data['introMobileUseFl'] =='n') { echo "style='display:none'"; } ?>>
            <th>모바일 쇼핑몰<br/>접속 권한</th>
            <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="introMobileAccess" value="free" <?php echo gd_isset($checked['introMobileAccess']['free']);?> />제한없음</label>
                    <label class="radio-inline"><input type="radio" name="introMobileAccess" value="adult" <?php echo gd_isset($checked['introMobileAccess']['adult']);?> onclick="checkIntroAccess();" />성인만 가능</label>
                    <label class="radio-inline"><input type="radio" name="introMobileAccess" value="member" <?php echo gd_isset($checked['introMobileAccess']['member']);?> />회원만 가능</label>
                    <label class="radio-inline"><input type="radio" name="introMobileAccess" value="walkout" <?php echo gd_isset($checked['introMobileAccess']['walkout']);?> />접속불가</label>
                <div class="mgt5 notice-info">
                    본인확인 인증 서비스(아이핀인증/휴대폰인증) 사용 시 성인인증 인트로(성인만 가능)를 사용할 수 있습니다.<br/>
                    본인확인 인증 서비스 사용상태 : <span class="bold"><?php echo (gd_isset($adultIdentifyFl) === 'y' ? '사용중' : '미사용');?></span>
                </div>
                <?php if($gGlobal['isUse']) { ?>
                <div class="mgt5 notice-info">
                    "성인 인증" 인트로는 해외몰에는 본인확인 인증서비스 불가로 사용이 불가하여, 해외몰 접속시에는 "회원 전용" 인트로가 적용되므로 유의바랍니다.
                </div>
                <?php } ?>
            </td>
        </tr>
    </table>
    <div class="notice-danger notice-sm mgl15 mgb15">인트로페이지 접속권한 설정에 따라 전체 쇼핑몰 접속 권한이 자동 변경됩니다.</div>
    <div class="linepd30"></div>

    <!-- 상품 구매 권한 -->
    <div class="table-title gd-help-manual">
        상품 구매 권한
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>상품 구매 권한</th>
            <td>

                    <label class="radio-inline"><input type="radio" name="buyAuthGb" value="free" <?php echo gd_isset($checked['buyAuthGb']['free']);?> />제한 없음</label>

                    <label class="radio-inline"><input type="radio" name="buyAuthGb" value="member" <?php echo gd_isset($checked['buyAuthGb']['member']);?> />회원만 가능</label>

            </td>
        </tr>
        <tr>
            <th>만14세 미만<br />비회원 주문 제한</th>
            <td>
                <div class="mgt5">
                    <label class="radio-inline"><input type="radio" name="guestUnder14Fl" value="n" <?php echo gd_isset($checked['guestUnder14Fl']['n']);?> />사용안함</label><br />
                </div>
                <div class="mgt5">
                    <label class="radio-inline"><input type="radio" name="guestUnder14Fl" value="y" <?php echo gd_isset($checked['guestUnder14Fl']['y']);?> />'만 14세 이상입니다.' 동의 항목 사용</label><br />
                </div>
                <div class="mgt5">
                    <label class="radio-inline"><input type="radio" name="guestUnder14Fl" value="s" <?php echo gd_isset($checked['guestUnder14Fl']['s']);?> />주문자 본인인증 서비스 사용</label>
                    <span class="notice-info notice-sm mgl20">본인인증 후 주문자가 만14세 미만일 경우, 비회원주문이 불가합니다.</span>
                </div>
                <div class="notice-danger notice-sm mgt10">정보통신망법에 따라 만 14세 미만의 아동의 개인정보 수집/이용 시 법정대리인의 동의 확인 후 개인정보 수집이 가능합니다.<a class="btn-link pdl5" style="cursor:pointer;" onclick="lawAlert();">[자세히보기]</a></div>
                <div class="notice-info notice-sm mgt5">본인확인 인증 서비스(아이핀인증/휴대폰인증)가 적용되어 있어야만 ‘본인인증 서비스 사용’ 설정 시 본인인증 서비스가 실행됩니다.<br />
                    <a href="../policy/member_auth_cellphone.php" target="_blank" class="btn-link-underline">휴대폰인증 설정></a>&nbsp;<a href="../policy/member_auth_ipin.php" target="_blank" class="btn-link-underline">아이핀인증 설정></a>
                </div>
            </td>
        </tr>
    </table>
    <!-- //상품 구매 권한 -->

    <!-- 마일리지 / 쿠폰 중복사용 설정 -->
    <div class="table-title gd-help-manual">
        마일리지 / 쿠폰 중복사용 설정
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>마일리지 / 쿠폰<br/>중복사용 설정</th>
            <td class="form-inline">
                <div class="mgt5">
                    <label class="radio-inline">
                        <input type="radio" name="chooseMileageCoupon" value="n" <?php echo gd_isset($checked['chooseMileageCoupon']['n']);?> />마일리지와 쿠폰 동시 사용 제한 없음. (허용)
                    </label>
                </div>
                <div class="mgt5 mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="chooseMileageCoupon" value="y" <?php echo gd_isset($checked['chooseMileageCoupon']['y']);?> />마일리지와 쿠폰 동시 사용 불가
                    </label>
                </div>
            </td>
        </tr>
    </table>
    <!-- //마일리지 / 쿠폰 중복사용 설정 -->

    <div class="table-title gd-help-manual">
        회원의 자동 로그아웃
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>자동 로그아웃</th>
            <td class="form-inline">
                <div class="mgt5">
                    <label class="radio-inline">
                        <input type="radio" name="sessTimeUseFl" value="n" <?php echo gd_isset($checked['sessTimeUseFl']['n']);?> />제한 없음 (회원이 브라우저를 닫거나 수동 로그아웃 할 때 로그아웃)
                    </label>
                </div>
                <div class="mgt5 mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="sessTimeUseFl" value="y" <?php echo gd_isset($checked['sessTimeUseFl']['y']);?> />로그인 후
                        <?php echo gd_select_box('sessTime', 'sessTime', gd_array_change_key_value(array('5','10','15','30','60','90','120')),null,$data['sessTime']);?>
                        분간 클릭이 없으면 자동 로그아웃 함
                    </label>
                </div>
            </td>
        </tr>
    </table>
</form>

<script type="text/javascript">
<!--
$(document).ready(function(){
    // 정보 저장
    $("#frmSetup").validate({
        submitHandler: function (form) {
            var check = checkIntroAccess();
            if (check == true) {
                form.target = 'ifrmProcess';
                form.submit();
            }
            return false;
        },
        rules: {
        },
        messages: {
        }
    });

    $("input[name=\'guestUnder14Fl\']").click(function () {
        <?php if($adultIdentifyFl == 'n') { ?>
        if ($(this).val() == 's') {
            dialog_alert('주문자 본인인증 서비스 사용이 불가합니다. <br/> 본인인증서비스 사용설정 후 다시 시도해주세요.');
            return false;
        }
        <?php } ?>
    });

    $("input[name='introFrontUseFl'],input[name='introMobileUseFl']").click(function () {
        if($('.'+$(this).data('target')).is(':hidden')) {
            $('.'+$(this).data('target')).show();
        } else {
            $('.'+$(this).data('target')).hide();
        }
    });

    // 인트로역할
    checkIntroAccess();

    // 자동로그아웃
    $('input[name=\'sessTimeUseFl\']').click(setSessTimeUse);
    $('input[name=\'sessTimeUseFl\']').each(function(){
        setSessTimeUse.call(this);
    });
});

/**
 * 접속권한 설정 체크
 */
function checkIntroAccess()
{
    if ('<?php echo gd_isset($adultIdentifyFl);?>' == 'n') {

        var frontVal = "";
        var mobileVal = "";

        if($('input[name=\'introFrontUseFl\']:checked').val() =='y') {
            frontVal = $('input[name=\'introFrontAccess\']:checked').val();
        }

        if($('input[name=\'introMobileUseFl\']:checked').val() =='y') {
            mobileVal = $('input[name=\'introMobileAccess\']:checked').val();
        }

        if (frontVal == 'adult' || mobileVal == 'adult') {
            alert('성인 인트로는 본인확인 인증 서비스를 사용해야 선택할 수 있습니다.');
            return false;
        }
    }
    return true;
}

/**
 * 자동로그아웃
 */
function setSessTimeUse()
{
    if ($(this).prop('checked') === false) return;

    var thisVal = $('input[name=\'sessTimeUseFl\']:checked').val();

    if (thisVal == 'y') {
        $('select[name=\'sessTime\']').prop('disabled',false);
    } else if (thisVal == 'n') {
        $('select[name=\'sessTime\']').prop('disabled',true);
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
