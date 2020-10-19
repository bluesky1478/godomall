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

<div class="table-title gd-help-manual">매입처순위 검색 <span class="notice-danger">통계 데이터는 2시간마다 집계되므로 주문데이터와 약 1시간~2시간의 데이터 오차가 있을 수 있습니다.</span></div>

<form id="formSearch" method="get" class="content-form js-search-form">
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tbody>
            <tr>
                <th>매입처</th>
                <td colspan="3">
                    <div class="form-inline">
                        <label><input type="button" value="매입처 선택" class="btn btn-sm btn-gray"  onclick="layer_add_info('purchase', {'mode': 'checkbox'} )"/></label>
                        <div id="purchaseLayer" class="selected-btn-group <?=!empty($searchPurchase['purchaseNo']) ? 'active' : ''?>">
                            <h5>선택된 매입처 : </h5>
                            <?php if (empty($searchPurchase['purchaseNo']) === false) {
                                foreach ($searchPurchase['purchaseNo'] as $k => $v) { ?>
                                    <div id="info_purchase_<?= $v ?>" class="btn-group btn-group-xs">
                                        <input type="hidden" name="purchaseNo[]" value="<?= $v ?>"/>
                                        <input type="hidden" name="purchaseNoNm[]" value="<?= $searchPurchase['purchaseNoNm'][$k] ?>"/>
                                        <span class="btn"><?= $searchPurchase['purchaseNoNm'][$k] ?></span>
                                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_purchase_<?= $v ?>">삭제</button>
                                    </div>
                                <?php }
                            } ?>
                            <label><input type="button" value="전체 삭제" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#purchaseLayer div"/></label>
                        </div>

                    </div>
                </td>
            </tr>
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
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<div class="table-dashboard">
    <table class="table table-cols">
        <tbody>
        <tr>
            <th class="bln point">총 상품금액</th>
            <th>총 매입가</th>
            <th>총 구매수량</th>
            <th>총 구매건수</th>
        </tr>
        <tr>
            <?php foreach($totalField['head'] as $k => $v) { ?>
            <td class="<?php if($k == '0') { ?>bln point<?php } ?> font-num">
                <strong><?=number_format($totalData[$v]['total']); ?></strong>
                <ul class="list-unstyled">
                    <?php foreach($totalField['device'] as $deviceKey => $deviceValue) { ?>
                    <li><strong><?=$deviceValue?></strong><span><?=number_format($totalData[$v][$deviceKey]); ?></span></li>
                    <?php } ?>
                </ul>
            </td>
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

<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-excel').click(function (e) {
            e.preventDefault();
            grid.setDisplayRowCount('<?=$orderCount?>');
            statistics_excel_download('<?=$naviMenu->location[2]?>');
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: true,
        columnFixCount: 1,
        headerHeight: 80,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnMerge : [
            {
                columnName : "goodsPrice",
                title : "상품금액",
                columnNameList : ["pcGoodsPrice", "mobileGoodsPrice", "writeGoodsPrice"]
            },
            {
                columnName : "costPrice",
                title : "매입가",
                columnNameList : ["pcCostPrice", "mobileCostPrice", "writeCostPrice"]
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
                "title" : "<b>매입처명</b>",
                "columnName" : "purchaseNm",
                "align" : "center",
                "width" : 150,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>PC</b>",
                "columnName" : "pcGoodsPrice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>모바일</b>",
                "columnName" : "mobileGoodsPrice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>수기주문</b>",
                "columnName" : "writeGoodsPrice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>합계</b>",
                "columnName" : "totalGoodsPrice",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>PC</b>",
                "columnName" : "pcCostPrice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>모바일</b>",
                "columnName" : "mobileCostPrice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>수기주문</b>",
                "columnName" : "writeCostPrice",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            },
            {
                "title" : "<b>합계</b>",
                "columnName" : "totalCostPrice",
                "width" : 100,
                "align" : "center",
                "editOption" : {
                    type: 'normal'
                },
                "formatter" : function(columnValue){
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
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
                    var sValue = (typeof columnValue =='undefined') ? '0' : String(columnValue);
                    var iValue = sValue.split('.');
                    return iValue[0].replace(/(\d)(?=(\d{3})+$)/gi, "$1,");
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);

</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
