<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">방문자 검색 <?php if ($noticeFl) { ?><span class="notice-danger">PC/모바일 구분 데이터가 2017년 2월23일부터 제공됨에 따라 2017년 2월23일 이전 데이터는 PC/모바일 구분 데이터가 제공되지 않습니다.</span><?php } ?></div>
<form id="frmSearchStatistics" method="get">
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
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $searchDate[0]; ?>"/>
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $searchDate[1]; ?>"/>
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>

                    <div class="btn-group js-dateperiod-statistics" data-toggle="buttons" data-target-name="searchDate[]">
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['1']; ?>">
                            <input type="radio" name="searchPeriod" value="1" <?= $checked['searchPeriod']['1']; ?> >전일
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['7']; ?>">
                            <input type="radio" name="searchPeriod" value="7" <?= $checked['searchPeriod']['7']; ?> >7일
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['15']; ?>">
                            <input type="radio" name="searchPeriod" value="15" <?= $checked['searchPeriod']['15']; ?> >15일
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['30']; ?>">
                            <input type="radio" name="searchPeriod" value="30" <?= $checked['searchPeriod']['30']; ?> >1개월
                        </label>
                        <label class="btn btn-white btn-sm hand <?= $active['searchPeriod']['90']; ?>">
                            <input type="radio" name="searchPeriod" value="90" <?= $checked['searchPeriod']['90']; ?> >3개월
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

    <ul class="nav nav-tabs mgb20">
        <li><a id="visitToday" class="hand">당일 방문현황</a></li>
        <li><a id="visitDay" class="hand">일별 방문현황</a></li>
        <li><a id="visitHour" class="hand">시간대별 방문현황</a></li>
        <li class="active"><a id="visitWeek" class="hand">요일별 방문현황</a></li>
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
                <th>최대 방문자수</th>
                <th>최소 방문자수</th>
                <th>최대 페이지뷰</th>
                <th>최소 페이지뷰</th>
                <th>최소·최대 편차</th>
            </tr>
            <tr>
                <td>
                    <strong><?= number_format($totalVisit['MaxCount']); ?></strong><br/><span class="font-date"><?= $totalVisit['hour']['MaxCount']; ?></span>
                    <ul class="list-unstyled">
                        <li><strong>PC</strong><span><?= number_format($totalVisit['pc']['MaxCount']); ?></span></li>
                        <li><strong>모바일</strong><span><?= number_format($totalVisit['mobile']['MaxCount']); ?></span></li>
                    </ul>
                </td>
                <td>
                    <strong><?= number_format($totalVisit['MinCount']); ?></strong><br/><span class="font-date"><?= $totalVisit['hour']['MinCount']; ?></span>
                    <ul class="list-unstyled">
                        <li><strong>PC</strong><span><?= number_format($totalVisit['pc']['MinCount']); ?></span></li>
                        <li><strong>모바일</strong><span><?= number_format($totalVisit['mobile']['MinCount']); ?></span></li>
                    </ul>
                </td>
                <td>
                    <strong><?= number_format($totalVisit['MaxPV']); ?></strong><br/><span class="font-date"><?= $totalVisit['hour']['MaxPV']; ?></span>
                    <ul class="list-unstyled">
                        <li><strong>PC</strong><span><?= number_format($totalVisit['pc']['MaxPV']); ?></span></li>
                        <li><strong>모바일</strong><span><?= number_format($totalVisit['mobile']['MaxPV']); ?></span></li>
                    </ul>
                </td>
                <td>
                    <strong><?= number_format($totalVisit['MinPV']); ?></strong><br/><span class="font-date"><?= $totalVisit['hour']['MinPV']; ?></span>
                    <ul class="list-unstyled">
                        <li><strong>PC</strong><span><?= number_format($totalVisit['pc']['MinPV']); ?></span></li>
                        <li><strong>모바일</strong><span><?= number_format($totalVisit['mobile']['MinPV']); ?></span></li>
                    </ul>
                </td>
                <td>
                    <ul class="list-unstyled no-border">
                        <li>
                            <strong>방문자수</strong><span class="bold"><?= number_format($totalVisit['countDiff']); ?></span>
                        </li>
                        <li>
                            <strong>페이지뷰</strong><span class="bold"><?= number_format($totalVisit['pvDiff']); ?></span>
                        </li>
                    </ul>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="table-header form-inline select-device">
        <div class="pull-left">
            데이터 노출형태 :
            <select name="searchDevice">
                <option value="all" <?= $checked['searchDevice']['all']; ?> >통합</option>
                <option value="pc" <?= $checked['searchDevice']['pc']; ?> >PC쇼핑몰</option>
                <option value="mobile" <?= $checked['searchDevice']['mobile']; ?> >모바일쇼핑몰</option>
            </select>
        </div>
    </div>
</form>

<?php
if (count($visitCount) > 0) {
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
        $('[name="searchDevice"]').change(function (e) {
            $('#frmSearchStatistics').submit();
        });
        $('#visitToday').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_today.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitDay').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_day.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitHour').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_hour.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitWeek').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_week.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitMonth').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_month.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitPageView').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_pageview.php');
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
        if (count($visitCount) > 0) {
        ?>
        var container = document.getElementById('chart-area'),
            data = {
                categories: [<?= implode(',', $visitChart['Date']); ?>],
                series: [
                    {
                        name: '방문자수',
                        data: [<?= implode(',', $visitChart['Count']); ?>]
                    },
                    {
                        name: '방문횟수',
                        data: [<?= implode(',', $visitChart['Number']); ?>]
                    },
                    {
                        name: '신규방문자수',
                        data: [<?= implode(',', $visitChart['NewCount']); ?>]
                    },
                    {
                        name: '재방문자수',
                        data: [<?= implode(',', $visitChart['ReCount']); ?>]
                    },
                    {
                        name: '방문당PV',
                        data: [<?= implode(',', $visitChart['Pv']); ?>]
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
                "title" : "<b>요일</b>",
                "columnName" : "visitWeek",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>방문자수</b>",
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
                "title" : "<b>방문횟수</b>",
                "columnName" : "visitNumber",
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
                "title" : "<b>신규방문자수</b>",
                "columnName" : "visitNewCount",
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
                "title" : "<b>재방문자수</b>",
                "columnName" : "visitReCount",
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
                "title" : "<b>방문당PV</b>",
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
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);
    //-->
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
