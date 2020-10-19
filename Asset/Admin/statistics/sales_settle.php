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
        <button type="submit" class="btn btn-lg btn-black js-search-button">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation" <?=$tabName == 'day' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_settle_day.php<?=$queryString?>">일별 매출현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'hour' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_settle_hour.php<?=$queryString?>">시간대별 매출현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'week' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_settle_week.php<?=$queryString?>">요일별 매출현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'month' ? 'class="active"' : ''?>>
        <a href="../statistics/sales_settle_month.php<?=$queryString?>">월별 매출현황</a>
    </li>
</ul>
<div class="table-dashboard">
    <?php
    // 남은셀 갯수 구하기
    $totalKind = count($settleKind);
    $cols = ceil($totalKind / 3);
    $restBlank = $cols - ($totalKind % $cols);
    ?>
    <table class="table table-cols">
        <colgroup>
            <col style="width:12%;" />
            <?php for ($i = 0; $i < $cols; $i++) { ?>
            <col style="width:<?=floor(88 / ceil(count($settleKind) / 3))?>%;" />
            <?php } ?>
        </colgroup>
        <tbody>
        <tr>
            <th class="bln point right-line2">
                총 매출금액
            </th>
        <?php
        $i = 0;
        foreach ($settleKind as $key => $val) {
            if ($cols == $i) {
                break;
            }
        ?>
            <th>
                <?= $val; ?>
            </th>
        <?php $i++; } ?>
        </tr>
        <tr>
            <td rowspan="5" class="bln point right-line2"><strong><?= gd_money_format(array_sum($daySales)); ?></strong>원</td>
            <?php
            $i = 0;
            foreach ($settleKind as $key => $val) {
                if ($cols == $i) {
                    break;
                }
            ?>
            <td><strong><?= gd_money_format($daySales[$key]); ?></strong>원</td>
            <?php $i++; } ?>
        </tr>
        <tr>
            <?php
            $j = $i;
            $i = 0;
            foreach ($settleKind as $key => $val) {
                if ($j > $i) {
                    $i++;
                    continue;
                }
                if ($cols * 2 == $i) {
                    break;
                }
                ?>
                <th><?= $val; ?></th>
            <?php $i++; } ?>
        </tr>
        <tr>
            <?php
            $i = 0;
            foreach ($settleKind as $key => $val) {
                if ($j > $i) {
                    $i++;
                    continue;
                }
                if ($cols * 2 == $i) {
                    break;
                }
                ?>
                <td><strong><?= gd_money_format($daySales[$key]); ?></strong>원</td>
            <?php $i++; } ?>
        </tr>
        <tr>
            <?php
            $j = $i;
            $i = 0;
            foreach ($settleKind as $key => $val) {
                if ($j > $i) {
                    $i++;
                    continue;
                }
                ?>
                <th><?= $val; ?></th>
            <?php } ?>
            <?php for ($k = 0; $k < $restBlank; $k++) { ?>
                <th> &nbsp;</th>
            <?php } ?>
        </tr>
        <tr>
            <?php
            $i = 0;
            foreach ($settleKind as $key => $val) {
                if ($i < $j) {
                    $i++;
                    continue;
                }
                ?>
                <td><strong><?= gd_money_format($daySales[$key]); ?></strong>원</td>
            <?php } ?>
            <?php for ($k = 0; $k < $restBlank; $k++) { ?>
                <td> &nbsp;</td>
            <?php } ?>
        </tr>
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
        headerHeight: 39,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList: [
            {
                "title": "<b>날짜</b>",
                "columnName": "paymentDate",
                "align": "center",
                "width": 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title": "<b>구분</b>",
                "columnName": "orderDevice",
                "align": "center",
                "width": 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title": "<b>매출총액</b>",
                "columnName": "orderSalesPrice",
                "align": "right",
                "width": 100,
                editOption: {
                    type: 'normal'
                }
            <?php
            foreach ($settleKind as $key => $val) {
            ?>
            },
            {
                "title": "<b><?= $val; ?></b>",
                "columnName": "<?= $key; ?>",
                "align": "right",
                "width": 100,
                editOption: {
                    type: 'normal'
                }
            <?php
            }
            ?>
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
        statistics_excel_download('결제수단별 ');
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
