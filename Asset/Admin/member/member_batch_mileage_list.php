<!-- //@formatter:off -->
<form id="form_search" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="sort" value="<?= gd_isset($search['sort']) ?>"/>
    <input type="hidden" name="pageNum" value="<?= gd_isset($search['pageNum']) ?>"/>
    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group"><input type="button" value="마일리지 지급/차감" class="btn btn-red-line btn-register"/></div>
    </div>
    <div class="form-inline search-detail-box">
        <input type="hidden" name="detailSearch" value="<?= gd_isset($search['detailSearch']); ?>"/>
        <div class="table-title gd-help-manual">마일리지 지급/차감내역 검색</div>
        <table class="table table-cols">
            <colgroup><col class="width-sm"/><col/><col class="width-sm"/><col/></colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <?= gd_select_box('key', 'key', $searchKey, null, gd_isset($search['key'])); ?>
                    <input type="text" name="keyword" value="<?= gd_isset($search['keyword']); ?>" class="form-control"/>
                </td>
            </tr>
            <tr>
                <th>지급/차감일</th>
                <td colspan="3">
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder="" value="<?= gd_isset($search['regDt'][0]); ?>">
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder="" value="<?= gd_isset($search['regDt'][1]); ?>">
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    <?= gd_search_date(Request::get()->get('regDtPeriod', 6), 'regDt', false); ?>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail">
            <tr>
                <th>회원등급</th>
                <td>
                    <?= gd_select_box('groupSno', 'groupSno', $groups, null, $search['groupSno'], '등급'); ?>
                </td>
                <th>지급/차감사유</th>
                <td>
                    <?= gd_select_box('reasonCd', 'reasonCd', $mileageReasons, null, $search['reasonCd'], '전체'); ?>
                    <div><input type="hidden" name="contents" class="form-control" value="<?= $search['contents']; ?>"/></div>
                </td>
            </tr>
            <tr>
                <th>지급/차감구분</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mode" value="all" <?= gd_isset($checked['mode']['all']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="mode" value="add" <?= gd_isset($checked['mode']['add']); ?>/>지급</label>
                    <label class="radio-inline"><input type="radio" name="mode" value="remove" <?= gd_isset($checked['mode']['remove']); ?>/>차감</label>
                </td>
                <th>금액범위</th>
                <td>
                    <input type="text" name="mileage[]" value="<?= gd_isset($search['mileage'][0]); ?>" class="form-control"/>
                    ~
                    <input type="text" name="mileage[]" value="<?= gd_isset($search['mileage'][1]); ?>" class="form-control"/>
                </td>
            </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색<span>펼침</span></button>
    </div>
    <div class="table-btn"><input type="submit" value="검색" class="btn btn-lg btn-black" id="btn_search"></div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left"><?= gd_display_only_search_result($page->recode['total'], '건'); ?></div>
        <div class="pull-right"><?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?></div>
    </div>
    <table class="table table-rows">
        <colgroup><col class="width-2xs"/><col class=""/><col/><col/><col/><col/><col/><col/><col/><col/></colgroup>
        <thead><tr><th>번호</th><th>아이디</th><th>이름</th><th>등급</th><th>지급액</th><th>차감액</th><th>지급/차감일</th><th>소멸예정일</th><th>처리자</th><th>사유</th></tr></thead>
        <tbody>
        <?php
        if (isset($data) && count($data) > 0) {
            foreach ($data as $val) {
                $isMinusSign = strpos($val['mileage'], '-') === 0;
                if($val['reasonCd'] == '01005001' && $val['contents'] == '상품구매 시 마일리지 사용 (취소복원)') {
                    $reasonContents = $val['contents'];
                }else{
                    $reasonContents = gd_isset($mileageReasons[$val['reasonCd']]);
                }
                switch ($val['reasonCd']) {
                    case '01005001':
                    case '01005002':
                    case '01005003':
                    case '01005004':
                    case '01005008':
                    case '01005501':
                    case '01005504':
                    case '01005505':
                        if ($val['reasonCd'] == '01005504' || $val['reasonCd'] == '01005505') {
                            $reasonContents = $val['contents'];
                        }
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '<br/>(주문번호 : ';
                            $reasonContents .= '<a href="#" class="js-link-order" data-order-no="' . $val['handleCd'] . '">' . $val['handleCd'] . '</a>)';
                        }
                        break;
                    case '01005006':
                    case '01005007':
                        $reasonContents = $val['contents'];
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '<br/>(' . $val['handleCd'] . ')';
                        }
                        break;
                    case '01005009':
                    case '01005010':
						if($val['handleCd'] == 'plusReview'){
							$reasonContents = $val['contents'];
						}else{
						    if (empty($val['handleCd']) === false) {
                                $reasonContents .= '<br/>(' . $boards[$val['handleCd']] . ')';
                            }
						}
                        break;
                    case '01005011':
                    case '01005502':
                        $reasonContents = $val['contents'];
                        if($val['reasonCd'] == '01005502' && empty($val['modifyEventNm']) === false){
                            $reasonContents .= '<div>(' . $val['modifyEventNm'] . ')</div>';
                        }
                        break;
                    case '01005005':
                        $reasonContents = $val['contents'];
                        if ($val['handleNo'] == 'smsFl') {
                            $reasonContents .= '<br/>Sms 수신동의';
                        } elseif ($val['handleNo'] == 'mailingFl') {
                            $reasonContents .= '<br/>이메일 수신동의';
                        }
                        break;
                }
                ?>
                <tr class="text-center" data-member-no="<?= $val['memNo']; ?>">
                    <td class="font-num"><?= $page->idx--; ?></td>
                    <td><span class="font-eng js-layer-crm hand"><?= $val['memId']; ?></span></td>
                    <td><span class="js-layer-crm hand"><?= $val['memNm']; ?></span></td>
                    <td><span class="js-layer-crm hand"><?= gd_isset($groups[$val['groupSno']]); ?></span></td>
                    <td class="font-num"><?= $isMinusSign ? '-' : '(+)' . gd_money_format($val['mileage']) . gd_display_mileage_unit() ?></td>
                    <td class="font-num"><?= $isMinusSign ? '(-)' . gd_money_format(substr($val['mileage'], 1)) . gd_display_mileage_unit() : '-' ?></td>
                    <td class="font-date"><?= gd_date_format('Y-m-d', $val['regDt']); ?>
                        <br/><?= gd_date_format('H:i', $val['regDt']); ?></td>
                    <td class="font-date">
                        <?php if (!$isMinusSign) { ?>
                            <?= gd_date_format('Y-m-d', $val['deleteScheduleDt']); ?>
                            <br/><?= gd_date_format('H:i', $val['deleteScheduleDt']); ?>
                        <?php } ?>
                    </td>
                    <td class="text-center"><?= $val['managerId']; ?><?= $val['deleteText'] ?></td>
                    <td><?= $reasonContents; ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr><td class="no-data" colspan="10">검색된 정보가 없습니다.</td></tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    var gd_batch_mileage_list = {
        reasonCd: <?= json_encode($mileageReasons); ?>
    };
    $(document).ready(function () {
        var $jsSearchForm = $('.js-search-form');
        $jsSearchForm.on('change', ':radio[name=mode]:checked', function (e) {
            var $target = $(e.target);
            var $mileage = $(':text[name="mileage[]"]');
            var mileage1 = $mileage.eq(0).val();
            var mileage2 = $mileage.eq(1).val();
            switch ($target.val()) {
                case 'all':
                    $mileage.eq(0).val('');
                    $mileage.eq(1).val('');
                    break;
                case 'add':
                    mileage1 = mileage1.replace('-', '');
                    mileage2 = mileage2.replace('-', '');
                    if (mileage1 >= mileage2) {
                        $mileage.eq(0).val(mileage2);
                        $mileage.eq(1).val(mileage1);
                    }
                    break;
                case 'remove':
                    mileage1 = ('-' + mileage1) * 1;
                    mileage2 = ('-' + mileage2) * 1;
                    if (mileage1 >= mileage2) {
                        $mileage.eq(0).val(mileage2);
                        $mileage.eq(1).val(mileage1);
                    }
                    break;
            }
        });
        $jsSearchForm.on('change', 'select[name=reasonCd]', function (e) {
            var $target = $(e.target);
            var $option = $target.find(':selected');
            var $contents = $('input[name=contents]');

            if ('01005011' == $option.val()) {
                $contents.attr('type', 'text').focus();
                if (_.isEmpty($contents.val()) === false) {
                    $contents.val('');
                }
            } else {
                $contents.attr('type', 'hidden');
                $contents.val(gd_batch_mileage_list.reasonCd[$option.val()]);
            }
        });
        $jsSearchForm.on('click', '.btn-register', function (e) {
            e.preventDefault();
            location.href = '../member/member_batch_mileage.php';
        });

        $('select[name=reasonCd]').trigger('change');
        $('input[name=contents]').val('<?=$search['contents']?>');
        $('select[name=\'pageNum\']').change({targetForm: $jsSearchForm}, member.page_number);
    });
</script>
