<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>

    <?php //if (!isset($isProvider) && $isProvider != true) { ?>
    <div class="btn-group">
        <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./bankda_match.php');">
        <input type="button" class="btn btn-red-line bankdaManaulMatchLink" value="매칭">
    </div>
    <?php //} ?>
</div>

<table class="table table-cols">
    <colgroup>
        <col width="49.5%">

        <col width="49.5%">
    </colgroup>
    <tbody>
    <tr>
        <td style="vertical-align:top">
            <form id="frmBankSearchBase" method="get" class="js-form-enter-submit">
                <!--입금 내역-->
                <input type="hidden" name="sort"/>
                <input type="hidden" name="page_num"/>
                <input type="hidden" name="query"/>

                <div class="table-title gd-help-manual">입금내역 검색</div>

                <table class="table table-cols">
                    <colgroup>
                        <col>
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th>검색어</th>
                        <td>
                            <div class="form-inline">
                                <?php echo gd_select_box('skey', 'skey', array('bkjukyo' => '입금자명', 'bkinput' => '입금예정금액'), '', gd_isset($search['key']), ''); ?>
                                <input type="text" name="sword" value="<?php echo gd_isset($search['keyword']); ?>" class="form-control"/>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>입금일</th>
                        <td colspan="3">
                            <div class="form-inline">
                                <div class="input-group js-datepicker">
                                    <input type="text" name="bkdate[]" value="<?= $search['bkdate'][0]; ?>" class="form-control width-xs">
                                    <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                    </span>
                                </div>
                                ~
                                <div class="input-group js-datepicker">
                                    <input type="text" name="bkdate[]" value="<?= $search['bkdate'][1]; ?>" class="form-control width-xs">
                                    <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                    </span>
                                </div>

                                <?= gd_search_date(gd_isset($search['periodFl'], 6), 'bkdate[]', false) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>은행명</th>
                        <td>
                            <div class="form-inline">
                                <select name="bkname" class="form-control">
                                    <option value="">↓은행검색</option>
                                    <?php foreach($rBank as $v) { ?>
                                        <option value="<?php echo $v; ?>" <?php echo gd_isset($selected['bkname'][$v]); ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>현재상태</th>
                        <td>
                            <div class="form-inline">
                                <select name="gdstatus" class="form-control">
                                    <option value="F" <?php echo gd_isset($selected['gdstatus']['F']); ?>>매칭실패(불일치)</option>
                                    <option value="S" <?php echo gd_isset($selected['gdstatus']['S']); ?>>매칭실패(동명이인)</option>
                                    <option value="U" <?php echo gd_isset($selected['gdstatus']['U']); ?>>관리자미확인</option>
                                </select>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="table-btn">
                    <input type="submit" value="검색" class="btn btn-lg btn-black">
                </div>
            </form>

            <!--입금내역 리스트-->
            <form id="frmBankList" action="" method="get">
                <input type="hidden" name="mode" value="">
                <div class="table-header form-inline">
                    <div class="pull-left">
                        검색 <b><span id="page_recode">0</span></b>개 / 전체 <b><span id="page_rtotal">0</span></b>개
                    </div>
                    <div class="pull-right">
                        <div>
                            <select name="sort" class="form-control">
                                <option value="bkdate desc" <?php echo gd_isset($selected['sort']['entryDt desc']); ?>>입금일 ↓</option>
                                <option value="bkdate asc" <?php echo gd_isset($selected['sort']['entryDt asc']); ?>>입금일 ↑</option>
                                <option value="gddatetime desc" <?php echo gd_isset($selected['sort']['entryDt asc']); ?>>최종매칭일 ↓</option>
                                <option value="gddatetime asc" <?php echo gd_isset($selected['sort']['entryDt asc']); ?>>최종매칭일 ↑</option>
                            </select>&nbsp;
                            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50]), '개 보기', Request::get()->get('page_num'), null); ?>
                        </div>
                    </div>
                </div>
                <table class="table table-rows" id="list_form">
                    <colgroup>
                        <col width="5%">
                        <col width="10%">
                        <col width="10%">
                        <col width="20%">
                        <col width="15%">
                        <col width="15%">
                        <col width="20%">
                    </colgroup>
                    <thead>
                    <tr>
                        <th></th>
                        <th>번호</th>
                        <th>입금일</th>
                        <th>계좌번호</th>
                        <th>입금금액</th>
                        <th>입금자명</th>
                        <th>현재상태</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

                <div class="center">
                    <nav>
                        <ul class="pagination pagination-sm" id="page_navi"></ul>
                    </nav>
                </div>
            </form>
            <!--입금 내역 END-->
        </td>

        <td style="vertical-align:top">
            <form id="frmOrdSearchBase" method="get" class="js-form-enter-submit">
                <!--주문 내역-->
                <input type="hidden" name="view" value="<?= $ordSearch['view'] ?>"/>
                <input type="hidden" name="searchFl" value="y">

                <div class="table-title gd-help-manual">입금대기 주문 검색</div>

                <table class="table table-cols">
                    <colgroup>
                        <col>
                        <col>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th>검색어</th>
                        <td>
                            <div class="form-inline">
                                <?php echo gd_select_box('key', 'key', array('o.bankSender' => '입금자명', 'oi.orderName' => '주문자명', 'oi.receiverName' => '수령자명', 'o.orderNo' => '주문번호'), '', gd_isset($ordSearch['keyword']), ''); ?>
                                <input type="text" name="keyword" value="<?php echo gd_isset($ordSearch['keyword']); ?>" class="form-control"/>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>주문일</th>
                        <td colspan="3">
                            <div class="form-inline">
                                <div class="input-group js-datepicker">
                                    <input type="text" name="treatDate[]" value="<?= $ordSearch['treatDate'][0]; ?>" class="form-control width-xs">
                                    <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                    </span>
                                </div>
                                ~
                                <div class="input-group js-datepicker">
                                    <input type="text" name="treatDate[]" value="<?= $ordSearch['treatDate'][1]; ?>" class="form-control width-xs">
                                    <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                    </span>
                                </div>

                                <?= gd_search_date(gd_isset($ordSearch['periodFl'], 6), 'treatDate[]', false) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>실 결제금액</th>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="settlePrice[]" value="<?= $ordSearch['settlePrice'][0]; ?>" class="form-control width-sm"/>원 ~
                                <input type="text" name="settlePrice[]" value="<?= $ordSearch['settlePrice'][1]; ?>" class="form-control width-sm"/>원
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="notice-info">주문 상품 중 일부만 취소된 '부분취소' 주문은 수동매칭 지원이 되지 않습니다.</div>

                <div class="table-btn">
                    <input type="submit" value="검색" class="btn btn-lg btn-black" style="margin-top:23px;">
                </div>
                <div class="table-header form-inline">
                    <div class="pull-left">
                        검색 <b><span id="ord_page_recode">0</span></b>개 / 전체 <b><span id="ord_page_rtotal">0</span></b>개
                    </div>
                    <div class="pull-right">
                        <div>
                            <select name="sort" class="form-control">
                                <option value="og.orderNo desc" <?php echo gd_isset($ordSelected['sort']['og.orderNo desc']); ?>>주문번호 ↓</option>
                                <option value="og.orderNo asc" <?php echo gd_isset($ordSelected['sort']['og.orderNo asc']); ?>>주문번호 ↑</option>
                                <option value="o.settlePrice desc" <?php echo gd_isset($ordSelected['sort']['o.settlePrice desc']); ?>>실결제금액 ↓</option>
                                <option value="o.settlePrice asc" <?php echo gd_isset($ordSelected['sort']['o.settlePrice asc']); ?>>실결제금액 ↑</option>
                            </select>&nbsp;
                            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50]), '개 보기', Request::get()->get('pageNum'), null); ?>
                        </div>
                    </div>
                </div>
                <table class="table table-rows" id="ord_list_form">
                    <colgroup>
                        <col width="5%">
                        <col width="10%">
                        <col width="13%">
                        <col width="10%">
                        <col width="12%">
                        <col width="10%">
                        <col width="10%">
                    </colgroup>
                    <thead>
                    <tr>
                        <th></th>
                        <th>번호</th>
                        <th>주문일시</th>
                        <th>주문번호</th>
                        <th>실결제금액</th>
                        <th>임금자명</th>
                        <th>처리상태</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

                <div class="center" id="ord_page_navi">
                </div>
                <!--주문 내역 END-->
        </td>
    </tr>
    </tbody>
</table>


<script type="text/javascript">
    $(document).ready(function () {

        /*
            입금내역 검색
        */

        // 정렬&출력수
        $('#frmBankList select[name=sort]').change(function (e) {
            $('input[name=sort]').val($(this).val());
            $('#frmBankSearchBase').submit();
        });

        //page 버튼 액션
        $('#frmBankList select[name=pageNum]').change(function (e) {
            $('input[name=page_num]').val($(this).val());
            $('#frmBankSearchBase').submit();
        });

        // Bankda 리스트
        $('#frmBankSearchBase').submit(function (e) {
            account_unmaching_list();
            return false;
        });

        /*
            입금대기 주문 검색
        */
        $('#frmOrdSearchBase select[name=sort]').change(function (e) {
            $('input[name=sort]').val($(this).val());
            $('#frmOrdSearchBase').submit();
        });

        $('#frmOrdSearchBase select[name=pageNum]').change(function (e) {
            $('input[name=pageNum]').val($(this).val());
            $('#frmOrdSearchBase').submit();
        });

        // 주문 리스트
        $('#frmOrdSearchBase').submit(function (e) {
            manualMatchOrderList();
            return false;
        });

        // 입금 매칭 버튼 클릭
        $('.bankdaManaulMatchLink').click(function () {
            var bkCodeCnt = $('input[name="bkcode"]:checked').length;
            var orderNoCnt = $('input[name="statusCheck[]"]:checked').length;
            if(bkCodeCnt < 1) {
                dialog_alert('입금내역을 선택해주세요..', '경고');
                return false;
            } else {
                if(bkCodeCnt > 10) {
                    dialog_alert('입금내역은 최대 10건까지 선택 가능합니다.', '경고');
                    return false;
                }
            }
            if(orderNoCnt < 1) {
                dialog_alert('주문내역을 선택해주세요.', '경고');
                return false;
            } else {
                if(orderNoCnt > 10) {
                    dialog_alert('주문내역은 최대 10건까지 선택 가능합니다.', '경고');
                    return false;
                }
            }

            dialog_confirm(bkCodeCnt + '건의 입금내역과 ' + orderNoCnt + '건의 주문내역을 매칭하시겠습니까?<br/>매칭 시 주문의 처리상태가 <span style="color:#fa2828">결제완료</span>로 변경됩니다.', function (result) {
                if(result) {
                    var orderNoStringArray = new Array;
                    var orderNoString = "";
                    $('input[name="statusCheck[]"]:checked').each(function () {
                        orderNoStringArray.push($(this).val());
                    });
                    orderNoString = orderNoStringArray.join('||');

                    var orderNoBankda = '<input type="hidden" name="bkmemo4" value="' + orderNoString + '">'
                    orderNoBankda += '<input type="hidden" name="gdstatus" value="M">';
                    $('input[name="bkcode"]:checked').each(function () {
                        $(this).parents('tr').first().append(orderNoBankda);
                    });
                    layer_open_batch_update('manualBatchUpdate');
                }
            });

        });
    });

</script>