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
            <th>강원</th>
            <th>경기</th>
            <th>경남</th>
            <th>경북</th>
            <th>광주</th>
            <th>대구</th>
            <th>대전</th>
            <th>부산</th>
            <th>서울</th>
        </tr>
        <tr>
            <td class="bln point font-num" rowspan="3">
                <strong><?= number_format($memberTotal['total']); ?></strong>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['강원']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentKW']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['경기']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentKG']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['경남']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentKN']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['경북']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentKB']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['광주']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentKJ']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['대구']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentDG']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['대전']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentDJ']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['부산']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentBS']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['서울']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentSW']; ?></span></b>
            </td>
        </tr>
        <tr>
            <th>세종</th>
            <th>울산</th>
            <th>인천</th>
            <th>전남</th>
            <th>전북</th>
            <th>제주</th>
            <th>충남</th>
            <th>충북</th>
            <th>지역 미확인</th>
        </tr>
        <tr>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['세종']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentSJ']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['울산']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentWS']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['인천']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentIC']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['전남']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentJN']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['전북']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentJB']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['제주']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentJJ']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['충남']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentCN']; ?></span></b>
            </td>
            <td class="font-num">
                <strong>
                    <?= number_format($memberTotal['충북']); ?>
                    <span class="font-normal">|</span>
                </strong>
                <b><span><?= $memberTotal['percentCB']; ?></span></b>
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
                    <?php
                        foreach ($cityArrName as $cityKey => $cityVal) {
                    ?>
                    {
                        name: '<?= $cityKey; ?>',
                        data: [<?= implode(',', $memberChart['Member'.$cityVal]); ?>]
                    },
                    <?php
                    }
                    ?>
                    {
                        name: '지역 미확인',
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
        columnFixCount: 2,
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
            <?php
            foreach ($cityArrName as $cityKey => $cityVal) {
            ?>
            {
                "title": "<b><?= $cityKey; ?></b>",
                "columnName": "now<?= $cityVal; ?>",
                "align": "center",
                "width": 100,
                editOption: {
                    type: 'normal'
                },
                "formatter": function (columnValue) {
                    var sValue = String(columnValue) || "0";
                    return sValue.replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            <?php
            }
            ?>
            {
                "title" : "<b>지역 미확인</b>",
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
