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

<div class="table-title gd-help-manual">메인분류 검색</div>

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
            <th>메인분류</th>
            <td class="contents">
                <div class="form-inline">
                    <select id="deviceFl" name="deviceFl" class="form-control">
                        <option value="" <?= $checked['deviceFl']['']; ?>>= 전체 =</option>
                        <option value="n" <?= $checked['deviceFl']['n']; ?>>PC 쇼핑몰</option>
                        <option value="y" <?= $checked['deviceFl']['y']; ?>>모바일 쇼핑몰</option>
                    </select>
                    <select id="mainChannelFl" name="mainChannelFl" class="form-control">
                        <option value="" <?= $checked['mainChannelFl']['']; ?>>= 메인페이지 분류 선택 =</option>
                        <?php
                        foreach ($getLinkMainArr as $deviceKey => $deviceVal) {
                            if ($deviceKey === 'n') {
                                $themeDevice = 'pc';
                            } else {
                                $themeDevice = '모바일';
                            }
                            foreach ($deviceVal as $key => $val) {
                                ?>
                                <option value="<?= $key; ?>" <?= $checked['mainChannelFl'][$key]; ?> class="js-<?= $deviceKey;?>"><?= $themeDevice . ' | ' . $val; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="notice-danger">통계 데이터는 2시간마다 집계되므로 주문데이터와 약 1시간~2시간의 데이터 오차가 있을 수 있습니다.</div>
    <div class="notice-danger">메인분류 통계 테이터는 2018년 12월 05일부터 제공됨에 따라 2018년 12월 05일 이전 데이터는 제공되지 않습니다.</div>
    <div class="notice-danger">상품 주문 시 클릭한 메인분류 기준으로 데이터가 집계되며, 데이터가 없는 메인분류 정보는 검색조건에 표시되지 않습니다.</div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<ul class="nav nav-tabs mgb30" role="tablist">
    <li role="presentation">
        <a href="goods_main_rank.php<?=$queryString?>">메인분류별 현황</a>
    </li>
    <li role="presentation" class="active">
        <a href="goods_main_goods_rank.php<?=$queryString?>">상품별 현황</a>
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
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($goodsTotal['totalGoodsCnt']); ?></strong>
                <ul class="list-unstyled">
                    <li><strong>PC</strong><span><?php echo number_format($goodsTotal['pcGoodsCnt']); ?></span></li>
                    <li><strong>모바일</strong><span><?php echo number_format($goodsTotal['mobileGoodsCnt']); ?></span></li>
                </ul>
            </td>
            <td class="font-num">
                <strong><?= number_format($goodsTotal['totalOrderCnt']); ?></strong>
                <ul class="list-unstyled">
                    <li><strong>PC</strong><span><?php echo number_format($goodsTotal['pcOrderCnt']); ?></span></li>
                    <li><strong>모바일</strong><span><?php echo number_format($goodsTotal['mobileOrderCnt']); ?></span></li>
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
        function changeMainSearchSelect() {
            let deviceFl = $('#deviceFl').val();
            if (deviceFl === 'n') {
                $('.js-y').prop('disabled', true).hide();
                $('.js-n').prop('disabled', false).show();
            } else if (deviceFl === 'y') {
                $('.js-y').prop('disabled', false).show();
                $('.js-n').prop('disabled', true).hide();
            } else {
                $('.js-n').prop('disabled', false).show();
                $('.js-y').prop('disabled', false).show();
            }
        }
        $('#deviceFl').on('change blur', function () {
            changeMainSearchSelect();
        });
        changeMainSearchSelect();
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: true,
        columnFixCount: 3,
        headerHeight: 80,
        rowHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnMerge : [
            {
                columnName : "price",
                title : "상품금액",
                columnNameList : ["pcPrice", "mobilePrice"]
            },
            {
                columnName : "goodsCnt",
                title : "구매수량",
                columnNameList : ["pcGoodsCnt", "mobileGoodsCnt"]
            },
            {
                columnName : "orderCnt",
                title : "구매건수",
                columnNameList : ["pcOrderCnt", "mobileOrderCnt"]
            }
        ],
        columnModelList : [
            {
                "title" : "<b>메인 분류</b>",
                "columnName" : "themeNm",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            },
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
                "title" : "<b>상품 명</b>",
                "columnName" : "goodsNm",
                "align" : "center",
                "width" : 150,
                editOption: {
                    type: 'normal'
                }
            },
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
