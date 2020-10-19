<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get">
    <div class="table-title gd-help-manual">매출 검색 <span class="notice-danger">통계 데이터는 2시간마다 집계되므로 주문데이터와 약 1시간~2시간의 데이터 오차가 있을 수 있습니다.</span></div>
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
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation" <?=$tabName == 'day' ? 'class="active"' : ''?>>
        <a href="sales_day.php<?=$queryString?>">일별 매출현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'hour' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_hour.php<?=$queryString?>">시간대별 매출현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'week' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_week.php<?=$queryString?>">요일별 매출현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'month' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_month.php<?=$queryString?>">월별 매출현황</a>
    </li>
    <?php if (!$isProvider) { ?>
        <?php if (!$page['useScmFl']) { ?>
            <li role="presentation" <?=$tabName == 'member' ? 'class="active"' : ''?>>
                <a href="../statistics/sales_member.php<?=$queryString?>">회원구분 매출현황</a>
            </li>
            <li role="presentation" <?=$tabName == 'tax' ? 'class="active"' : ''?>>
                <a href="../statistics/sales_tax.php<?=$queryString?>">과세구분 매출현황</a>
            </li>
        <?php } ?>
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
                총 매출금액
            </th>
            <th>
                최대 매출금액
            </th>
            <th>
                최소 매출금액
            </th>
            <th>
                PC쇼핑몰 매출금액
            </th>
            <th>
                모바일쇼핑몰 매출금액
            </th>
            <th>
                수기주문 매출금액
            </th>
        </tr>
        </thead>
        <tbody>
        <td class="bln point">
            <strong><?= gd_money_format($daySalesTotal['all']['sales'] - $daySalesTotal['all']['refund']); ?></strong>원
            <ul class="list-unstyled">
                <li><strong>판매금액</strong><span><?= gd_money_format($daySalesTotal['all']['sales']); ?></span></li>
                <li><strong>환불금액</strong><span><?= gd_money_format($daySalesTotal['all']['refund']); ?></span></li>
            </ul>
        </td>
        <td>
            <strong><?= gd_money_format($daySalesTotal['max']['price']); ?></strong>원 <br /><span class="font-date"><?= $daySalesTotal['max']['date']; ?></span>
        </td>
        <td>
            <strong><?= gd_money_format($daySalesTotal['min']['price']); ?></strong>원 <br /><span class="font-date"><?= $daySalesTotal['min']['date']; ?></span>
        </td>
        <td>
            <strong><?= gd_money_format($deviceSales['pc']['sales'] - $deviceSales['pc']['refund']); ?></strong>원
            <ul class="list-unstyled">
                <li><strong>판매금액</strong><span><?= gd_money_format($deviceSales['pc']['sales']); ?></span></li>
                <li><strong>환불금액</strong><span><?= gd_money_format($deviceSales['pc']['refund']); ?></span></li>
            </ul>
        </td>
        <td>
            <strong><?= gd_money_format($deviceSales['mobile']['sales'] - $deviceSales['mobile']['refund']); ?></strong>원
            <ul class="list-unstyled">
                <li><strong>판매금액</strong><span><?= gd_money_format($deviceSales['mobile']['sales']); ?></span></li>
                <li><strong>환불금액</strong><span><?= gd_money_format($deviceSales['mobile']['refund']); ?></span></li>
            </ul>
        </td>
        <td>
            <strong><?= gd_money_format($deviceSales['write']['sales'] - $deviceSales['write']['refund']); ?></strong>원
            <ul class="list-unstyled">
                <li><strong>판매금액</strong><span><?= gd_money_format($deviceSales['write']['sales']); ?></span></li>
                <li><strong>환불금액</strong><span><?= gd_money_format($deviceSales['write']['refund']); ?></span></li>
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
        columnFixCount: 3,
        headerHeight: 80,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnMerge : [
            {
                columnName : "ysales",
                title : "판매금액",
                columnNameList : ["ygoodsPrice", "ygoodsDcPrice", "ygoodsTotal", "ydeliveryPrice", "ydeliveryDcPrice", "ydeliveryTotal", "ytotalPrice"]
            },
            {
                columnName : "yrefund",
                title : "환불금액",
                columnNameList : ["yrefundGoodsPrice", "yrefundDeliveryPrice", "yrefundFeePrice", "yrefundTotal"]
            },
            {
                columnName : "nsales",
                title : "판매금액",
                columnNameList : ["ngoodsPrice", "ngoodsDcPrice", "ngoodsTotal", "ndeliveryPrice", "ndeliveryDcPrice", "ndeliveryTotal", "ntotalPrice"]
            },
            {
                columnName : "nrefund",
                title : "환불금액",
                columnNameList : ["nrefundGoodsPrice", "nrefundDeliveryPrice", "nrefundFeePrice", "nrefundTotal"]
            },
            {
                columnName : "member",
                title : "<?= $topTitle[0]; ?>",
                columnNameList : ["ysales", "yrefund"]
            },
            {
                columnName : "no",
                title : "<?= $topTitle[1]; ?>",
                columnNameList : ["nsales", "nrefund"]
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
                "title" : "<b>구분</b>",
                "columnName" : "orderDevice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>매출총액</b>",
                "columnName" : "orderSalesPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>상품판매가</b>",
                "columnName" : "ygoodsPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>상품할인</b>",
                "columnName" : "ygoodsDcPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>결제금액</b>",
                "columnName" : "ygoodsTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>배송비</b>",
                "columnName" : "ydeliveryPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>배송비할인</b>",
                "columnName" : "ydeliveryDcPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>결제금액</b>",
                "columnName" : "ydeliveryTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매총액</b>",
                "columnName" : "ytotalPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>상품결제금액</b>",
                "columnName" : "yrefundGoodsPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>배송비결제금액</b>",
                "columnName" : "yrefundDeliveryPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>환불수수료</b>",
                "columnName" : "yrefundFeePrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>환불총액</b>",
                "columnName" : "yrefundTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>매출금액</b>",
                "columnName" : "yorderSalesPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>상품판매가</b>",
                "columnName" : "ngoodsPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>상품할인</b>",
                "columnName" : "ngoodsDcPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>결제금액</b>",
                "columnName" : "ngoodsTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>배송비</b>",
                "columnName" : "ndeliveryPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>배송비할인</b>",
                "columnName" : "ndeliveryDcPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>결제금액</b>",
                "columnName" : "ndeliveryTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매총액</b>",
                "columnName" : "ntotalPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>상품결제금액</b>",
                "columnName" : "nrefundGoodsPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>배송비결제금액</b>",
                "columnName" : "nrefundDeliveryPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>환불수수료</b>",
                "columnName" : "nrefundFeePrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>환불총액</b>",
                "columnName" : "nrefundTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>매출금액</b>",
                "columnName" : "norderSalesPrice",
                "width" : 100,
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
