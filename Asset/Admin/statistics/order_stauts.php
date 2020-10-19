<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get">
    <div class="table-title gd-help-manual">주문 검색</div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
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

                    <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="treatDate[]">
                        <?php if ($page['groupType'] == 'month') { ?>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="27" />1개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="83" />3개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="167" />6개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="335" />12개월</label>
                        <?php } else { ?>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="0" />오늘</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="6" />7일</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="13" />15일</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="27" />1개월</label>
                            <label class="btn btn-white btn-sm hand"><input type="radio" name="periodFl" value="83" />3개월</label>
                        <?php } ?>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation" <?=$page['groupType'] == 'day' ? 'class="active"' : ''?>>
        <a href="../statistics/order_area_day.php">일별 매출현황</a>
    </li>
    <li role="presentation" <?=$page['groupType'] == 'hour' ? 'class="active"' : ''?>>
        <a href="../statistics/order_area_hour.php">시간대별 매출현황</a>
    </li>
    <li role="presentation" <?=$page['groupType'] == 'week' ? 'class="active"' : ''?>>
        <a href="../statistics/order_area_week.php">요일별 매출현황</a>
    </li>
    <li role="presentation" <?=$page['groupType'] == 'month' ? 'class="active"' : ''?>>
        <a href="../statistics/order_area_month.php">월별 매출현황</a>
    </li>
</ul>

<table class="table table-cols">
    <colgroup>
        <col style="width:20%;" />
        <col style="width:16%;" />
        <col style="width:16%;" />
        <col style="width:16%;" />
        <col style="width:16%;" />
        <col style="width:16%;" />
    </colgroup>
    <thead>
    <tr>
        <th>
            <dl>
                <dt>총 구매자수</dt>
                <dd><span id="totalMemberCnt">0</span>명</dd>
            </dl>
            <ul class="list-unstyled">
                <li>PC쇼핑몰 | <span id="pcMemberCnt">0</span></li>
                <li>모바일쇼핑몰 | <span id="mobileMemberCnt">0</span></li>
                <li>수기주문 | <span id="writeMemberCnt">0</span></li>
            </ul>
        </th>
        <th>
            <dl>
                <dt>총 구매건수</dt>
                <dd><span id="totalOrderCnt">0</span>건</dd>
            </dl>
            <ul class="list-unstyled">
                <li>PC쇼핑몰 | <span id="pcOrderCnt">0</span></li>
                <li>모바일쇼핑몰 | <span id="mobileOrderCnt">0</span></li>
                <li>수기주문 | <span id="writeOrderCnt">0</span></li>
            </ul>
        </th>
        <th>
            <dl>
                <dt>총 구매개수</dt>
                <dd><span id="totalGoodsCnt">0</span>개</dd>
            </dl>
            <ul class="list-unstyled">
                <li>PC쇼핑몰 | <span id="pcGoodsCnt">0</span></li>
                <li>모바일쇼핑몰 | <span id="mobileGoodsCnt">0</span></li>
                <li>수기주문 | <span id="writeGoodsCnt">0</span></li>
            </ul>
        </th>
        <th>
            <dl>
                <dt>총 판매금액</dt>
                <dd><span id="totalGoodsPrice">0</span>원</dd>
            </dl>
            <ul class="list-unstyled">
                <li>PC쇼핑몰 | <span id="pcGoodsPrice">0</span></li>
                <li>모바일쇼핑몰 | <span id="mobileGoodsPrice">0</span></li>
                <li>수기주문 | <span id="writeGoodsPrice">0</span></li>
            </ul>
        </th>
        <th>
            <dl>
                <dt>최대/최소 구매건수</dt>
                <dd><span id="maxSalePrice">0</span>건 <span id="maxSaleDate" class="font-date">2015.01</span></dd>
            </dl>
            <ul class="list-unstyled">
                <li>최대 구매건수 | <span id="pcTotalOrderSalePrice">0</span></li>
                <li>최소 구매건수 | <span id="pcTotalRefundSalePrice">0</span></li>
            </ul>
        </th>
        <th>
            <dl>
                <dt>최대/최소 판매금액</dt>
                <dd><span id="minSalePrice">0</span>원 <span id="minSaleDate" class="font-date">2015.01</span></dd>
            </dl>
            <ul class="list-unstyled">
                <li>최대 판매금액 | <span id="pcTotalOrderSalePrice">0</span></li>
                <li>최소 판매금액 | <span id="pcTotalRefundSalePrice">0</span></li>
            </ul>
        </th>
    </tr>
    </thead>
</table>

<div class="table-action mgb0">
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel">엑셀 다운로드</button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-rows">
        <colgroup>
            <col width="10%"/>
            <col span="8" width="10%"/>
        </colgroup>
        <thead>
        <tr class="nowrap text-center">
            <th rowspan="2">날짜</th>
            <th rowspan="2">구분</th>
            <?php foreach ($ages as $age) { ?>
                <th colspan="4"><?=$age?>대</th>
            <?php } ?>
        </tr>
        <tr class="nowrap text-center">
            <?php foreach ($ages as $age) { ?>
                <th>구매자수</th>
                <th>구매건수</th>
                <th>구매개수</th>
                <th>판매금액</th>
            <?php } ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $total = [];
        $totalMemberCnt = [];
        $totalOrderCnt = [];
        $totalGoodsCnt = [];
        $totalGoodsPrice = [];
        for ($i = 0; $i <= $page['forLimit']; $i++) {
            switch ($page['groupType']) {
                case 'day':
                    $paymentDt = date('Y-m-d', strtotime('-' . ($page['forLimit'] - $i) . ' day', strtotime($search['treatDate'][1])));
                    $paymentDtStr = $paymentDt;
                    break;
                case 'hour':
                    $paymentDt = sprintf('%02d', $i);
                    $paymentDtStr = $paymentDt . ':00';
                    break;
                case 'week':
                    $paymentDt = $i;
                    $paymentDtStr = $page['dayOfWeek'][$i];
                    break;
                case 'month':
                    $paymentDt = gd_previous_month_date(($page['forLimit'] - $i), $search['treatDate'][1]);
                    $paymentDtStr = $paymentDt;
                    break;
            }

            // column별 합계
            foreach ($ages as $age) {
                // row별 합계
                $total[$age]['memberCnt']['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['memberCnt'], 0);
                $total[$age]['orderCnt']['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['orderCnt'], 0);
                $total[$age]['goodsCnt']['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['goodsCnt'], 0);
                $total[$age]['goodsPrice']['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['goodsPrice'], 0);
                $total[$age]['memberCnt']['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['memberCnt'], 0);
                $total[$age]['orderCnt']['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['orderCnt'], 0);
                $total[$age]['goodsCnt']['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['goodsCnt'], 0);
                $total[$age]['goodsPrice']['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['goodsPrice'], 0);
                $total[$age]['memberCnt']['write'] += gd_isset($payment['write'][$paymentDt][$age]['memberCnt'], 0);
                $total[$age]['orderCnt']['write'] += gd_isset($payment['write'][$paymentDt][$age]['orderCnt'], 0);
                $total[$age]['goodsCnt']['write'] += gd_isset($payment['write'][$paymentDt][$age]['goodsCnt'], 0);
                $total[$age]['goodsPrice']['write'] += gd_isset($payment['write'][$paymentDt][$age]['goodsPrice'], 0);

                // 전체 합계
                $totalMemberCnt['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['memberCnt'], 0);
                $totalMemberCnt['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['memberCnt'], 0);
                $totalMemberCnt['write'] += gd_isset($payment['write'][$paymentDt][$age]['memberCnt'], 0);
                $totalOrderCnt['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['orderCnt'], 0);
                $totalOrderCnt['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['orderCnt'], 0);
                $totalOrderCnt['write'] += gd_isset($payment['write'][$paymentDt][$age]['orderCnt'], 0);
                $totalGoodsCnt['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['goodsCnt'], 0);
                $totalGoodsCnt['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['goodsCnt'], 0);
                $totalGoodsCnt['write'] += gd_isset($payment['write'][$paymentDt][$age]['goodsCnt'], 0);
                $totalGoodsPrice['pc'] += gd_isset($payment['pc'][$paymentDt][$age]['goodsPrice'], 0);
                $totalGoodsPrice['mobile'] += gd_isset($payment['mobile'][$paymentDt][$age]['goodsPrice'], 0);
                $totalGoodsPrice['write'] += gd_isset($payment['write'][$paymentDt][$age]['goodsPrice'], 0);
            }

            // 최대 구매건수
            if (!isset($maxMemberCnt)) {
                $maxMemberCnt = $memberCnt;
                $maxMemberCntDate = $paymentDtStr;
            }
            if ($maxMemberCnt < $memberCnt) {
                $maxMemberCnt = $memberCnt;
                $maxMemberCntDate = $paymentDtStr;
            }

            // 최소 구매건수
            if ($memberCnt > 0) {
                if (!isset($minMemberCnt)) {
                    $minMemberCnt = $memberCnt;
                    $minMemberCntDate = $paymentDtStr;
                }
                if ($minMemberCnt > $memberCnt) {
                    $minMemberCnt = $memberCnt;
                    $minMemberCntDate = $paymentDtStr;
                }
            }

            // 최대 판매금액
            if (!isset($maxGoodsPrice)) {
                $maxGoodsPrice = $goodsPrice;
                $maxGoodsPriceDate = $paymentDtStr;
            }
            if ($maxGoodsPrice < $goodsPrice) {
                $maxGoodsPrice = $goodsPrice;
                $maxGoodsPriceDate = $paymentDtStr;
            }

            // 최소 판매금액
            if ($goodsPrice > 0) {
                if (!isset($minGoodsPrice)) {
                    $minGoodsPrice = $goodsPrice;
                    $minGoodsPriceDate = $paymentDtStr;
                }
                if ($minGoodsPrice > $goodsPrice) {
                    $minGoodsPrice = $goodsPrice;
                    $minGoodsPriceDate = $paymentDtStr;
                }
            }
            ?>
            <tr class="nowrap text-right">
                <td class="font-date" rowspan="3"><span class="font-date"><?php echo $paymentDtStr; ?></span></td>
                <td class="font-num">PC쇼핑몰</td>
                <?php foreach ($ages as $age) { ?>
                    <td class="font-num border-left"><?php echo number_format(gd_isset($payment['pc'][$paymentDt][$age]['memberCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['pc'][$paymentDt][$age]['orderCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['pc'][$paymentDt][$age]['goodsCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['pc'][$paymentDt][$age]['goodsPrice'], 0)); ?></td>
                <?php } ?>
            </tr>
            <tr class="nowrap text-right">
                <td class="font-num">모바일쇼핑몰</td>
                <?php foreach ($ages as $age) { ?>
                    <td class="font-num border-left"><?php echo number_format(gd_isset($payment['mobile'][$paymentDt][$age]['memberCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['mobile'][$paymentDt][$age]['orderCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['mobile'][$paymentDt][$age]['goodsCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['mobile'][$paymentDt][$age]['goodsPrice'], 0)); ?></td>
                <?php } ?>
            </tr>
            <tr class="nowrap text-right">
                <td class="font-num">수기주문</td>
                <?php foreach ($ages as $age) { ?>
                    <td class="font-num border-left"><?php echo number_format(gd_isset($payment['write'][$paymentDt][$age]['memberCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['write'][$paymentDt][$age]['orderCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['write'][$paymentDt][$age]['goodsCnt'], 0)); ?></td>
                    <td class="font-num"><?php echo number_format(gd_isset($payment['write'][$paymentDt][$age]['goodsPrice'], 0)); ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
        <tr class="nowrap text-right">
            <th class="font-num" rowspan="3">합계</th>
            <th class="font-num">PC쇼핑몰</th>
            <?php foreach ($ages as $age) { ?>
                <th class="font-num border-left"><?php echo number_format(gd_isset($total[$age]['memberCnt']['pc'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['orderCnt']['pc'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['goodsCnt']['pc'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['goodsPrice']['pc'], 0)); ?></th>
            <?php } ?>
        </tr>
        <tr class="nowrap text-right">
            <th class="font-num">모바일쇼핑몰</th>
            <?php foreach ($ages as $age) { ?>
                <th class="font-num border-left"><?php echo number_format(gd_isset($total[$age]['memberCnt']['mobile'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['orderCnt']['mobile'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['goodsCnt']['mobile'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['goodsPrice']['mobile'], 0)); ?></th>
            <?php } ?>
        </tr>
        <tr class="nowrap text-right">
            <th class="font-num">수기주문</th>
            <?php foreach ($ages as $age) { ?>
                <th class="font-num border-left"><?php echo number_format(gd_isset($total[$age]['memberCnt']['write'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['orderCnt']['write'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['goodsCnt']['write'], 0)); ?></th>
                <th class="font-num"><?php echo number_format(gd_isset($total[$age]['goodsPrice']['write'], 0)); ?></th>
            <?php } ?>
        </tr>

        </tfoot>
    </table>
</div>

<script type="text/javascript">
    $(function(){
        $('#totalMemberCnt').text('<?=number_format(array_sum($totalMemberCnt))?>');
        $('#pcMemberCnt').text('<?=number_format($totalMemberCnt['pc'])?>');
        $('#mobileMemberCnt').text('<?=number_format($totalMemberCnt['mobile'])?>');
        $('#writeMemberCnt').text('<?=number_format($totalMemberCnt['write'])?>');

        $('#totalOrderCnt').text('<?=number_format(array_sum($totalOrderCnt))?>');
        $('#pcOrderCnt').text('<?=number_format($totalOrderCnt['pc'])?>');
        $('#mobileOrderCnt').text('<?=number_format($totalOrderCnt['mobile'])?>');
        $('#writeOrderCnt').text('<?=number_format($totalOrderCnt['write'])?>');

        $('#totalGoodsCnt').text('<?=number_format(array_sum($totalGoodsCnt))?>');
        $('#pcGoodsCnt').text('<?=number_format($totalGoodsCnt['pc'])?>');
        $('#mobileGoodsCnt').text('<?=number_format($totalGoodsCnt['mobile'])?>');
        $('#writeGoodsCnt').text('<?=number_format($totalGoodsCnt['write'])?>');

        $('#totalGoodsPrice').text('<?=number_format(array_sum($totalGoodsPrice))?>');
        $('#pcGoodsPrice').text('<?=number_format($totalGoodsPrice['pc'])?>');
        $('#mobileGoodsPrice').text('<?=number_format($totalGoodsPrice['mobile'])?>');
        $('#writeGoodsPrice').text('<?=number_format($totalGoodsPrice['write'])?>');

        // 기간 체크
        $('#frmSearchBase').validate({
            dialog: false,
            submitHandler: function(form) {
                $elements = $('input[name="treatDate[]"]');
                interval = moment($($elements[1]).val()).diff(moment($($elements[0]).val()), 'days');
                if (interval > <?=$page['maxLimit']?>) {
                    alert('최대 <?=intval($page['maxLimit']/30)?>개월까지 조회할 수 있습니다.');
                    return false;
                }

                form.submit();
            }
        });
    });
</script>
