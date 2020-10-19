<div class="table-title gd-help-manual">
    기본정보
</div>
<div class="form-inline">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="<?= (!empty($widthClass)) ? $widthClass : 'width-3xl' ?>"/>
            <col class="width-sm"/>
            <col class="<?= (!empty($widthClass)) ? $widthClass : '' ?>"/>
        </colgroup>
        <tr>
            <th>회원구분</th>
            <td <?= $data['mallSno'] > 0 ? '' : 'colspan="3"' ?>>
                <label class="radio-inline">
                    <input type="radio" name="memberFl" value="personal"
                           data-target=".div-business" <?= $checked['memberFl']['personal']; ?>/>
                    개인회원
                </label>
                <?php if ($data['mallSno'] == $gGlobal['defaultMallSno'] || $mode == 'register') { ?>
                    <label class="radio-inline">
                        <input type="radio" name="memberFl" value="business"
                               data-target=".div-business" <?= $checked['memberFl']['business']; ?>/>
                        사업자회원
                    </label>
                <?php } ?>
            </td>
            <?php if ($data['mallSno'] > 0) { ?>
                <th>상점구분</th>
                <td>
                    <span class="flag flag-16 flag-<?= gd_isset($gGlobal['mallList'][$data['mallSno']]['domainFl'], 'kr'); ?>"></span><?= gd_isset($gGlobal['mallList'][$data['mallSno']]['mallName'], '기준몰'); ?>
                </td>
            <?php } ?>
        </tr>
        <tr>
            <th>등급</th>
            <td>
                <?= gd_select_box_by_group_list($data['groupSno'], '등급선택'); ?>
                <span id="groupSnoMsg" class="input_error_msg"></span>
                <?php if ($mode != 'register') { ?>
                    <div>수정일:
                        <span class="font-num"><?= $data['groupModDt']; ?></span>
                    </div>
                <?php } ?>
            </td>
            <th>승인</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="appFl" value="y" <?= $checked['appFl']['y']; ?>/>
                        승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="appFl" value="n" <?= $checked['appFl']['n']; ?>/>
                        미승인
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">아이디</th>
            <td>
                <?php if ($mode == 'register') { ?>
                    <span title="이용하실 아이디를 입력해주세요!">
                        <input type="text" name="memId" id="memId" value="" class="form-control error" data-pattern="gdMemberId"/>
                        <button type="button" id="overlap_memId" class="btn btn-gray btn-sm">중복확인</button>
                    </span>
                <?php } else { ?>
                    <input type="hidden" name="memId" id="memId" class="" value="<?= $data['memId'] ?>"/>
                    <strong><?= $data['memId'] ?></strong>
                    <?= gd_get_third_party_icon_web_path($data['snsTypeFl']); ?>
                    <?php if($data['simpleJoinFl'] == 'order') { ?><span class="text-blue">(주문 간단 가입)</span><?php } ?>
                    <?php if($data['simpleJoinFl'] == 'push') { ?><span class="text-blue">(가입 유도 푸시)</span><?php } ?>
                <?php } ?>
            </td>
            <th>닉네임</th>
            <td>
                <span title="별명을 입력해주세요!">
                    <input type="text" name="nickNm" id="nickNm" value="<?= $data['nickNm'] ?>"
                           class="form-control width-sm error">
                </span>
                <button type="button" id="overlap_nickNm" class="btn btn-gray btn-sm">중복확인</button>
            </td>
        </tr>
        <tr>
            <th class="require">이름</th>
            <td>
                <span title="이름을 입력해주세요!">
                    <input type="text" name="memNm" id="memNm" value="<?= $data['memNm'] ?>"
                           class="form-control width-sm" data-pattern="<?= (($data['mallSno'] > $gGlobal['defaultMallSno'])) ? 'gdEngKor' : 'gdMemberNmGlobal' ?>" maxlength="20">
                </span>
                <?php if ($joinField['pronounceName']['use'] == 'y') { ?>
                    <input type="text" name="pronounceName" id="pronounceName" value="<?= $data['pronounceName'] ?>"
                           class="form-control width-sm" maxlength="20" placeholder="발음 표시" data-pattern="<?= (($data['mallSno'] > 1)) ? 'gdEngKor' : 'gdMemberNmGlobal' ?>">
                <?php } ?>
            </td>
            <th class="require">비밀번호</th>
            <td>
                <span title="비밀번호를 입력해주세요!">
                    <input type="password" name="memPw" value="" class="form-control width-sm"
                           placeholder="비밀번호입력">
                    &nbsp;&nbsp;
                    <input type="password" name="memPwRe" value="" class="form-control width-sm"
                           placeholder="비밀번호확인">
                </span>
                <?php
                if ($data['mallSno'] == $gGlobal['defaultMallSno'] || $mode == 'register') {
                    echo '<p class="notice-danger notice-info">*영문대문자/영문소문자/숫자/특수문자 중 2개 이상 포함, 10~16자리 이하로 설정할 수 있습니다.</p>';
                } else {
                    if ($joinField['passwordCombineFl'] == 'engNum') {
                        echo '<p class="notice-danger notice-info">*영문대문자/영문소문자/숫자 중 2개 이상 포함,';
                    } elseif ($joinField['passwordCombineFl'] == 'engNumEtc') {
                        echo '<p class="notice-danger notice-info">*영문대문자/영문소문자/숫자/특수문자 중 3개 이상 포함,';
                    } else {
                        echo '<p class="notice-danger notice-info">*영문대문자/영문소문자 or 숫자,';
                    }
                    echo ' ' . $joinField['memPw']['minlen'] . '~' . $joinField['memPw']['maxlen'] . '자리 이하로 설정할 수 있습니다.</p>';
                } ?>
            </td>
        </tr>
        <?php if ($mode != 'register') { ?>
            <tr>
                <th>주문금액</th>
                <td colspan="3"><?= gd_currency_display($data['saleAmt']); ?></td>
            </tr>
            <tr>
                <th>마일리지</th>
                <td><?= gd_money_format($data['mileage']) . gd_display_mileage_unit() ?></td>
                <th>예치금</th>
                <td><?= gd_money_format($data['deposit']) . gd_display_deposit('unit') ?></td>
            </tr>
            <tr>
                <th>회원가입일</th>
                <td><?= $data['entryDt'] ?></td>
                <th>가입경로</th>
                <td><?= $data['entryPath'] ?></td>
            </tr>
            <tr>
                <th>최종로그인일</th>
                <td><?= $data['lastLoginDt'] ?></td>
                <th>방문횟수</th>
                <td><?= $data['loginCnt'] ?></td>
            </tr>
            <tr>
                <th>휴면해제일</th>
                <td colspan="3"><?= $data['sleepWakeDt']; ?></td>
            </tr>
        <?php } ?>
        <tr>
            <th>이메일</th>
            <td>
                <div class="form-inline mgb5">
                    <input type="hidden" name="chkEmail" value=""/>
                    <span title="이메일을 입력해주세요!">
                        <input type="text" id="emailAddress" name="email[]" value="<?= $data['email'][0] ?>"
                               class="form-control width-sm">
                        @
                    </span>
                    <span title="이메일을 입력해주세요!">
                        <input type="text" id="emailDomain" name="email[]" value="<?= $data['email'][1] ?>"
                               class="form-control width-sm error">
                    </span>
                    <?= gd_select_box_by_mail_domain('email_site', 'email_site', null, $data['email'][1], '직접입력'); ?>
                    <button type="button" id="overlap_email" class="btn btn-gray btn-sm">중복확인</button>
                </div>
                <div class="form-inline">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="maillingFl" value="y" data-default="" <?= $checked['maillingFl']['y']; ?>/>
                        정보/이벤트 MAIL 수신에 동의합니다.
                    </label>
                </div>
                <p class="notice-danger notice-info">*수신동의설정 안내메일의 자동발송여부에 따라 회원정보의 수신동의설정 변경 시 해당 회원에게 안내메일이 자동 발송됩니다.</p>
            </td>
            <th>이메일<br/>수신동의/거부일</th>
            <td>
                <?php
                $mailLastReceiveAgreementDt = gd_isset($data['lastReceiveAgreementDt']['mail'], $data['entryDt']);
                $mailAgreementView = '동의 : ' . $mailLastReceiveAgreementDt;
                if (gd_isset($data['lastNotificationDt']['mail'], '-') != '-') {
                    $mailAgreementView .= '<br/>(수신동의여부 확인메일 발송<br/> : ' . $data['lastNotificationDt']['mail'] . ')';
                }
                ?>
                <?= $data['maillingFl'] == 'y' ? $mailAgreementView : '거부 : ' . gd_date_format('Y-m-d', $mailLastReceiveAgreementDt); ?>
            </td>
        </tr>
        <tr>
            <th>휴대폰번호</th>
            <td>
                <div class="form-inline mgb5">
                    <?php
                    $cellPhoneCallPrefixAttributes = [
                        'id'   => 'cellPhoneCountryCode',
                        'name' => 'cellPhoneCountryCode',
                    ];
                    if ($mode != 'register' && $data['mallSno'] != $gGlobal['defaultMallSno']) {
                        echo gd_select_box_by_call_prefix($cellPhoneCallPrefixAttributes, $data['cellPhoneCountryCode']);
                    }
                    ?>
                    <span title="휴대폰번호를 입력해주세요!">
                        <input type="text" name="cellPhone" value="<?= $data['cellPhone'] ?>" maxlength="12" class="form-control js-number-only width-md"/>
                    </span>
                </div>
                <?php if ($data['mallSno'] == $gGlobal['defaultMallSno'] || $mode == 'register') { ?>
                    <div class="form-inline">
                        <span id="cellPhoneMsg" class="input_error_msg"></span>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="smsFl" value="y" <?= $checked['smsFl']['y']; ?>>
                            정보/이벤트 SMS 수신에 동의합니다.
                        </label>
                    </div>
                    <p class="notice-danger notice-info">*수신동의설정 안내메일의 자동발송여부에 따라 회원정보의 수신동의설정 변경 시 해당 회원에게 SMS가 자동 발송됩니다.</p>
                <?php } ?>
            </td>
            <th>SMS<br/>수신동의/거부일</th>
            <td>
                <?php
                $smsLastReceiveAgreementDt = gd_isset($data['lastReceiveAgreementDt']['sms'], $data['entryDt']);
                $smsAgreementView = '동의 : ' . $smsLastReceiveAgreementDt;
                if (gd_isset($data['lastNotificationDt']['sms'], '-') != '-') {
                    $smsAgreementView .= '<br/>(수신동의여부 확인SMS 발송<br/> : ' . $data['lastNotificationDt']['sms'] . ')';
                }
                ?>
                <?= $data['smsFl'] == 'y' ? $smsAgreementView : '거부 : ' . gd_date_format('Y-m-d', $smsLastReceiveAgreementDt); ?>
            </td>
        </tr>
        <tr>
            <th>주소</th>
            <td colspan="3">
                <?php if ($data['mallSno'] == $gGlobal['defaultMallSno'] || $mode == 'register') { ?>
                    <div class="form-inline mgb5">
                        <span title="우편번호를 입력해주세요!">
                            <input type="text" size="6" maxlength="5" name="zonecode" class="form-control js-number"
                                   data-number="5"
                                   value="<?= $data['zonecode'] ?>"/>
                            <input type="hidden" name="zipcode" value="<?= $data['zipcode'] ?>"/>
                            <span id="zipcodeText" class="number <?php if (strlen($data['zipcode']) != 7) {
                                echo 'display-none';
                            } ?>">(<?php echo $data['zipcode']; ?>)
                            </span>
                        </span>
                        <input type="button"
                               onclick="postcode_search('zonecode', 'address', 'zipcode');"
                               value="우편번호찾기" class="btn btn-gray btn-sm"/>
                    </div>
                    <div class="form-inline">
                        <span title="주소를 입력해주세요!">
                            <input type="text" name="address" id="address"
                                   class="form-control width-2xl"
                                   value="<?= $data['address']; ?>"/>
                        </span>
                        <span title="상세주소를 입력해주세요!">
                            <input type="text" name="addressSub" id="addressSub"
                                   class="form-control width-2xl"
                                   value="<?= $data['addressSub']; ?>"/>
                        </span>
                    </div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th>전화번호</th>
            <td colspan="3">
                <div class="form-inline">
                    <?php
                    $phoneCallPrefixAttributes = [
                        'id'   => 'phoneCountryCode',
                        'name' => 'phoneCountryCode',
                    ];
                    if ($mode != 'register' && $data['mallSno'] != $gGlobal['defaultMallSno']) {
                        gd_select_box_by_call_prefix($phoneCallPrefixAttributes, $data['phoneCountryCode']);
                    }
                    ?>
                    <span title="전화번호를 입력해주세요!">
                        <input type="text" name="phone" value="<?= $data['phone'] ?>" maxlength="12" class="form-control js-number-only width-md"/>
                    </span>
                </div>
            </td>
        </tr>
    </table>
</div>
