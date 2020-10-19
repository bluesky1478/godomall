<div class="container-fluid search-member-popup">
    <div class="row">
        <div class="col-xs-6">
            <span class="column-head">회원선택</span>
        </div>
        <div class="col-xs-6">
            <span class="column-head">선택 회원 리스트</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-13">
            <div class="row">
                <div class="row-height">
                    <div class="col-xs-6 col-height col-top pdl0 pdr0">
                        <div class="inside-full-height">
                            <div class="table-scroll">
                                <form role="form" id="frmSearchBase" method="get" class="content-form js-search-form">
                                    <input type="hidden" name="sendMode" value="<?= $search['sendMode']; ?>"/>
                                    <input type="hidden" name="mode" value="<?= $search['mode']; ?>"/>
                                    <input type="hidden" name="sort" value="<?= $search['sort']; ?>"/>
                                    <input type="hidden" name="pageNum" value="<?= $search['pageNum']; ?>"/>

                                    <div class="search-detail-box form-inline">
                                        <input type="hidden" name="detailSearch" value="<?= gd_isset($search['detailSearch']); ?>"/>
                                        <p class="table-sub-title notice-danger mgl5">* 정보통신망법에 따라 수신거부한 회원에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과됩니다.</p>
                                        <table class="table table-cols">
                                            <colgroup>
                                                <col class="width-sm">
                                                <col>
                                            </colgroup>
                                            <tbody>
                                            <?php if ($gGlobal['isUse'] && $sendMode == 'mail') { ?>
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
                                                <th>주문금액</th>
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
                                                    <input type="text" class="form-control js-number width-xs" name="mileage[]" size="5"
                                                           value="<?= gd_isset($search['mileage'][0]); ?>"/><?php echo Globals::get('gSite.member.mileageBasic.unit'); ?> ~
                                                    <input type="text" class="form-control js-number width-xs" name="mileage[]" size="5"
                                                           value="<?= gd_isset($search['mileage'][1]); ?>"/><?php echo Globals::get('gSite.member.mileageBasic.unit'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>예치금</th>
                                                <td class="form-inline">
                                                    <input type="text" class="form-control js-number width-xs" name="deposit[]" size="5"
                                                           value="<?= gd_isset($search['deposit'][0]); ?>"/><?php echo Globals::get('gSite.member.depositConfig.unit'); ?> ~
                                                    <input type="text" class="form-control js-number width-xs" name="deposit[]" size="5"
                                                           value="<?= gd_isset($search['deposit'][1]); ?>"/><?php echo Globals::get('gSite.member.depositConfig.unit'); ?>
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
                                                        <input type="hidden" id="yBirthDt1" class="form-control width-xs" placeholder="" value="<?= gd_isset($search['birthDt'][0]); ?>" size="7"/>
                                                        <span class="input-group-addon">
                                                            <span class="btn-icon-calendar"></span>
                                                        </span>
                                                    </div>
                                                    <div class="input-group seconddate"> ~ </div>
                                                    <div class="input-group js-datepicker-month seconddate">
                                                        <input type="text" id="birthDt2" class="form-control js-number seconddate" placeholder="" name="birthDt[]" value="<?= gd_isset($search['birthDt'][1]); ?>" size="9" maxlength="10"/>
                                                    </div>
                                                    <div class="input-group js-datepicker seconddate">
                                                        <input type="hidden" id="yBirthDt2" class="form-control width-xs seconddate" placeholder="" value="<?= gd_isset($search['birthDt'][1]); ?>" size="7"/>
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
                                    <div class="table-btn">
                                        <input type="button" value="검색" class="btn btn-lg btn-black js-search-button"/>
                                    </div>
                                </form>
                                <div class="table-action mgb0">
                                    <div class="pull-left">
                                        <button type="button" class="btn btn-white" id="btnAddSearchResult">검색회원 전체 추가</button>
                                    </div>
                                    <div class="pull-right form-inline">
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
                                            <option value="saleAmt desc" <?= gd_isset($selected['sort']['saleAmt desc']); ?>>주문금액&darr;</option>
                                            <option value="saleAmt asc" <?= gd_isset($selected['sort']['saleAmt asc']); ?>>주문금액&uarr;</option>
                                        </select>&nbsp;
                                        <?php echo gd_select_box_by_page_view_count(\Request::get()->get('pageNum')) ?>
                                    </div>
                                </div>
                                <div class="">
                                    <table class="table table-bordered table-rows" id="searchTable">
                                        <colgroup>
                                            <col class="width2p">
                                            <col class="width2p">
                                            <?php if ($gGlobal['isUse'] && $sendMode == 'mail') { ?>
                                                <col class="width10p">
                                            <?php } ?>
                                            <col class="width10p">
                                            <col class="width10p">
                                            <col class="width5p">
                                            <col class="width15p">
                                            <col>
                                        </colgroup>
                                        <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
                                            </th>
                                            <th>번호</th>
                                            <?php if ($gGlobal['isUse'] && $sendMode == 'mail') { ?>
                                                <th>상점 구분</th>
                                            <?php } ?>
                                            <th>아이디/닉네임</th>
                                            <th>이름</th>
                                            <th>등급</th>
                                            <th>이메일</th>
                                            <th>휴대폰번호</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="center js-pagination pdt10"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-1 col-height col-middle">
                        <div class="inside-full-height btn-group-wrapper">
                            <div class="btn-group btn-group-vertical btn-group-lg btn-group-area">
                                <button class="btn btn-9 btn-white btn-icon-plus-bottom" type="button">
                                    추가
                                </button>
                                <button class="btn btn-9 btn-icon-check-bottom js-btn-selected" type="button">
                                    선택<br/>완료
                                </button>
                                <button class="btn btn-9 btn-white btn-icon-minus-bottom" type="button">
                                    삭제
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-6 col-height col-top pdl0 pdr0">
                        <div class="inside-full-height table-scroll">
                            <table class="table table-bordered table-rows" id="selectTable">
                                <colgroup>
                                    <col class="width2p">
                                    <col class="width2p">
                                    <?php if ($gGlobal['isUse'] && $sendMode == 'mail') { ?>
                                        <col class="width10p">
                                    <?php } ?>
                                    <col class="width10p">
                                    <col class="width10p">
                                    <col class="width5p">
                                    <col class="width15p">
                                    <col>
                                </colgroup>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 center mgt20">
            <button type="button" class="btn btn-lg btn-white js-btn-cancel">취소</button>
            <button type="button" class="btn btn-lg btn-black js-btn-selected">선택완료</button>
        </div>
    </div>
</div>
<script type="text/javascript">
    var send_mode = '<?= gd_isset($sendMode, 'mail'); ?>';
    var opener_div_list = 'divMaillingList';
    if (send_mode == 'sms') {
        opener_div_list = 'divSmsList';
    }
    var selected_count = {
        total: 0, accept: 0, reject: 0
    };
    var group_names = <?= $groupNames; ?>;
    var mall_list = <?= $mallList; ?>;
    var use_global = '<?= $useGlobal; ?>';
    use_global = (use_global == 'use') && (send_mode == 'mail');
    var payco_icon = '<?= $paycoIcon; ?>';
    var facebook_icon = '<?= $facebookIcon; ?>';
    var third_party_icon_web_path = {
        "payco": payco_icon,
        "facebook": facebook_icon
    };
    $(document).ready(function () {
        var member_popup = new gd_member_popup();
        member_popup.init();

        // 수신거부 선택시 안내 메시지 출력
        $(':radio[name="maillingFl"],:radio[name="smsFl"]').change(function (e) {
            e.preventDefault();

            if ($(e.target).val() !== 'y') {
                member.dialog({message: "검색 시 수신거부 회원이 포함됩니다. 정보통신망법에 따라 수신거부한 회원에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과되므로 유의하시기 바랍니다."});
            }
        });

        // 키워드 enter 검색
        $('input[name="keyword"]').on('keypress', function (event) {
            if (event.which == 13) { // enter 키
                $('.js-search-button').click();
            }
        });

        $('.js-datepicker').datetimepicker().on('dp.change', function(){
            if($(this).prop('class') == 'input-group js-datepicker firstdate')  $('#birthDt1').val($('#yBirthDt1').val());
            else if($(this).prop('class') == 'input-group js-datepicker seconddate')    $('#birthDt2').val($('#yBirthDt2').val());
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

    function checkBirthFl() {
        if($('input:checkbox[name="birthFl"]').val() == 'y') {
            $('#birthCheckFl').prop('checked', true);
            $('.seconddate').hide();
            $('.seconddate').val('');
        }
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

    var gd_member_popup = (function ($, _) {
        // 테이블 헤더 컬럼 카운트
        function count_table_head(search) {
            return search.find('th').length;
        }

        return function () {
            var search_table, select_table, search_head;
            var search_form;
            var btn_add_search_member, btn_search, btn_plus, btn_minus, btn_selected, btn_cancel;
            var select_sort, select_page_number;
            var div_search_count, div_send_list_by_opener;
            var template_load, template_empty, template_body;
            var pagination;
            var is_add_member_by_search = false;

            function set_elements() {
                search_table = $('#searchTable');
                search_head = search_table.find('thead');
                select_table = $('#selectTable');
                search_form = $('#frmSearchBase');
                btn_add_search_member = $('#btnAddSearchResult');
                btn_search = $('.js-search-button');
                btn_plus = $('.btn-icon-plus-bottom');
                btn_minus = $('.btn-icon-minus-bottom');
                btn_selected = $('.js-btn-selected');
                btn_cancel = $('.js-btn-cancel');
                select_sort = $('select[name="sort"]');
                select_page_number = $('select[name="pageNum"]');
                template_load = $('#templateLoad');
                template_empty = $('#templateEmpty');
                template_body = $('#templateBody');
                pagination = $('.js-pagination');
                div_search_count = top.opener.$('#divSearchCount');
                div_send_list_by_opener = top.opener.$('#' + opener_div_list);
            }

            // 검색 중
            function set_template_load() {
                var settings = {col_span: count_table_head(search_head)};
                var compiled = _.template(template_load.html());
                search_head.after(compiled(settings));
            }

            // 검색 결과가 없는 경우
            function set_template_empty() {
                var settings = {col_span: count_table_head(search_head)};
                if (arguments.length > 0) {
                    settings.is_skip = arguments[0];
                }
                var compiled = _.template(template_empty.html());
                search_head.after(compiled(settings));
            }

            // 검색 결과가 있는 경우
            function set_template_body(items, page_index) {
                var settings = {"items": items, "page_index": page_index, "group_names": group_names, "mall_list": mall_list, "use_global": use_global, "third_party_icon_web_path": third_party_icon_web_path, "checkbox_name": "chk"};
                var compiled = _.template(template_body.html());
                search_head.after(compiled(settings));
            }

            function set_template_body_by_select_list(items, page_index) {
                var settings = {"items": items, "page_index": page_index, "group_names": group_names, "mall_list": mall_list, "use_global": use_global, "third_party_icon_web_path": third_party_icon_web_path, "checkbox_name": "selectChk"};
                var compiled = _.template(template_body.html());
                var clone_select_list = select_table.find('tbody').clone();
                select_table.find('tbody').remove();
                select_table.append(compiled(settings));
                clone_select_list.find('tr').each(function (idx, item) {
                    var member_number = $(item).find('input[name="selectChk[]"]').val();
                    var checkbox = select_table.find(':checkbox[value="' + member_number + '"]');
                    if (checkbox.length > 0) {
                        return true;
                    }
                    select_table.find('tbody').append(item);
                });
            }

            // 검색 시 기존 화면을 제거
            function remove_template_by_search_body() {
                var tbody = search_head.next('tbody');
                if (tbody.length > 0) {
                    tbody.remove();
                }
            }

            // 이벤트 함수 바인딩
            function bind_event() {
                select_sort.change(async_search_member);
                select_page_number.change(async_search_member);
                btn_search.click(async_search_member);
                btn_plus.click(add_rows);
                btn_minus.click(remove_row);
                btn_selected.click(add_send_list);
                btn_cancel.click(close_popup);
                btn_add_search_member.click(async_search_member_and_add_list);
                pagination.on('a').click(function (e) {
                    search_table.find('.js-checkall').prop('checked', false);
                    $(this).find('.active').removeClass('active');
                    var target = $(e.target);
                    if (target.hasClass('img-page-arrow')) {
                        target = target.closest('a');
                    }
                    target.addClass('active');
                    async_search_member();
                });
            }

            // 팝업 창 닫기
            function close_popup() {
                self.close();
            }

            // 이미 선택된 회원 리스트를 선택회원 리스트에 재설정
            function set_select_list_by_send_list() {
                var find_tbody = select_table.find('tbody');
                if (find_tbody.length < 1) {
                    select_table.append(div_send_list_by_opener.find('tbody').clone());
                } else {
                    find_tbody.html(div_send_list_by_opener.find('tbody').html());
                }
            }

            // 메일 발송 리스트에 발송자 정보 추가 및 카운트 표시
            function add_send_list() {
                div_send_list_by_opener.find('tbody').remove();
                var elements = select_table.find('input[name="selectChk[]"]');
                selected_count.total = elements.length;
                $.each(elements, function (idx, item) {
                    var checkbox = $(item);
                    var receive_flag = checkbox.data('maillingfl');
                    if (send_mode === 'sms') {
                        receive_flag = checkbox.data('smsfl');
                    }
                    if (receive_flag === 'y') {
                        selected_count.accept++;
                    } else {
                        selected_count.reject++;
                    }
                });
                div_send_list_by_opener.data({
                    total: selected_count.total,
                    accept: selected_count.accept,
                    reject: selected_count.reject
                });
                div_send_list_by_opener.html(select_table.html());
                div_search_count.removeClass('display-none');
                div_search_count.find('.js-receive-total').text(selected_count.total).data('receive-total', selected_count.total);
                div_search_count.find('.js-reject-count').text(selected_count.reject).data('reject-count', selected_count.reject);
                self.close();
            }

            // 선택 회원 리스트 회원 추가
            function add_rows() {
                var checked_rows = search_table.find(':checked[name="chk[]"]');
                var selected_count = select_table.find(':checkbox[name="selectChk[]"]').length + 1;
                var select_tbody = select_table.find('tbody');
                $.each(checked_rows.get().reverse(), function (idx, item) {
                    var checkbox = $(item);
                    var find_checkbox = select_tbody.find(':checkbox[value="' + checkbox.val() + '"]');
                    if (find_checkbox.length > 0) {
                        return true;
                    }
                    var row = checkbox.closest('tr').clone();
                    row.find('td:eq(1) span').text(selected_count++);
                    row.find(':checkbox').attr({"checked": false, "name": "selectChk[]"});
                    select_tbody.prepend(row);
                });
            }

            // 선택 회원 리스트 회원 제거
            function remove_row() {
                select_table.find(':checked[name="selectChk[]"]').closest('tr').remove();
                var checkbox = select_table.find(':checkbox[name="selectChk[]"]');
                var select_count = checkbox.length;
                $.each(checkbox, function (idx, item) {
                    var checkbox = $(item);
                    var row = checkbox.closest('tr');
                    row.find('td:eq(1) span').text(select_count--);
                });
            }

            // 선택 회원 리스트 화면 초기화
            function init_select_table() {
                var header = search_head.clone();
                header.find('.js-checkall').data("targetName", "selectChk");
                select_table.append(header);
                if (select_table.find('tbody').length < 1) {
                    select_table.append('<tbody></tbody>');
                }
            }

            // 회원 검색 후 전체 추가 비동기 통신
            function async_search_member_and_add_list() {
                is_add_member_by_search = true;
                var params = search_form.serializeArray();
                var sort_value = select_sort.val();
                params.push({"name": "sort", "value": sort_value});
                params.push({"name": "searchMode", "value": "addList"});
                $.ajax('../share/ifrme_add_member_search.php', {
                    method: "get",
                    data: params,
                    beforeSend: function () {
                        // 검색회원 전체 추가 일 때에는 실행하지 않는다.
                        if (!is_add_member_by_search) {
                            remove_template_by_search_body();
                            set_template_load();
                        }
                    },
                    success: function () {
                        var json_response = arguments[0];
                        try {
                            if (json_response.member_list.length > 0) {
                                set_template_body_by_select_list(json_response.member_list, json_response.page_index);
                                var elements = select_table.find('input[name="selectChk[]"]').closest('tr');
                                var elements_count = elements.length;
                                elements.each(function (idx, row) {
                                    $(row).find('td:eq(1) span').text(elements_count--);
                                });
                                is_add_member_by_search = false;
                            } else {
                                set_template_empty();
                            }
                        } catch (e) {
                            logger.error(e);
                            set_template_empty();
                        }
                    },
                    error: function () {
                        alert('검색 중 오류가 발생하였습니다.');
                    }
                });
            }

            // 회원 검색 비동기 통신
            function async_search_member() {
                var params = {};
                if (Array.isArray(arguments[0])) {
                    params = search_form.serializeArray().concat(arguments[0]);
                } else {
                    params = search_form.serializeArray();
                }

                var sort_value = select_sort.val();
                var page_value = pagination.find('a.active').data('page');
                if (typeof page_value === 'undefined') {
                    page_value = 1;
                }
                var page_number_value = select_page_number.val();
                params.push({"name": "sort", "value": sort_value});
                params.push({"name": "page", "value": page_value});
                params.push({"name": "pageNum", "value": page_number_value});
                $.ajax('../share/ifrme_add_member_search.php', {
                    method: "get",
                    data: params,
                    beforeSend: function () {
                        // 검색회원 전체 추가 일 때에는 실행하지 않는다.
                        remove_template_by_search_body();
                        set_template_load();
                    },
                    success: function () {
                        var json_response = arguments[0];
                        pagination.html(json_response.pagination);
                        remove_template_by_search_body();
                        try {
                            if (json_response.member_list.length > 0) {
                                set_template_body(json_response.member_list, json_response.page_index);
                            } else {
                                set_template_empty(json_response.is_skip);
                            }
                        } catch (e) {
                            logger.error(e);
                            set_template_empty();
                        }
                    },
                    error: function () {
                        alert('검색 중 오류가 발생하였습니다.');
                    }
                });
            }

            return {
                init: function () {
                    set_elements();
                    set_template_load();
                    async_search_member([{name:"initSearch", value: true}]);
                    bind_event();
                    init_select_table();
                    set_select_list_by_send_list();
                }
            }
        }
    })($, _);
</script>
<script type="text/template" id="templateLoad">
    <tbody>
    <tr>
        <td colspan="<%= col_span %>" class="center loading height200"></td>
    </tr>
    </tbody>
</script>
<script type="text/template" id="templateEmpty">
    <tbody>
    <tr>
        <% if (is_skip) { %>
        <td colspan="<%= col_span %>" class="center">검색기능을 이용해주세요.</td>
        <% } else { %>
        <td colspan="<%= col_span %>" class="center">검색된 정보가 없습니다.</td>
        <% } %>
    </tr>
    </tbody>
</script>
<script type="text/template" id="templateBody">
    <tbody>
    <% _.each(items, function(item) { %>
    <tr class="text-center" data-member-no="<%= item.memNo %>">
        <td>
            <input type="checkbox" name="<%=checkbox_name%>[]" value="<%= item.memNo %>" data-appFl="<%= item.appFl %>" data-maillingFl="<%= item.maillingFl %>" data-smsFl="<%= item.smsFl %>"/>
        </td>
        <td>
            <span class="font-num js-layer-crm hand"><%= page_index-- %></span>
        </td>
        <% if (use_global) {%>
        <td>
            <span class="flag flag-16 flag-<%= mall_list[item.mallSno]['domainFl'] %>"></span>
            <%= mall_list[item.mallSno]['mallName'] %>
        </td>
        <% } %>
        <td>
            <span class="font-eng js-layer-crm hand"><%= item.memId %></span>
            <%= third_party_icon_web_path[item.snsTypeFl] %> <% if (item.nickNm) { %>
            <div class="notice-ref notice-sm"><%= item.nickNm %></div>
            <% } %>
        </td>
        <td>
            <span class="js-layer-crm hand"><%= item.memNm %></span>
        </td>
        <td>
            <span class="js-layer-crm hand"><%= group_names[item.groupSno] %></span>
        </td>
        <td>
            <span class="font-eng js-layer-crm hand"><%= item.email %></span>
            <div class="notice-ref notice-sm">(<% if (item.maillingFl == 'y') { %>수신<% } else { %>수신거부<% } %>)</div>
        </td>
        <td>
            <span class="font-num js-layer-crm hand"><%= item.cellPhone %></span>
            <div class="notice-ref notice-sm">(<% if (item.smsFl == 'y') { %>수신<% } else { %>수신거부<% } %>)</div>
        </td>
    </tr>
    <% }); %>
    </tbody>
</script>
