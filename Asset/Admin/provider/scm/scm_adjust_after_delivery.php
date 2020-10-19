<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="content-form js-form-enter-submit">
    <div class="table-title gd-help-manual">
        정산 후 배송비 검색
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, null, 'form-control input-sm'); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('treatDateFl', 'treatDateFl', $search['combineTreatDate'], null, $search['treatDateFl'], null, null, 'form-control input-sm'); ?>
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][0]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" name="treatDate[]" value="<?= $search['treatDate'][1]; ?>" class="form-control width-xs">
                                <span class="input-group-addon">
                                    <span class="btn-icon-calendar">
                                    </span>
                                </span>
                        </div>
                        <?= gd_search_date($search['treatDate'], 'treatDate', false) ?>
                    </div>
                </td>
            </tr>
            <?php if (empty($statusSearchableRange) === false) { ?>
                <tr>
                    <th>주문상태</th>
                    <td colspan="3">
                        <dl class="dl-horizontal dl-checkbox">
                            <dt>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="orderStatus[]" value="" class="js-not-checkall" data-target-name="orderStatus[]" <?= gd_isset($checked['orderStatus']['']) ?>/> 전체
                                </label>
                            </dt>
                            <dd>
                                <?php $chk = 0;
                                foreach ($statusSearchableRange as $key => $val) { ?>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="orderStatus[]" value="<?= $key ?>" <?= gd_isset($checked['orderStatus'][$key]) ?> /> <?= $val ?>
                                    </label>
                                    <?php $chk++;
                                    if ($chk % 8 == 0) {
                                        echo '<br/>';
                                    }
                                } ?>
                            </dd>
                        </dl>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="text-center mgb30">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>개 /
            전체 <strong class="text-danger"><?= number_format(gd_isset($page->recode['amount'], 0)); ?></strong>개
        </div>
        <div class="pull-right">
            <ul>
                <li>
                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
                </li>
                <li>
                    <?php echo gd_select_box(
                        'pageNum', 'pageNum', gd_array_change_key_value(
                        [
                            10,
                            20,
                            30,
                            40,
                            50,
                            60,
                            70,
                            80,
                            90,
                            100,
                            200,
                            300,
                            500,
                        ]
                    ), '개 출력', Request::get()->get('pageNum'), null
                    ); ?>
                </li>
            </ul>
        </div>
    </div>
</form>

<form id="frmScmAdjust" action="./scm_adjust_ps.php" method="post">
    <input type="hidden" name="mode" value="insertScmAdjustAfterDelivery"/>
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width3p">
                <input type="checkbox" value="y" class="js-checkall" data-target-name="orderDeliveryNo"/>
            </th>
            <th class="width3p">번호</th>
            <th class="width5p">주문일시</th>
            <th class="width10p">주문번호</th>
            <th class="width7p">주문자</th>
            <th class="width5p">배송번호</th>
            <th class="width7p">배송비</th>
            <th class="width7p">수수료</th>
            <th class="width7p">배송수수료</th>
            <th class="width7p">정산금액</th>
            <th class="width7p">처리상태</th>
            <th class="width10p">공급사</th>
            <th class="width5p">결제방법</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $key => $val) {
                ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="orderDeliveryNo[]" value="<?= $val['sno']; ?>"/></td>
                    <td class="font-num">
                        <small><?= $page->idx--; ?></small>
                    </td>
                    <td><?= str_replace(' ', '<br>', gd_date_format('Y-m-d H:i', $val['regDt'])); ?></td>
                    <td>
                        <a href="../order/order_view.php?orderNo=<?= $val['orderNo']; ?>" title="주문번호" target="_blank" class="btn btn-link"><?= $val['orderNo']; ?></a>
                    </td>
                    <td>
                        <?= $val['orderName'] ?>
                        <p>
                            <?php if (!$val['memNo']) { ?>
                                (비회원)
                            <?php } else { ?>
                                (<?= $val['memId'] ?>)
                            <?php } ?>
                        </p>
                    </td>
                    <td class="border-left font-num"><?= $val['deliverySno'] ?></td>
                    <td><?= gd_currency_display($val['deliveryCharge']); ?></td>
                    <td><?= $val['commission']; ?>%</td>
                    <td><?= gd_currency_symbol() ?><?= gd_money_format($val['deliveryAdjustCommission']); ?></span><?= gd_currency_string() ?></td>
                    <td><?= gd_currency_symbol() ?><?= gd_money_format($val['deliveryAdjustPrice']); ?></span><?= gd_currency_string() ?></td>
                    <td>
                        <div title="주문 상품별 주문 상태"><?= $val['orderStatusStr']; ?></div>
                    </td>
                    <td><?= $val['companyNm'] ?></td>
                    <td>
                        <?= gd_html_image(UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon', 'icon_settlekind_' . $val['settleKind'] . '.gif')->www(), $val['settleKindStr']); ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="20" class="no-data">
                    검색된 배송비가 없습니다.
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 배송비를</span>
            <button type="submit" class="btn btn-white"/>
            정산요청</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white btn-icon-excel js-excel-download" data-target-form="frmSearchGoods" data-target-list-form="frmScmAdjust" data-target-list-sno="orderDeliveryNo[]" data-search-count="<?=$page->recode['total']?>" data-total-count="<?=$page->recode['amount']?>">엑셀다운로드</button>
        </div>
    </div>
</form>
<div class="text-center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    $(document).ready(function () {
        // 폼체크
        $('#frmScmAdjust').validate({
            submitHandler: function (form) {
                if ($('input[name*=orderDeliveryNo]:checked').length < 1) {
                    alert('선택된 배송비가 없습니다.');
                    return false;
                }

                form.target = 'ifrmProcess';
                form.submit();
            }
        });

        // 리스트 정렬
        $('#sort, #pageNum').change(function (e) {
            $('#frmSearchGoods').submit();
        });
    });
</script>
