<div class="search-detail-box form-inline">
    <input type="hidden" name="detailSearch" value="<?= gd_isset($search['detailSearch']); ?>"/>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md">
            <col class="width-3xl">
            <col class="width-md">
            <col class="width-3xl">
        </colgroup>
        <tbody>
        <?php if ($gGlobal['isUse'] && !gd_isset($disableGlobalSearch, false)) { ?>
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
            <th>검색어</th>
            <td colspan="3">
                <?= gd_select_box('key', 'key', $combineSearch, null, gd_isset($search['key']), null, null, 'form-control'); ?>
                <input type="text" name="keyword" value="<?= gd_isset($search['keyword']); ?>"
                       class="form-control"/>
            </td>
        </tr>
        <tr>
            <th>회원등급</th>
            <td>
                <?= gd_select_box_by_group_list(gd_isset($search['groupSno']), '등급'); ?>
            </td>
            <th>회원구분</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="memberFl"
                           value="" <?= gd_isset($checked['memberFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="memberFl"
                           value="personal" <?= gd_isset($checked['memberFl']['personal']); ?>/>
                    개인회원
                </label>
                <label class="radio-inline">
                    <input type="radio" name="memberFl"
                           value="business" <?= gd_isset($checked['memberFl']['business']); ?>/>
                    사업자회원
                </label>
            </td>
        </tr>
        <tr>
            <th>가입승인</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="appFl"
                           value="" <?= gd_isset($checked['appFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="appFl"
                           value="y" <?= gd_isset($checked['appFl']['y']); ?>/>
                    승인
                </label>
                <label class="radio-inline">
                    <input type="radio" name="appFl"
                           value="n" <?= gd_isset($checked['appFl']['n']); ?>/>
                    미승인
                </label>
            </td>
            <th>회원가입일</th>
            <td>
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="entryDt[]"
                           value="<?= gd_isset($search['entryDt'][0]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
                ~
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="entryDt[]"
                           value="<?= gd_isset($search['entryDt'][1]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
            </td>
        </tr>
        </tbody>
        <tbody class="js-search-detail">
        <tr>
            <th>방문횟수</th>
            <td>
                <input type="text" class="form-control" name="loginCnt[]" size="7"
                       value="<?= gd_isset($search['loginCnt'][0]); ?>"/>
                회 ~
                <input type="text" class="form-control" name="loginCnt[]" size="7"
                       value="<?= gd_isset($search['loginCnt'][1]); ?>"/>
                회
            </td>
            <th>최종로그인일</th>
            <td>
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="lastLoginDt[]"
                           value="<?= gd_isset($search['lastLoginDt'][0]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
                ~
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="lastLoginDt[]"
                           value="<?= gd_isset($search['lastLoginDt'][1]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <th>마일리지</th>
            <td>
                <input type="text" class="form-control js-number" name="mileage[]" size="7"
                       value="<?= gd_isset($search['mileage'][0]); ?>"/>
                원 ~
                <input type="text" class="form-control js-number" name="mileage[]" size="7"
                       value="<?= gd_isset($search['mileage'][1]); ?>"/>
                원
            </td>
            <th>예치금</th>
            <td>
                <input type="text" class="form-control js-number" name="deposit[]" size="7"
                       value="<?= gd_isset($search['deposit'][0]); ?>"/>
                원 ~
                <input type="text" class="form-control js-number" name="deposit[]" size="7"
                       value="<?= gd_isset($search['deposit'][1]); ?>"/>
                원
            </td>
        </tr>
        <tr>
            <th>상품주문건수</th>
            <td>
                <input type="text" class="form-control js-number" name="saleCnt[]" size="7"
                       value="<?= gd_isset($search['saleCnt'][0]); ?>"/>
                건 ~
                <input type="text" class="form-control js-number" name="saleCnt[]" size="7"
                       value="<?= gd_isset($search['saleCnt'][1]); ?>"/>
                건
            </td>
            <th>주문금액</th>
            <td>
                <input type="text" class="form-control js-number" name="saleAmt[]" size="7"
                       value="<?= gd_isset($search['saleAmt'][0]); ?>"/>
                원 ~
                <input type="text" class="form-control js-number" name="saleAmt[]" size="7"
                       value="<?= gd_isset($search['saleAmt'][1]); ?>"/>
                원
            </td>
        </tr>
        <tr>
            <th>SMS수신동의</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="smsFl"
                           value="" <?= gd_isset($checked['smsFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="smsFl"
                           value="y" <?= gd_isset($checked['smsFl']['y']); ?>/>
                    수신
                </label>
                <label class="radio-inline">
                    <input type="radio" name="smsFl"
                           value="n" <?= gd_isset($checked['smsFl']['n']); ?>/>
                    수신거부
                </label>
            </td>
            <th>메일수신동의</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="maillingFl"
                           value="" <?= gd_isset($checked['maillingFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="maillingFl"
                           value="y" <?= gd_isset($checked['maillingFl']['y']); ?>/>
                    수신
                </label>
                <label class="radio-inline">
                    <input type="radio" name="maillingFl"
                           value="n" <?= gd_isset($checked['maillingFl']['n']); ?>/>
                    수신거부
                </label>
            </td>
        </tr>
        <tr>
            <th>가입경로</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="entryPath"
                           value="" <?= gd_isset($checked['entryPath']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="entryPath"
                           value="pc" <?= gd_isset($checked['entryPath']['pc']); ?>/>
                    PC
                </label>
                <label class="radio-inline">
                    <input type="radio" name="entryPath"
                           value="mobile" <?= gd_isset($checked['entryPath']['mobile']); ?>/>
                    모바일
                </label>
            </td>
            <th>장기 미로그인</th>
            <td>
                <input type="text" class="form-control js-number" name="novisit" size="7"
                       value="<?= gd_isset($search['novisit']); ?>"/>
                일 이상 로그인하지 않은 회원
            </td>
        </tr>
        <tr>
            <th>성별</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="sexFl"
                           value="" <?= gd_isset($checked['sexFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="sexFl"
                           value="m" <?= gd_isset($checked['sexFl']['m']); ?>/>
                    남자
                </label>
                <label class="radio-inline">
                    <input type="radio" name="sexFl"
                           value="w" <?= gd_isset($checked['sexFl']['w']); ?>/>
                    여자
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
                ], null, gd_isset($search['calendarFl']), '전체'
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
                           value="" <?= gd_isset($checked['marriFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="marriFl"
                           value="n" <?= gd_isset($checked['marriFl']['n']); ?>/>
                    미혼
                </label>
                <label class="radio-inline">
                    <input type="radio" name="marriFl"
                           value="y" <?= gd_isset($checked['marriFl']['y']); ?>/>
                    기혼
                </label>
            </td>
            <th>결혼기념일</th>
            <td>
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="marriDate[]"
                           value="<?= gd_isset($search['marriDate'][0]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
                ~
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="marriDate[]"
                           value="<?= gd_isset($search['marriDate'][1]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
            </td>
        </tr>
        <tr>
            <th>개인정보유효기간</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl"
                           value=""<?= gd_isset($checked['expirationFl']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl"
                           value="1" <?= gd_isset($checked['expirationFl'][1]); ?>/>
                    1년
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl"
                           value="3" <?= gd_isset($checked['expirationFl'][3]); ?>/>
                    3년
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl"
                           value="5" <?= gd_isset($checked['expirationFl'][5]); ?>/>
                    5년
                </label>
                <label class="radio-inline">
                    <input type="radio" name="expirationFl"  id="expirationNone"
                           value="999" <?= gd_isset($checked['expirationFl'][999]); ?>/>
                    탈퇴 시
                </label>
            </td>
            <th>휴면 전환 예정 회원</th>
            <td>
                <label class="checkbox-inline mgl10">
                    <input type="checkbox" id="dormantMemberExpected" name="dormantMemberExpected" value="<?= gd_isset($search['dormantMemberExpected']); ?>">
                    휴면 전환
                    <?= gd_select_box('expirationDay', 'expirationDay', [7 => 7,30=> 30, 60=>60],null,gd_isset($search['expirationDay']),null,'disabled="true"',null); ?>일 전 회원
                </label>
            </td>
        </tr>
        <tr>
            <th>연결계정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="connectSns"
                           value="" <?= gd_isset($checked['connectSns']['']); ?>/>
                    전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="connectSns"
                           value="payco" <?= gd_isset($checked['connectSns']['payco']); ?>/>
                    페이코
                </label>
                <label class="radio-inline">
                    <input type="radio" name="connectSns"
                           value="facebook" <?= gd_isset($checked['connectSns']['facebook']); ?>/>
                    페이스북
                </label>
                <label class="radio-inline">
                    <input type="radio" name="connectSns"
                           value="naver" <?= gd_isset($checked['connectSns']['naver']); ?>/>
                    네이버
                </label>
                <label class="radio-inline">
                    <input type="radio" name="connectSns"
                           value="kakao" <?= gd_isset($checked['connectSns']['kakao']); ?>/>
                    카카오
                </label>
                <label class="radio-inline">
                    <input type="radio" name="connectSns"
                           value="wonder" <?= gd_isset($checked['connectSns']['wonder']); ?>/>
                    위메프
                </label>
            </td>
            <th>휴면해제일</th>
            <td>
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="sleepWakeDt[]"
                           value="<?= gd_isset($search['sleepWakeDt'][0]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
                ~
                <div class="input-group js-datepicker">
                    <input type="text" class="form-control" placeholder="" name="sleepWakeDt[]"
                           value="<?= gd_isset($search['sleepWakeDt'][1]); ?>"/>
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색
        <span>펼침</span>
    </button>
</div>
<div class="table-btn">
    <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
</div>

<script type="text/javascript">
    var detailSearch = {
        $age: $('#age', '.search-detail-box'),
        $under14: $(':checkbox[name=under14]', '.search-detail-box')
    };
    detailSearch.$under14.click(function () {
        detailSearch.$age.val('');
    });
    detailSearch.$age.change(function () {
        if (detailSearch.$age.val() > 0) {
            detailSearch.$under14.prop('checked', false);
        }
    });
    function checkDormant() {
        if($('input:checkbox[name="dormantMemberExpected"]').val() == 'y') {
            $('#dormantMemberExpected').prop('checked', true);
            $('input[name="novisit"]').attr('disabled', true);
            $("#expirationDay").attr('disabled', false);
        }
    }
    $(document).on('click', 'input[name=dormantMemberExpected]', function(e){
        if($(this).prop("checked")) {
            $(this).val('y');
            $('input[name="novisit"]').attr('disabled',true);
            if($('input[name="novisit"]').val() != '') { //장기 미로그인 값이 존재하면 drop
                $('input[name="novisit"]').val('');
            }
            $("#expirationDay").attr('disabled', false);
            $('input[id="expirationNone"]').attr('disabled',true);
        } else {
            $(this).val('n');
            $('input[name="novisit"]').attr('disabled',false);
            $("#expirationDay").attr('disabled', true);
            $('input[id="expirationNone"]').attr('disabled',false);
        }
    });
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

    $(document).ready(function () {

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
        checkDormant();
    })
</script>
