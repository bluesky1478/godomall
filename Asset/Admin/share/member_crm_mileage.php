<!-- //@formatter:off -->
<form id="form_search" method="get" class="content-form js-search-form">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="pageNum" value="<?= $search['pageNum']; ?>"/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $memberData['memNo'] ?>"/>
    <input type="hidden" name="detailSearch" value="<?= gd_isset($search['detailSearch']); ?>"/>
    <div class="form-inline search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm"/><col/><col class="width-sm"/><col/></colgroup>
            <tbody>
            <tr>
                <th>처리자</th>
                <td colspan="3">
                    <input type="hidden" name="key" value="managerId"/>
                    <input type="text" name="keyword" value="<?= gd_isset($search['keyword']); ?>" class="form-control"/>
                </td>
            </tr>
            <tr>
                <th>지급/차감일</th>
                <td>
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
                <th>지급/차감사유</th>
                <td>
                    <?= gd_select_box('reasonCd', 'reasonCd', $modifiedMileageReasons, null, $search['reasonCd'], '전체'); ?>
                    <div><input type="hidden" name="contents" class="form-control" value="<?= $search['contents']; ?>"/></div>
                </td>
            </tr>
            <tr>
                <th>지급/차감구분</th>
                <td>
                    <label><input type="radio" name="mode" value="all" <?= gd_isset($checked['mode']['all']); ?>/>전체</label>
                    <label><input type="radio" name="mode" value="add" <?= gd_isset($checked['mode']['add']); ?>/>지급</label>
                    <label><input type="radio" name="mode" value="remove" <?= gd_isset($checked['mode']['remove']); ?>/>차감</label>
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
    </div>
    <div class="table-btn"><input type="submit" value="검색" class="btn btn-lg btn-black" id="btn_search"></div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left">검색<strong><?= $page->recode['total']; ?></strong>건</div>
        <div class="pull-right">
            <div><?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500,]), '개 보기', Request::get()->get('pageNum'), null); ?></div>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup><col class="width-xs"/><col/><col/><col/><col/><col/><col/><col/></colgroup>
        <thead><tr><th>번호</th><th>지급액</th><th>차감액</th><th>잔액</th><th>지급/차감일</th><th>처리자</th><th>사유</th><th>수정</th></tr></thead>
        <tbody>
        <?php
        // @todo : 시스템 적립 건은 자동지급으로 표기해야함 자동지급일 경우 mangerId 확인 필요
        if (isset($data) && is_array($data)) {
            $listHtml = [];
            foreach ($data as $val) {
                $isMinusSign = strpos($val['mileage'], '-') === 0;
                if($val['reasonCd'] == '01005001' && $val['contents'] == '상품구매 시 마일리지 사용 (취소복원)') {
                    $reasonContents = '<span class="js-reason1">' . gd_isset($val['contents']) . '</span>';
                }else{
                    $reasonContents = '<span class="js-reason1">' . gd_isset($mileageReasons[$val['reasonCd']]) . '</span>';
                }
                $reasonContents .= '<span class="js-reason2 display-none">'.gd_select_box('reasonModiyCode'.$val['sno'], 'reasonModiyCode['.$val['sno'].']', $mileageReasons, null, $val['reasonCd']).'</span>';
                $reasonContents .= '<div class="js-reason2 display-none"><input type="hidden" name="reasonContentsModify'.$val['sno'].'" class="form-control" value="'.$val['contents'].'" maxlength="250"/></div>';
                switch ($val['reasonCd']) {
                    case '01005001':
                    case '01005002':
                    case '01005003':
                    case '01005004':
                    case '01005008':
                    case '01005501':
                    case '01005504':
                    case '01005505':
                        if ($val['reasonCd'] == '01005504') {
                            $reasonContents = $val['contents'];
                        }
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '<div>(주문번호 : ';
                            $reasonContents .= '<a href="#" class="js-link-order" data-order-no="' . $val['handleCd'] . '">' . $val['handleCd'] . '</a>)</div>';
                        }
                        break;
                    case '01005006':
                    case '01005007':
                        $reasonContents = $val['contents'];
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '<div>(' . $val['handleCd'] . ')</div>';
                        }
                        break;
                    case '01005009':
                    case '01005010':
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '<div>(' . $boards[$val['handleCd']] . ')</div>';
                        }
                        break;
                    case '01005011':
                    case '01005502':
                        $reasonContents = '<span class="js-reason1">'.$val['contents'].'</span>';
                        $reasonContents .= '<span class="js-reason2 display-none">'.gd_select_box('reasonModiyCode'.$val['sno'], 'reasonModiyCode['.$val['sno'].']', $mileageReasons, null, $val['reasonCd']).'</span>';
                        $reasonContents .= '<div class="js-reason2 display-none"><input type="text" name="reasonContentsModify'.$val['sno'].'" class="form-control" value="'.$val['contents'].'" maxlength="250"/></div>';
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
                $listHtml[] = '<tr class="center" data-member-no="' . $val['memNo'] . '">';
                $listHtml[] = '<td class="font-num">' . $page->idx-- . '<input type="hidden" name="sno" value="'.$val['sno'].'"> <input type="hidden" name="reasonCdOld" value="'.$val['reasonCd'].'"> <input type="hidden" name="contentsOld" value="'.$val['contents'].'"></td>';
                if ($isMinusSign) {
                    $listHtml[] = '<td class="font-num">-</td>';
                } else {
                    $listHtml[] = '<td class="font-num">(+)' . gd_money_format($val['mileage']) . gd_display_mileage_unit() . '</td>';
                }
                if ($isMinusSign) {
                    $listHtml[] = '<td class="font-num">(-)' . gd_money_format(substr($val['mileage'], 1)) . gd_display_mileage_unit() . '</td>';
                } else {
                    $listHtml[] = '<td class="font-num">-</td>';
                }
                $listHtml[] = '<td class="font-num">' . gd_money_format($val['afterMileage']) . gd_display_mileage_unit() . '</td>';
                $listHtml[] = '<td class="font-date">' . gd_date_format('Y-m-d', $val['regDt']) . '<br/>' . gd_date_format('H:i', $val['regDt']) . '</td>';
                $listHtml[] = '<td class="center">' . $val['managerId'] . '</td>';
                $listHtml[] = '<td class="center width30p">' . $reasonContents . '</td>';

                $listHtml[] = '<td>
								<div class="js-reason1"><button type="button" class="btn btn-gray btn-sm">수정</button></div>
								<div class="js-reason2 display-none">
								<button type="button" class="btn btn-black btn-sm js-reason-modify">확인</button><br>
								<button type="button" class="btn btn-white btn-sm js-reason-cancel">취소</button>
								</div>
							   </td>';
				$listHtml[] = '</tr>';
            }
            echo implode('', $listHtml);
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

        $('.js-search-form').on('change', ':radio[name=mode]:checked', function (e) {
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
        }).on('change', 'select[name=reasonCd]', function (e) {
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
        $('.btn-register').click(function () {
            layer_member_mileage();
        });
        $('select[name=reasonCd]').trigger('change');
        $('input[name=contents]').val('<?=$search['contents']?>');
        $('select[name=\'pageNum\']').change({targetForm: '.js-search-form'}, member.page_number);


        $('.js-reason1 > button').click(function () {
            var closest = $(this).closest('tr');
            var sno = $(closest).find('input[name=sno]').val();
            var reasonCdOld = $(closest).find('input[name=reasonCdOld]').val();
            var contents = $('input[name=reasonContentsModify'+ sno +']');
            if ('01005011' == reasonCdOld) {
                contents.attr('type', 'text').focus();
            } else {
                contents.attr('type', 'hidden');
            }
            $(closest).find('.js-reason1').addClass('display-none');
            $(closest).find('.js-reason2').removeClass('display-none');
        });

        $('.js-reason-cancel').click(function () {
            var closest = $(this).closest('tr');
            var sno = $(closest).find('input[name=sno]').val();
            var reasonCdOld = $(closest).find('input[name=reasonCdOld]').val();
            var contentsOld = $(closest).find('input[name=contentsOld]').val();
            $("select[name='reasonModiyCode[" + sno + "]").val(reasonCdOld).prop("selected", true);
            $('input[name=reasonContentsModify'+ sno +']').val(contentsOld);
            $(closest).find('.js-reason1').removeClass('display-none');
            $(closest).find('.js-reason2').addClass('display-none');
        });

        $('.js-reason-modify').click(function () {
            var closest = $(this).closest('tr');
            var sno = $(closest).find('input[name=sno]').val();
            var reasonModiyCode = $("select[name='reasonModiyCode[" + sno + "]").val();
            var contents = $('input[name=reasonContentsModify'+ sno +']').val();
            $.post('../member/member_batch_ps.php', {
                'mode': 'reasonCd_modify_mileage',
                'sno': sno,
                'reasonModiyCode': reasonModiyCode,
                'contents': contents
            }, function (data, status) {
                if (status == 'success' && data[0]) {
                    location.reload(true);
                }
            });
        });

        $('select[name*="reasonModiyCode["]').change(function() {
            var option = $(this).find(':selected');
            var closest = $(this).closest('tr');
            var sno = $(closest).find('input[name=sno]').val();
            var contents = $('input[name=reasonContentsModify'+ sno +']');
            if ('01005011' == option.val()) {
                contents.attr('type', 'text').focus();
            } else {
                contents.attr('type', 'hidden');
            }
        });

        $('select[name*="reasonModiyCode["]').css("display", "inline");
    });
</script>
