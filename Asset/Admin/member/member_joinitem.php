<form id="frmSetup" action="./member_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="member_joinitem"/>
    <input type="hidden" name="mallSno" value="<?= $mall['mallSno']; ?>"/>

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small></small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs mgb20" role="tablist">
        <?php foreach ($gGlobal['useMallList'] as $val) { ?>
            <li role="presentation" class="<?= $mall['mallSno'] == $val['sno'] ? 'active' : ''; ?>">
                <a href="#<?= $val['domainFl'] ?>" role="tab" data-toggle="tab" aria-controls="<?= $val['sno'] ?>">
                    <span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $mall['mallSno'] == $val['sno'] ? $val['mallName'] : ''; ?>
                </a>
            </li>
        <?php } ?>
    </ul>
    <div class="tab-contents">
        <div class="tab-pane" role="tabpanel" id="<?= $mall['domainFl'] ?>">

            <div class="table-title gd-help-manual">
                기본 정보
            </div>
            <table class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col class="width-lg"/>
                    <col/>
                    <col class="width-xs center"/>
                </colgroup>
                <tr>
                    <th>회원구분</th>
                    <td colspan="2">
                        <input type="hidden" name="memberFl[use]" value="y"/>
                        <input type="hidden" name="memberFl[require]" value="y"/>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="memberFl[use]" value="y" disabled="disabled" checked="checked"/>
                            개인회원
                        </label>
                        <?php if ($mall['mallSno'] == 1) { ?>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="businessinfo[use]" value="y">
                                사업자회원
                            </label>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th>아이디</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                            사용
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                            필수
                        </label>
                        <input type="hidden" name="memId[use]" value="y"/>
                        <input type="hidden" name="memId[require]" value="y"/>
                    </td>
                    <td class="form-inline">
                        <input type="text" class="form-control width-2xs js-number" data-number="2,20,4" name="memId[minlen]" value="<?= $data['memId']['minlen']; ?>"/>
                        ~
                        <input type="text" class="form-control width-2xs js-number" data-number="2,50,50" name="memId[maxlen]" value="<?= $data['memId']['maxlen']; ?>"/>
                        자 입력
                    </td>
                </tr>
                <tr>
                    <th>이름</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                            사용
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                            필수
                        </label>
                        <input type="hidden" name="memNm[use]" value="y"/>
                        <input type="hidden" name="memNm[require]" value="y"/>
                    </td>
                    <td></td>
                </tr>
                <?php if ($mall['mallSno'] != 1) { ?>
                    <tr>
                        <th>이름(발음)</th>
                        <td>
                            <label class="checkbox-inline">
                                <input type="checkbox" value="y" name="pronounceName[use]"/>
                                사용
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                                필수
                            </label>
                            <input type="hidden" name="pronounceName[require]" value="y"/>
                        </td>
                        <td></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th>닉네임</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="nickNm[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="nickNm[require]" value="y"/>
                            필수
                        </label>
                        <input type="hidden" name="nickNm[minlen]" value="2"/>
                        <input type="hidden" name="nickNm[maxlen]" value="20"/>
                    </td>
                    <td class="form-inline">
                        <input type="text" class="form-control width-2xs js-number" data-number="2,20,2" name="nickNm[minlen]" value="<?= $data['nickNm']['minlen']; ?>"/>
                        ~
                        <input type="text" class="form-control width-2xs js-number" data-number="2,20,20" name="nickNm[maxlen]" value="<?= $data['nickNm']['maxlen']; ?>"/>
                        자 입력
                    </td>
                </tr>
                <tr>
                    <th>비밀번호</th>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                            사용
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                            필수
                        </label>
                        <input type="hidden" name="memPw[use]" value="y"/>
                        <input type="hidden" name="memPw[require]" value="y"/>
                    </td>

                    <?php if ($mall['mallSno'] == 1) { ?>
                        <td>
                            <p class="notice-danger mgb0">
                                영문대문자/영문소문자/숫자/특수문자 중 2개 포함, 10자리 이상 16자리 이하로 설정<br> 방송통신위원회 고시 [개인정보의 기술적&middot;관리적 보호조치 기준]에 의한 비밀번호 설정 규칙입니다.
                            </p>
                        </td>
                    <?php } else { ?>
                        <td class="form-inline">
                            <input type="text" class="form-control width-2xs js-number" data-number="2,20,4" name="memPw[minlen]" value="<?= $data['memPw']['minlen']; ?>"/>
                            ~
                            <input type="text" class="form-control width-2xs js-number" data-number="2,16,16" name="memPw[maxlen]" value="<?= $data['memPw']['maxlen']; ?>"/>
                            자 입력 / 입력규칙 : <select class="" name="passwordCombineFl">
                                <option value="engNum" <?= $selected['passwordCombineFl']['engNum']; ?>>영문 대소문자+숫자조합</option>
                                <option value="engNumEtc" <?= $selected['passwordCombineFl']['engNumEtc']; ?>>영문 대소문자+숫자+특수문자조합</option>
                                <option value="default" <?= $selected['passwordCombineFl']['default']; ?>>영문 대소문자 or 숫자</option>
                            </select>
                        </td>
                    <?php } ?>
                </tr>
                <tr>
                    <th>이메일</th>
                    <td>
                        <?php if ($mall['mallSno'] == 1) { ?>
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="email[use]" value="y"/>
                                사용
                            </label>
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="email[require]" value="y"/>
                                필수
                            </label>
                        <?php } else { ?>
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                                사용
                            </label>
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" value="y" checked="checked" disabled="disabled"/>
                                필수
                            </label>
                            <input type="hidden" name="email[use]" value="y"/>
                            <input type="hidden" name="email[require]" value="y"/>
                        <?php } ?>
                    </td>
                    <td>
                        <label class="checkbox-inline" for="maillingFl">
                            <input type="checkbox" id="maillingFl" name="maillingFl[use]" value="y">
                            정보/이벤트 메일 수신 동의 사용
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>휴대폰번호</th>
                    <td <?= $mall['mallSno'] == 1 ? '' : 'colspan=2' ?>>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="cellPhone[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="cellPhone[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <?php if ($mall['mallSno'] == 1) { ?>
                        <td>
                            <label class="checkbox-inline" for="smsFl">
                                <input type="checkbox" id="smsFl" name="smsFl[use]" value="y">
                                정보/이벤트 SMS 수신 동의 사용
                            </label>
                        </td>
                    <?php } ?>
                </tr>
                <?php if ($mall['mallSno'] == 1) { ?>
                    <tr>
                        <th>주소</th>
                        <td>
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="address[use]" value="y"/>
                                사용
                            </label>
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="address[require]" value="y"/>
                                필수
                            </label>
                        </td>
                        <td></td>
                    </tr>
                <?php } ?>
                <tr>
                    <th>전화번호</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="phone[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="phone[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td></td>
                </tr>
            </table>

            <?php if ($mall['mallSno'] == 1) { ?>
                <div class="table-title div-business gd-help-manual">
                    사업자 정보
                </div>
                <table class="table table-cols div-business">
                    <colgroup>
                        <col class="width-md"/>
                        <col class="width-xl"/>
                        <col/>
                        <col class="width-xs center"/>
                    </colgroup>
                    <tr>
                        <th rowspan="6">사업자회원</th>
                        <td class="business">
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="company[use]" class="defaultBusinessInfoUse" value="y"/>
                                상호
                            </label>
                            (
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="company[require]" class="defaultBusinessInfoRequire" value="y" onclick="return false;" />
                                필수
                            </label>
                            )
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="business">
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="busiNo[use]" class="defaultBusinessInfoUse" value="y"/>
                                사업자번호
                            </label>
                            (
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="busiNo[require]" class="defaultBusinessInfoRequire" value="y" onclick="return false;" />
                                필수
                            </label>
                            )
                        </td>
                        <td>
                            <label class="checkbox-inline">
                                <input type="checkbox" name="busiNo[overlapBusiNoFl]" value="y">
                                사업자번호 중복가입 제한 기능 사용
                            </label>
                            <p class="notice-info">설정 시점 이후 회원가입에 한해서만 중복가입 제한 기능이 적용됩니다.</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="business">
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="ceo[use]" value="y"/>
                                대표자명
                            </label>
                            (
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="ceo[require]" value="y"/>
                                필수
                            </label>
                            )
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="business">
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="service[use]" value="y"/>
                                업태
                            </label>
                            (
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="service[require]" value="y"/>
                                필수
                            </label>
                            )
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="business">
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="item[use]" value="y"/>
                                종목
                            </label>
                            (
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="item[require]" value="y"/>
                                필수
                            </label>
                            )
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td class="business">
                            <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                <input type="checkbox" name="comAddress[use]" value="y"/>
                                사업장 주소
                            </label>
                            (
                            <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                <input type="checkbox" name="comAddress[require]" value="y"/>
                                필수
                            </label>
                            )
                        </td>
                        <td></td>
                    </tr>
                </table>
            <?php } ?>

            <div class="table-title gd-help-manual">
                부가정보
            </div>
            <table id="t1" class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col class="width-lg"/>
                    <col/>
                    <col class="width-xs center"/>
                </colgroup>
                <tr>
                    <th>팩스번호</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="fax[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="fax[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <th>추천인아이디</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="recommId[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="recommId[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="recommFl[use]" value="y">
                            회원정보 변경 시 추천인아이디 등록 불가
                        </label>
                        <p class="notice-info">체크 시 회원 가입시에만 추천인아이디 등록이 가능합니다.</p>
                        <p class="notice-info">신규회원 가입 시 추천인을 등록하면 자동으로 지급되는 마일리지를 설정할 수 있습니다. <a href="./member_mileage_give.php" target="_blank" class="notice-ref btn-link">마일리지 지급설정 바로가기</a></p>
                    </td>
                </tr>
                <tr>
                    <th>생일</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="birthDt[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="birthDt[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td>
                        생일 양/음력(
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="calendarFl[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="calendarFl[require]" value="y"/>
                            필수
                        </label>
                        )
                    </td>
                </tr>
                <tr>
                    <th>성별</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="sexFl[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="sexFl[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <th>결혼여부</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="marriFl[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="marriFl[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td>
                        결혼 기념일 (
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="marriDate[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="marriDate[require]" value="y"/>
                            필수
                        </label>
                        )
                    </td>
                </tr>
                <tr>
                    <th>직업</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="job[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="job[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td>
                        <div id="jobField">
                            <button type="button" class="itemEdit btn btn-gray btn-sm">설정</button>
                            직업 <?= $data['jobCnt']; ?>개
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>관심분야</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="interest[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="interest[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td>
                        <div id="interestField">
                            <button type="button" class="itemEdit btn btn-gray btn-sm">설정</button>
                            관심분야 <?= $data['interestCnt']; ?>개
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>개인정보유효기간</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="expirationFl[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="expirationFl[require]" value="y"/>
                            필수
                        </label>
                    <td>
                        <p class="notice-danger mgb0">
                            회원이 ‘휴면회원 방지기간‘을 설정할 수 있는 항목입니다.<br/>해당 설정 미사용 시, 기본값인 개인정보유효기간은 1년으로 자동 설정됩니다. <a class="expiration_detail" style="cursor:pointer; color: dodgerblue;">관련 내용 자세히 보기></a>

                        </p>
                    </td>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <th>남기는 말씀</th>
                    <td>
                        <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                            <input type="checkbox" name="memo[use]" value="y"/>
                            사용
                        </label>
                        <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                            <input type="checkbox" name="memo[require]" value="y"/>
                            필수
                        </label>
                    </td>
                    <td></td>
                </tr>
            </table>

            <div class="table-title gd-help-manual">
                추가 정보
            </div>
            <table id="t2" class="table table-cols">
                <colgroup>
                    <col class="width-md"/>
                    <col class="width-lg"/>
                    <col/>
                    <col class="width-xs center"/>
                </colgroup>
                <?php
                $cnt = 0;
                foreach ($data as $k => $v) {
                    if (preg_match('/^ex[1-6]+$/', $k)) $cnt++;
                }
                if ($cnt < 1) $cnt = 1;
                for ($i = 1; $i <= $cnt; $i++) {
                    unset($selected, $checked);
                    $exData = $data['ex' . $i];
                    if ($i == 1 || $exData['name']) {
                        if ($exData['use'] == 'y') $checked['use'] = 'checked="checked"';
                        if ($exData['require'] == 'y') $checked['require'] = 'checked="checked"';
                        $msg = $exData['type'] . '&nbsp;&nbsp;';
                        if ($exData['value'] != '') {
                            $msg .= '(' . $exData['value'] . ')';
                        }
                        ?>
                        <tr>
                            <th class="form-inline">
                                <input type="text" name="ex[name][]" value="<?= $exData['name']; ?>" class="form-control width90p"/>
                            </th>
                            <td>
                                <label class="checkbox-inline" title="사용여부를 선택해주세요!">
                                    <input type="checkbox" name="ex[use][<?= ($i - 1) ?>]" value="y" <?= $checked['use']; ?> />
                                    사용
                                </label>
                                <label class="checkbox-inline" title="필수 사용여부를 선택해주세요!">
                                    <input type="checkbox" name="ex[require][<?= ($i - 1) ?>]" value="y" <?= $checked['require']; ?> />
                                    필수
                                </label>
                            </td>
                            <td>
                                <input type="hidden" name="ex[type][]" value="<?= $exData['type']; ?>"/>
                                <input type="hidden" name="ex[value][]" value="<?= $exData['value']; ?>"/>

                                <div><a class="setup_field btn btn-gray btn-sm">설정</a></div>
                                <span class="msg"><?= $msg; ?></span>
                            </td>
                            <?php if ($i == 1) { ?>
                                <td><a class="addfield btn btn-white btn-icon-plus btn-sm">추가</a></td>
                            <?php } else { ?>
                                <td><a class="delfield btn btn-white btn-icon-minus btn-sm">삭제</a></td>
                            <?php } ?>
                        </tr>
                        <?php
                    }
                }
                ?>
            </table>
        </div>
    </div>
</form>

<!-- 추가가입항목 설정폼 -->
<div id="setup_field_form" class="display-none">
    <form>
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tr>
                <th>타입</th>
                <td class="form-inline">
                    <?php echo gd_select_box(
                        'stype', 'stype', gd_array_change_key_value(
                            [
                                'TEXT',
                                'RADIO',
                                'SELECT',
                                'CHECKBOX',
                            ]
                        )
                    ) ?>
                </td>
            </tr>
            <tr>
                <th>선택값</th>
                <td class="form-inline">
                    <div>
                        <input type="text" name="svalue" class="form-control" disabled="disabled"/>
                    </div>
                    <div>,(쉼표)로 연결하여 적어주세요</div>
                </td>
            </tr>
        </table>
    </form>
</div>
<!-- //추가가입항목 설정폼 -->

<div id="code" class="display-none"></div>


<script type="text/javascript">
    <!--
    var joinitem = {
        currentModal: null
    };
    function minMaxRangeValidation(element) {
        var minChkVal = 4;
        if (element == 'nickNm') {
            var nickNmUse = $('input[name="nickNm[use]"]').prop('checked');
            minChkVal = 2;
            if (nickNmUse == false) return true;
        }

        var $input = $('input[name="' + element + '[minlen]"]:text');
        var $input2 = $('input[name="' + element + '[maxlen]"]:text');
        var minVal = $input.val();
        var maxVal = $input2.val();
        minVal = (minVal * 1);
        maxVal = (maxVal * 1);

        if (minVal < minChkVal) {
            alert('최소자리수는 ' + minChkVal + ' 미만으로 입력할 수 없습니다.');
            return false;
        } else if (maxVal < 10) {
            alert('최대자리수는 10 미만으로 입력할 수 없습니다.');
            return false;
        } else if (minVal >= maxVal && element != 'nickNm') {
            alert('최소자리수는 최대자리수보다 같거나 클수 없습니다.');
            return false;
        }

        return true;
    }

    function extraValidation() {
        var $names = $('input[name="ex[name][]"]');
        var $values = $('input[name="ex[value][]"]');
        var $types = $('input[name="ex[type][]"]');
        var $use = $('input[name*="ex[use]"]');
        var pass = true;

        for (var i = 0; i < $names.length; i++) {
            var useExtra = $use[i].checked;
            if (useExtra && ($names[i].value.length < 1)) {
                alert('추가정보의 항목명을 입력해주세요.');
                pass = false;
                break;
            }
            if (useExtra && ($values[i].value.length < 1) && ($types[i].value != 'TEXT')) {
                alert('추가정보의 데이터를 설정해주세요.');
                pass = false;
                break;
            }
        }
        return pass;
    }

    $(document).ready(function () {
        // 아이디 자리수 체크
        $('input[name*="memId"]:text').on('blur', function() {
            minMaxRangeValidation('memId')
        });

        // 닉네임 자리수 체크
        $('input[name*="nickNm"]:text').on('blur', function() {
            minMaxRangeValidation('nickNm')
        });

        // 체크처리
        var items = eval(<?= gd_htmlspecialchars_decode($data['items']);?>);
        logger.debug('load items', items);
        _.map(items, function (item, fieldName) {
            _.map(item, function (option, optionName) {
                if ((optionName == 'use' || optionName == 'require') && option == 'n') {
                    return false;
                }
                var $target = $('input[name="' + fieldName + '[' + optionName + ']"]', '#frmSetup');
                $target.prop('checked', true);
            });
        });

        var $passwordCombineFl = $('select[name=passwordCombineFl]');
        if ($passwordCombineFl.length > 0) {
            $passwordCombineFl.find('option[value="' + items.passwordCombineFl + '"]').prop('selected', true);
        }

        // 사용에 따른 필수 활성화
        var $ableRequire = $('#frmSetup label input[name*=\'[use]\']').not('.defaultBusinessInfoUse');
        $ableRequire.click(able_rquire).each(able_rquire);
        $ableRequire.click(function () {
            able_rquire.call(this);
        });

        // 사업자회원 활성화
        var $businessinfo = $('input[name=\'businessinfo[use]\']');
        var $defaultBusinessInfoUse = $('.defaultBusinessInfoUse');

        if ($(':checkbox[name="busiNo[overlapBusiNoFl]"]').prop('checked')) {
            $('input[name="busiNo[use]"]').addClass('defaultBusinessInfoRequire');
            $('input[name="busiNo[use]"]').attr('onclick', '').on('click', function () {
                return false;
            });
        }

        $businessinfo.click(setBusinessinfo).each(setBusinessinfo);
        $businessinfo.click(function () {
            setBusinessinfo.call(this);
            if ($(this).prop('checked') == true) {
                $defaultBusinessInfoUse.prop('checked', true);
                $defaultBusinessInfoUse.closest('td').find('input[name*="[require]"]').prop('checked', true);
            }
        });

        $defaultBusinessInfoUse.change(function(e) {
            $(e.target).closest('td').find('input[name*="[require]"]').prop('checked', $(e.target).prop('checked'));
        });

        // 사업자번호 중복 제한 활성화
        $(':checkbox[name="busiNo[overlapBusiNoFl]"]').change(function (e) {
            if ($(this).prop('checked') && $('input[name="busiNo[use]"]').prop('checked') == false) {
                $('input[name="busiNo[use]"]').trigger('click');
            }


            $('input[name="busiNo[use]"]').attr('readonly', $(this).prop('checked'));
            if ($(this).prop('checked')) {
                $('input[name="busiNo[use]"]').addClass('defaultBusinessInfoRequire');
                $('input[name="busiNo[use]"]').attr('onclick', '').on('click', function () {
                    return false;
                });
            } else {
                $('input[name="busiNo[use]"]').removeClass('defaultBusinessInfoRequire');
                $('input[name="busiNo[use]"]').off('click');
            }
        });

        $(':checkbox[name="email[use]"], :checkbox[name="cellPhone[use]"]', '#frmSetup').on('click', function (e) {
            var $target = $(e.target);
            if ($target.prop('checked')) {
                var $tr = $target.closest('tr', '#frmSetup');
                $tr.find(':checkbox:last').prop('checked', true);
            }
        });

        $(':checkbox[name="maillingFl[use]"], :checkbox[name="smsFl[use]"]', '#frmSetup').on('change', function (e) {
            var $target = $(e.target);
            if ($target.prop('checked') === false) {
                BootstrapDialog.confirm({
                    title: "정보/이벤트 메일(SMS)수신 동의 사용 체크 해제",
                    message: "정보통신망법에 따라 수신동의한 회원에게만 정보/이벤트 소식을 전송할 수 있습니다. 수신동의 사용을 해제하시겠습니까?",
                    btnCancelLabel: "취소",
                    btnOkLabel: "해제",
                    callback: function (result) {
                        if (result) {
                            $target.prop('checked', false);
                        } else {
                            $target.prop('checked', true);
                        }
                    }
                });
            }
        });
        $('.expiration_detail').on('click', function () {
            var msg = "일정 기간 동안 쇼핑몰을 이용하지 않는 회원은 휴면회원으로 전환되어 쇼핑몰의 이용이 제한되고,"+'<br>'+"쇼핑몰 운영자는 회원에게 정보/이벤트성 안내를 할 수 없습니다."+'<br>'+"-&nbsp;&nbsp;&nbsp;회원가입 항목에서 개인정보유효기간 미사용 시 1년간 쇼핑몰을 이용하지 않는 회원들을 "+'<br>'+"&nbsp;&nbsp;&nbsp;&nbsp;휴면회원으로 일괄 전환합니다."+'<br>'+"-&nbsp;&nbsp;&nbsp;개인정보유효기간 사용 시 회원에게 ‘휴면회원 방지기간’ 정보를 수집할 수 있으며, 각 회원이 "+'<br>'+"&nbsp;&nbsp;&nbsp;&nbsp;“1년(기본값)/3년/5년/탈퇴 시” 중 선택한 기간 동안 쇼핑몰을 이용하지 않는 경우 해당 회원의"+'<br>'+"&nbsp;&nbsp;&nbsp;&nbsp;정보를 휴면회원으로 전환합니다."+'<br><div><img src="../admin/gd_share/img/expirationFl_img.png" style="width:80%; height:100%; margin-top:20px; margin-left:50px;"></div><br><strong>'+"관련 법령 근거"+'</strong><br>'+"정보통신망 이용촉진 및 정보보호 등에 관한 법률 제29조 2항"+'<br>'+
                    "정보통신서비스 제공자등은 정보통신서비스를 1년의 기간 동안 이용하지 아니하는 이용자의 개인정보를 보호하기 위하여 대통령령으로 정하는 바에 따라 개인정보의 파기 등 필요한 조치를 취하여야 한다."+'<span style ="color: dodgerblue;">'+" 다만, 그 기간에 대하여 다른 법령 또는 이용자의 요청에 따라 달리 정한 경우에는 그에 따른다.";
            BootstrapDialog.alert({
                title: "안내",
              message: msg
            });
        });
        // 가입항목추가 버튼
        $('.addfield').on('click', add_field);

        // 가입항목추가필드 삭제
        $('.delfield').one('click', function () {
            $(this).parents('tr').remove();
        });

        // 가입항목추가필드 설정창 호출
        $('#frmSetup .setup_field').click(call_setup_field);

        // 관심분야 설정창 호출 버튼
        $('#interestField button').click(function () {
            win = popup({
                url: '../policy/base_code_list.php?popupMode=y&categoryGroupCd=01&groupCd=01001'
                , target: 'code'
                , width: 800
                , height: 600
                , resizable: 'yes'
                , scrollbars: 'yes'
            });
            win.focus();
        });

        // 직업 설정창 호출 버튼
        $('#jobField button').click(function () {
            win = popup({
                url: '../policy/base_code_list.php?popupMode=y&categoryGroupCd=01&groupCd=01002'
                , target: 'code'
                , width: 800
                , height: 600
                , resizable: 'yes'
                , scrollbars: 'yes'
            });
            win.focus();
        });

        $('#frmSetup').validate({
            submitHandler: function (form) {
                if (minMaxRangeValidation('memId') && minMaxRangeValidation('nickNm') && extraValidation()) {
                    form.submit();
                }
                return false;
            }
        });

        $('li[role=presentation]').click(function (e) {
            e.preventDefault();
            var controls = $(e.target).attr('aria-controls');
            if (typeof controls === 'undefined') {
                controls = $(e.target).closest('a').attr('aria-controls');
            }
            var url = '../member/member_joinitem.php?mallSno=' + controls;
            logger.debug('tab click location: ' + url);
            window.location.href = url;
        });
    });

    function add_field(e) {
        if ($('.delfield').length >= 5) {
            alert('가입항목은 6개까지만  추가할 수 있습니다.');
            return;
        }

        var setupFieldLength = $('.setup_field').length;
        var obj = $(this).parents('tr').clone();
        $(obj).find(':checkbox').each(function (idx, item) {
            item.name = item.name.replace(0, setupFieldLength);
        });
        obj.find('.addfield').parents('td').html('<a class="delfield btn btn-white btn-icon-minus btn-sm">삭제</a>');
        obj.find('.msg').html('');
        obj.find('input').each(function () {
            if (this.type == 'text' || this.type == 'hidden') {
                this.value = '';
            } else if (this.type == 'checkbox') {
                this.checked = false;
            }
        });
        $('.setup_field', obj).parent().prev().html('');
        obj.appendTo($(this).parents('table'));

        // event element
        $('label input[name*=\'[use]\']', obj).click(able_rquire).each(able_rquire);
        $('.delfield', obj).one('click', function () {
            $(this).parents('tr').remove();
        });
        $('.setup_field', obj).on('click', call_setup_field);
    }

    /**
     * 가입항목추가필드 설정창 호출
     */
    function call_setup_field(e) {
        var btnObj = $(e.target);
        var dialogMessage = $('<div>').append($('#setup_field_form').clone().removeClass('display-none')).html().replace(/\r/g, '').replace(/\n/g, '');
        var dialog = new BootstrapDialog({
            title: "추가 가입 항목 설정",
            message: dialogMessage,
            buttons: [{id: "btn_apply", cssClass: "apply_field btn-red", label: "적용"}],
            onshow: function () {
                var svalue = $('input[name=svalue]');
                svalue.prop('disabled', $('#stype').val() == 'TEXT');
            }
        });
        dialog.realize();
        dialog.$modal.on('shown.bs.modal', function () {
            var $inputs = btnObj.parents('tr').find('input');
            var tmp = '';
            $inputs.each(function () {
                tmp = $(this).val();
                if ($(this).attr('name') == 'ex[type][]') {
                    $('#setup_field_form select option').each(function () {
                        if ($(this).text() == tmp) {
                            $(this).prop('selected', true);
                        }
                    });
                }
                if ($(this).attr('name') == 'ex[value][]') {
                    $('#setup_field_form input[name=\'svalue\']').val(tmp);
                }
            });

            $('#setup_field_form input[name=\'svalue\']').prop('disabled', $('#setup_field_form select option:selected').val() == 'TEXT');
        });
        dialog.$modal.on('change', '#stype', function () {
            var svalue = $('input[name=svalue]');
            svalue.prop('disabled', this.value == 'TEXT');
        });
        var btnApply = dialog.getButton('btn_apply');
        btnApply.click({'obj': btnObj}, function (e) {
            apply_field_data(e.data.obj);
        });
        dialog.open();
        joinitem.currentModal = dialog;
    }

    /**
     * 가입항목추가필드 설정 수정값 적용
     * @param btnObj 호출한 버튼 Object
     */
    function apply_field_data(btnObj) {
        var sel = '';
        var msg = '';
        var svalue = '';
        sel = $('div.modal-content select option:selected');
        svalue = $('div.modal-content :input[name=\'svalue\']').val();
        btnObj.parents('tr').find('input').each(function () {
            if ($(this).attr('name') == 'ex[type][]') $(this).val(sel.val());
            if ($(this).attr('name') == 'ex[value][]')$(this).val(svalue);
        });
        msg = sel.text() + '&nbsp;&nbsp;';
        if (svalue != '') {
            msg += '(' + svalue + ')';
        }
        btnObj.parent().prev().html(msg);
        btnObj.parents('tr').find('.msg').html(msg);
        joinitem.currentModal.close();
    }

    /**
     * 사용에 따른 필수 활성화
     */
    function able_rquire() {
        var nextTdInput = $(this).parents('td:eq(0)').next('td').find('input');
        var nextInput = $(this).parent().next('label').find('input');

        if ($(this).prop('checked') === true) {
            nextInput.prop('disabled', false);
            if ($.inArray($(this).attr('name'), ['email[use]', 'cellPhone[use]', 'birthDt[use]', 'marriFl[use]']) === true) {
                nextTdInput.prop('disabled', false);
            }
        } else {
            nextInput.prop('disabled', true);
            if ($.inArray($(this).attr('name'), ['email[use]', 'cellPhone[use]', 'birthDt[use]', 'marriFl[use]']) === true) {
                nextTdInput.prop('disabled', true);
            }
        }

        var $pronounceNameCheckBox = $(':checkbox[name="pronounceName\[use\]"]');
        if ($pronounceNameCheckBox.length > 0) {
            $pronounceNameCheckBox.parent().next('label').find(':checkbox').prop({checked: true, disabled: true});
        }
    }

    /**
     * 사업자회원 활성화
     */
    function setBusinessinfo() {
        var $use = $('.input_area.business input[name*=\'[use]\']');
        var $require = $('.input_area.business input[name*=\'[require]\']');
        if ($(this).prop('checked') == true) {
            $use.prop('disabled', false).prop('checked', true);
            $require.prop('disabled', false);
        } else {
            $use.prop('disabled', true).prop('checked', false);
            $require.prop('disabled', true);
        }
    }


    /**
     * 가입항목추가필드 설정 호출시 값 대입
     * @param btnObj 호출한 버튼 Object
     */
    function load_field_data(btnObj) {
        var tmp = '';
        btnObj.parents('tr').find('input').each(function () {
            tmp = $(this).val();
            if ($(this).attr('name') == 'ex[type][]') {
                $('#setup_field_form select option').each(function () {
                    if ($(this).text() == tmp) {
                        $(this).prop('selected', true);
                    }
                });
            }
            if ($(this).attr('name') == 'ex[value][]') {
                $('#setup_field_form :input[name=\'svalue\']').val(tmp);
            }
        });
    }

    //-->
</script>
