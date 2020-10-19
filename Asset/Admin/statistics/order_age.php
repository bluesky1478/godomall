<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get">
    <div class="table-title gd-help-manual">주문 검색 <span class="notice-danger">통계 데이터는 2시간마다 집계되므로 주문데이터와 약 1시간~2시간의 데이터 오차가 있을 수 있습니다.</span></div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <?php
        if ($tabName == 'month') {
            ?>
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datepicker-months">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $searchDate[0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker-months">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $searchDate[1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>

                        <div class="btn-group js-dateperiod-months" data-toggle="buttons" data-target-name="searchDate[]">
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['0']; ?>">
                                <input type="radio" name="searchPeriod" value="0" <?= $checked['searchPeriod']['0']; ?> >1개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['2']; ?>">
                                <input type="radio" name="searchPeriod" value="2" <?= $checked['searchPeriod']['2']; ?> >3개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['5']; ?>">
                                <input type="radio" name="searchPeriod" value="5" <?= $checked['searchPeriod']['5']; ?> >6개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['11']; ?>">
                                <input type="radio" name="searchPeriod" value="11" <?= $checked['searchPeriod']['11']; ?> >12개월
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <th>기간검색</th>
                <td>
                    <div class="form-inline">
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $searchDate[0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $searchDate[1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>

                        <div class="btn-group js-dateperiod" data-toggle="buttons" data-target-name="searchDate[]">
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['0']; ?>">
                                <input type="radio" name="searchPeriod" value="0" <?= $checked['searchPeriod']['0']; ?> >오늘
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['6']; ?>">
                                <input type="radio" name="searchPeriod" value="6" <?= $checked['searchPeriod']['6']; ?> >7일
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['14']; ?>">
                                <input type="radio" name="searchPeriod" value="14" <?= $checked['searchPeriod']['14']; ?> >15일
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['29']; ?>">
                                <input type="radio" name="searchPeriod" value="29" <?= $checked['searchPeriod']['29']; ?> >1개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['89']; ?>">
                                <input type="radio" name="searchPeriod" value="89" <?= $checked['searchPeriod']['89']; ?> >3개월
                            </label>
                        </div>
                    </div>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation" <?= $tabName == 'day' ? 'class="active"' : '' ?>>
        <a href="../statistics/order_age_day.php<?= $queryString ?>">일별 주문현황</a>
    </li>
    <li role="presentation" <?= $tabName == 'hour' ? 'class="active"' : '' ?>>
        <a href="../statistics/order_age_hour.php<?= $queryString ?>">시간대별 주문현황</a>
    </li>
    <li role="presentation" <?= $tabName == 'week' ? 'class="active"' : '' ?>>
        <a href="../statistics/order_age_week.php<?= $queryString ?>">요일별 주문현황</a>
    </li>
    <li role="presentation" <?= $tabName == 'month' ? 'class="active"' : '' ?>>
        <a href="../statistics/order_age_month.php<?= $queryString ?>">월별 주문현황</a>
    </li>
</ul>

<div class="table-dashboard">
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
            <th class="bln point">
                총 판매금액
            </th>
            <th>
                총 구매건수
            </th>
            <th>
                총 구매자수
            </th>
            <th>
                총 구매개수
            </th>
            <th>
                최대/최소 판매금액
            </th>
            <th>
                최대/최소 구매건수
            </th>
        </tr>
        </thead>
        <tbody>
        <td class="bln point">
            <strong><?= gd_money_format(array_sum($deviceSales['goodsPriceTotal'])); ?></strong>원
            <ul class="list-unstyled">
                <li><strong>PC</strong><span><?= gd_money_format($deviceSales['goodsPriceTotal']['pc']); ?></span></li>
                <li><strong>모바일</strong><span><?= gd_money_format($deviceSales['goodsPriceTotal']['mobile']); ?></span></li>
                <li><strong>수기주문</strong><span><?= gd_money_format($deviceSales['goodsPriceTotal']['write']); ?></span></li>
            </ul>
        </td>
        <td>
            <strong><?= gd_money_format(array_sum($deviceSales['orderCntTotal'])); ?></strong>
            <ul class="list-unstyled">
                <li><strong>PC</strong><span><?= gd_money_format($deviceSales['orderCntTotal']['pc']); ?></span></li>
                <li><strong>모바일</strong><span><?= gd_money_format($deviceSales['orderCntTotal']['mobile']); ?></span></li>
                <li><strong>수기주문</strong><span><?= gd_money_format($deviceSales['orderCntTotal']['write']); ?></span></li>
            </ul>
        </td>
        <td>
            <strong><?= gd_money_format(array_sum($deviceSales['memberCntTotal'])); ?></strong>
            <ul class="list-unstyled">
                <li><strong>PC</strong><span><?= gd_money_format($deviceSales['memberCntTotal']['pc']); ?></span></li>
                <li><strong>모바일</strong><span><?= gd_money_format($deviceSales['memberCntTotal']['mobile']); ?></span></li>
                <li><strong>수기주문</strong><span><?= gd_money_format($deviceSales['memberCntTotal']['write']); ?></span></li>
            </ul>
        </td>
        <td>
            <strong><?= gd_money_format(array_sum($deviceSales['goodsCntTotal'])); ?></strong>
            <ul class="list-unstyled">
                <li><strong>PC</strong><span><?= gd_money_format($deviceSales['goodsCntTotal']['pc']); ?></span></li>
                <li><strong>모바일</strong><span><?= gd_money_format($deviceSales['goodsCntTotal']['mobile']); ?></span></li>
                <li><strong>수기주문</strong><span><?= gd_money_format($deviceSales['goodsCntTotal']['write']); ?></span></li>
            </ul>
        </td>
        <td>
            <ul class="list-unstyled">
                <li><strong>최대 판매금액</strong><span><?= gd_money_format($daySalesTotal['max']['price']); ?></span></li>
                <li><strong>최소 판매금액</strong><span><?= gd_money_format($daySalesTotal['min']['price']); ?></span></li>
            </ul>
        </td>
        <td>
            <ul class="list-unstyled">
                <li><strong>최대 구매건수</strong><span><?= gd_money_format($daySalesTotal['max']['orderCnt']); ?></span></li>
                <li><strong>최소 구매건수</strong><span><?= gd_money_format($daySalesTotal['min']['orderCnt']); ?></span></li>
            </ul>
        </td>
        </tbody>
    </table>
</div>

<div class="table-action mgt30 mgb0">
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
    </div>
</div>

<div class="code-html js-excel-data">
    <div id="grid"></div>
</div>

<script type="text/javascript" class="code-js">
    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: false,
        columnFixCount: 2,
        headerHeight: 80,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnMerge : [
            <?php
            for ($i = 10; $i <= 70; $i += 10) {
            ?>
            {
                columnName: "<?= $i; ?>",
                title: "<?= $i; ?>대",
                columnNameList: ["goodsPrice<?= $i; ?>", "orderCnt<?= $i; ?>", "memberCnt<?= $i; ?>", "goodsCnt<?= $i; ?>"]
            },
            <?php
            }
            ?>
            {
                columnName : "etc",
                title : "연령미확인",
                columnNameList : ["goodsPriceEtc", "orderCntEtc", "memberCntEtc", "goodsCntEtc"]
            }
        ],
        columnModelList: [
            {
                "title": "<b>날짜</b>",
                "columnName": "paymentDate",
                "align": "center",
                "width": 80,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title": "<b>구분</b>",
                "columnName": "orderDevice",
                "align": "center",
                "width": 80,
                editOption: {
                    type: 'normal'
                }
            },
            <?php
            for ($i = 10; $i <= 70; $i += 10) {
            ?>
            {
                "title" : "<b>판매금액</b>",
                "columnName" : "goodsPrice<?= $i; ?>",
                "align" : "right",
                "width" : 80,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매건수</b>",
                "columnName" : "orderCnt<?= $i; ?>",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매자수</b>",
                "columnName" : "memberCnt<?= $i; ?>",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매개수</b>",
                "columnName" : "goodsCnt<?= $i; ?>",
                "width" : 50,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            <?php
            }
            ?>
            {
                "title" : "<b>판매금액</b>",
                "columnName" : "goodsPriceEtc",
                "align" : "right",
                "width" : 80,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매건수</b>",
                "columnName" : "orderCntEtc",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매자수</b>",
                "columnName" : "memberCntEtc",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매개수</b>",
                "columnName" : "goodsCntEtc",
                "width" : 50,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);

    //    grid.use('Net', {
    //        el: $('#grid'),
    //        initialRequest: true,
    //        readDataMethod: 'GET',
    //        perPage: 500,
    //        enableAjaxHistory: true,
    //        api: {
    //            readData: '/sample',
    //            downloadExcel: '/download/excel',
    //            downloadExcelAll: '/download/excelAll'
    //        }
    //    });
    // 엑셀다운로드
    $('.btn-excel').click(function () {
        grid.setDisplayRowCount('<?=$orderCount?>');
        statistics_excel_download('연령별 ');
        grid.setDisplayRowCount('<?= $rowDisplay; ?>');
    });
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
