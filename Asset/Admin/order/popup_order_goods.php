<style>
    body { overflow:hidden; }
</style>

<table cellpadding="0" cellspacing="0" width="100%" border="0" class="goodsChoice_outlineTable" style="margin-left:-20px;">
    <colgroup>
        <col style="width:560px;"/>
        <col/>
        <col style="width:560px;"/>
    </colgroup>
    <tr>
        <td class="goodsChoice_outlineTdCenter">
                     <span class="goodsChoice_title"><span class="goodsChoice_titleArrow">▼</span>상품선택
            </span>
            <span class="goodsChoice_title_sub">최대 등록 가능한 상품수는 <span class="text-orange-red">500</span>개 입니다. 500개 초과시 기존 등록된 상품은 <span class="text-orange-red">자동 삭제</span> 됩니다</span>
        </td>
        <td class="goodsChoice_outlineTdCenter">
            <div class="goodsChoice_title"><span class="goodsChoice_titleArrow">▼</span>등록 상품 리스트</div>
        </td>
    </tr>
    <tr>
        <!-- 상품선택 리스트-->
        <td valign="top" class="goodsChoice_outlineTd" id="iframe_goodsChoiceList">

            <div style="width:560px;height:600px;overflow-x:hidden;overflow-y:auto">
                <form id="frmSearchBase" name="frmSearchBase" method="post">
                    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
                    <input type="hidden" name="sort"/> <input type="hidden" name="page"/>
                    <input type="hidden" name="pageNum"/> <input type="hidden" name="setGoodsList">

                    <div class="search-detail-box">
                        <table class="table table-cols">
                            <colgroup>
                                <col class="width-sm"/>
                                <col/>
                                <col class="width-sm"/>
                                <col/>
                            </colgroup>
                            <tbody>
                            <?php if (gd_use_provider() === true) { ?>
                                <?php if(!$isProvider){ ?>
                                    <tr>
                                        <th>공급사 구분
                                        </th>
                                        <td colspan="3">
                                            <label class="radio-inline"><input type="radio" name="scmFl"
                                                                               value="all" <?php echo gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="scmFl" value="n" <?php echo gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                                            </label>
                                            <label class="radio-inline">
                                                <input type="radio" name="scmFl" value="y" <?php echo gd_isset($checked['scmFl']['y']); ?>
                                                       onclick="layer_register('scm','checkbox')"/>공급사
                                            </label>

                                            <label>
                                                <button type="button" class="btn btn-sm btn-default" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                                            </label>

                                            <div id="scmLayer" class="width100p">
                                                <?php if ($search['scmFl'] == 'y') {
                                                    foreach ($search['scmNo'] as $k => $v) { ?>
                                                        <span id="info_scm_<?= $v ?>" class="btn-group btn-group-xs">
                                        <input type="hidden" name="scmNo[]" value="<?= $v ?>"/>
                                        <input type="hidden" name="scmNoNm[]" value="<?= $search['scmNoNm'][$k] ?>"/>
                                        <span class="btn"><?= $search['scmNoNm'][$k] ?></span>
                                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_scm_<?= $v ?>">삭제</button>

                                        </span>

                                                    <?php }
                                                } ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } else { ?>
                                    <input type="hidden" name="scmNo[]" value="<?= $providerScmNo ?>"/>
                                    <input type="hidden" name="scmNoNm[]" value="<?= $providerManagerNickNm ?>"/>
                                <?php } ?>
                            <?php } ?>
                            <tr>
                                <th>검색어</th>
                                <td colspan="3">
                                    <div class="form-inline">
                                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>기간검색</th>
                                <td colspan="3">
                                    <div class="form-inline">
                                        <select name="searchDateFl" class="form-control">
                                            <option value="regDt" <?php echo gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                                            <option value="modDt" <?php echo gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                                        </select>

                                        <div class="input-group js-datepicker">
                                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][0]; ?>">
                <span class="input-group-addon">
                    <span class="btn-icon-calendar">
                    </span>
                </span>
                                        </div>

                                        ~
                                        <div class="input-group js-datepicker">
                                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][1]; ?>">
                <span class="input-group-addon">
                    <span class="btn-icon-calendar">
                    </span>
                </span>
                                        </div>

                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            <tbody class="js-search-detail" class="display-none">
                            <tr>
                                <th>카테고리</th>
                                <td>
                                    <div class="form-inline">
                                        <?php echo $category->getMultiCategoryBox(null, gd_isset($search['cateGoods']), 'class="form-control width-md"'); ?></div>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="categoryNoneFl" value="y" <?php echo gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>브랜드</th>
                                <td>
                                    <div class="form-inline">
                                        <?php echo $brand->getMultiCategoryBox(null, gd_isset($search['brand']), 'class="form-control"'); ?></div>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="brandNoneFl" value="y" <?php echo gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>PC쇼핑몰<br />상품노출 상태</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="" <?=gd_isset($checked['goodsDisplayFl']['']); ?> />전체</label>
                                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="y" <?=gd_isset($checked['goodsDisplayFl']['y']); ?> />노출함</label>
                                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="n" <?=gd_isset($checked['goodsDisplayFl']['n']); ?> />노출안함</label>
                                </td>
                            </tr>
                            <tr>
                                <th>모바일쇼핑몰<br />상품노출 상태</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="" <?=gd_isset($checked['goodsDisplayMobileFl']['']); ?> />전체</label>
                                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="y" <?=gd_isset($checked['goodsDisplayMobileFl']['y']); ?> />노출함</label>
                                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="n" <?=gd_isset($checked['goodsDisplayMobileFl']['n']); ?> />노출안함</label>
                                </td>
                            </tr>
                            <tr>
                                <th>PC쇼핑몰<br />상품판매 상태</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="" <?=gd_isset($checked['goodsSellFl']['']); ?> />전체</label>
                                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="y" <?=gd_isset($checked['goodsSellFl']['y']); ?> />판매함</label>
                                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="n" <?=gd_isset($checked['goodsSellFl']['n']); ?> />판매안함</label>
                                </td>
                            </tr>
                            <tr>
                                <th>모바일쇼핑몰<br />상품판매 상태</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="" <?=gd_isset($checked['goodsSellMobileFl']['']); ?> />전체</label>
                                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="y" <?=gd_isset($checked['goodsSellMobileFl']['y']); ?> />판매함</label>
                                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="n" <?=gd_isset($checked['goodsSellMobileFl']['n']); ?> />판매안함</label>
                                </td>
                            </tr>
                            <tr>
                                <th>판매가</th>
                                <td>
                                    <div class="form-inline">
                                        <input type="text" name="goodsPrice[0]" value="<?php echo $search['goodsPrice'][0]; ?>"
                                               class="form-control"/> ~ <input type="text" name="goodsPrice[1]"
                                                                               value="<?php echo $search['goodsPrice'][1]; ?>"
                                                                               class="form-control"/></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-sm btn-link js-search-toggle">상세검색 <span>펼침</span></button>
                    </div>

                    <div class="table-btn">
                        <input type="button" value="검색" class="btn btn-lg btn-black search-goods-btn">
                    </div>

                    <div class="table-header">
                        <div class="pull-right">
                            <ul>
                                <li>
                                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                                </li>
                                <li>
                                    <?php echo gd_select_box(
                                        'pageNum', 'pageNum', gd_array_change_key_value(
                                        [
                                            10,
                                            20,
                                            30,
                                            40,
                                            50,
                                            60,
                                            70,
                                            80,
                                            90,
                                            100,
                                            200,
                                            300,
                                            500,
                                        ]
                                    ), '개 보기', Request::get()->get('pageNum'), null
                                    ); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>

                <form id="frmList" action="" method="get" target="ifrmProcess">
                    <input type="hidden" name="mode" value="">
                    <input type="hidden" name="openMode" value="<?=$openMode?>">
                    <table class="table table-rows" id="tbl_add_goods">
                        <thead>
                        <tr id="goodsRegisteredTrArea">
                            <th class="width5p">번호</th>
                            <th class="width10p">이미지</th>
                            <th class="width10p">상품명</th>
                            <th class="width10p">판매가</th>
                            <th class="width10p">공급사</th>
                            <th class="width10p">재고</th>
                            <th class="width5p">품절여부</th>
                            <th class="width5p">선택</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        if (is_array(gd_isset($data))) {

                            foreach ($data as $key => $val) {

                                list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                                ?>

                                <tr id="tbl_add_goods_<?php echo $val['goodsNo']; ?>" class="add_goods_free">
                                    <td class="center number addGoodsNumber_<?php echo $val['goodsNo']; ?>">
                                        <?php echo number_format($page->idx--); ?>
                                        <input type="hidden" name="itemGoodsNm[]" value="<?= gd_remove_only_tag($val['goodsNm']) ?>"/>
                                        <input type="hidden" name="itemTimeSaleSno[]" value="<?= gd_remove_only_tag($val['timeSaleSno']) ?>"/>
                                        <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice']) ?>"/>
                                        <input type="hidden" name="itemScmNm[]" value="<?= $val['scmNm'] ?>"/>
                                        <input type="hidden" name="itemTotalStock[]" value="<?= $val['totalStock'] ?>"/>
                                        <input type="hidden" name="itemBrandNm[]" value="<?= gd_isset($val['brandNm']) ?>"/>
                                        <input type="hidden" name="itemMakerNm[]" value="<?= gd_isset($val['makerNm']) ?>"/>
                                        <input type="hidden" name="itemSoldOutFl[]" value="<?= gd_isset($val['soldOutFl']) ?>"/>
                                        <input type="hidden" name="itemStockFl[]" value="<?= gd_isset($val['stockFl']) ?>"/>
                                        <input type="hidden" name="itemImage[]" value="<?= rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>"/>
                                    </td>
                                    <td class="center"><?php echo gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></td>
                                    <td>
                                        <?php  if($val['timeSaleSno']) {
                                                echo "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' style='height:auto;' />";
                                                }
                                        ?>
                                        <?php echo gd_remove_only_tag(stripslashes($val['goodsNm'])); ?>
                                        <input type="hidden" name="goodsNoData[]" value="<?= $val['goodsNo'] ?>"/>
                                        <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?php echo $val['goodsNo']; ?>" value="<?php echo $val['goodsNo']; ?>" style="display:none">
                                    </td>
                                    <td><?php echo gd_currency_display($val['goodsPrice']); ?></td>
                                    <td><?php echo $val['scmNm']; ?></td>
                                    <td class="center"><?php echo $totalStock ?></td>
                                    <td class="center"><?= $stockText ?></td>
                                    <td>
                                        <input type="button" value="선택" class="btn btn-gray btn-xs btn-select-goods" data-goods-no="<?php echo $val['goodsNo']; ?>"/>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td class="center" colspan="11">검색된 정보가 없습니다.</td>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </form>

                <div class="center"><?php echo $page->getPage("#"); ?></div>
            </div>


            <!--
            <iframe id="iframe_goodsChoiceList"
                    src="../share/ifrme_gooods_search.php?scmFl=<?= Request::get()->get('scmFl') ?>&scmNo=<?= Request::get()->get('scmNo') ?>&scmNoNm=<?= Request::get()->get('scmNoNm') ?>"
                    frameborder="0" scrolling="yes" style="overflow-x:hidden"></iframe> -->


        </td>
        <!-- 상품선택 리스트-->

        <!-- 등록상품 리스트-->
        <td valign="top" class="goodsChoice_outlineTd">

            <table cellpadding="0" cellpadding="0" width="100%">
                <td valign="top" class="goodsChoice_registeredTdArea">
                    <div id="goodsChoice_registerdOutlineDiv">
                        <form id="addGoodsFrm" method="post" action="./order_ps.php" target="ifrmProcess">
                            <input type="hidden" name="mode" value="order_write_goods">
                            <input type="hidden" name="memNo" value="<?=$memNo?>">
                            <input type="hidden" name="goodsKey[]" value="">
                            <input type="hidden" name="useBundleGoods" value="1" />
                            <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_result" class="table table-rows">
                                <thead>
                                <tr id="goodsRegisteredTrArea">
                                    <th class="width5p">삭제</th>
                                    <th class="width10p">공급사</th>
                                    <th class="width10p">이미지</th>
                                    <th>상품명</th>
                                    <th class="width10p">수량</th>
                                    <th class="width10p">판매가</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if ($setGoodsList) {
                                    echo $setGoodsList;
                                } ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </td>
                </tr></table>
        </td>
        <!-- 등록상품 리스트-->
    </tr>

    <tr class="goodChoice_buttonArea">
        <td colspan="3">
            <!--  <div class="registeredGoodsCountMsgArea">선택상품 개수 : <span id="registeredCheckedGoodsCountMsg"
                                                                       class="registeredGoodsCountInfo">0</span>개 / 등록상품
                  개수 : <span id="registeredGoodsCountMsg" class="registeredGoodsCountInfo">0</span>개
              </div> -->
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td>
                        <input type="button" value="취 소" class="btn btn-lg btn-white" onclick="self.close();"/> &nbsp;
                        <input type="button" value="선택완료"  class="order-goods-confirm btn btn-lg btn-black"/>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>


<script type="text/javascript">
    <!--
    var loadPageType = '<?=$loadPageType?>';
    $(document).ready(function () {

        $('input').keydown(function(e) {
            if (e.keyCode == 13) {
                $("input[name='setGoodsList']").val(encodeURIComponent($("#tbl_add_goods_result tbody").html()));
                $("#frmSearchBase").submit();
                return false
            }
        });

        //기존에 등록된 내용이 있는지 확인
        if(loadPageType !== 'orderWrite'){
            if(opener != null &&  $("#tbl_add_goods_result tbody").html().trim() =='' ) {
                set_goods();
            }
        }

        $('.search-goods-btn').click(function () {
            $("input[name='setGoodsList']").val(encodeURIComponent($("#tbl_add_goods_result tbody").html()));
            $("#frmSearchBase").submit();

        });

        $('.pagination li a').click(function () {
            $("input[name='page']").val($(this).data('page'));
            $('.search-goods-btn').click();
        });


        $('.btn-select-goods').click(function () {
            var addParam = {
                "goodsNo": $(this).data('goods-no'),
                "memNo":$('input[name="memNo"]',opener.document).val(),
                "loadPageType" : loadPageType
            };
            layer_add_info('goods_option', addParam);
        });

        //선택완료
        $('.order-goods-confirm').click(function (e) {
            var parameters = $("#addGoodsFrm").serialize();

            $.ajax({
                method: 'POST',
                cache: false,
                url: './order_ps.php',
                data: parameters,
            }).success(function () {
                //교환
                if(loadPageType === 'orderViewExchange' || loadPageType ==='orderViewAdd'){
                    opener.parent.setAnotherExchangeLayout();
                }
                else {
                    opener.parent.set_goods('y');
                }
                self.close();
            }).error(function (e) {
                alert(e.responseText);
            });

            //임시카트 등록
            //$("#addGoodsFrm").submit();
        });

        goods_delete();

    });

    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr, addParam);
    }

    function set_goods_option() {
        var targetFrm = "#frmViewLayer";
        var dataIndex = "";

        var goodsImage = $(targetFrm + " div.img-section").html();
        var goodsTitle = $(targetFrm + " h3.goods-name-t").html();

        var goodsList = $(targetFrm).find("div[id*='option_display_item_']").clone();

        goodsList.each(function () {
            var rowCount = "1";
            dataIndex = $("#tbl_add_goods_result").find("tr[class*='order-add-goods-']:last").length;
            if(dataIndex) {
                dataIndex = $("#tbl_add_goods_result").find("tr[class*='order-add-goods-']:last").data('index')+1;
            } else {
                dataIndex = "0";
            }

            var goodsInfo = $(this).find("div[class*='optionKey_']");


            var optionCheck = goodsInfo.attr('class');

            var goodsPrice = goodsInfo.find("div.price em").html();

            var goodsFixPrice = parseFloat($(targetFrm+" input[name='set_goods_price']").val()) + parseFloat(goodsInfo.find("input[name*='option_price_']").val());
            var goodsTotalPrice = parseFloat($(targetFrm+" input[name='goodsPriceSum[]']").val()) + parseFloat(goodsInfo.find("input[name='optionPriceSum[]']").val());
            var goodsNo = goodsInfo.find("input[name='goodsNo[]']").val();
            var optionSno = goodsInfo.find("input[name='optionSno[]']").val();

            var setOptionTextFl = false;
            if(goodsInfo.find("input[name='optionTextPriceSum[]']").length) {
                setOptionTextFl = true;
                goodsFixPrice += parseFloat(goodsInfo.find("input[name*='option_text_price_']").val());
                goodsTotalPrice += parseFloat(goodsInfo.find("input[name='optionTextPriceSum[]']").val());
            }

            var goodsCount = goodsInfo.find("input[name='goodsCnt[]']").val();

            var optionFl = $(targetFrm + " input[id='optionFl']").val();

            goodsInfo.find("div.price").remove();
            goodsInfo.find("div.del").remove();
            goodsInfo = goodsInfo.html();

            if(optionFl =='y') goodsInfo = $("h3.goods-name-t").html() +goodsInfo ;
            goodsInfo = goodsInfo.replace(/optionText\[0\]/g,"optionText["+dataIndex+"]");

            var addGoodsHtml = "";
            if($(this).find("div.add").length){

                $(this).find("div[id*='add_goods_display_item_']").each(function () {

                    var addGoodsInfo = $(this).children("span.name").html();
                    addGoodsInfo = addGoodsInfo.replace(/addGoodsNo\[0\]/g,"addGoodsNo["+dataIndex+"]");

                    var addGoodsCount = $(this).find("input[name*='addGoodsCnt[']").val();
                    var addGoodsFixPrice = parseFloat($(this).find("input[name*='add_goods_price_']").val());

                    addGoodsInfo = addGoodsInfo.replace(/addGoodsNo\[\]/g,"addGoodsNo["+dataIndex+"]");
                    addGoodsInfo = addGoodsInfo.replace(/addGoodsCnt\[\]/g,"addGoodsCnt["+dataIndex+"]")

                    optionCheck +="_"+$(this).find("input[name*='addGoodsNo[']").val();


                    var complied = _.template($('#layerAddGoodsTemplate').html());
                    addGoodsHtml += complied({
                        dataIndex: dataIndex,
                        dataAddGoodsInfo: addGoodsInfo,
                        dataAddGoodsCount: addGoodsCount,
                        dataAddGoodsFixPriceStr : numeral(addGoodsFixPrice.toFixed(<?php echo $currency["decimal"]?>)).format()
                    });
                    rowCount++;
                });
            }

            var addFl = "y";
            $("#tbl_add_goods_result input[name='goodsNo[]']").each(function () {
                if($(this).val() == goodsNo && $(this).closest('td').find('input[name="optionSno[]"]').val() == optionSno && !setOptionTextFl) {
                    addFl = "n";
                }
            });

            if(addFl =='y') {
                if($(targetFrm + " select[name='deliveryCollectFl']").length) {
                    var deliveryCollectFl = $(targetFrm + " select[name='deliveryCollectFl']").val();
                } else if($(targetFrm + " input[name='deliveryCollectFl']").length) {
                    var deliveryCollectFl = $(targetFrm + " input[name='deliveryCollectFl']").val();
                } else {
                    var deliveryCollectFl = "";
                }
                if($(targetFrm + " select[name='deliveryMethodFl']").length) {
                    var deliveryMethodFl = $(targetFrm + " select[name='deliveryMethodFl']").val();
                }
                else {
                    var deliveryMethodFl = $(targetFrm + " input[name='deliveryMethodFl']").val();
                }
                var goodHtml = "";
                var complied = _.template($('#layerGoodsTemplate').html());
                goodHtml += complied({
                    dataIndex: dataIndex,
                    dataCartSno : "",
                    dataRowCount: rowCount,
                    dataGoodsImage: goodsImage,
                    dataGoodsInfo: goodsInfo,
                    dataGoodsFixPriceStr:  numeral(goodsFixPrice.toFixed(<?php echo $currency["decimal"]?>)).format(),
                    dataGoodsCount: goodsCount,
                    dataGoodsTotalPrice: goodsTotalPrice,
                    deliveryCollectFl : deliveryCollectFl,
                    deliveryMethodFl : deliveryMethodFl,
                    dataGoodsKey : optionCheck.replace("check","").trim(),
                    dataScmInfo: $(targetFrm + " input[name='scmNm']").val(),
                    dataScmNo : $(targetFrm + " input[name='scmNo']").val()
                });

                $("#tbl_add_goods_result tbody").append(goodHtml+addGoodsHtml);
            }

        });

        $("#tbl_add_goods_result span.name em").css("display","block");
        $(".modal-dialog  .close").click();

        $("#tbl_add_goods_result .js-goods-image img").css("width","30px");

        goods_delete();
    }

    function order_goods_delete(idx) {
        $(".order-add-goods-"+idx).remove();
    }

    function set_goods(){
        var memNo = $('input[name="memNo"]').val();
        $.post('./order_ps.php', {'mode': 'order_write_search_goods', 'memNo': memNo }, function (frmData) {

            var goodHtml = "";

            if(frmData.cartPrice.totalSettlePrice > 0) {

                $.each(frmData.cartInfo, function (key, val) {

                    $.each(val, function (key1, val1) {

                        $.each(val1, function (key2, val2) {

                            var tmp = $(goodHtml).clone();
                            var dataIndex  = tmp.find("input[name='cartSno[]']").length;

                            var goodsNm = val2.goodsNm+"<input type='hidden' name='goodsNo[]' value='"+val2.goodsNo+"'>";

                            if(val2.option.length) {
                                var optionInfo = [];
                                $.each(val2.option, function (optKey, optVal) {
                                    optionInfo.push(optVal.optionName+":"+optVal.optionValue+"<input type='hidden' name='optionSno[]' value='"+optVal.optionSno+"'>");
                                });
                                goodsNm +=  "<br>"+optionInfo.join(",");
                            } else if(val2.optionSno) {
                                goodsNm +=  "<input type='hidden' name='optionSno[]' value='"+val2.optionSno+"'>";
                            }

                            if(val2.optionText.length) {
                                var optionTextInfo = [];
                                $.each(val2.optionText, function (optTextKey, optTextVal) {
                                    optionTextInfo.push(optTextVal.optionName+":"+optTextVal.optionValue+"<input type='hidden' name='optionText["+dataIndex+"]["+optTextVal.optionSno+"]' value='"+optTextVal.optionValue+"'>");
                                });
                                goodsNm +=  "<br>"+optionTextInfo.join(",");
                            }

                            var goodsPrice = val2.price.goodsPriceSum+val2.price.optionPriceSum+val2.price.optionTextPriceSum;

                            var complied = _.template($('#layerGoodsTemplate').html());

                            goodHtml += complied({
                                dataIndex: dataIndex,
                                dataCartSno : val2.sno,
                                dataRowCount: 1+val2.addGoods.length,
                                dataGoodsImage: val2.goodsImage,
                                dataGoodsInfo: goodsNm,
                                dataGoodsFixPriceStr:  numeral(goodsPrice.toFixed(<?php echo $currency["decimal"]?>)).format(),
                                dataGoodsCount: val2.goodsCnt,
                                dataGoodsTotalPrice: val2.price.goodsPriceSubtotal,
                                deliveryCollectFl :val2.goodsDeliveryCollectFl ,
                                dataScmInfo:frmData.cartScmInfo[key]['companyNm'],
                                dataScmNo : key
                            });

                            if(val2.addGoods.length > 0 ) {
                                $.each(val2.addGoods, function (agKey, agVal) {
                                    var complied = _.template($('#layerAddGoodsTemplate').html());
                                    goodHtml += complied({
                                        dataIndex: dataIndex,
                                        dataAddGoodsInfo:  agVal.addGoodsNm+"<input type='hidden' name='addGoodsNo["+dataIndex+"][]' value='"+agVal.addGoodsNo+"'>",
                                        dataAddGoodsCount:  agVal.addGoodsCnt,
                                        dataAddGoodsFixPriceStr : numeral( parseFloat(agVal.addGoodsPrice).toFixed(<?php echo $currency["decimal"]?>)).format()
                                    });


                                });

                            }

                        });
                    });

                });
                $("#tbl_add_goods_result tbody").append(goodHtml);


                // 상품삭제
                goods_delete();

            }
        });
    }

    // 상품삭제
    function goods_delete() {

        $('.js-goods-delete').bind('click', function(e){

            if($(this).data('cart-sno')) {
                $.post('./order_ps.php', {'mode': 'order_write_delete_goods','cartSno':$(this).data('cart-sno') }, function () {
                    set_goods();
                    opener.parent.set_goods('y');
                });
            }

            order_goods_delete($(this).data('index'));
        });
    }



    //-->
</script>
<script type="text/html" id="layerGoodsTemplate">
    <tr class="order-add-goods-<%=dataIndex%>" data-index="<%=dataIndex%>">
        <td class="add-goods-popup center" rowspan="<%=dataRowCount%>"><input type="button" value="삭제"  class="btn btn-gray btn-xs js-goods-delete" data-index="<%=dataIndex%>" data-cart-sno ="<%=dataCartSno%>"/></td>
        <td class="center" rowspan="<%=dataRowCount%>"><%=dataScmInfo%><input type="hidden" name="scmNo[]" value="<%=dataScmNo%>" /><input type="hidden" name="deliveryCollectFl[]" value="<%=deliveryCollectFl%>" /><input type="hidden" name="deliveryMethodFl[]" value="<%=deliveryMethodFl%>" /> <input type="hidden" name="setTotalPrice[]" value="<%=dataGoodsTotalPrice%>" /><input type="hidden" name="goodsCnt[]" value="<%=dataGoodsCount%>" /><input type="hidden" name="cartSno[]" value="<%=dataCartSno%>"><input type="hidden" name="goodsKey[]" value="<%=dataGoodsKey%>"></td>
        <td class="js-goods-image"><%=dataGoodsImage%></td>
        <td><%=dataGoodsInfo%></td>
        <td class="center" ><%=dataGoodsCount%></td>
        <td class="center" ><?=gd_currency_symbol()?><%=dataGoodsFixPriceStr%><?=gd_currency_string()?></td>
    </tr>
</script>
<script type="text/html" id="layerAddGoodsTemplate">
    <tr class="order-add-goods-<%=dataIndex%>"  data-index="<%=dataIndex%>">
        <td colspan="2">└ <%=dataAddGoodsInfo%></td>
        <td class="center"><%=dataAddGoodsCount%><input type="hidden" name="addGoodsCnt[<%=dataIndex%>][]" value="<%=dataAddGoodsCount%>" /></td>
        <td class="center"><?=gd_currency_symbol()?><%=dataAddGoodsFixPriceStr%><?=gd_currency_string()?></td>
    </tr>
</script>
