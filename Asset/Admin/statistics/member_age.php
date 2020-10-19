<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">전체 회원 검색</div>
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

<?php include('_member_tabs.php') ?>

<div class="table-dashboard">
    <table class="table table-cols">
        <tbody>
        <tr>
            <th class="bln point">총 회원수</th>
            <th>10대 회원수</th>
            <th>20대 회원수</th>
            <th>30대 회원수</th>
            <th>40대 회원수</th>
            <th>50대 회원수</th>
            <th>60대 회원수</th>
            <th>70대 회원수</th>
            <th>연령 미확인 회원수</th>
        </tr>
        <tr>
            <td class="bln point font-num">
                <strong><?= number_format($memberTotal['total']); ?></strong>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['10']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent10']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['20']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent20']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['30']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent30']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['40']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent40']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['50']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent50']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['60']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent60']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['70']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percent70']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['etc']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentEtc']; ?></span></b>
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
                        name: '총 회원수',
                        data: [<?= implode(',', $memberChart['MemberTotal']); ?>]
                    },
                    {
                        name: '10대',
                        data: [<?= implode(',', $memberChart['Member10']); ?>]
                    },
                    {
                        name: '20대',
                        data: [<?= implode(',', $memberChart['Member20']); ?>]
                    },
                    {
                        name: '30대',
                        data: [<?= implode(',', $memberChart['Member30']); ?>]
                    },
                    {
                        name: '40대',
                        data: [<?= implode(',', $memberChart['Member40']); ?>]
                    },
                    {
                        name: '50대',
                        data: [<?= implode(',', $memberChart['Member50']); ?>]
                    },
                    {
                        name: '60대',
                        data: [<?= implode(',', $memberChart['Member60']); ?>]
                    },
                    {
                        name: '70대',
                        data: [<?= implode(',', $memberChart['Member70']); ?>]
                    },
                    {
                        name: '연령 미확인 회원수',
                        data: [<?= implode(',', $memberChart['MemberEtc']); ?>]
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
                "title" : "<b>총 회원수</b>",
                "columnName" : "nowTotal",
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
                "title" : "<b>10대 회원수</b>",
                "columnName" : "now10",
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
                "title" : "<b>20대 회원수</b>",
                "columnName" : "now20",
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
                "title" : "<b>30대 회원수</b>",
                "columnName" : "now30",
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
                "title" : "<b>40대 회원수</b>",
                "columnName" : "now40",
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
                "title" : "<b>50대 회원수</b>",
                "columnName" : "now50",
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
                "title" : "<b>60대 회원수</b>",
                "columnName" : "now60",
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
                "title" : "<b>70대 회원수</b>",
                "columnName" : "now70",
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
                "title" : "<b>성별 미확인 회원수</b>",
                "columnName" : "nowEtc",
                "align" : "center",
                "width" : 100,
                editOption: {
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
