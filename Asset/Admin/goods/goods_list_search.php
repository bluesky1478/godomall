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
                            <?php if($search['delFl'] =='y') { ?>
                                <option value="delDt" <?=gd_isset($selected['searchDateFl']['delDt']); ?>>삭제일</option>
                            <?php } ?>
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
            </tbody>
            <tbody class="js-search-detail" style="display: none;">
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
            <tr>
                <th>메인분류</th>
                <td class="contents" colspan="3">
                    <div class="form-inline">
                        <?=$goods->getGoodsListDisplayThemeMultiBox(null, $search['displayTheme']);?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>브랜드</th>
                <td>
                    <div class="form-inline">

                        <label><input type="button" value="브랜드선택" class="btn btn-sm btn-gray"  onclick="layer_register('brand', 'radio-search')"/></label>

                        <label class="checkbox-inline mgl10"><input type="checkbox" name="brandNoneFl" value="y" <?=gd_isset($checked['brandNoneFl']['y']); ?>><span id="brandNoneFlText"><?php if(empty($search['brandCd']) === false) echo "선택한 " ?>브랜드 미지정 상품</span></label>

                        <div id="brandLayer" class="selected-btn-group <?=!empty($search['brandCd']) ? 'active' : ''?>">
                            <h5>선택된 브랜드 : </h5>
                            <?php if (empty($search['brandCd']) === false) { ?>
                                <div id="info_brand_<?= $search['brandCd'] ?>" class="btn-group btn-group-xs">
                                    <input type="hidden" name="brandCd" value="<?= $search['brandCd'] ?>"/>
                                    <input type="hidden" name="brandCdNm" value="<?= $search['brandCdNm'] ?>"/>
                                    <span class="btn"><?= $search['brandCdNm'] ?></span>
                                    <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_brand_<?= $search['brandCd'] ?>" data-none="#brandNoneFlText">삭제</button>
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
            <?php
            if(gd_isset($goodsBenefitSelect) && gd_is_provider() === false) {
                ?>
            <tr>
                <th>상품 혜택</th>
                <td class="contents" colspan="3">
                    <div class="form-inline">
                        <label><input type="button" value="혜택 선택" class="btn btn-sm btn-gray"  onclick="layer_register('goods_benefit', 'layer_search_page')"/></label>
                        <label class="checkbox-inline mgl10"><input type="checkbox" name="goodsBenefitNoneFl" value="y" <?=gd_isset($checked['goodsBenefitNoneFl']['y']); ?>><span id="goodsBenefitNoneFlText"><?php if(empty($search['goodsBenefitSno']) === false) echo "선택한 " ?>혜택 미적용 상품</span></label>
                        <div id="goodsSearchBenefitLayer" class="selected-btn-group <?=!empty($search['goodsBenefitSno']) ? 'active' : ''?>">
                            <?php if (empty($search['goodsBenefitSno']) === false) {
                                foreach ($search['goodsBenefitSno'] as $k => $v) { ?>
                                    <div id="info_goods_benefit_<?= $v ?>" class="btn-group btn-group-xs" style="display:block;">
                                        <input type="hidden" name="goodsBenefitSno[]" value="<?= $v ?>"/>
                                        <input type="hidden" name="goodsBenefitNm[]" value="<?= $search['goodsBenefitNm'][$k] ?>">
                                        <input type="hidden" name="goodsBenefitDiscount[]" value="<?= $search['goodsBenefitDiscount'][$k] ?>">
                                        <input type="hidden" name="goodsBenefitDiscountGroup[]" value="<?= $search['goodsBenefitDiscountGroup'][$k] ?>">
                                        <input type="hidden" name="goodsBenefitPeriod[]" value="<?= $search['goodsBenefitPeriod'][$k] ?>">
                                        <span><?= $search['goodsBenefitNm'][$k]; ?> (<?= $search['goodsBenefitDiscount'][$k]; ?><?php if ($search['goodsBenefitDiscountGroup'][$k] != '특정회원등급') { ?> - <?php } ?> <?=$search['goodsBenefitDiscountGroup'][$k];?>) (<?=$search['goodsBenefitPeriod'][$k];?>)</span>
                                        <span>
                                        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_goods_benefit_<?= $v ?>" data-none="#goodsBenefitNoneFlText">삭제</button>
                                        </span>
                                    </div>
                                <?php }
                            } ?>
                        </div>
                    </div>
                </td>
            </tr>
                <?php
            }
            ?>
            <tr>
                <?php if(gd_isset($optionStockFlag)) { ?>
                    <th>
                        <select name="stockType">
                            <option value="goods" <?=gd_isset($selected['stockType']['goods']); ?>>상품 재고</option>
                            <option value="option" <?=gd_isset($selected['stockType']['option']); ?>>옵션 재고</option>
                            </select>
                        </th>
                    <?php } else { ?>
                    <th>상품 재고</th>
                <?php } ?>
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
            <?php
            if($optionStockFlag  == true){
            ?>
            <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
            <!--<tr>
                <th>판매중지수량</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="sellStopFl" value="" <?=gd_isset($checked['sellStopFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="sellStopFl" value="y" <?=gd_isset($checked['sellStopFl']['y']); ?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="sellStopFl" value="n" <?=gd_isset($checked['sellStopFl']['n']); ?> />사용안함</label>
                </td>
                <th>확인요청수량</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="confirmRequestFl" value="" <?=gd_isset($checked['confirmRequestFl']['']); ?> />전체</label>
                    <label class="radio-inline"><input type="radio" name="confirmRequestFl" value="y" <?=gd_isset($checked['confirmRequestFl']['y']); ?> />사용함</label>
                    <label class="radio-inline"><input type="radio" name="confirmRequestFl" value="n" <?=gd_isset($checked['confirmRequestFl']['n']); ?> />사용안함</label>
                </td>
            </tr>
            -->
            <tr>
                <th>옵션품절상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="optionSellFl" value="" <?=gd_isset($checked['optionSellFl']['']); ?> />전체</label>
                    <?php
                    foreach($stockReason as $key =>  $value){
                        ?><label class="radio-inline"><input type="radio" name="optionSellFl" value="<?=$key?>" <?=gd_isset($checked['optionSellFl'][$key]); ?> /><?=$value?></label><?php
                    }
                    ?>
                </td>
                <th>옵션배송상태</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="optionDeliveryFl" value="" <?=gd_isset($checked['optionDeliveryFl']['']); ?> />전체</label>
                    <?php
                    foreach($deliveryReason as $key =>  $value){
                        ?><label class="radio-inline"><input type="radio" name="optionDeliveryFl" value="<?=$key?>" <?=gd_isset($checked['optionDeliveryFl'][$key]); ?> /><?=$value?></label><?php
                    }
                    ?>
                </td>
            </tr>
            <?php
            }
            ?>
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
            <?php if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) { ?>
            <tr>
                <th>매입처</th>
                <td colspan="3">
                    <div class="form-inline">

                        <label><input type="button" value="매입처 선택" class="btn btn-sm btn-gray"  onclick="layer_register('purchase', 'checkbox')"/></label>

                        <label class="checkbox-inline mgl10"><input type="checkbox" name="purchaseNoneFl" value="y" <?=gd_isset($checked['purchaseNoneFl']['y']); ?>><span id="purchaseNoneFlText"><?php if(empty($search['purchaseNo']) === false) echo "선택한 " ?>매입처 미지정 상품</span></label>

                        <div id="purchaseLayer" class="selected-btn-group <?=!empty($search['purchaseNo']) ? 'active' : ''?>">
                            <h5>선택된 매입처 : </h5>

                            <?php if (empty($search['purchaseNo']) === false) {
                                foreach ($search['purchaseNo'] as $k => $v) { ?>
                                    <div id="info_purchase_<?= $v ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="purchaseNo[]" value="<?= $v ?>"/>
                                <input type="hidden" name="purchaseNoNm[]" value="<?= $search['purchaseNoNm'][$k] ?>"/>
                                <span class="btn"><?= $search['purchaseNoNm'][$k] ?></span>
                                <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#info_purchase_<?= $v ?>" data-none="#purchaseNoneFlText">삭제</button>
                                </div>
                                <?php }
                            } ?>
                            <label><input type="button" value="전체 삭제" class="btn btn-sm btn-gray" data-toggle="delete" data-target="#purchaseLayer div" data-none="#purchaseNoneFlText"/></label>
                        </div>

                    </div>
                </td>
            </tr>
            <?php } ?>
            <tr class="js-search-mileage js-search-price display-none" id="eventSearchArea">
                <th>기획전 검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <input type="text" name="event_text" class="form-control" readonly="readonly" value="<?= $search['event_text'] ?>" />
                        <button type="button" class="btn btn-sm btn-gray" onclick="javascript:layer_register('event_select', 'search');">기획전 검색</button>
                        <input type="hidden" name="eventThemeSno" value="<?= $search['eventThemeSno'] ?>" />
                        <span id="eventGroupSelectArea" <?php if(count(array_filter($search['eventGroupSelectList'])) < 1) { ?>class="display-none" <?php } ?>>
                            <select name="eventGroup" id="eventGroupSearchSelect" class="form-control multiple-select">
                                <?php foreach($search['eventGroupSelectList'] as $key => $eventGroupData) { ?>
                                    <option value="<?php echo $eventGroupData['sno']; ?>" <?php echo gd_isset($selected['eventGroup'][$eventGroupData['sno']]); ?>><?php echo $eventGroupData['groupName']; ?></option>
                                <?php } ?>
                            </select>
                        </span>
                        <span id="eventSearchResetArea" class="pdl10<?php if(!$search['event_text'] && !$search['eventThemeSno'] && count(array_filter($search['eventGroupSelectList'])) < 1) { ?> display-none<?php } ?>">
                            <button type="button" class="btn btn-icon-delete" onclick="javascript:resetEventSearchCondition();">삭제</button>
                        </span>
                    </div>
                </td>
            </tr>
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
            <?php
            if($stateCount) {
            ?>
            | 품절 <?=number_format($stateCount['soldOutCnt']);?>개
            | 노출 : PC <?=number_format($stateCount['pcDisplayCnt']);?>개 / 모바일 <?=number_format($stateCount['mobileDisplayCnt']);?>개
            | 미노출 : PC <?=number_format($stateCount['pcNoDisplayCnt']);?>개 / 모바일 <?=number_format($stateCount['mobileNoDisplayCnt']);?>개
            <?php
            }
            ?>
        </div>
        <div class="pull-right form-inline">
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
            <?php
            if($goodsBatchStockAdminGridMode == 'goods_batch_stock_list'){
                ?><button type="button" class="js-layer-register btn btn-sm btn-black" style="height: 27px !important;" data-type="goods_batch_stock_grid_config" data-goods-batch-stock-grid-mode="<?=$goodsBatchStockAdminGridMode?>" data-title="조회항목 설정">조회항목설정</button><?php
            }
            ?>
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
