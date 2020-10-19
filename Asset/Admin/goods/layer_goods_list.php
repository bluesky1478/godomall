<div class="table-title gd-help-manual">
    상품 복사 등록 <span class="notice-info">기존 상품과 내용이 비슷한 상품일 경우 기존 상품정보를 복사해서 빠르게 상품등록이 가능합니다.</span>
</div>
<div>
    <form id="layer_search_goods">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?= gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                            <option value="modDt" <?= gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][0]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][1]; ?>"/>
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod']) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>카테고리</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= $cate->getMultiCategoryBox('cateGoodsList', gd_isset($search['cateGoods']), 'class="form-control"'); ?>
                        <label class="checkbox-inline mgl10">
                            <input type="checkbox" name="categoryNoneFl" value="y" <?= gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>브랜드</th>
                <td colspan="3">
                    <div class="form-inline">

                        <label><input type="button" value="브랜드선택" class="btn btn-sm btn-gray" onclick="layer_register('brand', 'radio-search')"/></label>

                        <label class="checkbox-inline mgl10"><input type="checkbox" name="brandNoneFl" value="y" <?= gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품</label>

                        <div id="brandLayer" class="selected-btn-group <?= !empty($search['brandCd']) ? 'active' : '' ?>">
                            <h5>선택된 브랜드 : </h5>
                            <?php if (empty($search['brandCd']) === false) { ?>
                                <div id="info_brand_<?= $search['brandCd'] ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="brandCd" value="<?= $search['brandCd'] ?>"/>
                                    <input type="hidden" name="brandCdNm" value="<?= $search['brandCdNm'] ?>"/>
                                    <span class="btn"><?= $search['brandCdNm'] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_brand_<?= $search['brandCd'] ?>">삭제</button>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상품노출 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="" <?= gd_isset($checked['goodsDisplayFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="y" <?= gd_isset($checked['goodsDisplayFl']['y']); ?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="n" <?= gd_isset($checked['goodsDisplayFl']['n']); ?> />노출안함</label>
                </td>
                <th>상품판매 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="" <?= gd_isset($checked['goodsSellFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="y" <?= gd_isset($checked['goodsSellFl']['y']); ?> />판매함</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="n" <?= gd_isset($checked['goodsSellFl']['n']); ?> />판매안함</label>
                </td>
            </tr>

            <tr>
                <th>판매 재고</th>
                <td>
                    <div class="form-inline">
                        <label class="radio-inline"><input type="radio" name="stockFl" value="" <?= gd_isset($checked['stockFl']['']); ?> />전체</label>
                        <label class="radio-inline"><input type="radio" name="stockFl" value="n" <?= gd_isset($checked['stockFl']['n']); ?> />무한정 판매</label>
                        <label class="radio-inline"><input type="radio" name="stockFl" value="y" <?= gd_isset($checked['stockFl']['y']); ?> />재고량에 따름</label>
                    </div>
                </td>
                <th>품절 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="" <?= gd_isset($checked['soldOut']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="y" <?= gd_isset($checked['soldOut']['y']); ?> />품절</label>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="n" <?= gd_isset($checked['soldOut']['n']); ?> />정상</label>
                </td>
            </tr>
        </table>

        <div class="table-btn">
            <input type="button" class="btn btn-lg btn-black " value="검색" onclick="layer_goods_list_search();"/>
        </div>
    </form>
</div>
<div class="table-header">
    <div class="pull-left">
        검색 <strong><?=number_format($page->recode['total']); ?></strong>개 /
        전체 <strong><?=number_format($page->recode['amount']); ?></strong>개
    </div>
</div>
<table class="table table-rows table-fixed">
    <thead>
    <tr>
        <th class="width5p center">번호</th>
        <th class="width-2xs">이미지</th>
        <th class="width40p">상품명</th>
        <th class="width10p">판매가</th>
        <th class="width10p">공급사</th>
        <th class="width10p">노출상태</th>
        <th class="width10p">판매상태</th>
        <th class="width5p">재고</th>
        <th class="width15p">등록일<br>수정일</th>
        <th class="width10p">내용복사</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (is_array($data)) {
        $arrGoodsDisplay = array('y' => '노출함', 'n' => '노출안함');
        $arrGoodsSell = array('y' => '판매함', 'n' => '판매안함');
        $arrGoodsTax = array('t' => '과세', 'f' => '면세');
        $arrGoodsApply = array('a' => '승인요청', 'y' => '승인완료', 'r' => '반려', 'n' => '철회',);
        $arrDeliveryFree = array('one' => '해당 상품만', 'goods' => '상품별 배송', 'all' => '모두 무료');
        foreach ($data as $key => $val) {
            list($totalStock, $stockText) = gd_is_goods_state($val['stockFl'], $val['totalStock'], $val['soldOutFl']);

            if ($val['applyFl'] != 'y') {
                $displayText = $arrGoodsApply[$val['applyFl']];
                $sellText = $arrGoodsApply[$val['applyFl']];
            } else {
                $displayText = $arrGoodsDisplay[$val['goodsDisplayFl']];
                $sellText = $arrGoodsSell[$val['goodsSellFl']];
            }
            ?>
            <tr>
                <td class="center number"><?= number_format($page->idx--); ?></td>
                <td>
                    <div class="width-2xs">
                        <?= gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?>
                    </div>
                </td>
                <td>
                    <div onclick="goods_register_popup('<?= $val['goodsNo']; ?>' <?php if (gd_is_provider() === true) {
                        echo ",'1'";
                    } ?>);" class="hand"><?= $val['goodsNm']; ?></div>
                    <div
                        class="notice-ref notice-sm"><?=Globals::get('gDelivery.' . $val['deliveryFl']); ?><?php if ($val['deliveryFl'] == 'free') {
                            echo '(' . $arrDeliveryFree[$val['deliveryFree']] . ')';
                        } ?></div>
                    <div>
                </td>
                <td class="center number"><?= gd_currency_display($val['goodsPrice']); ?></td>
                <td class="center lmenu"><?=$val['scmNm']; ?>
                <td class="center lmenu"><?=$displayText; ?></td>
                <td class="center lmenu"><?=$sellText; ?></td>
                <td class="center number"><?=$totalStock; ?></td>
                <td class="center date">
                    <?=gd_date_format('Y-m-d', $val['regDt']); ?>
                    <?php if ($val['modDt']) {
                        echo "<br/>" . gd_date_format('Y-m-d', $val['modDt']);
                    } ?>
                </td>
                <td class="center"><a href="./goods_register.php?<?php if ($goodsNo) { ?>goodsNo=<?= $goodsNo; ?>&<?php } ?>applyNo=<?= $val['goodsNo']; ?>"
                                      class="btn btn-gray btn-xs">내용복사</a></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td class="no-data" colspan="9">검색된 상품이 없습니다.</td>
        </tr>
        <?php
    }
    ?>

    </tbody>
</table>

<div class="center"><?= $page->getPage('layer_goods_list_search(\'PAGELINK\')'); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#layer_search_goods input').keyup(function (e) {
            if (e.which == 13) {
                layer_goods_list_search();
            }
        });
    });

    /**
     * 상품 리스트 검색
     *
     * @param string pagelink 페이지 parameters
     */
    function layer_goods_list_search(pagelink) {
        var cateGoods = '';
        for (var i = <?=DEFAULT_DEPTH_CATE;?>; i > 0; i--) {
            if ($('#cateGoodsList' + i).val()) {
                cateGoods = $('#cateGoodsList' + i).val();
                break;
            }
        }
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            'goodsNo': '<?=$goodsNo;?>',
            'pagelink': pagelink
        };


        var frm = $("#layer_search_goods").serializeArray();
        $.each(frm, function (i, field) {
            if (field.name) {
                parameters[field.name] = field.value;
            }
        });

        parameters['cateGoods[]'] = cateGoods;

        parameters['searchDate[0]'] = $("#layer_search_goods input[name='searchDate[]']").eq(0).val();
        parameters['searchDate[1]'] = $("#layer_search_goods input[name='searchDate[]']").eq(1).val();

        parameters['stock[0]'] = $("#layer_search_goods input[name='stock[]']").eq(0).val();
        parameters['stock[1]'] = $("#layer_search_goods input[name='stock[]']").eq(1).val();

        goods_list_layer('search', parameters);
    }
    //-->
</script>
