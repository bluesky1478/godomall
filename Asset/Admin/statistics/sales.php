<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <?php if(!$isProvider){ ?>
    <div class="btn-group">
        <a href="#" class="select-sales-process btn btn-white">통계 수집 방식 설정</a>
    </div>
    <?php }?>
</div>

<form id="frmSearchBase" method="get">
    <div class="table-title gd-help-manual">매출 검색</div>
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
                        <div class="input-group js-datepicker<?=$tabName == 'month' ? '-months' : ''?>">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $searchDate[0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker<?=$tabName == 'month' ? '-months' : ''?>">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $searchDate[1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>

                        <div class="btn-group js-dateperiod<?=$tabName == 'month' ? '-months' : ''?>" data-toggle="buttons" data-target-name="searchDate[]">
                            <label class="btn btn-white btn-sm hand <?= $tabName == 'month' ? $active['searchPeriod']['0'] : $active['searchPeriod']['29']; ?>">
                                <input type="radio" name="searchPeriod" value=<?= $tabName == 'month' ? "0" : "29" ?> <?= $tabName == 'month' ? $checked['searchPeriod']['0'] : $checked['searchPeriod']['29']; ?> >1개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $tabName == 'month' ? $active['searchPeriod']['2'] : $active['searchPeriod']['89']; ?>">
                                <input type="radio" name="searchPeriod" value=<?= $tabName == 'month' ? "2" : "89" ?> <?= $tabName == 'month' ? $checked['searchPeriod']['2'] : $checked['searchPeriod']['89']; ?> >3개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $tabName == 'month' ? $active['searchPeriod']['5'] : $active['searchPeriod']['179']; ?>">
                                <input type="radio" name="searchPeriod" value=<?= $tabName == 'month' ? "5" : "179" ?> <?= $tabName == 'month' ? $checked['searchPeriod']['5'] : $checked['searchPeriod']['179']; ?> >6개월
                            </label>
                            <label class="btn btn-white btn-sm hand <?= $tabName == 'month' ? $active['searchPeriod']['11'] : $active['searchPeriod']['359']; ?>">
                                <input type="radio" name="searchPeriod" value=<?= $tabName == 'month' ? "11" : "359" ?> <?= $tabName == 'month' ? $checked['searchPeriod']['11'] : $checked['searchPeriod']['359']; ?> >12개월
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
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
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
                columnName : "sales",
                title : "판매금액",
                columnNameList : ["goodsPrice", "goodsDcPrice", "goodsTotal", "deliveryPrice", "deliveryDcPrice", "deliveryTotal", "totalPrice"]
            },
            {
                columnName : "refund",
                title : "환불금액",
                columnNameList : ["refundGoodsPrice", "refundDeliveryPrice", "refundFeePrice", "refundTotal"]
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
                "title" : "<b>매출금액</b>",
                "columnName" : "orderSalesPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>상품판매가</b>",
                "columnName" : "goodsPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>상품할인</b>",
                "columnName" : "goodsDcPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>결제금액</b>",
                "columnName" : "goodsTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>배송비</b>",
                "columnName" : "deliveryPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>배송비할인</b>",
                "columnName" : "deliveryDcPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>결제금액</b>",
                "columnName" : "deliveryTotal",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>판매총액</b>",
                "columnName" : "totalPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>상품결제금액</b>",
                "columnName" : "refundGoodsPrice",
                "align" : "right",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>배송비결제금액</b>",
                "columnName" : "refundDeliveryPrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>환불수수료</b>",
                "columnName" : "refundFeePrice",
                "width" : 100,
                "align" : "right",
                "editOption" : {
                    type: 'normal'
                }
            },
            {
                "title" : "<span class='c-gdred'>(-)</span> <b>환불총액</b>",
                "columnName" : "refundTotal",
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
    
    //검색 버튼 제한
    var searchButtonFl = true;
    $('.js-search-button').click(function () {
        if (searchButtonFl === false) {
            alert('처리중입니다. 잠시만 기다려주세요.');
            return false;
        }
        searchButtonFl = false;
        return true;
    });

    $('.select-sales-process').click(function () {
        $.ajax({
            url: '../share/layer_select_sales_process.php',
            type: 'get',
            async: false,
            success: function (data) {
                data = '<div id="layerSelectSalesProcess">' + data + '</div>';
                BootstrapDialog.show({
                    title: '통계 수집 방식 설정',
                    size: BootstrapDialog.SIZE_WIDE_LARGE,
                    message: $(data),
                    closable: true
                });
            },
            error: function (e) {
                console.log(e);
                alert('다시 시도해주세요.');
            }
        });
    });
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
