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

<div class="table-title gd-help-manual">장바구니 검색</div>

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
                <td>
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
            <th>검색어</th>
            <td class="form-inline">
                <?=gd_select_box('key', 'key', $searchField, null, $searchKey, null); ?>
                <input type="text" name="keyword" value="<?=$searchKeyword; ?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th>구매여부</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="orderFl" value="" <?= gd_isset($checked['orderFl']['']); ?>/>전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderFl" value="y" <?= gd_isset($checked['orderFl']['y']); ?>/>구매함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="orderFl" value="n" <?= gd_isset($checked['orderFl']['n']); ?>/>구매안함
                </label>
            </td>
        </tr>
        <tr>
            <th>판매상태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="goodsSellFl" value="" <?= gd_isset($checked['goodsSellFl']['']); ?>/>전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="goodsSellFl" value="y" <?= gd_isset($checked['goodsSellFl']['y']); ?>/>판매함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="goodsSellFl" value="n" <?= gd_isset($checked['goodsSellFl']['n']); ?>/>판매안함
                </label>
            </td>
        </tr>
        <tr>
            <th>품절상태</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="soldOut" value="" <?= gd_isset($checked['soldOut']['']); ?>/>전체
                </label>
                <label class="radio-inline">
                    <input type="radio" name="soldOut" value="y" <?= gd_isset($checked['soldOut']['y']); ?>/>품절
                </label>
                <label class="radio-inline">
                    <input type="radio" name="soldOut" value="n" <?= gd_isset($checked['soldOut']['n']); ?>/>정상
                </label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<div class="table-action mgt30 mgb0">
    <div class="pull-left pdt5">검색결과
        <strong class="text-danger"><?= $goodsCount ?></strong>
        개
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
        $('.btn-excel').click(function (e) {
            e.preventDefault();
            grid.setDisplayRowCount('<?=$goodsCount?>');
            statistics_excel_download('<?=$naviMenu->location[2]?>');
            grid.setDisplayRowCount('<?= $rowDisplay; ?>');
        });
    });

    $(document).on('click', '#grid .js-cart-member', function(){
        layerGoodsCartMemberList($(this).data('goods'));
    });

    var grid = new tui.Grid({
        el: $('#grid'),
        autoNumbering: true,
        columnFixCount: 0,
        headerHeight: 50,
        rowHeight: 50,
        displayRowCount: <?= $rowDisplay; ?>,
        minimumColumnWidth: 20,
        columnModelList : [
            {
                "title" : "<b>고객수(회원/비회원)</b>",
                "columnName" : "goodsMember",
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
                "title" : "<b>상품명</b>",
                "columnName" : "goodsNm",
                "align" : "center",
                "width" : 150,
                editOption: {
                    type: 'normal'
                }
            },
            {
                "title" : "<b>가격</b>",
                "columnName" : "goodsPrice",
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
                "title" : "<b>재고</b>",
                "columnName" : "goodsStock",
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
                "title" : "<b>등록일</b>",
                "columnName" : "goodsRegDt",
                "align" : "center",
                "width" : 100,
                editOption: {
                    type: 'normal'
                }
            }
        ]
    });
    grid.setRowList(<?= $rowList; ?>);

    function layerGoodsCartMemberList(goodsNo) {
        var loadChk = $('#js-goods-cart-member').length;
        var urlParam = '';
        if (window.location.search) {
            urlParam = window.location.search;
            urlParam = urlParam.split('?');
            urlParam = urlParam[1];
        } else {
            urlParam = $('#formSearch').serialize();
        }
        var dataString = urlParam + '&goodsNo=' + goodsNo;

        $.ajax({
            url: '../share/layer_goods_cart_member.php',
            type: 'post',
            data: dataString,
            async: false,
            success: function (data) {
                if (loadChk == 0) {
                    data = '<div id="js-goods-cart-member">' + data + '</div>';
                }
                BootstrapDialog.show({
                    title: '고객수 상세보기',
                    message: data,
                    closable: true,
                });
            }
        });
    }
</script>
<script type="text/javascript" src="<?=PATH_ADMIN_GD_SHARE?>script/statistics.js"></script>
