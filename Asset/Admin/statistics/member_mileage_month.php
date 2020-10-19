<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">마일리지 검색</div>
<form id="frmSearchStatistics" method="get">
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
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
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation" <?= $tabName == 'day' ? 'class="active"' : '' ?>>
        <a href="../statistics/member_mileage.php<?= $queryString ?>">일별 마일리지 현황</a>
    </li>
    <li role="presentation" <?= $tabName == 'month' ? 'class="active"' : '' ?>>
        <a href="../statistics/member_mileage_month.php<?= $queryString ?>">월별 마일리지 현황</a>
    </li>
</ul>

<div class="table-dashboard">
    <table class="table table-cols">
        <tbody>
        <tr>
            <th class="bln point">총 잔여 마일리지</th>
            <th>총 지급건수</th>
            <th>총 지급금액</th>
            <th>총 사용건수</th>
            <th>총 사용금액</th>
            <th>총 소멸건수</th>
            <th>총 소멸금액</th>
        </tr>
        <tr>
            <td class="bln point font-num">
                <strong><?= number_format($memberTotal['total']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['saveCount']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['saveMileage']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['useCount']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['useMileage']) ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['deleteCount']) ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['deleteMileage']) ?></strong>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php
if ($memberCount > 0) {
    ?>
    <div class="mgt30">
        <table class="table table-cols">
            <tr>
                <td class="in-chart">
                    <div id="chart-area"></div>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
?>

<div class="table-action mgt30 mgb0">
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
    </div>
</div>

<div class="code-html js-excel-data">
    <div id="grid"></div>
</div>

<script type="text/javascript">
    var widthSize = 930;
    $(document).ready(function () {
        $('.btn-excel').click(function (e) {
            e.preventDefault();
            grid.setDisplayRowCount('<?=$memberCount?>');
            statistics_excel_download();
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });

        var $chart = $('#chart-area');
        if ($chart.width() > 930) {
            widthSize = $chart.width();
        }

        <?php
        if ($memberCount > 0) {
        ?>
        var container = document.getElementById('chart-area'),
            data = {
                categories: [<?= implode(',', $memberChart['Date']); ?>],
                series: [
                    {
                        name: '잔여 금액',
                        data: [<?= implode(',', $memberChart['total']); ?>]
                    },
                    {
                        name: '지급 금액',
                        data: [<?= implode(',', $memberChart['saveMileage']); ?>]
                    },
                    {
                        name: '사용 금액',
                        data: [<?= implode(',', $memberChart['useMileage']); ?>]
                    },
                    {
                        name: '소멸 금액',
                        data: [<?= implode(',', $memberChart['deleteMileage']); ?>]
                    }
                ]
            },
            options = {
                chart: {
                    width: widthSize - 50,
                    height: 400,
                    title: ''
                },
                yAxis: {
                    min: 0,
                    title: ''
                },
                xAxis: {
                    title: ''
                },
                series: {
                    hasDot: false
                },
                tooltip: {
                    suffix: ''
                },
                legend: {
                    align: 'bottom'
                }
            };
        tui.chart.lineChart(container, data, options);
        <?php
        }
        ?>
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: false,
        columnFixCount: 1,
        headerHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList : [
            {
                "title" : "<b>날짜</b>",
                "columnName" : "memberDate",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>잔여 마일리지</b>",
                "columnName" : "total",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>지급건수</b>",
                "columnName" : "saveCount",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>지급금액</b>",
                "columnName" : "saveMileage",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>사용건수</b>",
                "columnName" : "useCount",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>사용금액</b>",
                "columnName" : "useMileage",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>소멸건수</b>",
                "columnName" : "deleteCount",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>소멸금액</b>",
                "columnName" : "deleteMileage",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
