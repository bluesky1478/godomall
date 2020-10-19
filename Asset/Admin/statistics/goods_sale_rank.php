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

<div class="table-title gd-help-manual">판매순위 검색</div>

<form id="formSearch" method="get" class="content-form js-search-form">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
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
            <th>카테고리</th>
            <td class="contents">
                <div class="form-inline">
                    <?= $selectBox ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>검색범위</th>
            <td class="contents">
                <div class="form-inline">
                    <select id="noCategoryFl" name="noCategoryFl" class="form-control">
                        <option value="y" <?= $checked['noCategoryFl']['y']; ?>>카테고리 미지정 상품 포함</option>
                        <option value="n" <?= $checked['noCategoryFl']['n']; ?>>카테고리 미지정 상품 미포함</option>
                    </select>
                    <select id="underCategoryFl" name="underCategoryFl" class="form-control">
                        <option value="y" <?= $checked['underCategoryFl']['y']; ?>>하위 카테고리 포함</option>
                        <option value="n" <?= $checked['underCategoryFl']['n']; ?>>하위 카테고리 미포함</option>
                    </select>
                    <select id="superCategoryFl" name="superCategoryFl" class="form-control">
                        <option value="y" <?= $checked['superCategoryFl']['y']; ?>>대표카테고리 기준 데이터 표시</option>
                        <option value="n" <?= $checked['superCategoryFl']['n']; ?>>다중카테고리 기준 데이터 표시</option>
                    </select>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품명</th>
            <td class="contents">
                <div class="form-inline">
                    <input type="text" class="form-control width-2xl" name="goodsNm" value="<?php echo $searchGoods; ?>"/>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="notice-danger">통계 데이터는 2시간마다 집계되므로 주문데이터와 약 1시간~2시간의 데이터 오차가 있을 수 있습니다.</div>
    <div class="notice-danger">옵션과 다중카테고리 기준 데이터가 2018년 12월 05일부터 제공됨에 따라 2018년 12월 05일 이전 데이터는 옵션과 다중카테고리 기준 데이터가 제공되지 않습니다.</div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation" <?=$tabName == 'goods' ? 'class="active"' : ''?>>
        <a href="goods_sale_rank.php<?=$queryString?>">상품별 현황</a>
    </li>
    <li role="presentation" <?=$tabName == 'option' ? 'class="active"' : ''?>>
        <a href="goods_sale_option_rank.php<?=$queryString?>">옵션별 현황</a>
    </li>
</ul>

<div class="table-dashboard">
    <table class="table table-cols">
        <tbody>
        <tr>
            <th class="bln point">총 상품금액</th>
            <th>총 구매수량</th>
            <th>총 구매건수</th>
        </tr>
        <tr>
            <td class="bln point font-num">
                <strong><?= number_format($goodsTotal['totalPrice']); ?></strong>
                <ul class="list-unstyled">
                    <li><strong>PC</strong><span><?php echo number_format($goodsTotal['pcPrice']); ?></span></li>
                    <li><strong>모바일</strong><span><?php echo number_format($goodsTotal['mobilePrice']); ?></span></li>
                    <li><strong>수기주문</strong><span><?php echo number_format($goodsTotal['writePrice']); ?></span></li>
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($goodsTotal['totalGoodsCnt']); ?></strong>
                <ul class="list-unstyled">
                    <li><strong>PC</strong><span><?php echo number_format($goodsTotal['pcGoodsCnt']); ?></span></li>
                    <li><strong>모바일</strong><span><?php echo number_format($goodsTotal['mobileGoodsCnt']); ?></span></li>
                    <li><strong>수기주문</strong><span><?php echo number_format($goodsTotal['writeGoodsCnt']); ?></span></li>
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($goodsTotal['totalOrderCnt']); ?></strong>
                <ul class="list-unstyled">
                    <li><strong>PC</strong><span><?php echo number_format($goodsTotal['pcOrderCnt']); ?></span></li>
                    <li><strong>모바일</strong><span><?php echo number_format($goodsTotal['mobileOrderCnt']); ?></span></li>
                    <li><strong>수기주문</strong><span><?php echo number_format($goodsTotal['writeOrderCnt']); ?></span></li>
                </ul>
            </td>
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

<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-excel').click(function (e) {
            e.preventDefault();
            grid.setDisplayRowCount('<?=$goodsCount?>');
            statistics_excel_download('<?=$naviMenu->location[2]?>');
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });
        $('#cateCd1').on('blur', function () {
            if ($(this).val().trim() !== '' && $('#noCategoryFl').val() === 'y') {
                $('#noCategoryFl').val('n');
                alert('카테고리 선택 시 카테고리 미지정 상품은 제외 됩니다.');
            }
        });
        $('#noCategoryFl').on('blur', function () {
            if ($('#cateCd1').val().trim() !== '' && $(this).val() === 'y') {
                $(this).val('n');
                alert('카테고리 선택 시 카테고리 미지정 상품은 제외 됩니다.');
            }
        });
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: true,
        columnFixCount: <?php if ($tabName === 'option') { ?>4<?php } else { ?>3<?php } ?>,
        headerHeight: 80,
        rowHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnMerge : [
            {
                columnName : "price",
                title : <?php if ($tabName === 'option') { ?>"옵션금액"<?php } else { ?>"상품금액"<?php } ?>,
                columnNameList : ["pcPrice", "mobilePrice", "writePrice"]
            },
            {
                columnName : "goodsCnt",
                title : "구매수량",
                columnNameList : ["pcGoodsCnt", "mobileGoodsCnt", "writeGoodsCnt"]
            },
            {
                columnName : "orderCnt",
                title : "구매건수",
                columnNameList : ["pcOrderCnt", "mobileOrderCnt", "writeOrderCnt"]
            }
        ],
        columnModelList : [
            {
                "title" : "<b>이미지</b>",
                "columnName" : "goodsImg",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>상품 코드</b>",
                "columnName" : "goodsNo",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>상품 명</b>",
                "columnName" : "goodsNm",
                "align" : "center",
                "width" : 150,
                editOption: {
                    type: 'normal'
                }
            },
            <?php if ($tabName === 'option') { ?>
            {
                "title" : "<b>옵션</b>",
                "columnName" : "optionInfo",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
            <?php } ?>
            {
                "title" : "<b>PC</b>",
                "columnName" : "pcPrice",
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
                "title" : "<b>모바일</b>",
                "columnName" : "mobilePrice",
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
                "title" : "<b>수기주문</b>",
                "columnName" : "writePrice",
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
                "title" : "<b>합계</b>",
                "columnName" : "totalPrice",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>PC</b>",
                "columnName" : "pcGoodsCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>모바일</b>",
                "columnName" : "mobileGoodsCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>수기주문</b>",
                "columnName" : "writeGoodsCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>합계</b>",
                "columnName" : "totalGoodsCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>PC</b>",
                "columnName" : "pcOrderCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>모바일</b>",
                "columnName" : "mobileOrderCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>수기주문</b>",
                "columnName" : "writeOrderCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>합계</b>",
                "columnName" : "totalOrderCnt",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = String(columnValue) || "0";
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
