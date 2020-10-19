<?php
/**
 * @date        2017-01-02
 * @author      yjwee
 * @usage       Asset/Admin/share/popup_add_member2.php
 * @description 해당 스킨은 구버전입니다. usage 의 스킨을 사용하세요. 해당 스킨은 글로벌 기능을 지원합니다.
 */
?>
<style>
    .layout-blank #content .col-xs-12 { padding:0; height:100%; }

    .table-rows > tbody > tr > td { word-break:break-all; padding:15px 0 }

    .table > thead > tr > th:nth-child(1) { width:35px; }

    .table > thead > tr > th:nth-child(2) { width:39px; }

    .table > thead > tr > th:nth-child(3) { width:133px; }

    .table > thead > tr > th:nth-child(4) { width:90px; }

    .table > thead > tr > th:nth-child(5) { width:90px; }

    .table > thead > tr > th:nth-child(6) { width:139px; }

    .table > thead > tr > th:nth-child(7) { width:123px; }
</style>
<div style="width: 630px">
    <form id="frmSearchBase" method="get" class="content-form js-search-form js-form-enter-submit">
        <input type="hidden" name="sendMode" value="<?= $search['sendMode']; ?>"/>
        <input type="hidden" name="mode" value="<?= $search['mode']; ?>"/>
        <input type="hidden" name="sort" value="<?= $search['sort']; ?>"/>
        <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10); ?>"/>

        <div class="search-detail-box form-inline">
            <input type="hidden" name="detailSearch" value="<?= gd_isset($search['detailSearch']); ?>"/>
            <p class="table-sub-title notice-danger mgl5">* 정보통신망법에 따라 수신거부한 회원에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과됩니다.</p>

            <table class="table table-cols">
                <colgroup>
                    <col class="width-sm">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th>검색어</th>
                    <td class="form-inline" colspan="3">
                        <?= gd_select_box('key', 'key', $combineSearch, null, gd_isset($search['key']), null, null, 'form-control input-sm'); ?>
                        <input type="text" name="keyword" value="<?= gd_isset($search['keyword']); ?>" class="form-control"/>
                    </td>
                </tr>
                <tr>
                    <th>메일수신동의</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="maillingFl" value="" <?= gd_isset($checked['maillingFl']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="maillingFl" value="y" <?= gd_isset($checked['maillingFl']['y']); ?>/>
                            수신
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="maillingFl" value="n" <?= gd_isset($checked['maillingFl']['n']); ?>/>
                            수신거부
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>SMS수신동의</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="smsFl" value="" <?= gd_isset($checked['smsFl']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="smsFl" value="y" <?= gd_isset($checked['smsFl']['y']); ?>/>
                            수신
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="smsFl" value="n" <?= gd_isset($checked['smsFl']['n']); ?>/>
                            수신거부
                        </label>
                    </td>
                </tr>
                </tbody>
                <tbody class="js-search-detail">
                <tr>
                    <th>회원구분</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="memberFl" value="" <?= gd_isset($checked['memberFl']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="memberFl" value="personal" <?= gd_isset($checked['memberFl']['personal']); ?>/>
                            개인회원
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="memberFl" value="business" <?= gd_isset($checked['memberFl']['business']); ?>/>
                            사업자회원
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>회원등급</th>
                    <td class="form-inline">
                        <?= gd_select_box('groupSno', 'groupSno', $groups, null, $search['groupSno'], '등급'); ?>
                    </td>
                </tr>
                <tr>
                    <th>가입승인</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="appFl" value="" <?= gd_isset($checked['appFl']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="appFl" value="y" <?= gd_isset($checked['appFl']['y']); ?>/>
                            승인
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="appFl" value="n" <?= gd_isset($checked['appFl']['n']); ?>/>
                            미승인
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>구매액</th>
                    <td class="form-inline">
                        <?php echo gd_currency_symbol(); ?>
                        <input type="text" class="form-control js-number width-xs" name="saleAmt[]" size="5" value="<?= gd_isset($search['saleAmt'][0]); ?>"/><?php echo gd_currency_string(); ?> ~
                        <?php echo gd_currency_symbol(); ?>
                        <input type="text" class="form-control js-number width-xs" name="saleAmt[]" size="5" value="<?= gd_isset($search['saleAmt'][1]); ?>"/><?php echo gd_currency_string(); ?>
                    </td>
                </tr>
                <tr>
                    <th>마일리지</th>
                    <td class="form-inline">
                        <input type="text" class="form-control js-number width-xs" name="mileage[]" size="5" value="<?= gd_isset($search['mileage'][0]); ?>"/><?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> ~
                        <input type="text" class="form-control js-number width-xs" name="mileage[]" size="5" value="<?= gd_isset($search['mileage'][1]); ?>"/><?php echo Globals::get('gSite.member.mileageBasic.unit'); ?>
                    </td>
                </tr>
                <tr>
                    <th>예치금</th>
                    <td class="form-inline">
                        <input type="text" class="form-control js-number width-xs" name="deposit[]" size="5" value="<?= gd_isset($search['deposit'][0]); ?>"/><?php echo Globals::get('gSite.member.depositConfig.unit'); ?> ~
                        <input type="text" class="form-control js-number width-xs" name="deposit[]" size="5" value="<?= gd_isset($search['deposit'][1]); ?>"/><?php echo Globals::get('gSite.member.depositConfig.unit'); ?>
                    </td>
                </tr>
                <tr>
                    <th>회원가입일</th>
                    <td class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" placeholder="" name="entryDt[]" value="<?= gd_isset($search['entryDt'][0]); ?>" size="7"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" placeholder="" name="entryDt[]" value="<?= gd_isset($search['entryDt'][1]); ?>" size="7"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>가입경로</th>
                    <td class="form-inline contents" colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="entryPath" value="" <?= gd_isset($checked['entryPath']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="entryPath" value="pc" <?= gd_isset($checked['entryPath']['pc']); ?>/>
                            PC
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="entryPath" value="mobile" <?= gd_isset($checked['entryPath']['mobile']); ?>/>
                            모바일
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>최종로그인일</th>
                    <td class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" placeholder="" name="lastLoginDt[]" value="<?= gd_isset($search['lastLoginDt'][0]); ?>" size="7"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" placeholder="" name="lastLoginDt[]" value="<?= gd_isset($search['lastLoginDt'][1]); ?>" size="7"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>방문횟수</th>
                    <td class="form-inline">
                        <input type="text" class="form-control" name="loginCnt[]" size="7" value="<?= gd_isset($search['loginCnt'][0]); ?>"/>
                        회 ~
                        <input type="text" class="form-control" name="loginCnt[]" size="7" value="<?= gd_isset($search['loginCnt'][1]); ?>"/>
                        회
                    </td>
                </tr>
                <tr>
                    <th>장기 미로그인</th>
                    <td class="form-inline">
                        <input type="text" class="form-control js-number" name="novisit" size="3" value="<?= gd_isset($search['novisit']); ?>"/>
                        일 이상 로그인하지 않은 회원
                    </td>
                </tr>
                <tr>
                    <th>성별</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="sexFl" value="" <?= gd_isset($checked['sexFl']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sexFl" value="m" <?= gd_isset($checked['sexFl']['m']); ?>/>
                            남자
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="sexFl" value="w" <?= gd_isset($checked['sexFl']['w']); ?>/>
                            여자
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>연령층</th>
                    <td class="form-inline">
                        <?= gd_select_box('age', 'age', gd_array_change_key_value(range(10, 70, 10)), '대', $search['age'], '전체'); ?>
                        <label class="radio-inline">
                            <input type="checkbox" name="under14" <?= gd_isset($checked['under14']); ?>/>
                            만 14세 미만 회원만 보기
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>생일</th>
                    <td class="form-inline">
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
                            <input type="text" id="birthDt1" class="form-control js-number" placeholder="" name="birthDt[]" value="<?= gd_isset($search['birthDt'][0]); ?>" size="9" maxlength="10"/>
                        </div>
                        <div class="input-group js-datepicker firstdate">
                            <input type="hidden" id="yBirthDt1" class="form-control width-xs" placeholder="" value="<?= gd_isset($search['birthDt'][0]); ?>"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                        <div class="input-group seconddate"> ~ </div>
                        <div class="input-group js-datepicker-month seconddate">
                            <input type="text" id="birthDt2" class="form-control js-number seconddate" placeholder="" name="birthDt[]" value="<?= gd_isset($search['birthDt'][1]); ?>" maxlength="10"/>
                        </div>
                        <div class="input-group js-datepicker seconddate">
                            <input type="hidden" id="yBirthDt2" class="form-control width-xs seconddate" placeholder="" value="<?= gd_isset($search['birthDt'][1]); ?>"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>결혼여부</th>
                    <td>
                        <label class="radio-inline">
                            <input type="radio" name="marriFl" value="" <?= gd_isset($checked['marriFl']['']); ?>/>
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="marriFl" value="n" <?= gd_isset($checked['marriFl']['n']); ?>/>
                            미혼
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="marriFl" value="y" <?= gd_isset($checked['marriFl']['y']); ?>/>
                            기혼
                        </label>
                    </td>
                </tr>
                <tr>
                    <th>결혼기념일</th>
                    <td class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" placeholder="" name="marriDate[]" value="<?= gd_isset($search['marriDate'][0]); ?>" size="7"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" placeholder="" name="marriDate[]" value="<?= gd_isset($search['marriDate'][1]); ?>" size="7"/>
                            <span class="input-group-addon">
                                <span class="btn-icon-calendar"></span>
                            </span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>연결계정</th>
                    <td>
                        <label>
                            <input type="radio" name="connectSns"
                                   value="" <?= gd_isset($checked['connectSns']['']); ?>/>
                            전체
                        </label>
                        <label>
                            <input type="radio" name="connectSns"
                                   value="payco" <?= gd_isset($checked['connectSns']['payco']); ?>/>
                            페이코
                        </label>
                        <label>
                            <input type="radio" name="connectSns"
                                   value="facebook" <?= gd_isset($checked['connectSns']['facebook']); ?>/>
                            페이스북
                        </label>
                        <label>
                            <input type="radio" name="connectSns"
                                   value="naver" <?= gd_isset($checked['connectSns']['naver']); ?>/>
                            네이버
                        </label>
                        <label>
                            <input type="radio" name="connectSns"
                                   value="kakao" <?= gd_isset($checked['connectSns']['kakao']); ?>/>
                            카카오
                        </label>
                        <label>
                            <input type="radio" name="connectSns"
                                   value="wonder" <?= gd_isset($checked['connectSns']['wonder']); ?>/>
                            위메프
                        </label>
                    </td>
                </tr>
                </tbody>
            </table>
            <button type="button" class="btn btn-sm btn-link js-search-toggle">상세검색
                <span>펼침</span>
            </button>
        </div>
        <div class="table-btn" style="padding:20px 0 30px 0">
            <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
        </div>
    </form>
    <form id="formSearchList" action="" method="get" target="ifrmProcess">
        <div class="table-action mgb0 ">
            <div class="pull-left">
                <button type="button" class="btn btn-white" id="btnAddSearchResult">검색회원 전체 추가</button>
            </div>
            <div class="pull-right form-inline">
                <div>
                    <select name="sort" class="form-control ">
                        <option value="entryDt desc" <?= gd_isset($selected['sort']['entryDt desc']); ?>>회원가입일&darr;</option>
                        <option value="entryDt asc" <?= gd_isset($selected['sort']['entryDt asc']); ?>>회원가입일&uarr;</option>
                        <option value="lastLoginDt desc" <?= gd_isset($selected['sort']['lastLoginDt desc']); ?>>최종로그인&darr;</option>
                        <option value="lastLoginDt asc" <?= gd_isset($selected['sort']['lastLoginDt asc']); ?>>최종로그인&uarr;</option>
                        <option value="loginCnt desc" <?= gd_isset($selected['sort']['loginCnt desc']); ?>>방문횟수&darr;</option>
                        <option value="loginCnt asc" <?= gd_isset($selected['sort']['loginCnt asc']); ?>>방문횟수&uarr;</option>
                        <option value="memNm desc" <?= gd_isset($selected['sort']['memNm desc']); ?>>이름&darr;</option>
                        <option value="memNm asc" <?= gd_isset($selected['sort']['memNm asc']); ?>>이름&uarr;</option>
                        <option value="memId desc" <?= gd_isset($selected['sort']['memId desc']); ?>>아이디&darr;</option>
                        <option value="memId asc" <?= gd_isset($selected['sort']['memId asc']); ?>>아이디&uarr;</option>
                        <option value="mileage desc" <?= gd_isset($selected['sort']['mileage desc']); ?>>마일리지&darr;</option>
                        <option value="mileage asc" <?= gd_isset($selected['sort']['mileage asc']); ?>>마일리지&uarr;</option>
                        <option value="saleAmt desc" <?= gd_isset($selected['sort']['saleAmt desc']); ?>>구매금액&darr;</option>
                        <option value="saleAmt asc" <?= gd_isset($selected['sort']['saleAmt asc']); ?>>구매금액&uarr;</option>
                    </select>&nbsp;
                    <?php echo gd_select_box_by_page_view_count(Request::get()->get('pageNum')) ?>
                </div>
            </div>
        </div>

        <table class="table table-rows">
            <thead>
            <tr>
                <th>
                    <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
                </th>
                <th>번호</th>
                <th>아이디/닉네임</th>
                <th>이름</th>
                <th>등급</th>
                <th>이메일</th>
                <th>휴대폰번호</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                foreach ($data as $val) {
                    $arrReceiveFl = [
                        'y' => '수신',
                        'n' => '수신거부',
                    ];
                    ?>
                    <tr class="text-center" data-member-no="<?= $val['memNo']; ?>">
                        <td>
                            <input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>" data-appFl="<?= ($val['appFl'] == 'y' ? 'y' : 'n') ?>" data-maillingFl="<?= ($val['maillingFl'] == 'y' ? 'y' : 'n') ?>"
                                   data-smsFl="<?= ($val['smsFl'] == 'y' ? 'y' : 'n') ?>"/>
                        </td>
                        <td>
                            <span class="font-num js-layer-crm hand"><?= $page->idx--; ?></span>
                        </td>
                        <td>
                            <span class="font-eng js-layer-crm hand"><?= $val['memId']; ?></span>
                            <?= gd_get_third_party_icon_web_path($val['snsTypeFl']); ?>
                            <?php if ($val['nickNm']) { ?>
                                <div class="notice-ref notice-sm"><?= $val['nickNm']; ?></div><?php } ?>
                        </td>
                        <td>
                            <span class="js-layer-crm hand"><?= $val['memNm']; ?></span>
                        </td>
                        <td>
                            <span class="js-layer-crm hand"><?= $groups[$val['groupSno']]; ?></span>
                        </td>
                        <td>
                            <span class="font-eng js-layer-crm hand"><?= $val['email']; ?></span>
                            <div class="notice-ref notice-sm">(<?= $arrReceiveFl[$val['maillingFl']]; ?>)</div>
                        </td>
                        <td>
                            <span class="font-num js-layer-crm hand"><?= $val['cellPhone']; ?></span>
                            <div class="notice-ref notice-sm">(<?= $arrReceiveFl[$val['smsFl']]; ?>)</div>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td class="center" colspan="7">검색된 정보가 없습니다.</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <div class="center"><?= $page->getPage(); ?></div>
    </form>
</div>
<script type="text/javascript">

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
        var $formList = $('#formSearchList');
        var $formSearch = $('#frmSearchBase');

        // 정렬&출력수
        $('select[name=\'sort\']', $formList).change({targetForm: '#frmSearchBase'}, member.page_sort);
        $('select[name=\'pageNum\']', $formList).change({targetForm: '#frmSearchBase'}, member.page_number);

        // 수신거부 선택시 안내 메시지 출력
        $(':radio[name="maillingFl"],:radio[name="smsFl"]', $formSearch).change(function (e) {
            e.preventDefault();

            if ($(e.target).val() !== 'y') {
                member.dialog({message: "검색 시 수신거부 회원이 포함됩니다. 정보통신망법에 따라 수신거부한 회원에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과되므로 유의하시기 바랍니다."});
            }
        });

        // 추가 버튼 클릭시
        $('#btnAddSearchResult', $formList).click(function (e) {
            e.preventDefault();
            var data = $formSearch.serializeArray();
            data.push({name: "mode", value: "addSearchResult"});
            ajax_with_layer('../member/mail_ps.php', data, function (result, textStatus, jqXHR) {
                var $tbody = $('#formSelectList', top.document).find('tbody');
                var $checkbox = $tbody.find(':checkbox');
                var html = [];
                var addCount = result.length;

                $.each(result, function (idx, item) {
                    if ($tbody.find(':checkbox[value=' + item.memNo + ']').length > 0) {
                        addCount--;
                    }
                });
                var total = addCount + $checkbox.length;

                result.reverse();

                $.each(result, function (idx, item) {
                    if ($tbody.find(':checkbox[value=' + item.memNo + ']').length == 0) {
                        html[idx] = '<tr class="center">';
                        html[idx] += '<td><input type="checkbox" name="selectChk[]" value="' + item.memNo + '" data-maillingFl="' + item.maillingFl + '" data-smsFl="' + item.smsFl + '" /></td>';
                        html[idx] += '<td>' + total-- + '</td>';
                        var memId = '<span>' + item.memId + '</span>';
                        if (_.isString(item.nickNm) === true && _.isEmpty(item.nickNm) === false) {
                            memId += '<div class="notice-ref notice-sm">' + item.nickNm + '</div>';
                        }
                        html[idx] += '<td><span class="font-eng">' + memId + '</span></td>';
                        html[idx] += '<td>' + item.memNm + '</td>';
                        html[idx] += '<td>' + item.groupNm + '</td>';
                        html[idx] += '<td>' + item.email + '</td>';
                        html[idx] += '<td>' + item.cellPhone + '</td>';
                        html[idx] += '</tr>';
                    }
                });

                $tbody.prepend(html.join(''));
                parent.backgroundShow();
            });
        });

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

        $('.js-search-button').on('click', function(){
            if(($('#birthDt1').val().length == 5 && $('#birthDt2').val().length == 10)
                || ($('#birthDt1').val().length == 10 && $('#birthDt2').val().length == 5)){
                dialog_alert('생일 검색 기간을 다시 확인해주세요.', '정보');
                return false;
            }
        })

        checkBirthFl();

    });
</script>
