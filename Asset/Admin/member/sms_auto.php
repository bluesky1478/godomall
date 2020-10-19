<!-- //@formatter:off -->
<input type="hidden" id="kakaoUse" name="kakaoUse" value="<?php echo $kakaoSetting['useFlag']; ?>"/>
<input type="hidden" id="kakaoOrderUse" name="kakaoOrderUse" value="<?php echo $kakaoSetting['orderUseFlag']; ?>"/>
<input type="hidden" id="kakaoMemberUse" name="kakaoMemberUse" value="<?php echo $kakaoSetting['memberUseFlag']; ?>"/>
<input type="hidden" id="kakaoBoardUse" name="kakaoBoardUse" value="<?php echo $kakaoSetting['boardUseFlag']; ?>"/>
<input type="hidden" id="kakaoLunaUse" name="kakaoLunaUse" value="<?php echo $kakaoLunaSetting['useFlag']; ?>"/>
<input type="hidden" id="kakaoLunaOrderUse" name="kakaoLunaOrderUse" value="<?php echo $kakaoLunaSetting['orderUseFlag']; ?>"/>
<input type="hidden" id="kakaoLunaMemberUse" name="kakaoLunaMemberUse" value="<?php echo $kakaoLunaSetting['memberUseFlag']; ?>"/>
<input type="hidden" id="kakaoLunaBoardUse" name="kakaoLunaBoardUse" value="<?php echo $kakaoLunaSetting['boardUseFlag']; ?>"/>
<form id="frmSmsAuto" name="frmSmsAuto" method="post" action="sms_ps.php">
    <input type="hidden" name="mode" value="smsAuto"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
        </h3>
        <div class="btn-group">
            <input type="submit" value="설정 저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        SMS 발송 정보 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>SMS 잔여 포인트</th>
            <td class="form-inline">
                <span class="number text-darkred bold"><?php echo number_format($smsPoint, 1) ?></span>
                포인트
                <button type="button" class="btn btn-gray btn-sm" onclick="show_popup('./sms_charge.php?popupMode=yes')">SMS 포인트 충전하기</button>
                <?php if ($smsPoint === '0') { ?>
                <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 포인트 정보가 정상 출력됩니다.</div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>SMS 인증번호</th>
            <td class="form-inline">
                <div class="mgb10">
                    <input type="password" class="form-control mgr5" name="password" value="**********" disabled="disabled"/>
                    <button type="button" class="btn btn-gray btn-sm btn-change-password">SMS 인증번호 변경하기</button>
                </div>
                <div class="notice-danger font12"><a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a> 에서 SMS 인증번호를 확인 및 변경할 수 있습니다.</div>
                <div class="notice-danger font12">반드시 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 SMS 인증번호와 동일한 번호로 설정하셔야만, SMS가 발송됩니다. (자동 SMS 포함, 다른 SMS 서비스 모두 해당됨)
</div>
            </td>
        </tr>
        <tr>
            <th>SMS 발신번호</th>
            <td class="form-inline">
                <?php if ($smsPreRegister === false) { ?>
                    <div>
                        <input type="hidden" name="smsCallNum" value=""/>
                        <span class="smsCallNumText">
                            <span class="text-darkred">등록된 SMS 발신번호가 없습니다.</span>
                        </span>
                        <?php if (gd_is_provider() === false) { ?>
                            <a href="https://www.godo.co.kr/mygodo/sms/intro.gd" target="_blank" class="btn btn-gray btn-sm">발신번호 등록하기</a>
                        <?php } ?>
                    </div>
                    <div class="notice-info">
                        발신번호 사전등록제 : (전기통신사업법 제 84조의 2) 거짓으로 표시된 전화번호로 인한 이용자 피해 예방을 위해 사전 등록한 발신번호로만 SMS를 발송하실 수 있습니다. <a href="https://www.godo.co.kr/customer/board-view.gd?type=notice&idx=1247" target="_blank" class="snote bold">자세히보기 ></a>
                    </div>
                <?php } elseif ($smsPreRegister === 'reset') { ?>
                    <div>
                        <input type="hidden" name="smsCallNum" value=""/>
                        <span class="smsCallNumText">
                            <span class="text-darkred">기 설정된 SMS 발신번호는 사전 등록된 번호가 아닙니다.</span>
                        </span>
                        <?php if (gd_is_provider() === false) { ?>
                            <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 선택하기</button>
                        <?php } ?>
                    </div>
                <?php } elseif ($smsPreRegister === 'empty') { ?>
                    <div>
                        <input type="hidden" name="smsCallNum" value=""/>
                        <span class="smsCallNumText">
                            <span class="text-darkred">SMS 발신번호를 선택해주세요.</span>
                        </span>
                        <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 선택하기</button>
                    </div>
                <?php } elseif ($smsPreRegister === true) { ?>
                    <input type="hidden" name="smsCallNum" value="<?php echo gd_isset($smsAutoData['smsCallNum']) ?>"/>
                    <span class="smsCallNumText number text-blue bold"><?php echo gd_number_to_phone($smsAutoData['smsCallNum']) ?></span>
                    <?php if (gd_is_provider() === false) { ?>
                        <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 변경하기</button>
                    <?php } ?>
                <?php } ?>
                <?php if ($smsPreRegister !== true) { ?>
                <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 발신번호 정보가 정상 출력됩니다.</div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>90 Byte 초과시<br/>메시지 전송 방법</th>
            <td class="form-inline">
                <label title="90Byte 까지만 SMS 발송" class="radio-inline">
                    <input type="radio" name="smsAutoSendOver" value="limit" <?php echo gd_isset($checked['smsAutoSendOver']['limit']); ?> />
                    90byte 까지만 SMS 발송
                </label>
                <label title="분할 SMS 발송" class="radio-inline">
                    <input type="radio" name="smsAutoSendOver" value="division" <?php echo gd_isset($checked['smsAutoSendOver']['division']); ?> />
                    분할 SMS 발송
                </label>
                <label title="LMS 발송" class="radio-inline">
                    <input type="radio" name="smsAutoSendOver" value="lms" <?php echo gd_isset($checked['smsAutoSendOver']['lms']); ?> />
                    LMS 발송
                </label>
                <div class="notice-info">자동발송 문구가 쇼핑몰명, 주문번호 등으로 인하여 90Byte를 초과할 경우의 메시지 전송 방법 입니다.<br>‘90Byte 까지만 SMS 발송’으로 설정할 경우 90Byte 초과시 메시지가 짤릴 수 있습니다.</div>
            </td>
        </tr>
        <tr>
            <th>080 수신거부 번호</th>
            <td>
                <?php if ($policy['rejectNumber'] != '' && $policy['status'] == 'O' && $policy['use'] == 'y') { ?>
                    <div class="text-blue"><?= $policy['rejectNumber'] ?></div>
                <?php } else { ?>
                    <div><b><a href="https://www.godo.co.kr/echost/power/add/convenience/sms-refusal-intro.gd" target="_blank" class="text-blue">[080 수신거부 사용신청]</a></b>을 먼저 해주시기 바랍니다.</div>
                <?php } ?>
                <div class="notice-danger">광고성 문자의 경우 메시지가 시작되는 부분에 (광고) 표시, 메시지가 끝나는 부분에 무료수신거부가 포함되어야 합니다.</div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        SMS 발송 조건 / 문구 설정
    </div>
    <ul class="nav nav-tabs mgb10">
        <li role="presentation" class="active">
            <a href="#order" aria-controls="sms-auto-content-order" role="tab" data-toggle="tab">주문배송관련</a>
        </li>
        <li role="presentation" class="">
            <a href="#member" aria-controls="sms-auto-content-member" role="tab" data-toggle="tab">회원관련</a>
        </li>
        <li role="presentation" class="">
            <a href="#promotion" aria-controls="sms-auto-content-promotion" role="tab" data-toggle="tab">쿠폰/프로모션관련</a>
        </li>
        <li role="presentation" class="">
            <a href="#board" aria-controls="sms-auto-content-board" role="tab" data-toggle="tab">게시물등록 알림</a>
        </li>
    </ul>
    <div id="kakaoNotice" style="background: url(<?=PATH_ADMIN_GD_SHARE?>img/ico_notice2.png) no-repeat 10px 50%; padding:10px 10px 10px 60px; border:3px solid #eee; display: <?php if ( ($kakaoSetting['useFlag'] == 'y' && $kakaoSetting['orderUseFlag'] == 'y') || ($kakaoLunaSetting['useFlag'] == 'y' && $kakaoLunaSetting['orderUseFlag'] == 'y') ) { ?>block<?php } else { ?>none<?php } ?>">
        <div class="text-blue" style="margin-bottom: 3px; font-weight: bold;">현재 주문배송관련 메시지는 "카카오 알림톡"으로 사용 중입니다.</div>
        <div class="notice-danger">해당 항목은 카카오 알림톡으로 발송이 되며, 자동 SMS를 동시에 사용하실 수 없습니다.</div>
        <div class="notice-danger">다시 자동 SMS로 사용하시려면, 회원 > 카카오 알림톡 > 카카오 알림톡 설정 에서 '알림톡 사용 설정'을 '사용안함'으로 변경해주세요.</div>
    </div>

    <div class="tab-content mgt20">

        <?php
        $rePayPartHideFl = false;
        if($mallSettingDate > '20181226') $rePayPartHideFl = true; // @todo sdate > 20181226  20181227 이후 설치솔루션은 카드 부분 취소 미노출(설치일기준)
        foreach ($smsAutoList as $sKey => $sVal) {
            ?>
            <div role="tabpanel" class="tab-pane fade<?php if ($sKey === 'order') { ?> in active<?php } ?>" id="<?php echo $sKey; ?>">
                <table class="table table-cols member-sms-auto">
                    <colgroup>
                        <col class="width-sm"/>
                        <?php if ($sKey != 'admin') { ?>
                            <col class="width-md"/>
                            <col class="width-lg"/>
                        <?php } ?>
                        <col class="width-lg"/>
                        <col class="width-lg"/>
                    </colgroup>
                    <tr>
                        <th rowspan="2" class="text-center">발송항목</th>
                        <?php if ($sKey != 'admin') { ?>
                            <th rowspan="2" class="text-center">발송종류</th> <?php } ?>
                        <th colspan="<?= gd_use_provider() ? 3 : 2 ?>" class="text-center">발송대상 및 SMS 문구설정</th>
                    </tr>
                    <tr>
                        <?php if ($sKey != 'admin') { ?>
                            <th class="text-center">회원</th><?php } ?>
                        <th class="text-center">본사 운영자</th>
                        <?php if (gd_use_provider()) { ?>
                            <th class="text-center">공급사 운영자</th>
                        <?php } ?>
                    </tr>
                    <?php
                    if (empty($smsAutoData[$sKey]) === false) {
                        foreach ($smsAutoData[$sKey] as $typeData) {
                            if($typeData['code'] == 'REPAYPART' && $rePayPartHideFl === true) continue; //  20181227 이후 설치솔루션은 카드 부분 취소 미노출(설치일기준)
                            $sendType = gd_array_change_key_value(explode('_', $typeData['sendType']));
                            ?>
                            <tr>
                                <td class="text-center">
                                    <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][subject]" value="<?php echo $typeData['text']; ?>"/>
                                    <?php echo $typeData['text']; ?>
                                    <?php if (empty($typeData['desc']) === false) {
                                        echo '<br/><p class="notice-info left">(' . $typeData['desc'] . ')</p>';
                                    } ?>
                                </td>
                                <?php if ($sKey != 'admin') { ?>
                                    <td class="form-inline text-center">
                                        <?php if ($typeData['orderCheck'] === 'y') { ?>
                                            <div class="pdt5">
                                                최근
                                                <?php echo gd_select_box(null, $sKey . '[' . $typeData['code'] . '][smsOrderDate]', gd_array_change_key_value($smsAutoOrderPeriod), '일', $typeData['smsOrderDate']); ?>
                                                주문건만 발송
                                            </div>
                                        <?php } ?>
                                        <?php if ($typeData['agreeCheck'] === 'y') { ?>
                                            <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][smsAgree]" value="y"/>
                                            <div class="text-center text-blue bold">수신동의 회원만 발송</div>
                                        <?php } ?>
                                        <?php if ($typeData['couponCheck'] === 'y') { ?>
                                            <div style="margin:10px 0 0; padding:7px 0 0; border-top:1px solid #d6d6d6">
                                                쿠폰만료
                                                <?php echo gd_select_box(null, $sKey . '[' . $typeData['code'] . '][smsCouponLimitDate]', gd_array_change_key_value($smsAutoCouponLimitPeriod), '일', $typeData['smsCouponLimitDate']); ?>
                                                전
                                            </div>
                                        <?php } ?>
                                        <?php if ($typeData['nightCheck'] === 'y') { ?>
                                            <div <?php if ($typeData['orderCheck'] === 'y' || $typeData['agreeCheck'] === 'y') { ?>style="margin:10px 0 0; padding:7px 0 0; border-top:1px solid #d6d6d6"<?php } ?>>
                                                <label title="설정시 체크" class="radio-inline">
                                                    <input type="checkbox" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][smsNightSend]" value="y" <?php if (gd_isset($typeData['smsNightSend']) === 'y') {
                                                        echo 'checked=\'checked\'';
                                                    } ?> />
                                                    야간시간에도 발송
                                                </label>
                                                <br/>
                                                <p class="notice-info">(정보통신망법에 의해 08:00 ~ 21:00 에만 발송)</p>
                                            </div>
                                        <?php } ?>
                                        <?php if ($typeData['code'] === 'DELIVERY') { ?>
                                            <?= gd_select_box('', $sKey.'['.$typeData['code'].'][smsOrdDeliverySend]', ['n'=>'주문번호 기준 1회만 발송','y'=>'부분 배송 시 배송 건 별 발송'], null, $typeData['smsOrdDeliverySend']) ?>
                                        <?php } ?>
                                        <?php if ($typeData['code'] === 'DELIVERY_COMPLETED') { ?>
                                            <?= gd_select_box('', $sKey.'['.$typeData['code'].'][smsOrdDeliveryCompletedSend]', ['n'=>'주문번호 기준 1회만 발송','y'=>'부분 배송 시 배송 건 별 발송'], null, $typeData['smsOrdDeliveryCompletedSend']) ?>
                                        <?php } ?>
                                        <?php if ($typeData['code'] === 'CANCEL') { ?>
                                            <?= gd_select_box('', $sKey.'['.$typeData['code'].'][smsOrdCancelSend]', ['n'=>'주문번호 기준 1회만 발송','y'=>'부분 배송 시 배송 건 별 발송'], null, $typeData['smsOrdCancelSend']) ?>
                                        <?php } ?>
                                        <?php if ($typeData['code'] === 'REPAY') { ?>
                                            <?= gd_select_box('', $sKey.'['.$typeData['code'].'][smsOrdRefundSend]', ['n'=>'주문번호 기준 1회만 발송','y'=>'부분 배송 시 배송 건 별 발송'], null, $typeData['smsOrdRefundSend']) ?>
                                        <?php } ?>
                                        <?php if ($typeData['deliveryCheck'] === 'y') { ?>
                                            <?= gd_select_box('', $sKey.'['.$typeData['code'].'][smsDelivery]', ['n'=>'주문번호 기준 1회만 발송','y'=>'부분 배송 시 배송 건 별 발송'], null, $typeData['smsDelivery']) ?>
                                        <?php } ?>
                                        <?php if ($typeData['disapprovalCheck'] === 'y' && $useApprovalFlag) { ?>
                                            <label title="설정시 체크" class="radio-inline">
                                                <input type="checkbox" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][smsDisapproval]" value="y" <?php if (gd_isset($typeData['smsDisapproval']) === 'y') {
                                                    echo 'checked=\'checked\'';
                                                } ?> />
                                                승인대기 회원포함
                                            </label>
                                        <?php } ?>
                                        <?php if ($typeData['code'] == 'ACCOUNT') { ?>
                                            <div style="margin:10px 0 0; padding:7px 0 0; border-top:1px solid #d6d6d6">
                                                <label title="설정시 체크" class="radio-inline">
                                                    <input type="checkbox" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][smsRepeatSend]" value="y" <?php if (gd_isset($typeData['smsRepeatSend']) === 'y') {
                                                        echo 'checked=\'checked\'';
                                                    } ?> />
                                                </label>
                                                주문
                                                <?php echo gd_select_box(null, $sKey . '[' . $typeData['code'] . '][smsOrderAfterDate]', gd_array_change_key_value($smsAutoOrderAfterPeriod), '일', gd_isset($typeData['smsOrderAfterDate'], 3)); ?> 후
                                                <div
                                                        class="pdt5"><?php echo gd_select_box(null, $sKey . '[' . $typeData['code'] . '][smsReSendTime]', gd_array_change_key_value($smsAutoReSendTime), '시', gd_isset($typeData['smsReSendTime'], 10)); ?> 재발송
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <?php if ($typeData['code'] == 'AGREEMENT2YPERIOD') { ?>
                                            <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][smsAgree]" value="y"/>
                                            <div class="text-center text-blue bold">수신동의여부 안내메일<br/>발송불가 회원 대상</div>
                                        <?php } ?>
                                        <?php if (isset($typeData['reserveHour']) && $typeData['reserveHour'] > 0) { ?>
                                            <div class="<?php if ($typeData['code'] == 'COUPON_WARNING') { ?>pdt5<?php } ?>" <?php if ($typeData['code'] == 'AGREEMENT2YPERIOD' || $typeData['code'] == 'COUPON_BIRTH' || $typeData['code'] == 'BIRTH') { ?>style="margin:10px 0 0; padding:7px 0 0; border-top:1px solid #d6d6d6"<?php } ?>>
                                                <?php
                                                echo gd_select_box(null, $sKey . '[' . $typeData['code'] . '][reserveHour]', gd_array_change_key_value($smsAutoReservationTime[$typeData['code']]), '시', $typeData['reserveHour']); ?>
                                                발송
                                            </div>
                                        <?php } ?>
                                    </td>

                                    <td class="form-inline">
                                        <?php if (isset($sendType['member']) === true) { ?>
                                            <label title="자동발송시 체크" class="radio-inline">
                                                <input type="checkbox" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][memberSend]" value="y" <?php if (gd_isset($typeData['memberSend']) === 'y') {
                                                    if ($typeData['code'] != 'APPROVAL' || $typeData['code'] == 'APPROVAL' && $useApprovalFlag) {
                                                        echo 'checked=\'checked\'';
                                                    }
                                                }
                                                if ($typeData['code'] == 'APPROVAL' && !$useApprovalFlag) {
                                                    echo 'disabled=\'checked\'';
                                                } ?> />
                                                자동발송
                                            </label>
                                            <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][memberMode]" value="<?php echo $typeData['memberMode']; ?>"/>
                                            <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][memberSno]" value="<?php echo $typeData['memberSno']; ?>"/>
                                            <textarea name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][memberContents]" rows="4" class="form-control"><?php echo $typeData['memberContents']; ?></textarea>
                                        <?php } else { ?>
                                            <div class="text-center text-blue">운영자 전용</div>
                                        <?php } ?>
                                    </td>
                                <?php } ?>
                                <td class="form-inline">
                                    <?php if (isset($sendType['admin']) === true) { ?>
                                        <label title="자동발송시 체크" class="radio-inline">
                                            <?php if ($sKey != 'admin') { ?>
                                                <input type="checkbox" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][adminSend]" value="y" <?php if (gd_isset($typeData['adminSend']) === 'y') {
                                                    echo 'checked=\'checked\'';
                                                } ?> /> 자동발송
                                            <?php } else { ?>
                                                <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][adminSend]" value="y"/>
                                            <?php } ?>
                                        </label>
                                        <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][adminMode]" value="<?php echo $typeData['adminMode']; ?>"/>
                                        <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][adminSno]" value="<?php echo $typeData['adminSno']; ?>"/>
                                        <textarea name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][adminContents]" rows="4"
                                                  class="form-control <?php if ($sKey == 'admin') { ?> width100p <?php } ?>"><?php echo $typeData['adminContents']; ?></textarea>
                                    <?php } else { ?>
                                        <div class="text-center text-orange-red">회원 전용</div>
                                    <?php } ?>
                                </td>
                                <?php if (gd_use_provider()) { ?>
                                    <td class="form-inline">
                                        <?php if (isset($sendType['provider']) === true) { ?>
                                            <label title="자동발송시 체크" class="radio-inline">
                                                <input type="checkbox" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerSend]" value="y" <?php if (gd_isset($typeData['providerSend']) === 'y') {
                                                    echo 'checked=\'checked\'';
                                                } ?> />
                                                자동발송
                                            </label>
                                            <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerMode]" value="<?php echo $typeData['providerMode']; ?>"/>
                                            <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerSno]" value="<?php echo $typeData['providerSno']; ?>"/>
                                            <textarea name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerContents]" rows="4" class="form-control"><?php echo $typeData['providerContents']; ?></textarea>
                                        <?php } else { ?>
                                            <?php if ($sKey == 'admin') { ?>
                                                <div class="text-center text-orange-red">본사 전용</div>
                                            <?php } else { ?>
                                                <div class="text-center text-orange-red">회원 전용</div>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                <?php } else { ?>
                                    <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerSend]" value="<?php echo $typeData['providerSend'] === 'y' ? 'y' : 'n'; ?>"/>
                                    <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerSno]" value="<?php echo $typeData['providerSno']; ?>"/>
                                    <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerMode]" value="<?php echo $typeData['providerMode']; ?>"/>
                                    <input type="hidden" name="<?php echo $sKey; ?>[<?php echo $typeData['code']; ?>][providerContents]" value="<?php echo $typeData['providerContents']; ?>"/>
                                <?php } ?>
                            </tr>
                            <?php
                        }
                    }
                    ?>

                </table>
            </div>
            <?php
        }
        ?>
    </div>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.btn-change-password').click(function () {
            $.get('../share/layer_sms_password.php?mode=change', function () {
                BootstrapDialog.closeAll();
                BootstrapDialog.show({
                    title: 'SMS 인증번호 변경하기',
                    size: BootstrapDialog.SIZE_WIDE,
                    message: arguments[0]
                });
            });
        });

        // 폼 체크
        $('#frmSmsAuto').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                smsAutoSendOver: "required",
            },
            messages: {
                smsAutoSendOver: {
                    required: '90 Byte 초과시 메시지 전송 방법을 선택해 주세요.'
                }
            }
        });

        $(':checkbox[name$="[smsDisapproval]"]').change(function (e) {
            if (!e.target.checked) {
                alert('\'승인대기 회원 포함\' 설정을 해제하시면, 승인대기 회원에게 회원가입 SMS가 발송되지 않습니다.<br/>해당 설정을 해제하실 경우, 가입 승인 시 발송되는 가입승인 SMS 사용을 권장합니다.');
            }
        });

        // HASH가 있는 경우 자동으로 탭 이동 처리
        if (window.location.hash) {
            $('a[href="' + window.location.hash + '"]').tab('show');
        }

        // SMS 사전 등록 발신번호 선택하기
        $('.js-sms-call-number').click(function (e) {
            var params = {
                returnInput: 'smsCallNum'
            };
            $.get('../member/layer_sms_call_number_select.php', params, function (data) {
                BootstrapDialog.show({
                    title: 'SMS 발신번호 목록',
                    message: $(data),
                    closable: true
                });
            });
        });

        // SMS 발송 조건 / 문구 설정 탭
        $('.nav-tabs a').click(function (e) {
            e.preventDefault();

            if ($(this).attr('href') == '#order') {
                if ( ($('#kakaoUse').val() == 'y' && $('#kakaoOrderUse').val() == 'y') || ($('#kakaoLunaUse').val() == 'y' && $('#kakaoLunaOrderUse').val() == 'y')  ) {
                    $('#kakaoNotice').show();
                } else {
                    $('#kakaoNotice').hide();
                }
            } else if ($(this).attr('href') == '#member') {
                if ( ($('#kakaoUse').val() == 'y' && $('#kakaoMemberUse').val() == 'y') || ($('#kakaoLunaUse').val() == 'y' && $('#kakaoLunaMemberUse').val() == 'y') ) {
                    $('#kakaoNotice').show();
                } else {
                    $('#kakaoNotice').hide();
                }
            } else if ($(this).attr('href') == '#promotion') {
                $('#kakaoNotice').hide();
            } else if ($(this).attr('href') == '#board') {
                if ( ($('#kakaoUse').val() == 'y' && $('#kakaoBoardUse').val() == 'y') || ($('#kakaoLunaUse').val() == 'y' && $('#kakaoLunaBoardUse').val() == 'y') ) {
                    $('#kakaoNotice').show();
                } else {
                    $('#kakaoNotice').hide();
                }
            }

            $(this).tab('show');
        });

        // SMS 발송 조건 / 문구 설정 탭
        $(':checkbox').click(function (e) {
            var thisName = $(this).attr('name');
            var arrName = thisName.split('[');
            var thisChecked = $(this).prop('checked');
            if (thisChecked) {
                var nowCheckVal = '';
            } else {
                var nowCheckVal = 'checked';
            }
            if (arrName[0] == 'order') {
                if ( ($('#kakaoUse').val() == 'y' && $('#kakaoOrderUse').val() == 'y') || ($('#kakaoLunaUse').val() == 'y' && $('#kakaoLunaOrderUse').val() == 'y')  ) {
                    alert('해당 항목은 카카오 알림톡을 사용중이여서 SMS로 발송할 수 없습니다. SMS를 이용하시려면 카카오 알림톡 설정 메뉴에서 알림톡 사용 설정을 사용안함으로 변경해주세요.');
                    $(this).prop('checked', nowCheckVal);
                }
            } else if (arrName[0] == 'member') {
                if ( ($('#kakaoUse').val() == 'y' && $('#kakaoMemberUse').val() == 'y') || ($('#kakaoLunaUse').val() == 'y' && $('#kakaoLunaMemberUse').val() == 'y') ) {
                    alert('해당 항목은 카카오 알림톡을 사용중이여서 SMS로 발송할 수 없습니다. SMS를 이용하시려면 카카오 알림톡 설정 메뉴에서 알림톡 사용 설정을 사용안함으로 변경해주세요.');
                    $(this).prop('checked', nowCheckVal);
                }
            } else if (arrName[0] == 'board') {
                if ( ($('#kakaoUse').val() == 'y' && $('#kakaoBoardUse').val() == 'y') || ($('#kakaoLunaUse').val() == 'y' && $('#kakaoLunaBoardUse').val() == 'y') ) {
                    alert('해당 항목은 카카오 알림톡을 사용중이여서 SMS로 발송할 수 없습니다. SMS를 이용하시려면 카카오 알림톡 설정 메뉴에서 알림톡 사용 설정을 사용안함으로 변경해주세요.');
                    $(this).prop('checked', nowCheckVal);
                }
            }

        });
    });
    //-->
</script>
