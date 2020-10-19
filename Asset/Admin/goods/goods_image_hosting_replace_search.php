<?php if($goodsColorList) {?>
    <script>
        <!--
        function selectColor(val,target,name) {
            var color = $(val).data('color');
            var title = $(val).data('content');

            if($(target+" #"+name+color).length == '0') {
                var addHtml = "<div id='"+name+ color + "' class='btn-group btn-group-xs'>";
                addHtml += "<input type='hidden' name='goodsColor[]' value='" + color + "'>";
                addHtml += "<button type='button' class='btn btn-default js-popover' data-html='true' data-content='"+title+"' data-placement='bottom' style='background:#" + color + ";'>&nbsp;&nbsp;&nbsp;</button>";
                addHtml += "<button type='button' class='btn btn-icon-delete' data-toggle='delete' data-target='#"+name+ color + "'>삭제</button></div>";
            }
            $(target+" #selectColorLayer").append(addHtml);

            $('.js-popover').popover({trigger: 'hover',container: '#content',});
        }
        //-->
    </script>
<?php } ?>


<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">
        <?php if($search['delFl'] =='y') { echo "삭제 "; } ?>상품 검색
        <?php if(empty($searchConfigButton) && $searchConfigButton != 'hide'){?>
        <span class="search"><button type="button" class="btn btn-sm btn-black" onclick="set_search_config(this.form)">검색설정저장</button></span>
        <?php }?>
    </div>

    <div class="search-detail-box">
        <input type="hidden" name="detailSearch" value="<?=$search['detailSearch']; ?>"/>
        <input type="hidden" name="delFl" value="<?=$search['delFl']; ?>"/>
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if(gd_use_provider() === true) { ?>
            <?php if(gd_is_provider() === false) { ?>
            <tr>
                <th>공급사 구분</th>
                <td colspan="3">
                    <?php if($mode['page']!='delivery') { ?>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="all" <?=gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');"/>전체
                    </label>
                    <?php } ?>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="n" <?=gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('')" ;/>본사
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="scmFl" value="y" <?=gd_isset($checked['scmFl']['y']); ?> onclick="layer_register('scm', 'checkbox')"/>공급사
                    </label>
                    <label>
                        <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button>
                    </label>

                    <div id="scmLayer" class="selected-btn-group <?=$search['scmFl'] == 'y' && !empty($search['scmNo']) ? 'active' : ''?>">
                        <h5>선택된 공급사 : </h5>
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
            <?php } ?>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?=gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?=$search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                            <option value="modDt" <?=gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" />
                            <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                        </div>
                        <?= gd_search_date($search['searchPeriod']) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>카테고리</th>
                <td class="contents" colspan="3">
                    <div class="form-inline">
                        <?=$cate->getMultiCategoryBox(null, $search['cateGoods']); ?>
                        <label class="checkbox-inline mgl10">
                            <input type="checkbox" name="categoryNoneFl" value="y" <?=gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품
                        </label>
                    </div>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail" style="display: none;">
            <tr>
                <th>브랜드</th>
                <td>
                    <div class="form-inline">

                        <label><input type="button" value="브랜드선택" class="btn btn-sm btn-gray"  onclick="layer_register('brand', 'radio')"/></label>

                        <label class="checkbox-inline mgl10"><input type="checkbox" name="brandNoneFl" value="y" <?=gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품</label>

                        <div id="brandLayer" class="selected-btn-group <?=!empty($search['brandCd']) ? 'active' : ''?>">
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
                <th>판매가</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="goodsPrice[]" value="<?=$search['goodsPrice'][0]; ?>" class="form-control width-sm js-number"/>이상 ~
                        <input type="text" name="goodsPrice[]" value="<?=$search['goodsPrice'][1]; ?>" class="form-control width-sm js-number"/>이하
                    </div>
                </td>
            </tr>
            <?php if(gd_is_provider() === false) { ?>
            <tr>
                <th>마일리지</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="mileage[]" value="<?=$search['mileage'][0]; ?>" class="form-control width-sm js-number"/>이상 ~
                        <input type="text" name="mileage[]" value="<?=$search['mileage'][1]; ?>" class="form-control width-sm js-number"/>이하</div>
                </td>
                <th>마일리지 지급방법</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="mileageFl" value="" <?=gd_isset($checked['mileageFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="mileageFl" value="c" <?=gd_isset($checked['mileageFl']['c']); ?> />통합설정</label>
                    <label class="radio-inline"><input type="radio" name="mileageFl" value="g" <?=gd_isset($checked['mileageFl']['g']); ?> />개별설정</label>
                </td>
            </tr>
            <?php } ?>
            <tr>
                <th>상품 재고</th>
                <td>
                    <div class="form-inline">
                        <input type="text" name="stock[]" value="<?=$search['stock'][0]; ?>" class="form-control width-sm"/>개 이상 ~
                        <input type="text" name="stock[]" value="<?=$search['stock'][1]; ?>" class="form-control width-sm"/>개 이하
                    </div>
                </td>
                <th>옵션 사용</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="optionFl" value="" <?=gd_isset($checked['optionFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="optionFl" value="y" <?=gd_isset($checked['optionFl']['y']); ?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="optionFl" value="n" <?=gd_isset($checked['optionFl']['n']); ?> />사용안함</label>
                </td>
            </tr>
            <tr>
                <th>텍스트옵션 사용</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="optionTextFl" value="" <?=gd_isset($checked['optionTextFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="optionTextFl" value="y" <?=gd_isset($checked['optionTextFl']['y']); ?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="optionTextFl" value="n" <?=gd_isset($checked['optionTextFl']['n']); ?> />사용안함</label>
                </td>
                <th>추가상품 사용</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="addGoodsFl" value="" <?=gd_isset($checked['addGoodsFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="addGoodsFl" value="y" <?=gd_isset($checked['addGoodsFl']['y']); ?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="addGoodsFl" value="n" <?=gd_isset($checked['addGoodsFl']['n']); ?> />사용안함</label>
                </td>
            </tr>
            <tr>
                <th>PC쇼핑몰<br />상품노출 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="" <?=gd_isset($checked['goodsDisplayFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="y" <?=gd_isset($checked['goodsDisplayFl']['y']); ?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayFl" value="n" <?=gd_isset($checked['goodsDisplayFl']['n']); ?> />노출안함</label>
                </td>
                <th>PC쇼핑몰<br />상품판매 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="" <?=gd_isset($checked['goodsSellFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="y" <?=gd_isset($checked['goodsSellFl']['y']); ?> />판매함</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellFl" value="n" <?=gd_isset($checked['goodsSellFl']['n']); ?> />판매안함</label>
                </td>
            </tr>
            <tr>
                <th>모바일쇼핑몰<br />상품노출 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="" <?=gd_isset($checked['goodsDisplayMobileFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="y" <?=gd_isset($checked['goodsDisplayMobileFl']['y']); ?> />노출함</label>
                    <label class="radio-inline"><input type="radio" name="goodsDisplayMobileFl" value="n" <?=gd_isset($checked['goodsDisplayMobileFl']['n']); ?> />노출안함</label>
                </td>
                <th>모바일쇼핑몰<br />상품판매 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="" <?=gd_isset($checked['goodsSellMobileFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="y" <?=gd_isset($checked['goodsSellMobileFl']['y']); ?> />판매함</label>
                    <label class="radio-inline"><input type="radio" name="goodsSellMobileFl" value="n" <?=gd_isset($checked['goodsSellMobileFl']['n']); ?> />판매안함</label>
                </td>
            </tr>
            <tr>
                <th>판매 재고</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="stockFl" value="" <?=gd_isset($checked['stockFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="stockFl" value="n" <?=gd_isset($checked['stockFl']['n']); ?> />무한정 판매</label>
                    <label class="radio-inline"><input type="radio" name="stockFl" value="y" <?=gd_isset($checked['stockFl']['y']); ?> />재고량에 따름</label>
                </td>
                <th>품절 상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="" <?=gd_isset($checked['soldOut']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="y" <?=gd_isset($checked['soldOut']['y']); ?> />품절</label>
                    <label class="radio-inline"><input type="radio" name="soldOut" value="n" <?=gd_isset($checked['soldOut']['n']); ?> />정상</label>
                </td>
            </tr>
            <tr class="js-search-icon">
                <th>아이콘(기간제한)</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsIconCdPeriod" value="" <?=gd_isset($checked['goodsIconCdPeriod']['']); ?> />전체</label>
                    <?php
                    foreach ($getIcon as $key => $val) {
                        if ($val['iconPeriodFl'] == 'y' && $val['iconUseFl'] =='y') {
                            echo '<label class="radio-inline"><input type="radio" name="goodsIconCdPeriod" value="' . $val['iconCd'] . '" ' . gd_isset($checked['goodsIconCdPeriod'][$val['iconCd']]) . ' />' . gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . '&nbsp;</label>' . chr(10);
                        }
                    }
                    ?>
                </td>
                <th>아이콘(무제한)</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="goodsIconCd" value="" <?=gd_isset($checked['goodsIconCd']['']); ?> />전체</label>
                    <?php
                    foreach ($getIcon as $key => $val) {
                        if ($val['iconPeriodFl'] == 'n' && $val['iconUseFl'] =='y') {
                            echo '<label class="radio-inline"><input type="radio" name="goodsIconCd" value="' . $val['iconCd'] . '" ' . gd_isset($checked['goodsIconCd'][$val['iconCd']]) . ' />' . gd_html_image(UserFilePath::icon('goods_icon', $val['iconImage'])->www(), $val['iconNm']) . '&nbsp;</label>' . chr(10);
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr class="js-search-delivery">
                <th>배송비 조건</th>
                <td colspan="3">
                    <div class="radio">
                        <label class="radio-inline">
                            <input type="radio" name="goodsDeliveryFl" value="" <?=gd_isset($checked['goodsDeliveryFl']['']); ?>/> 전체
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="goodsDeliveryFl" value="y" <?=gd_isset($checked['goodsDeliveryFl']['y']); ?>/> 배송비조건별
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="goodsDeliveryFl" value="n" <?=gd_isset($checked['goodsDeliveryFl']['n']); ?>/> 상품별
                        </label>
                    </div>
                    <div class="checkbox">
                        <label class="checkbox-inline">
                            <input type="checkbox" name="goodsDeliveryFixFl[]" value="all" class="js-not-checkall" data-target-name="goodsDeliveryFixFl[]" <?=gd_isset($checked['goodsDeliveryFixFl']['all']); ?>> 전체
                        </label>
                    <?php foreach($mode['fix'] as $k => $v) { ?>
                        <label>
                            <input class="checkbox-inline" type="checkbox" name="goodsDeliveryFixFl[]" value="<?=$k?>"  <?=gd_isset($checked['goodsDeliveryFixFl'][$k]); ?>> <?=$v?>
                        </label>
                    <?php } ?>
                    </div>
                </td>
            </tr>
            <?php if($goodsColorList) {?>
            <tr class="js-search-icon">
                <th>대표색상</th>
                <td colspan="3">
                    <?php foreach($goodsColorList as $k => $v) {  ?>
                        <button type="button" class="btn btn-gray btn-sm js-popover" data-html="true" data-color="<?=$v?>" data-content="<?=$k?>" data-placement="bottom" style="background:#<?=$v?>;" onclick="selectColor(this,'#frmSearchGoods','goodsSearchColor_')">&nbsp;&nbsp;&nbsp;</button>

                    <?php } ?>
                        <br/>선택색상 : <span id="selectColorLayer">

                         <?php if(is_array($search['goodsColor'])) {
                         foreach($search['goodsColor'] as $k => $v) {
                                 if (!in_array($v,$goodsColorList) ) {
                                     continue;
                                 }
                             ?>
                             <div id='goodsSearchColor_<?=$v?>' class='btn-group btn-group-xs'>
                                 <input type='hidden' name='goodsColor[]' value='<?=$v?>'>
                                 <button type='button' class='btn btn-default js-popover' style='background:#<?= $v ?>;' data-html="true" data-content="<?=array_flip($goodsColorList)[$v]?>" data-placement="bottom">&nbsp;&nbsp;&nbsp;</button>
                                 <button type='button' class='btn btn-icon-delete' data-toggle='delete' data-target='#goodsSearchColor_<?=$v?>'>삭제</button>
                             </div>

                         <?php } } ?>
                    </span>
                </td>
            </tr>
            <?php } ?>
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true  && gd_is_provider() === false) { ?>
            <tr>
                <th>매입처</th>
                <td colspan="3">
                    <div class="form-inline">

                        <label><input type="button" value="매입처 선택" class="btn btn-sm btn-gray"  onclick="layer_register('purchase', 'checkbox')"/></label>

                        <label class="checkbox-inline mgl10"><input type="checkbox" name="purchaseNoneFl" value="y" <?=gd_isset($checked['purchaseNoneFl']['y']); ?>> 매입처 미지정 상품</label>

                        <div id="purchaseLayer" class="selected-btn-group <?=!empty($search['purchaseNo']) ? 'active' : ''?>">
                            <h5>선택된 매입처 : </h5>

                            <?php if (empty($search['purchaseNo']) === false) {
                                foreach ($search['purchaseNo'] as $k => $v) { ?>
                                    <div id="info_purchase_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="purchaseNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="purchaseNoNm[]" value="<?= $search['purchaseNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['purchaseNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_purchase_<?= $v ?>">삭제</button>
                                </div>
                                <?php }
                            } ?>
                            <label><input type="button" value="전체 삭제" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#purchaseLayer div"/></label>
                        </div>

                    </div>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-link js-search-toggle bold">상세검색 <span>펼침</span></button>
    </div>

    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>


    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?=number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?=number_format($page->recode['amount']);?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
    <input type="hidden" name="searchFl" value="y">
    <input type="hidden" name="applyPath" value="<?=gd_php_self()?>">
</form>
<script>

function brand_del(){
    $('input[name=brandCdNm]').val('');
}
</script>
