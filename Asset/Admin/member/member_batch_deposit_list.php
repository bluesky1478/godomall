<?php
$requestGetParams = Request::get()->all();
$groups = gd_member_groups();
$depositReasons = gd_code('01006');
?>
<!-- //@formatter:off -->
<form id="form_search" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>
    <input type="hidden" name="detailSearch" value="<?= $requestGetParams['detailSearch']; ?>"/>

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?></h3>
        <div class="btn-group"><input type="button" value="예치금 지급/차감" class="btn btn-red-line btn-register"/></div>
    </div>
    <div class="table-title gd-help-manual">예치금 지급/차감내역 검색</div>
    <div class="form-inline search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm"/><col/><col class="width-sm"/><col/></colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <?= gd_select_box('key', 'key', $searchKey, null, $requestGetParams['key']); ?>
                    <input type="text" name="keyword" value="<?= $requestGetParams['keyword']; ?>" class="form-control"/>
                </td>
            </tr>
            <tr>
                <th>지급/차감일</th>
                <td colspan="3">
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder="" value="<?= $requestGetParams['regDt'][0]; ?>">
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="regDt[]" class="form-control width-xs" placeholder="" value="<?= $requestGetParams['regDt'][1]; ?>">
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
                    <?= gd_select_box('groupSno', 'groupSno', $groups, null, $requestGetParams['groupSno'], '등급'); ?>
                </td>
                <th>지급/차감사유</th>
                <td>
                    <?= gd_select_box('reasonCd', 'reasonCd', $depositReasons, null, $requestGetParams['reasonCd'], '전체'); ?>
                    <div><input type="hidden" name="contents" class="form-control" value="<?= $requestGetParams['contents']; ?>"/></div>
                </td>
            </tr>
            <tr>
                <th>지급/차감구분</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mode" value="all" <?= $checked['mode']['all']; ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="mode" value="add" <?= $checked['mode']['add']; ?>/>지급</label>
                    <label class="radio-inline"><input type="radio" name="mode" value="remove" <?= $checked['mode']['remove']; ?>/>차감</label>
                </td>
                <th>금액범위</th>
                <td>
                    <input type="text" name="deposit[]" value="<?= $requestGetParams['deposit'][0]; ?>" class="form-control"/>~<input type="text" name="deposit[]" value="<?= $requestGetParams['deposit'][1]; ?>" class="form-control"/>
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
        <div class="pull-right"><div><?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?></div></div>
    </div>
    <table class="table table-rows">
        <colgroup><col class="width-xs"/><col class="width-xs"/><col/><col/><col/><col/><col/><col/><col/></colgroup>
        <thead><tr><th>번호</th><th>아이디</th><th>이름</th><th>등급</th><th>지급액</th><th>차감액</th><th>지급/차감일</th><th>처리자</th><th>사유</th></tr></thead>
        <tbody>
        <?php
        if (isset($depositList) && count($depositList) > 0) {
            foreach ($depositList as $val) {
                $isMinusSign = strpos($val['deposit'], '-') === 0;
                if ($val['reasonCd'] == '01006003' && $val['contents'] == '상품구매 (취소복원)') {
                    $reasonContents = $val['contents'];
                } else {
                    $reasonContents = $depositReasons[$val['reasonCd']];
                }
                switch ($val['reasonCd']) {
                    case '01006001':
                    case '01006002':
                    case '01006003':
                    case '01006004':
                    case '01006005':
                    case '01006501':
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '<br/>(주문번호 : ';
                            $reasonContents .= '<a href="#" class="js-link-order" data-order-no="' . $val['handleCd'] . '">' . $val['handleCd'] . '</a>)';
                        }
                        break;
                    case '01006006':
                    case '01006505':
                        $reasonContents = $val['contents'];
                        break;
                }
                ?>
                <tr class="center" data-member-no="<?= $val['memNo']; ?>">
                    <td class="font-num"><?= $page->idx--; ?></td>
                    <td><span class="font-eng js-layer-crm hand"><?= $val['memId']; ?></span></td>
                    <td><span class="js-layer-crm hand"><?= $val['memNm']; ?></span></td>
                    <td><span class="js-layer-crm hand"><?= $groups[$val['groupSno']]; ?></span></td>
                    <td class="font-num"><?= $isMinusSign ? '-' : '(+)' . gd_money_format($val['deposit']) . gd_display_deposit('unit') ?></td>
                    <td class="font-num"><?= $isMinusSign ? '(-)' . gd_money_format(substr($val['deposit'], 1)) . gd_display_deposit('unit') : '-' ?></td>
                    <td class="font-date"><?= gd_date_format('Y-m-d', $val['regDt']); ?><br/><?= gd_date_format('H:i', $val['regDt']); ?></td>
                    <td class="center"><?= $val['managerId']; ?><?= $val['deleteText']; ?></td>
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
    var gd_batch_deposit_list = {
        reasonCd: <?= json_encode($depositReasons); ?>
    };
    $(document).ready(function () {
        $('.js-search-form').on('change', ':radio[name=mode]:checked', function (e) {
            var $target = $(e.target);
            var $deposit = $(':text[name="deposit[]"]');
            var deposit1 = $deposit.eq(0).val();
            var deposit2 = $deposit.eq(1).val();
            switch ($target.val()) {
                case 'all':
                    $deposit.eq(0).val('');
                    $deposit.eq(1).val('');
                    break;
                case 'add':
                    deposit1 = deposit1.replace('-', '');
                    deposit2 = deposit2.replace('-', '');
                    if (deposit1 >= deposit2) {
                        $deposit.eq(0).val(deposit2);
                        $deposit.eq(1).val(deposit1);
                    }
                    break;
                case 'remove':
                    deposit1 = ('-' + deposit1) * 1;
                    deposit2 = ('-' + deposit2) * 1;
                    if (deposit1 >= deposit2) {
                        $deposit.eq(0).val(deposit2);
                        $deposit.eq(1).val(deposit1);
                    }
                    break;
            }
        }).on('change', 'select[name=reasonCd]', function (e) {
            var $target = $(e.target);
            var $option = $target.find(':selected');
            var $contents = $('input[name=contents]');

            if ('01006006' == $option.val()) {
                $contents.attr('type', 'text').focus();
                if (_.isEmpty($contents.val()) === false) {
                    $contents.val('');
                }
            } else {
                $contents.attr('type', 'hidden');
                $contents.val(gd_batch_deposit_list.reasonCd[$option.val()]);
            }
        }).on('click', '.btn-register', function () {
            location.href = '../member/member_batch_deposit.php';
        });

        $('select[name=reasonCd]').trigger('change');
        $('input[name=contents]').val('<?=$requestGetParams['contents']?>');
        $('select[name=\'pageNum\']').change({targetForm: '.js-search-form'}, member.page_number);
    });
</script>
