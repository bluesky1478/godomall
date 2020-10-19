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
        <?php if (count($searchMallList) > 1) { ?>
            <tr>
                <th>상점</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="mallFl" value="all" <?= gd_isset($checked['searchMall']['all']); ?>/>전체
                    </label>
                    <?php
                    foreach ($searchMallList as $val) {
                        ?>
                        <label class="radio-inline">
                            <input type="radio" name="mallFl" value="<?= $val['sno'] ?>" <?= gd_isset($checked['searchMall'][$val['sno']]); ?>/><span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span> <?= $val['mallName'] ?>
                        </label>
                        <?php
                    }
                    ?>
                </td>
            </tr>
        <?php } ?>
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
    <li role="presentation" <?=$tabName == 'day' ? 'class="active"' : ''?>>
        <a href="order_day.php<?=$queryString?>">일별 주문현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'hour' ? 'class="active"' : ''?>>
        <a href="../statistics/order_hour.php<?=$queryString?>">시간대별 주문현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'week' ? 'class="active"' : ''?>>
        <a href="../statistics/order_week.php<?=$queryString?>">요일별 주문현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'month' ? 'class="active"' : ''?>>
        <a href="../statistics/order_month.php<?=$queryString?>">월별 주문현황</a>
    </li>
    <?php if (!$isProvider) { ?>
        <li role="presentation" <?=$tabName == 'member' ? 'class="active"' : ''?>>
            <a href="../statistics/order_member.php<?=$queryString?>">회원구분 주문현황</a>
        </li>
    <?php } ?>
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
        columnFixCount: 1,
        headerHeight: 80,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnMerge : [
            {
                columnName : "total",
                title : "전체",
                columnNameList : ["goodsPriceTotal", "orderCntTotal", "memberCntTotal", "goodsCntTotal"]
            },
            {
                columnName : "pc",
                title : "PC쇼핑몰",
                columnNameList : ["goodsPricePc", "orderCntPc", "memberCntPc", "goodsCntPc"]
            },
            {
                columnName : "mobile",
                title : "모바일쇼핑몰",
                columnNameList : ["goodsPriceMobile", "orderCntMobile", "memberCntMobile", "goodsCntMobile"]
            },
            {
                columnName : "write",
                title : "수기주문",
                columnNameList : ["goodsPriceWrite", "orderCntWrite", "memberCntWrite", "goodsCntWrite"]
            }
        ],
        columnModelList : [
            {
                "title" : "<b>날짜</b>",
                "columnName" : "paymentDate",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매금액</b>",
                "columnName" : "goodsPriceTotal",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매건수</b>",
                "columnName" : "orderCntTotal",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매자수</b>",
                "columnName" : "memberCntTotal",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매개수</b>",
                "columnName" : "goodsCntTotal",
                "width" : 50,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매금액</b>",
                "columnName" : "goodsPricePc",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매건수</b>",
                "columnName" : "orderCntPc",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매자수</b>",
                "columnName" : "memberCntPc",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매개수</b>",
                "columnName" : "goodsCntPc",
                "width" : 50,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매금액</b>",
                "columnName" : "goodsPriceMobile",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매건수</b>",
                "columnName" : "orderCntMobile",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매자수</b>",
                "columnName" : "memberCntMobile",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매개수</b>",
                "columnName" : "goodsCntMobile",
                "width" : 50,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매금액</b>",
                "columnName" : "goodsPriceWrite",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매건수</b>",
                "columnName" : "orderCntWrite",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매자수</b>",
                "columnName" : "memberCntWrite",
                "align" : "right",
                "width" : 50,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>구매개수</b>",
                "columnName" : "goodsCntWrite",
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
        statistics_excel_download();
        grid.setDisplayRowCount('<?= $rowDisplay; ?>');
    });
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
