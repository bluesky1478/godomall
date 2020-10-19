<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">방문자 경로 검색 <?php if ($noticeFl) { ?><span class="notice-danger">PC/모바일 구분 데이터가 2017년 2월23일부터 제공됨에 따라 2017년 2월23일 이전 데이터는 PC/모바일 구분 데이터가 제공되지 않습니다.</span><?php } ?></div>
<form id="frmSearchStatistics" method="get">
    <input type="hidden" name="searchDevice">
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
        <tr>
            <th>검색엔진 선택</th>
            <td>
                <select name="searchInflow">
                    <option value="all" <?= $checked['searchInflow']['all']; ?>>전체</option>
                    <option value="네이버" <?= $checked['searchInflow']['네이버']; ?>>네이버</option>
                    <option value="다음" <?= $checked['searchInflow']['다음']; ?>>다음</option>
                    <option value="구글" <?= $checked['searchInflow']['구글']; ?>>구글</option>
                    <option value="네이트" <?= $checked['searchInflow']['네이트']; ?>>네이트</option>
                    <option value="빙" <?= $checked['searchInflow']['빙']; ?>>빙</option>
                    <option value="카카오" <?= $checked['searchInflow']['카카오']; ?>>카카오</option>
                    <option value="기타" <?= $checked['searchInflow']['기타']; ?>>기타</option>
                </select>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <button type="submit" class="btn btn-lg btn-black">검색</button>
    </div>
</form>

<ul class="nav nav-tabs mgb20">
    <li><a id="visitInflow" class="hand">검색유입 현황</a></li>
    <li class="active"><a id="visitSearchword" class="hand">유입검색어 현황</a></li>
</ul>

<div class="table-action mgt30 mgb0">
    <div class="pull-left pdt5">
        데이터 노출형태 :
        <select name="deviceFl">
            <option value="all" <?= $checked['searchDevice']['all']; ?> >통합</option>
            <option value="pc" <?= $checked['searchDevice']['pc']; ?> >PC쇼핑몰</option>
            <option value="mobile" <?= $checked['searchDevice']['mobile']; ?> >모바일쇼핑몰</option>
        </select>
    </div>
    <div class="pull-right">
        <button type="button" class="btn btn-white btn-icon-excel btn-excel">엑셀 다운로드</button>
    </div>
</div>

<div class="code-html js-excel-data">
    <div id="grid"></div>
</div>

<script>
    <!--
    $(document).ready(function () {
        $('[name="deviceFl"]').change(function (e) {
            $('[name="searchDevice"]').val($('[name="deviceFl"]').val());
            $('#frmSearchStatistics').submit();
        });
        $('#visitInflow').click(function (e) {
            $('#frmSearchStatistics').attr('action', './visit_inflow.php');
            $('#frmSearchStatistics').submit();
        });
        $('#visitSearchword').click(function (e) {
            $('#frmSearchStatistics').attr('action','./visit_search_word.php');
            $('#frmSearchStatistics').submit();
        });
        $('.btn-excel').click(function () {
            grid.setDisplayRowCount('<?=$visitCount?>');
            statistics_excel_download();
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: true,
        columnFixCount: 1,
        headerHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList : [
            {
                "title" : "<b>검색어</b>",
                "columnName" : "searchWord",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>유입수</b>",
                "columnName" : "searchCount",
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
                "columnName" : "searchPercent",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal',
                    "afterContent" : " %"
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);
    //-->
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
