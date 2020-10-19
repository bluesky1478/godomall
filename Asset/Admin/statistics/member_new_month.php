<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">신규회원 검색</div>
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

<?php include('_member_new_tabs.php') ?>

<div class="table-dashboard">
    <table class="table table-cols">
        <tbody>
        <tr>
            <th class="bln point">총 신규회원수</th>
            <th>총 PC쇼핑몰 신규회원수</th>
            <th>총 모바일쇼핑몰 신규회원수</th>
            <th>최소 신규회원수</th>
            <th>최대 신규회원수</th>
        </tr>
        <tr>
            <td class="bln point font-num">
                <strong><?= number_format($memberTotal['total']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['pc']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['mobile']); ?></strong>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['min']); ?></strong>
                <br>
                <span class="font-date"><?= $memberTotal['minDate'] ?></span>
            </td>
            <td class="font-num">
                <strong><?= number_format($memberTotal['max']) ?></strong>
                <br>
                <span class="font-date"><?= $memberTotal['maxDate'] ?></span>
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
                        name: '전체 신규회원',
                        data: [<?= implode(',', $memberChart['MemberTotal']); ?>]
                    },
                    {
                        name: 'PC쇼핑몰 신규회원',
                        data: [<?= implode(',', $memberChart['MemberPC']); ?>]
                    },
                    {
                        name: '모바일쇼핑몰 신규회원',
                        data: [<?= implode(',', $memberChart['MemberMobile']); ?>]
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
                "title" : "<b>신규회원수</b>",
                "columnName" : "newTotal",
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
                "title" : "<b>PC쇼핑몰 신규회원수</b>( <button style=\"background-color:#5bc0de; width:10px; height:10px; border:none;\"></button> )",
                "columnName" : "newPc",
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
                "title" : "<b>모바일쇼핑몰 신규회원수</b>( <button style=\"background-color:#5cb85c; width:10px; height:10px; border:none;\"></button> )",
                "columnName" : "newMobile",
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
                "title" : "<b>비율</b>",
                "columnName" : "newPercent",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);

</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
