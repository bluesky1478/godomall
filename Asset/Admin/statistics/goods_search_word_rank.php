<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
?>

<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
</div>

<div class="table-title gd-help-manual">검색어 순위 검색</div>

<form id="formSearch" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="searchDevice">
    <table class="table table-cols">
        <colgroup>
            <col class="col-xs-1"/>
            <col class="col-xs-5"/>
            <col class="col-xs-1"/>
            <col class="col-xs-5"/>
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
            <td colspan="3">
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
            <th>검색어</th>
            <td>
                <div class="form-inline">
                    <div class="input-group">
                        <input type="text" class="form-control width-2xl" name="searchWord" value="<?= $searchWord; ?>">
                    </div>
                </div>
            </td>
            <th>검색범위</th>
            <td>
                <div class="form-inline">
                    <div class="input-group">
                        <label class="radio-inline">
                            <input type="radio" name="searchType" value="" <?= $checked['searchType']['']; ?> />
                            전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="searchType" value="goodsNm" <?= $checked['searchType']['goodsNm']; ?> />
                            상품명
                        </label>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<div class="table-action mgt30 mgb0">
    <div class="pull-left pdt5">
        (검색결과 <strong class="text-danger"><?= $goodsCount; ?></strong> 개) &nbsp;
        데이터 노출형태 :
        <select name="device">
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

<script type="text/javascript">
    $(document).ready(function () {
        $('[name="device"]').change(function (e) {
            $('[name="searchDevice"]').val($('[name="device"]').val());
            $('#formSearch').submit();
        });

        $('.btn-excel').click(function (e) {
            e.preventDefault();
            grid.setDisplayRowCount('<?=$goodsCount?>');
            statistics_excel_download('<?=$naviMenu->location[2]?>');
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: true,
        columnFixCount: 1,
        headerHeight: 39,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList : [
            {
                "title" : "<b>검색어</b>",
                "columnName" : "keyword",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>검색수</b>",
                "columnName" : "searchCount",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>비율</b>",
                "columnName" : "percent",
                "align" : "center",
                "width" : 150,
                editOption: {
                    type: 'normal'
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
