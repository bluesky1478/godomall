<?php
$requestGetParams = Request::get()->all();
$groups = gd_member_groups();
$depositReasons = gd_code('01006');
?>
<style>
    .js-preview{
        position:relative
    }
    .depositPreview{
        width:370px;
        height:220px;
        position: absolute;
        display: none;
        border: 2px solid #aaaaaa;
        padding: 0px;
        margin: 0px;
        background-color: #ffffff;
        z-index: 5000;
    }
    .bgColor{
        background-color:  #E8E8E8;
    }
</style>
<!-- //@formatter:off -->
<form id="form_search" method="get" class="content-form js-search-form">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $requestGetParams['memNo']; ?>"/>
    <input type="hidden" name="detailSearch" value="<?= $requestGetParams['detailSearch']; ?>"/>
    <div class="form-inline search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm"/><col/><col class="width-sm"/><col/></colgroup>
            <tbody>
            <tr>
                <th>처리자</th>
                <td colspan="3">
                    <input type="hidden" name="key" value="managerId"/>
                    <input type="text" name="keyword" value="<?= $requestGetParams['keyword']; ?>" class="form-control"/>
                </td>
            </tr>
            <tr>
                <th>지급/차감일</th>
                <td>
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
                <th>지급/차감사유</th>
                <td>
                    <?= gd_select_box('reasonCd', 'reasonCd', $depositReasons, null, $requestGetParams['reasonCd'], '전체'); ?>
                    <div>
                        <input type="hidden" name="contents" class="form-control" value="<?= $requestGetParams['contents']; ?>"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>지급/차감구분</th>
                <td>
                    <label><input type="radio" name="mode" value="all" <?= $checked['mode']['all']; ?>/>전체</label>
                    <label><input type="radio" name="mode" value="add" <?= $checked['mode']['add']; ?>/>지급</label>
                    <label><input type="radio" name="mode" value="remove" <?= $checked['mode']['remove']; ?>/>차감</label>
                </td>
                <th>금액범위</th>
                <td><input type="text" name="deposit[]" value="<?= $requestGetParams['deposit'][0]; ?>" class="form-control"/>~<input type="text" name="deposit[]" value="<?= $requestGetParams['deposit'][1]; ?>" class="form-control"/></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black" id="btn_search">
    </div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left">검색<strong><?= $page->recode['total']; ?></strong>건</div>
        <div class="pull-right">
            <div><?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500,]), '개 보기', Request::get()->get('pageNum'), null); ?></div>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup><col class="width-xs"/><col/><col/><col/><col/><col/><col/></colgroup>
        <thead><tr><th>번호</th><th>지급액</th><th>차감액</th><th>잔액</th><th>지급/차감일</th><th>처리자</th><th>사유</th><th>수정</th></tr></thead>
        <tbody>
        <?php
        if (!empty($depositList) && is_array($depositList)) {
            $listHtml = [];
            foreach ($depositList as $val) {
                $isMinusSign = strpos($val['deposit'], '-') === 0;
                if($val['reasonCd'] == '01006003' && $val['contents'] == '상품구매 (취소복원)'){
                    $reasonContents = '<span class="js-reason1">'.$val['contents'].'</span>';
                }else{
                    $reasonContents = '<span class="js-reason1">'.$depositReasons[$val['reasonCd']].'</span>';
                }
                $reasonContents .= '<span class="js-reason2 display-none">'.gd_select_box('reasonModiyCode'.$val['sno'], 'reasonModiyCode['.$val['sno'].']', $depositReasons, null, $val['reasonCd']).'</span>';
                $reasonContents .= '<div class="js-reason2 display-none"><input type="hidden" name="reasonContentsModify'.$val['sno'].'" class="form-control" value="'.$val['contents'].'" maxlength="250"/></div>';
                switch ($val['reasonCd']) {
                    case '01006001':
                    case '01006002':
                    case '01006003':
                    case '01006004':
                    case '01006005':
                    case '01006501':
                        $handleReason = '';
                        if($val['handleReason'] != '') {
                           $handleReason = ' <span class="text-blue">[' . $val['handleReason'] . '] </span>';
                        }
                        $reasonContents .= '<span class="js-reason1">'.$handleReason.'</span>';
                        $reasonContents .= '<div><span class="js-reason2 display-none">'.$handleReason.'</span>';
                        if (empty($val['handleCd']) === false) {
                            $reasonContents .= '(주문번호 : <a href="#" class="js-link-order" data-order-no="' . $val['handleCd'] . '">' . $val['handleCd'] . '</a>)</div>';
                        }
                        break;
                    case '01006006':
                    case '01006505':
                        $reasonContents = '<span class="js-reason1">'.$val['contents'].'</span>';
                        $reasonContents .= '<span class="js-reason2 display-none">'.gd_select_box('reasonModiyCode'.$val['sno'], 'reasonModiyCode['.$val['sno'].']', $depositReasons, null, $val['reasonCd']).'</span>';
                        $reasonContents .= '<div class="js-reason2 display-none"><input type="text" name="reasonContentsModify'.$val['sno'].'" class="form-control" value="'.$val['contents'].'" maxlength="250"/></div>';
                        break;

                }

                if($val['handleSno'] > 0 && $val['handleReason'] != ''){
                    $jsPreviewClass = 'js-preview';
                }else{
                    $jsPreviewClass = '';
                }

                $listHtml[] = '<tr class="center" data-member-no="' . $val['memNo'] . '">';
                $listHtml[] = '<td class="font-num">' . $page->idx-- . '<input type="hidden" name="sno" value="'.$val['sno'].'"> <input type="hidden" name="reasonCdOld" value="'.$val['reasonCd'].'"> <input type="hidden" name="contentsOld" value="'.$val['contents'].'"></td>';
                if ($isMinusSign) {
                    $listHtml[] = '<td class="font-num">-</td>';
                } else {
                    $listHtml[] = '<td class="font-num">(+)' . gd_money_format($val['deposit']) . gd_display_deposit('unit') . '</td>';
                }
                if ($isMinusSign) {
                    $listHtml[] = '<td class="font-num">(-)' . gd_money_format(substr($val['deposit'], 1)) . gd_display_deposit('unit') . '</td>';
                } else {
                    $listHtml[] = '<td class="font-num">-</td>';
                }
                $listHtml[] = '<td class="font-num">' . gd_money_format($val['afterDeposit']) . gd_display_deposit('unit') . '</td>';
                $listHtml[] = '<td class="font-date">' . gd_date_format('Y-m-d', $val['regDt']) . '<br/>' . gd_date_format('H:i', $val['regDt']) . '</td>';
                $listHtml[] = '<td class="center">' . $val['managerId'] . '</td>';
                $listHtml[] = '<td class="center width30p '.$jsPreviewClass.'" data-handle-sno="'.$val['handleSno'].'" data-order-no="'.$val['handleCd'].'">' . $reasonContents . ' <div class="depositPreview loading"></div></td>';

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
        } else {
            ?>
            <tr><td colspan="10" class="no-data">예치금 내역이 없습니다.</td></tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<div id="bottom"></div>
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
        });

        $('.btn-register').click(function () { // 온 타겟을 바꿔야함
            var loadChk = $('div#formMemberDeposit').length;
            $.get('../share/layer_member_deposit.php', {}, function (data) {
                if (loadChk === 0) {
                    data = '<div id="#formMemberDeposit">' + data + '</div>';
                }

                BootstrapDialog.show({
                    name: "layer_member_deposit",
                    title: "예치금 지급/차감",
                    size: BootstrapDialog.SIZE_WIDE,
                    message: $(data),
                    closable: true
                });
            });
        });
        $('select[name=reasonCd]').trigger('change');
        $('input[name=contents]').val('<?=$requestGetParams['contents']?>');
        $('select[name=\'pageNum\']').change({targetForm: '.js-search-form'}, member.page_number);

        $('.js-preview').hover(function () {

            var previewLayer = $(this).find(".depositPreview");
            var scrollOffset = $('html').scrollTop();
            var winHeight = $('html').height() + scrollOffset;
            var thisOffset = $(this).offset().top + previewLayer.outerHeight();
            var hoverEleWidth = $(this).width();
            var maxHeight = $('#bottom').offset().top;

            $(this).addClass('bgColor');

            if(thisOffset > winHeight){
                if(maxHeight > winHeight){
                    var setTopPosition = (thisOffset - winHeight) * -1 ;
                    previewLayer.css('top',setTopPosition - 10).css('left',-360).show();

                }else{
                    thisOffset = $(this).offset().top + previewLayer.outerHeight();
                    previewLayer.css('top',maxHeight-thisOffset).css('left',-360).show();
                }
            }else{
                previewLayer.css('top',-10).css('left',-360).show();
            }

            var self = this;

            if(previewLayer.html() == '') {
                $.get('../share/deposit_preview.php', {
                    handleSno: $(this).data('handle-sno'),
                    orderNo: $(this).data('order-no')
                }, function (data) {
                    var layerForm = '<div id="viewInfoForm">' + data + '</div>';
                    $(self).find(".depositPreview").empty().append(layerForm).removeClass('loading');
                });
            }

        },function(){
            $(this).find(".depositPreview").hide();
            $(this).removeClass('bgColor');

        });

        $('.js-reason1 > button').click(function () {
            var closest = $(this).closest('tr');
            var sno = $(closest).find('input[name=sno]').val();
            var reasonCdOld = $(closest).find('input[name=reasonCdOld]').val();
            var contents = $('input[name=reasonContentsModify'+ sno +']');
            if ('01006006' == reasonCdOld) {
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
                'mode': 'reasonCd_modify_deposit',
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
            if ('01006006' == option.val()) {
                contents.attr('type', 'text').focus();
            } else {
                contents.attr('type', 'hidden');
            }
        });

        $('select[name*="reasonModiyCode["]').css("display", "inline");
    });
</script>