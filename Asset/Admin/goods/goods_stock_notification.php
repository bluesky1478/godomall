<form id="frmSmsAuto" name="frmSmsAuto" method="post" action="goods_stock_notification_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="goodsStockNotification"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        이메일 알림 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>발송범위</th>
            <td class="form-inline">
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[mailRange][]" value="stop" <?php if (gd_isset(in_array('stop', $goodsStock['mailRange']))) { echo 'checked=\'checked\''; } ?> />판매중지 옵션</label>
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[mailRange][]" value="request" <?php if (gd_isset(in_array('request', $goodsStock['mailRange']))) { echo 'checked=\'checked\''; } ?> />확인요청 옵션</label>
            </td>
        </tr>
        <tr>
            <th>수신자 이메일</th>
            <td class="form-inline">
                <div class="form-inline">
                    <input type="text" class="form-control mgr5" name="goodsStock[emailLocalPart][]" value="<?=$goodsStock['emailLocalPart'][0]?>" />@
                    <input type="text" class="form-control mgr5" name="goodsStock[emailDomain][]" value="<?=$goodsStock['emailDomain'][0]?>" />
                    <?=gd_select_box_by_mail_domain('emailSelector1', 'emailSelector[]', null, $goodsStock['emailDomain'][0], '직접입력', 'onchange="applyEmail(0);"') ?>
                </div>
                <div class="form-inline">
                    <input type="text" class="form-control mgr5" name="goodsStock[emailLocalPart][]" value="<?=$goodsStock['emailLocalPart'][1]?>" />@
                    <input type="text" class="form-control mgr5" name="goodsStock[emailDomain][]" value="<?=$goodsStock['emailDomain'][1]?>" />
                    <?=gd_select_box_by_mail_domain('emailSelector2', 'emailSelector[]', null, $goodsStock['emailDomain'][1], '직접입력', 'onchange="applyEmail(1);"') ?>
                </div>
                <div class="form-inline">
                    <input type="text" class="form-control mgr5" name="goodsStock[emailLocalPart][]" value="<?=$goodsStock['emailLocalPart'][2]?>" />@
                    <input type="text" class="form-control mgr5" name="goodsStock[emailDomain][]" value="<?=$goodsStock['emailDomain'][2]?>" />
                    <?=gd_select_box_by_mail_domain('emailSelector3', 'emailSelector[]', null, $goodsStock['emailDomain'][2], '직접입력', 'onchange="applyEmail(2);"') ?>
                </div>
            </td>
        </tr>
    </table>
    <div class="table-title gd-help-manual">
        SMS 알림 설정
    </div>
    <table class="table table-cols">
        <cdolgroup>
            <col class="width-md"/>
            <col/>
        </cdolgroup>
        <tr>
            <th>발송범위</th>
            <td class="form-inline">
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[smsRange][]" value="stop" <?php if (gd_isset(in_array('stop', $goodsStock['smsRange']))) { echo 'checked=\'checked\''; } ?> />판매중지 옵션</label>
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[smsRange][]" value="request" <?php if (gd_isset(in_array('request', $goodsStock['smsRange']))) { echo 'checked=\'checked\''; } ?> />확인요청 옵션</label>
            </td>
        </tr>
        <tr>
            <th>발송 시간</th>
            <td class="form-inline">
                <select name="goodsStock[smsSendDuration]">
                    <option value="0" <?php if (gd_isset($goodsStock['smsSendDuration']) == '0') { echo 'selected'; } ?>>실시간</option>
                    <option value="1" <?php if (gd_isset($goodsStock['smsSendDuration']) == '1') { echo 'selected'; } ?>>1시간마다</option>
                    <option value="3" <?php if (gd_isset($goodsStock['smsSendDuration']) == '3') { echo 'selected'; } ?>>3시간마다</option>
                    <option value="6" <?php if (gd_isset($goodsStock['smsSendDuration']) == '6') { echo 'selected'; } ?>>6시간마다</option>
                    <option value="12" <?php if (gd_isset($goodsStock['smsSendDuration']) == '12') { echo 'selected'; } ?>>12시간마다</option>
                    <option value="24" <?php if (gd_isset($goodsStock['smsSendDuration']) == '24') { echo 'selected'; } ?>>하루 한 번만</option>
                </select>
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[smsNight][]" value="night" <?php if (gd_isset(in_array('night', $goodsStock['smsNight']))) { echo 'checked=\'checked\''; } ?> />야간에도 발송</label>
            </td>
        </tr>
        <tr>
            <th>수신자 핸드폰번호</th>
            <td class="form-inline">
                <div>
                    <input type="text" class="form-control mgr5" name="goodsStock[smsNumber][]" value="<?=$goodsStock['smsNumber'][0]?>" />
                </div>
                <div>
                    <input type="text" class="form-control mgr5" name="goodsStock[smsNumber][]" value="<?=$goodsStock['smsNumber'][1]?>" />
                </div>
                <div>
                    <input type="text" class="form-control mgr5" name="goodsStock[smsNumber][]" value="<?=$goodsStock['smsNumber'][2]?>" />
                </div>
            </td>
        </tr>
    </table>
    <div class="table-title gd-help-manual">
        카카오 알림톡 알림 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>발송범위</th>
            <td class="form-inline">
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[kakaoRange][]" value="stop" <?php if (gd_isset(in_array('stop', $goodsStock['kakaoRange']))) { echo 'checked=\'checked\''; } ?> />판매중지 옵션</label>
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[kakaoRange][]" value="request" <?php if (gd_isset(in_array('request', $goodsStock['kakaoRange']))) { echo 'checked=\'checked\''; } ?> />확인요청 옵션</label>
            </td>
        </tr>
        <tr>
            <th>발송 시간</th>
            <td class="form-inline">
                <select name="goodsStock[kakaoSendDuration]">
                    <option value="0" <?php if (gd_isset($goodsStock['kakaoSendDuration']) == '0') { echo 'selected'; } ?>>실시간</option>
                    <option value="1" <?php if (gd_isset($goodsStock['kakaoSendDuration']) == '1') { echo 'selected'; } ?>>1시간마다</option>
                    <option value="3" <?php if (gd_isset($goodsStock['kakaoSendDuration']) == '3') { echo 'selected'; } ?>>3시간마다</option>
                    <option value="6" <?php if (gd_isset($goodsStock['kakaoSendDuration']) == '6') { echo 'selected'; } ?>>6시간마다</option>
                    <option value="12" <?php if (gd_isset($goodsStock['kakaoSendDuration']) == '12') { echo 'selected'; } ?>>12시간마다</option>
                    <option value="24" <?php if (gd_isset($goodsStock['kakaoSendDuration']) == '24') { echo 'selected'; } ?>>하루 한 번만</option>
                </select>
                <label title="설정시 체크" class="radio-inline"><input type="checkbox" name="goodsStock[kakaoNight][]" value="night" <?php if (gd_isset(in_array('night', $goodsStock['kakaoNight']))) { echo 'checked=\'checked\''; } ?> />야간에도 발송</label>
            </td>
        </tr>
        <tr>
            <th>수신자 핸드폰번호</th>
            <td class="form-inline">
                <div>
                    <input type="text" class="form-control mgr5" name="goodsStock[kakaoNumber][]" value="<?=$goodsStock['kakaoNumber'][0]?>" />
                </div>
                <div>
                    <input type="text" class="form-control mgr5" name="goodsStock[kakaoNumber][]" value="<?=$goodsStock['kakaoNumber'][1]?>" />
                </div>
                <div>
                    <input type="text" class="form-control mgr5" name="goodsStock[kakaoNumber][]" value="<?=$goodsStock['kakaoNumber'][2]?>" />
                </div>
            </td>
        </tr>
    </table>
    <div class="notice-info">
        <span class="text-danger">SMS와 카카오 알림톡 알림 발송 시 SMS 포인트가 차감되며, 잔여포인트가 없는 경우 SMS와 카카오 알림톡은 발송되지 않습니다.</span><input type="button" class="btn btn-sm btn-gray" onclick="show_popup('../member/sms_charge.php?popupMode=yes');" value="SMS 포인트 충전하기"><br />
        카카오 알림톡 알림은 <a href="../member/kakao_alrim_setting.php" target="_blank">[회원 > 카카오 알림톡 > 카카오 알림톡 설정]</a>에서 “사용함＂으로 설정하셔야 발송됩니다.<br />
        카카오톡 미설치 등으로 알림톡 발송 실패 시 SMS/LMS로 동일 메시지가 재발송됩니다.<br />
        "실시간"은 옵션 개별로 알림이 발송되며, 그 외의 발송 시간은 전체 옵션 기준으로 알림이 발송됩니다.<br />
        <span class="text-danger">많은 옵션에 재고 알림이 설정된 경우 실시간 외의 발송 시간으로 설정하시기를 권장합니다.</span><br />
        야간시간에도 발송 미체크 시 21시부터 익일 08시까지 SMS 알림이 발송되지 않습니다.
    </div>
    <div class="notice-info">
        <span class="text-danger">재고 알림 설정은 상품등록(수정)의 옵션/재고 설정에서 “판매중지수량/확인요청수량”을 “사용함”으로 설정하셔야 알림이 발송됩니다.</span>
    </div>
    <div class="table-title gd-help-manual">
        알림 상품 범위 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>상품범위</th>
            <td class="form-inline">
                <label title="설정시 체크" class="radio-inline"><input type="radio" name="goodsStock[sendRange][]" value="all" <?php if (gd_isset(in_array('all', $goodsStock['sendRange']))) { echo 'checked=\'checked\''; } ?> />전체 상품</label>
                <label title="설정시 체크" class="radio-inline"><input type="radio" name="goodsStock[sendRange][]" value="headquater" <?php if (gd_isset(in_array('headquater', $goodsStock['sendRange']))) { echo 'checked=\'checked\''; } ?>  />본사 상품</label>
                <label title="설정시 체크" class="radio-inline"><input type="radio" name="goodsStock[sendRange][]" value="scm" <?php if (gd_isset(in_array('scm', $goodsStock['sendRange']))) { echo 'checked=\'checked\''; } ?>  />공급사 상품</label>
            </td>
        </tr>
    </table>
</form>
<script type="text/javascript">
    function applyEmail(idx){
        $('[name="goodsStock[emailDomain][]"]')[idx].value = $('[name="emailSelector[]"]')[idx].value;
    }
</script>