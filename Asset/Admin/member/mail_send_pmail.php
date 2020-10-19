<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="submit" value="대량메일발송" class="btn btn-red" id="btnSendPowerMail">
</div>
<div class="table-title gd-help-manual">
    대상회원 선택
</div>
<form id="formSearch" method="get" action="" target="" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="sort" value="<?= $search['sort'] ?>"/>
    <input type="hidden" name="pageNum" value="<?= $search['pageNum'] ?>"/>
    <input type="hidden" name="rejectMailingFl" id="rejectMailingFl" value="">
    <input type="hidden" name="indicate" value="search"/>

    <div class="search-detail-box form-inline">
        <input type="hidden" name="detailSearch" value="<?= $search['detailSearch']; ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col>
                <col>
            </colgroup>
            <tbody>
            <?php if ($gGlobal['isUse']) { ?>
                <tr>
                    <th>상점</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="mallSno"
                                   value="" <?= gd_isset($checked['mallSno']['']); ?>/>
                            전체
                        </label>
                        <?php foreach ($gGlobal['useMallList'] as $item) { ?>
                            <label class="radio-inline">
                                <input type="radio" name="mallSno"
                                       value="<?= $item['sno']; ?>" <?= gd_isset($checked['mallSno'][$item['sno']]); ?>/>
                                <span class="flag flag-16 flag-<?= $item['domainFl']; ?>"></span><?= $item['mallName']; ?>
                            </label>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>대상회원 선택</th>
                <td colspan="3">
                    <select name="sendTarget" id="sendTarget">
                        <option value="select" class="js-apply-query">회원선택 적용</option>
                        <option value="query" class="js-apply-query">검색회원 전체적용</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th>검색어</th>
                <td>
                    <?= $combineSearchSelect; ?>
                    <input type="text" name="keyword" value="<?= $search['keyword']; ?>"
                           class="form-control"/>
                </td>
                <th>메일수신동의</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="maillingFl"
                               value="" <?= $checked['maillingFl']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="maillingFl"
                               value="y" <?= $checked['maillingFl']['y']; ?>/>
                        수신
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="maillingFl"
                               value="n" <?= $checked['maillingFl']['n']; ?>/>
                        수신거부
                    </label>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail">
            <tr>
                <th>회원구분</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="memberFl"
                               value="" <?= $checked['memberFl']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="memberFl"
                               value="personal" <?= $checked['memberFl']['personal']; ?>/>
                        개인회원
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="memberFl"
                               value="business" <?= $checked['memberFl']['business']; ?>/>
                        사업자회원
                    </label>
                </td>
            </tr>
            <tr>
                <th>회원등급</th>
                <td>
                    <?= gd_select_box('groupSno', 'groupSno', $groups, null, $search['groupSno'], '등급'); ?>
                </td>
                <th>주문금액</th>
                <td>
                    <input type="text" class="form-control js-number" name="saleAmt[]" size="7" maxlength="10"
                           value="<?= $search['saleAmt'][0]; ?>"/>
                    원 ~
                    <input type="text" class="form-control js-number" name="saleAmt[]" size="7" maxlength="10"
                           value="<?= $search['saleAmt'][1]; ?>"/>
                    원
                </td>
            </tr>
            <tr>
                <th>가입승인</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="appFl"
                               value="" <?= $checked['appFl']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="appFl"
                               value="y" <?= $checked['appFl']['y']; ?>/>
                        승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="appFl"
                               value="n" <?= $checked['appFl']['n']; ?>/>
                        미승인
                    </label>
                </td>
                <th>예치금</th>
                <td>
                    <input type="text" class="form-control js-number" name="deposit[]" size="7" maxlength="10"
                           value="<?= $search['deposit'][0]; ?>"/>
                    원 ~
                    <input type="text" class="form-control js-number" name="deposit[]" size="7" maxlength="10"
                           value="<?= $search['deposit'][1]; ?>"/>
                    원
                </td>
            </tr>
            <tr>
                <th>마일리지</th>
                <td>
                    <input type="text" class="form-control js-number" name="mileage[]" size="7" maxlength="10"
                           value="<?= $search['mileage'][0]; ?>"/>
                    원 ~
                    <input type="text" class="form-control js-number" name="mileage[]" size="7" maxlength="10"
                           value="<?= $search['mileage'][1]; ?>"/>
                    원
                </td>
                <th>가입경로</th>
                <td class="contents" colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="entryPath"
                               value="" <?= $checked['entryPath']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="entryPath"
                               value="pc" <?= $checked['entryPath']['pc']; ?>/>
                        PC
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="entryPath"
                               value="mobile" <?= $checked['entryPath']['mobile']; ?>/>
                        모바일
                    </label>
                </td>
            </tr>
            <tr>
                <th>회원가입일</th>
                <td>
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="entryDt[]"
                               value="<?= $search['entryDt'][0]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="entryDt[]"
                               value="<?= $search['entryDt'][1]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </td>
                <th>방문횟수</th>
                <td>
                    <input type="text" class="form-control" name="loginCnt[]" size="7"
                           value="<?= $search['loginCnt'][0]; ?>"/>
                    회 ~
                    <input type="text" class="form-control" name="loginCnt[]" size="7"
                           value="<?= $search['loginCnt'][1]; ?>"/>
                    회
                </td>
            </tr>
            <tr>
                <th>최종로그인일</th>
                <td>
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="lastLoginDt[]"
                               value="<?= $search['lastLoginDt'][0]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="lastLoginDt[]"
                               value="<?= $search['lastLoginDt'][1]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </td>
                <th>성별</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="sexFl"
                               value="" <?= $checked['sexFl']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="sexFl"
                               value="m" <?= $checked['sexFl']['m']; ?>/>
                        남자
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="sexFl"
                               value="w" <?= $checked['sexFl']['w']; ?>/>
                        여자
                    </label>
                </td>
            </tr>
            <tr>
                <th>장기 미로그인</th>
                <td>
                    <input type="text" class="form-control js-number" name="novisit" size="7"
                           value="<?= $search['novisit']; ?>"/>
                    일 이상 로그인하지 않은 회원
                </td>
                <th>연령층</th>
                <td>
                    <?= gd_select_box('age', 'age', gd_array_change_key_value(range(10, 70, 10)), '대', $search['age'], '전체'); ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="under14" <?= $checked['under14']; ?>/>
                        만 14세 미만 회원만 보기
                    </label>
                </td>
            </tr>
            <tr>
                <th>SMS수신동의</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="smsFl"
                               value="" <?= $checked['smsFl']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="smsFl"
                               value="y" <?= $checked['smsFl']['y']; ?>/>
                        수신
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="smsFl"
                               value="n" <?= $checked['smsFl']['n']; ?>/>
                        수신거부
                    </label>
                </td>
                <th>생일</th>
                <td>
                    <div class="input-group">
                        <label>
                            <input type="checkbox" id="birthCheckFl" name="birthFl" value="<?= gd_isset($search['birthFl']); ?>">
                            특정일 검색
                        </label>
                    </div>
                    <?= gd_select_box(
                        'calendarFl', 'calendarFl', [
                        's' => '양력',
                        'l' => '음력',
                    ], null, $search['calendarFl'], '전체'
                    ); ?>
                    <div class="input-group js-datepicker-month">
                        <input type="text" id="birthDt1" class="form-control js-number" placeholder="" name="birthDt[]" value="<?= gd_isset($search['birthDt'][0]); ?>" maxlength="10"/>
                    </div>
                    <div class="input-group js-datepicker firstdate">
                        <input type="hidden" id="yBirthDt1" class="form-control" placeholder="" value="<?= gd_isset($search['birthDt'][0]); ?>"/>
                        <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                    </div>
                    <div class="input-group seconddate"> ~ </div>
                    <div class="input-group js-datepicker-month seconddate">
                        <input type="text" id="birthDt2" class="form-control js-number seconddate" placeholder="" name="birthDt[]" value="<?= gd_isset($search['birthDt'][1]); ?>" maxlength="10"/>
                    </div>
                    <div class="input-group js-datepicker seconddate">
                        <input type="hidden" id="yBirthDt2" class="form-control seconddate" placeholder="" value="<?= gd_isset($search['birthDt'][1]); ?>"/>
                        <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>결혼여부</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="marriFl"
                               value="" <?= $checked['marriFl']['']; ?>/>
                        전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="marriFl"
                               value="n" <?= $checked['marriFl']['n']; ?>/>
                        미혼
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="marriFl"
                               value="y" <?= $checked['marriFl']['y']; ?>/>
                        기혼
                    </label>
                </td>
                <th>결혼기념일</th>
                <td>
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="marriDate[]"
                               value="<?= $search['marriDate'][0]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="marriDate[]"
                               value="<?= $search['marriDate'][1]; ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 펼침</button>
        <div class="notice-danger pull-right" style="margin-top: -10px">정보통신망법에 따라 수신거부한 회원에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과됩니다.</div>
    </div>
    <div class="text-center clear-both pdt30">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
    <div>&nbsp;</div>
</form>
<form id="formList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left">
            검색
            <strong><?= $page->recode['total']; ?></strong>
            명 / 전체
            <strong><?= $page->recode['amount']; ?></strong>
            명
        </div>
        <div class="pull-right">
            <div>
                <select name="sort" class="form-control">
                    <option value="entryDt desc" <?= gd_isset($selected['sort']['entryDt desc']); ?>>회원가입일&darr;</option>
                    <option value="entryDt asc" <?= gd_isset($selected['sort']['entryDt asc']); ?>>회원가입일&uarr;</option>
                    <option value="lastLoginDt desc" <?= gd_isset($selected['sort']['lastLoginDt desc']); ?>>
                        최종로그인&darr;</option>
                    <option value="lastLoginDt asc" <?= gd_isset($selected['sort']['lastLoginDt asc']); ?>>
                        최종로그인&uarr;</option>
                    <option value="loginCnt desc" <?= gd_isset($selected['sort']['loginCnt desc']); ?>>
                        방문횟수&darr;</option>
                    <option value="loginCnt asc" <?= gd_isset($selected['sort']['loginCnt asc']); ?>>방문횟수&uarr;</option>
                    <option value="memNm desc" <?= gd_isset($selected['sort']['memNm desc']); ?>>이름&darr;</option>
                    <option value="memNm asc" <?= gd_isset($selected['sort']['memNm asc']); ?>>이름&uarr;</option>
                    <option value="memId desc" <?= gd_isset($selected['sort']['memId desc']); ?>>아이디&darr;</option>
                    <option value="memId asc" <?= gd_isset($selected['sort']['memId asc']); ?>>아이디&uarr;</option>
                    <option value="mileage desc" <?= gd_isset($selected['sort']['mileage desc']); ?>>마일리지&darr;</option>
                    <option value="mileage asc" <?= gd_isset($selected['sort']['mileage asc']); ?>>마일리지&uarr;</option>
                    <option value="saleAmt desc" <?= gd_isset($selected['sort']['saleAmt desc']); ?>>주문금액&darr;</option>
                    <option value="saleAmt asc" <?= gd_isset($selected['sort']['saleAmt asc']); ?>>주문금액&uarr;</option>
                </select>&nbsp;
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
                <!--<button type="button" class="btn btn-sm btn-default" id="btnGrid">GRID</button>-->
            </div>
        </div>
    </div>

    <table class="table table-rows">
        <colgroup>
            <col class="width-xs"/>
            <?php if ($gGlobal['isUse']) { ?>
                <col/>
            <?php } ?>
            <col class="width-xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input type="checkbox" id="chk_all" class="js-checkall"
                       data-target-name="chk"/>
            </th>
            <th>번호</th>
            <?php if ($gGlobal['isUse']) { ?>
                <th>상점 구분</th>
            <?php } ?>
            <th>아이디/닉네임</th>
            <th>이름</th>
            <th>등급</th>
            <th>주문금액</th>
            <th>마일리지</th>
            <th>예치금</th>
            <th>회원가입일</th>
            <th>최종로그인</th>
            <th>가입승인</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $val) {
                $lastLoginDt = (substr($val['lastLoginDt'], 2, 8) != date('y-m-d')) ? substr($val['lastLoginDt'], 2, 8) : '<span class="">' . substr($val['lastLoginDt'], 11) . '</span>';
                $txtAppFl = ($val['appFl'] == 'y' ? '승인' : '미승인');
                ?>
                <tr class="center" data-member-no="<?= $val['memNo']; ?>">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>"
                               data-appFl="<?= ($val['appFl'] == 'y' ? 'y' : 'n') ?>"
                               data-maillingFl="<?= ($val['maillingFl'] == 'y' ? 'y' : 'n') ?>"/>
                    </td>
                    <td class="font-num">
                        <span class="number js-layer-crm hand"><?= $page->idx--; ?></span>
                    </td>
                    <?php if ($gGlobal['isUse']) { ?>
                        <td class="">
                            <span class="flag flag-16 flag-<?= gd_isset($gGlobal['mallList'][$val['mallSno']]['domainFl'], 'kr'); ?>"></span><?= gd_isset($gGlobal['mallList'][$val['mallSno']]['mallName'], '기준몰'); ?>
                        </td>
                    <?php } ?>
                    <td>
                        <span class="font-eng js-layer-crm hand"><?= $val['memId']; ?></span>
                        <?php if ($val['nickNm']) { ?>
                            <div class="notice-ref notice-sm"><?= $val['nickNm']; ?></div><?php } ?>
                    </td>
                    <td>
                        <span class="js-layer-crm hand"><?= $val['memNm']; ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm hand"><?= gd_isset($groups[$val['groupSno']]); ?></span>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= number_format($val['saleAmt']); ?></span>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= number_format($val['mileage']); ?></span>
                    </td>
                    <td class="font-num">
                        <span class="js-layer-crm hand"><?= number_format($val['deposit']); ?></span>
                    </td>
                    <td class="font-date">
                        <span class="js-layer-crm hand"><?= substr($val['entryDt'], 2, 8); ?></span>
                    </td>
                    <td class="font-date">
                        <span class="js-layer-crm hand"><?= $lastLoginDt; ?></span>
                    </td>
                    <td>
                        <span class="js-layer-crm hand"><?= $txtAppFl; ?></span>
                    </td>
                </tr>
                <?php
            }
        } elseif ($isSkip) {
            echo '<tr><td class="center" colspan="11">검색기능을 이용해주세요.</td></tr>';
        } else {
            echo '<tr><td class="center" colspan="11">검색된 정보가 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>

    <div class="table-btn clearfix">
        <div class="pull-right">
            <button type="button" class="btn btn-xs btn-white" id="btnPointCharge">대량메일 포인트 충전</button>
        </div>
    </div>

    <div class="center"><?= $page->getPage(); ?></div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        var $formSearch = $('#formSearch');
        var $formList = $('#formList');

        member.toggleEventApplyQuery();

        $('#btnSendPowerMail').click(function (e) {
            e.preventDefault();

            layer_close();

            if(!checkApproval()) {
                return false;
            }

            var $sendTarget = $("select[name='sendTarget'] :selected");
            var sendTargetMember = 0;
            var smsReceiveRefuseCount = 0;

            if ($sendTarget.val() == "select") { //회원선택 적용

                var $checked = $("input[name='chk[]']:checked", $formList);

                $.each($checked, function (idx, item) {
                    sendTargetMember++;
                    var $item = $(item);
                    if ($item.data('maillingfl') === 'n') {
                        smsReceiveRefuseCount++;
                    }
                });

                openLayerPopupReceiveRefuse(sendTargetMember + ',' + smsReceiveRefuseCount);

            } else { //검색회원 전체적용

                var dataArray = $('#formSearch').serializeArray();

                //수신거부 회원수 조회
                $.ajax({
                    type: "POST",
                    url: "../member/mail_send_receive_refuse_count.php",
                    data: dataArray,
                    dataType: "json",
                    success: function (data) {
                        sendTargetMember = <?= $page->recode['total']; ?>;
                        smsReceiveRefuseCount = data['count(*)'];
                        openLayerPopupReceiveRefuse(sendTargetMember + ',' + smsReceiveRefuseCount);
                    }
                });
            }

        });


        $('#btnPointCharge', $formList).click(function (e) {
            e.preventDefault();
            power_mail_popup('../member/mail_gate.php?charge=y');
        });

        var $checked = $("input[name='chk[]']:checked", $formList);

        $("input[name='maillingFl']").click(function (e) {
            if (this.value != 'y') {
                alert("검색 시 메일수신거부 회원이 포함됩니다. 정보통신망법에 따라 수신거부한 회원에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과되므로 유의하시기 바랍니다.");
            }
        });

        $('select[name=\'sort\']').change({targetForm: '#formSearch'}, member.page_sort);

        $('.js-datepicker').datetimepicker().on('dp.change', function(){
            if($(this).prop('class') == 'input-group js-datepicker firstdate') {
                $('#birthDt1').val($('#yBirthDt1').val());
            } else if($(this).prop('class') == 'input-group js-datepicker seconddate') {
                $('#birthDt2').val($('#yBirthDt2').val());
            }
        })

        $('#birthCheckFl').on('click', function(){
            if($(this).is(":checked")) {
                $(this).val('y');
                $('.seconddate').hide();
                $('.seconddate').val('');
            } else {
                $(this).val('n');
                $('.seconddate').show();
            }
        })

        $('input[name=\'birthDt[]\']').on('change', function(){
            if($(this).val().substr(0, 1) == '-') {
                var today = new Date();
                var month = today.getMonth() + 1;
                var day = today.getDate();

                if (month < 10)  month = '0' + month;
                if (day < 10)    day = '0' + day;

                $(this).val(today.getFullYear() + '-' + month + '-' + day);
            } else {
                var date = $(this).val().replace(/-/g, '');
                if(date.length > 0) {
                    date.length <= 4 ? $(this).val(getMonthFormatDate(date)) : $(this).val(getYearFormatDate(date))
                }
            }
        })

        $('input[type=submit]').on('click', function(){
            if(($('#birthDt1').val().length == 5 && $('#birthDt2').val().length == 10)
                || ($('#birthDt1').val().length == 10 && $('#birthDt2').val().length == 5)){
                dialog_alert('생일 검색 기간을 다시 확인해주세요.', '정보');
                return false;
            }
        })

        checkBirthFl();

    });

    function checkApproval() {
        var is_checked = false;
        $.ajax({
            url: '../member/mail_ps.php',
            async: false,
            type: 'post',
            data: {mode: 'checkApproval'},
            success: function(ret) {
                if(ret == true) {
                   is_checked = true;
                } else {
                    $.post('/share/layer_pmail_approval.php', null, function (data) {
                        layer_popup(data, '안내');
                    });
                }
            }
        })
        return is_checked;
    }

    function getMonthFormatDate(date) {
        var today = new Date();
        var month = date.substring(0, 2);
        var day = date.substring(month.length, date.length);
        var maxDate = 31;

        if(month.length == 1) month = '0' + month;
        if(day.length == 1) day = '0' + day;
        if(date.length <= 2) day = '01';

        if(month == 4 || month == 6 || month == 9 || month == 11) {
            maxDate = 30;
        } else if(month == 2) {
            maxDate = 29;
        }

        if(month > 12) {
            today.getMonth() >= 9 ? (month = today.getMonth() + 1) : (month = '0' + (today.getMonth() + 1))
            today.getDate() >= 10 ? day = today.getDate() : day = '0' + today.getDate()
        } else if(day > maxDate) {
            day = maxDate;
        }

        if (month == '00')  month = '01';
        if (day == '00')    day = '01';

        return month + '-' + day;
    }

    function getYearFormatDate(date) {
        var today = new Date();
        var year = date.substring(0, 4);
        var month = date.substr(year.length, 2);
        var day = date.substring((year.length + month.length), date.length);
        var maxDate = 31;

        if(month.length == 1) month = '0' + month;
        if(day.length == 1) day = '0' + day;
        if(date.length <= 6) day = '01';

        if(month == 4 || month == 6 || month == 9 || month == 11) {
            maxDate = 30;
        } else if(month == 2) {
            ((year%4 == 0 && year%100 != 0) || year%400 == 0) ? maxDate = 29 : maxDate = 28
        }

        if(month > 12) {
            year = today.getFullYear();
            today.getMonth() >= 9 ? (month = today.getMonth() + 1) : (month = '0' + (today.getMonth() + 1))
            today.getDate() >= 10 ? day = today.getDate() : day = '0' + today.getDate()
        } else if(day > maxDate) {
            day = maxDate;
        }

        if (year == '0000') year = '0001';
        if (month == '00')  month = '01';
        if (day == '00')    day = '01';

        return year + '-' + month + '-' + day;
    }

    function checkBirthFl() {
        if($('input:checkbox[name="birthFl"]').val() == 'y') {
            $('#birthCheckFl').prop('checked', true);
            $('.seconddate').hide();
            $('.seconddate').val('');
        }
    }

    function openLayerPopupReceiveRefuse(refuseMemberCount) {

        var dialogShow = '<?=!isset($checked['maillingFl']['y'])?>'; //메일수신검색이면 수신거부창 안뜨게
        var arr = refuseMemberCount.split(",");

        var sendTargetCount = parseInt(arr[0]);
        var receiveRefuseCount = parseInt(arr[1]);

        if (dialogShow && receiveRefuseCount > 0) {
            top.BootstrapDialog.show({
                title: '수신거부회원 포함 안내',
                message: '발송대상 ' + sendTargetCount + '명 중 수신거부회원이 ' + receiveRefuseCount + '명 포함되어 있습니다. 수신거부회원을 제외하고 메일을 발송하시겠습니까? 수신거부한 회원에게 광고성 정보를 발송하는 경우 과태료가 부과될 수 있습니다.',
                buttons: [
                    {
                        label: "제외하고 발송", cssClass: "btn-red", action: function (dialogRef) {
                        if (sendTargetCount <= receiveRefuseCount) {
                            alert("전송가능한 회원이 없습니다.");
                        } else {
                            sendAmail('y');
                        }
                        dialogRef.close();
                    }
                    },
                    {
                        label: "포함하고 발송", cssClass: "btn-danger", action: function (dialogRef) {
                        sendAmail('n');
                        dialogRef.close();
                    }
                    },
                    {
                        label: '발송 취소', cssClass: "btn-gray", action: function (dialogRef) {
                        dialogRef.close();
                    }
                    }
                ]
            });

        } else {
            sendAmail('n');
        }

    }

    function sendAmail(rejectMailingFl) {
        var $targetForm = null;
        var $sendTarget = $("select[name='sendTarget'] :selected");

        $("#rejectMailingFl").val(rejectMailingFl);

        switch ($sendTarget.val()) {
            case "select":
            {

                $targetForm = $("form#formList");
                var $checked = $("input[name='chk[]']:checked", $targetForm);

                if ($checked.length == 0) {
                    alert("받을 사람을 선택해 주세요.");
                    return false;
                }

                if (_.isUndefined(rejectMailingFl) === false && rejectMailingFl == 'y') {
                    $.each($checked, function (idx, item) {
                        var $item = $(item);
                        if ($item.data('maillingfl') === 'n') {
                            $item.prop('checked', false);
                        }
                    });
                }

                var $checked = $("input[name='chk[]']:checked", $targetForm);
                if ($checked.length == 0) {
                    alert("전송가능한 회원이 없습니다.");
                    return false;
                }
                $targetForm.append("<input type='hidden' name='sendTarget' value='select' />");
                $targetForm.append("<input type='hidden' name='rejectMailingFl' value='" + rejectMailingFl + "' />");
                break;
            }
            case "query":
            {
                var $formList = $("form#formList");

                if ($("tr", $formList).length - 1 == 0) {
                    alert("검색된 회원이 없습니다.");
                    return false;
                }

                $targetForm = $("#formSearch");
                break;
            }
        }

        var oriAction = $targetForm.attr("action");
        var oriMethod = $targetForm.attr("method");
        var oriTarget = $targetForm.attr("target");

        power_mail_popup('../member/mail_gate.php?start=y');

        $targetForm.attr("action", "mail_gate_bridge.php");
        $targetForm.attr("method", "post");
        $targetForm.attr("target", "powerMail");

        $targetForm.submit();

        $targetForm.attr("action", oriAction);
        $targetForm.attr("method", oriMethod);
        $targetForm.attr("target", oriTarget);
    }
</script>
