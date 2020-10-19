<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">방문자 검색 <?php if ($noticeFl) { ?><span class="notice-danger">PC/모바일 구분 데이터가 2017년 2월23일부터 제공됨에 따라 2017년 2월23일 이전 데이터는 PC/모바일 구분 데이터가 제공되지 않습니다.</span><?php } ?></div>
<form id="frmSearchStatistics" method="get">
    <input type="hidden" name="searchDevice">
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <?php if (count($searchMallList) > 1) { ?>
            <tr>
                <th>상점</th>
                <td colspan="3">
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
                    <div class="input-group">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $searchDate[0]; ?>" readonly="readonly" />
                    </div>
                    ~
                    <div class="input-group">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $searchDate[1]; ?>" readonly="readonly" />
                    </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb20">
    <li class="active"><a id="visitToday" class="hand">당일 방문현황</a></li>
    <li><a id="visitDay" class="hand">일별 방문현황</a></li>
    <li><a id="visitHour" class="hand">시간대별 방문현황</a></li>
    <li><a id="visitWeek" class="hand">요일별 방문현황</a></li>
    <li><a id="visitMonth" class="hand">월별 방문현황</a></li>
    <li><a id="visitPageView" class="hand">페이지뷰 현황</a></li>
</ul>

<div class="table-dashboard">
    <table class="table table-cols">
        <colgroup>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="bln point">총방문자수</th>
            <th>PC쇼핑몰 방문자수</th>
            <th>모바일쇼핑몰 방문자수</th>
            <th class="point">총 페이지뷰</th>
            <th>PC쇼핑몰 페이지뷰</th>
            <th>모바일쇼핑몰 페이지뷰</th>
        </tr>
        <tr>
            <td class="bln point">
                <strong><?= number_format($totalVisit['Count']); ?></strong>
            </td>
            <td>
                <strong><?= number_format($totalVisit['pc']['Count']); ?></strong>
            </td>
            <td>
                <strong><?= number_format($totalVisit['mobile']['Count']); ?></strong>
            </td>
            <td class="point">
                <strong><?= number_format($totalVisit['VisitPv']); ?></strong>
            </td>
            <td>
                <strong><?= number_format($totalVisit['pc']['VisitPv']); ?></strong>
            </td>
            <td>
                <strong><?= number_format($totalVisit['mobile']['VisitPv']); ?></strong>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php
if ($visitCount > 0) {
    ?>
    <div id="chart-area"></div>
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

<script>
    <!--
    var widthSize = 930;
    $(document).ready(function () {
        $('#visitToday').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_today.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitDay').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_day.php');
            $('input[name="searchDate[]"]').val('');
            $('#frmSearchStatistics').submit();
        });
        $('#visitHour').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_hour.php');
            $('input[name="searchDate[]"]').val('');
            $('#frmSearchStatistics').submit();
        });
        $('#visitWeek').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_week.php');
            $('input[name="searchDate[]"]').val('');
            $('#frmSearchStatistics').submit();
        });
        $('#visitMonth').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_month.php');
            $('input[name="searchDate[]"]').val('');
            $('#frmSearchStatistics').submit();
        });
        $('#visitPageView').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_pageview.php');
            $('input[name="searchDate[]"]').val('');
            $('#frmSearchStatistics').submit();
        });
        if ($('#chart-area').width() > 930) {
            widthSize = $('#chart-area').width();
        }
        $('.btn-excel').click(function () {
            grid.setDisplayRowCount('<?=$visitCount?>');
            statistics_excel_download();
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });
        <?php
        if ($visitCount > 0) {
        ?>
        var container = document.getElementById('chart-area'),
            data = {
                categories: [<?= implode(',', $visitChart['Date']); ?>],
                series: [
                    {
                        name: '총 방문자수',
                        data: [<?= implode(',', $visitChart['Count']); ?>]
                    },
                    {
                        name: 'PC 방문자수',
                        data: [<?= implode(',', $visitChart['CountPc']); ?>]
                    },
                    {
                        name: '모바일 방문자수',
                        data: [<?= implode(',', $visitChart['CountMobile']); ?>]
                    },
                    {
                        name: '총 페이지뷰',
                        data: [<?= implode(',', $visitChart['Pv']); ?>]
                    },
                    {
                        name: 'PC 페이지뷰',
                        data: [<?= implode(',', $visitChart['PvPc']); ?>]
                    },
                    {
                        name: '모바일 페이지뷰',
                        data: [<?= implode(',', $visitChart['PvMobile']); ?>]
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
                "title" : "<b>시간대</b>",
                "columnName" : "visitDate",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>총 방문자수</b>",
                "columnName" : "visitCount",
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
                "title" : "<b>PC쇼핑몰 방문자수</b>",
                "columnName" : "visitCountPc",
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
                "title" : "<b>모바일쇼핑몰 방문자수</b>",
                "columnName" : "visitCountMobile",
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
                "title" : "<b>총 페이지뷰</b>",
                "columnName" : "visitPv",
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
                "title" : "<b>PC쇼핑몰 페이지뷰</b>",
                "columnName" : "visitPvPc",
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
                "title" : "<b>모바일쇼핑몰 페이지뷰</b>",
                "columnName" : "visitPvMobile",
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
    //-->
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
