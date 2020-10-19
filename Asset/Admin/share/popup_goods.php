<style>
    body { overflow:hidden; }
    .layout-blank #content .col-xs-12 {
        padding: 0px 25px 10px;
        height: 100%;
    }
    .goodsChoice_outlineTd .table-rows > tbody > tr > td,
    .goodsChoice_registeredTdArea .table-rows > tbody > tr > td {
        padding: 15px 0px 10px 0;
    }
    .btn-icon-check-bottom{
        background: url(<?=PATH_ADMIN_GD_SHARE;?>img/btn_icon_check_off.png) no-repeat 50% 40px;
        height: 60px;
    }
    .btn-icon-check-bottom:hover{
        background: #f91d11 url(<?=PATH_ADMIN_GD_SHARE;?>img/btn_icon_check_on.png) no-repeat 50% 40px;
    }
</style>
<table cellpadding="0" cellspacing="0" width="100%" border="0" class="goodsChoice_outlineTable" style="margin-left:-10px;">
    <colgroup>
        <col style="width:560px;"/>
        <col/>
        <col style="width:560px;"/>
    </colgroup>
    <tr>
        <td class="goodsChoice_outlineTdCenter">
            <span class="goodsChoice_title">상품선택
            </span>
            <?php if($checkCheckboxType) {?>
            <span class="goodsChoice_title_sub">최대 등록 가능한 상품수는 <span class="text-orange-red">500</span>개 입니다. 500개 초과시 기존 등록된 상품은 <span class="text-orange-red">자동 삭제</span> 됩니다</span>
            <?php }?>
        </td>
        <?php if($checkCheckboxType) {?>
        <td rowspan="3" valign="top" class="goodsChoice_outlineTdCenter" style="padding: 0 27px">
            <table cellpadding="0" cellspacing="0" class="goodsChoice_addDelButtonArea" style="margin-top: 215px">
                <tr>
                <td>
                    <p style="margin: 0px"><input type="button" class="btn btn-9 btn-white btn-icon-plus-bottom" value="추가" id="addGoods"/></p>
                    <p style="margin: 20px 0"><button class="btn btn-9 btn-icon-check-bottom goodsChoiceConfirm">선택<br>완료</button></p>
                    <p style="margin: 0px"><input type="button" class="btn btn-9 btn-white btn-icon-minus-bottom" value="삭제" id="delGoods"/></p>
                </td>
                </tr>
            </table>
        </td>
        <td class="goodsChoice_outlineTdCenter">
            <div class="goodsChoice_title">등록 상품 리스트</div>
        </td>
        <?php }?>
    </tr>
    <tr>
        <!-- 상품선택 리스트-->
        <td valign="top" class="goodsChoice_outlineTd"  id="iframe_goodsChoiceList">

            <div  style="width:560px;height:632px;overflow-x:hidden;overflow-y:auto">
                <form id="frmSearchBase" name="frmSearchBase" method="post">
                    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>
                    <input type="hidden" name="sort"/>
                    <input type="hidden" name="page"/>
                    <input type="hidden" name="pageNum"/>
                    <input type="hidden" name="setGoodsList">
                    <input type="hidden" id="selectedGoodsList" name="selectedGoodsList" value="<?=$selectedGoodsList?>" />

                    <div class="search-detail-box">
                        <table class="table table-cols" style="border-top: 0px">
                            <colgroup>
                                <col class="width-sm"/>
                                <col/>
                                <col class="width-sm"/>
                                <col/>
                            </colgroup>
                            <tbody>
                            <?php if (gd_use_provider() === true) { ?>
                            <tr>
                                <th>공급사 구분</th>
                                <td colspan="3">
                                    <label class="radio-inline"><input type="radio" name="scmFl"
                                                  value="all" <?php echo gd_isset($checked['scmFl']['all']); ?> onclick="$('#scmLayer').html('');" />전체</label>
                                    <label class="radio-inline"><input type="radio" name="scmFl" value="n" <?php echo gd_isset($checked['scmFl']['n']); ?> onclick="$('#scmLayer').html('');"/>본사</label>
                                    <label class="radio-inline"><input type="radio" name="scmFl" value="y" <?php echo gd_isset($checked['scmFl']['y']); ?>
                                                  onclick="layer_register('scm','checkbox')"/>공급사
                                    </label>

                                    <label > <button type="button" class="btn btn-sm btn-gray" onclick="layer_register('scm','checkbox')">공급사 선택</button></label>

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
                            <?php } ?>
                            <tr>
                                <th>검색어</th>
                                <td colspan="3"><div class="form-inline">
                                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th >기간검색</th>
                                <td colspan="3"> <div class="form-inline">
                                        <select name="searchDateFl" class="form-control">
                                            <option value="regDt" <?php echo gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                                            <option value="modDt" <?php echo gd_isset($selected['searchDateFl']['modDt']); ?>>수정일</option>
                                        </select>

                                        <div class="input-group js-datepicker">
                                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][0]; ?>" >
                <span class="input-group-addon">
                    <span class="btn-icon-calendar">
                    </span>
                </span>
                                        </div>

                                        ~  <div class="input-group js-datepicker">
                                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?php echo $search['searchDate'][1]; ?>" >
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
                                <td><div class="form-inline">
                                        <?php echo $category->getMultiCategoryBox(null, gd_isset($search['cateGoods']), 'class="form-control width-md"'); ?></div>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" name="categoryNoneFl" value="y" <?php echo gd_isset($checked['categoryNoneFl']['y']); ?>> 카테고리 미지정 상품
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th>브랜드</th>
                                <td><div class="form-inline">
                                        <?php echo $brand->getMultiCategoryBox(null, gd_isset($search['brand']), 'class="form-control"'); ?></div>
                                    <label class="checkbox-inline"><input type="checkbox" name="brandNoneFl" value="y" <?php echo gd_isset($checked['brandNoneFl']['y']); ?>> 브랜드 미지정 상품</label>
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
                                <th>상품재고 상태</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="stockStateFl"
                                                  value="all" <?php echo gd_isset($checked['stockStateFl']['all']); ?>/>전체</label>
                                    <label class="radio-inline"><input type="radio" name="stockStateFl"
                                                  value="n" <?php echo gd_isset($checked['stockStateFl']['n']); ?>/>무한정 판매</label>
                                    <label class="radio-inline"><input type="radio" name="stockStateFl"
                                                  value="u" <?php echo gd_isset($checked['stockStateFl']['u']); ?>/>재고있음</label>
                                    <label class="radio-inline"><input type="radio" name="stockStateFl"
                                                  value="z" <?php echo gd_isset($checked['stockStateFl']['z']); ?>/>재고없음</label>
                                </td>
                            </tr>
                            <tr>
                                <th>품절 상태</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="soldOut" value="" <?=gd_isset($checked['soldOut']['']); ?> />전체</label>
                                    <label class="radio-inline"><input type="radio" name="soldOut" value="y" <?=gd_isset($checked['soldOut']['y']); ?> />품절</label>
                                    <label class="radio-inline"><input type="radio" name="soldOut" value="n" <?=gd_isset($checked['soldOut']['n']); ?> />정상</label>
                                </td>
                            </tr>
                            <tr>
                                <th>판매가</th>
                                <td><div class="form-inline">
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

                    <div class="table-header" style="border-top: 1px solid #d1d1d1">
                        <div class="pull-right">
                            <ul>
                                <li>
                                    <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                                </li>
                                <li>
                                    <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>

                <form id="frmList" action="" method="get" target="ifrmProcess">
                    <input type="hidden" name="mode" value="">
                    <input type="hidden" name="relationFl" value="<?=$relationFl?>">
                    <table class="table table-rows" id="tbl_add_goods" style="margin-bottom: 0px;width: 100%">
                        <thead>
                        <tr id="goodsRegisteredTrArea">
                            <th class="center">
                                <?php if($checkCheckboxType) {?>
                                <input type="checkbox" id="allCheck" value="y" onclick="all_checkbox(this,'tbl_add_goods')"/></th>
                            <?php }?>
                            <th>번호</th>
                            <th>이미지</th>
                            <th>상품명</th>
                            <th>판매가</th>
                            <th>공급사</th>
                            <th>재고</th>
                            <th>품절여부</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php
                        if (is_array(gd_isset($data))) {

                            foreach ($data as $key => $val) {

                                list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);

                                // 상품 아이콘
                                if (empty($val['goodsIconCd']) === false && is_array($val['goodsIconCd']) === true) {
                                    foreach ($val['goodsIconCd'] as $iKey => $iVal) {
                                        $val['goodsIcon'] .= gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                    }
                                }
                                // 기간 제한용 아이콘
                                if (empty($val['goodsIconStartYmd']) === false && empty($val['goodsIconEndYmd']) === false && empty($val['goodsIconCdPeriod']) === false && strtotime($val['goodsIconStartYmd']) <= time() && strtotime($val['goodsIconEndYmd']) >= time()) {
                                    foreach ($val['goodsIconCdPeriod'] as $iKey => $iVal) {
                                        $val['goodsIcon'] .=  gd_html_image(UserFilePath::icon('goods_icon', $iVal['iconImage'])->www(), $iVal['iconNm']) . ' ';
                                    }
                                }

                                // 품절 체크
                                if ($val['soldOutFl'] == 'y' || ($val['stockFl'] == 'y' && $val['totalStock'] <= 0)) {
                                    $val['goodsIcon'] .= gd_html_image(UserFilePath::icon('goods_icon')->www() . '/' . 'icon_soldout.gif', '품절상품') . ' ';
                                }

                                if($val['timeSaleSno']) {
                                    $val['goodsIcon'] .= "<img src='" . PATH_ADMIN_GD_SHARE . "img/time-sale.png' alt='타임세일' /> ";
                                }

                                ?>

                                <tr id="tbl_add_goods_<?php echo $val['goodsNo'];?>" class="add_goods_free">
                                    <td class="center">
                                        <input type="hidden" name="itemGoodsNm[]" value="<?=gd_remove_only_tag($val['goodsNm'])?>" />
                                        <input type="hidden" name="itemGoodsPrice[]" value="<?=gd_currency_display($val['goodsPrice'])?>" />
                                        <input type="hidden" name="itemScmNm[]" value="<?=$val['scmNm']?>" />
                                        <input type="hidden" name="itemTotalStock[]" value="<?=$val['totalStock']?>" />
                                        <input type="hidden" name="itemBrandNm[]" value="<?=gd_isset($val['brandNm'])?>" />
                                        <input type="hidden" name="itemMakerNm[]" value="<?=gd_isset($val['makerNm'])?>" />
                                        <input type="hidden" name="itemSoldOutFl[]" value="<?=gd_isset($val['soldOutFl'])?>" />
                                        <input type="hidden" name="itemStockFl[]" value="<?=gd_isset($val['stockFl'])?>" />
                                        <input type="hidden" name="itemImage[]" value="<?=rawurlencode(gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')); ?>" />

                                        <input type="<?=$checkType?>" name="itemGoodsNo[]" id="layer_goods_<?php echo $val['goodsNo'];?>"  value="<?php echo $val['goodsNo']; ?>" <?php if($timeSaleFl && $val['timeSaleSno']) { echo "disabled='disabled'"; } ?>/>
                                        <input type="hidden" name="itemGoodsDisplayFl[]" value="<?=gd_isset($val['goodsDisplayFl'])?>" />
                                        <input type="hidden" name="itemGoodsDisplayMobileFl[]" value="<?=gd_isset($val['goodsDisplayMobileFl'])?>" />
                                        <input type="hidden" name="itemGoodsSellFl[]" value="<?=gd_isset($val['goodsSellFl'])?>" />
                                        <input type="hidden" name="itemGoodsSellMobileFl[]" value="<?=gd_isset($val['goodsSellMobileFl'])?>" />
                                        <input type="hidden" name="itemIcon[]" value="<?=rawurlencode(gd_isset($val['goodsIcon'])); ?>" />
                                        <input type="hidden" name="regDt[]" value="<?=gd_date_format('Y-m-d', gd_isset($val['regDt']))?>" />

                                    </td>
                                    <td  class="center number addGoodsNumber_<?php echo $val['goodsNo'];?>" ><?php echo number_format($page->idx--); ?></td>
                                    <td  class="center"><span class="itemImage"><?php echo gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'); ?></span></td>
                                    <td >
                                        <span class="itemName"><a class="text-blue hand js-goods-popup" data-goodsno="<?=$val['goodsNo']; ?>"><?php echo gd_remove_only_tag(stripslashes($val['goodsNm'])); ?></a></span> <input type="hidden" name="goodsNoData[]" value="<?=$val['goodsNo']?>" />
                                        <input type="checkbox" name="sortFix[]" class="layer_sort_fix_<?php echo $val['goodsNo'];?>"  value="<?php echo $val['goodsNo']; ?>" style="display:none" >
                                        <div>
                                            <?php echo $val['goodsIcon']; ?>
                                        </div>
                                    </td>
                                    <td ><span class="itemPrice"><?php echo gd_currency_display($val['goodsPrice']); ?></span></td>
                                    <td ><?php echo $val['scmNm']; ?></td>
                                    <td  class="center"><?php echo $totalStock ?></td>
                                    <td  class="center"><?=$stockText ?></td>
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
                    <div class="center" style="padding-top: 20px"><?php echo $page->getPage("#"); ?></div>
                </form>
            </div>

        </td>
        <!-- 상품선택 리스트-->

        <!-- 등록상품 리스트-->
        <?php if($checkCheckboxType) {?>
        <td valign="top" class="goodsChoice_outlineTd">
            <table cellpadding="0" cellpadding="0" width="100%">
                <tr>

                    <td class="goodsChoice_outlineSort">

                        <table cellpadding="0" cellspacing="0" width="100%" height="30">
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td width="150">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-white btn-icon-bottom js-moverow goodsChoice_downArrowMore" data-direction="bottom">
                                                        맨아래
                                                    </button>
                                                    <button type="button" class="btn btn-white btn-icon-down js-moverow goodsChoice_downArrow" data-direction="down">
                                                        아래
                                                    </button>
                                                    <button type="button" class="btn btn-white btn-icon-up js-moverow goodsChoice_upArrow" data-direction="up">
                                                        위
                                                    </button>

                                                    <button type="button" class="btn btn-white btn-icon-top js-moverow goodsChoice_upArrowMore" data-direction="top">
                                                        맨위
                                                    </button>
                                                </div>

                                            </td>
                                            <td colspan="3" class="right pdr10"><span class="action-title">선택한 상품을</span> <input type="text" name="goodsChoice_sortText"
                                                                           class="goodsChoice_sortText"/> 번 위치로&nbsp;
                                                <input type="button" value="이동" class="btn btn-white goodsChoice_moveBtn">
                                                <?php
                                                if ($relationFl != 'm') {
                                                ?>
                                                <input type="button" value="고정" class="btn btn-white goodsChoice_fixBtn">
                                                <?php
                                                }
                                                ?>
                                            </td>

                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                <td valign="top" class="goodsChoice_registeredTdArea" >
                    <form id="addGoodsFrm">
                    <table cellpadding="0" cellpadding="0" width="100%" class="table table-rows" style="margin-bottom: 0px">
                        <thead>
                        <tr id="goodsRegisteredTrArea">
                            <th class="center" ><input type="checkbox" id="allCheck" value="y" onclick="all_checkbox(this,'tbl_add_goods_result')"/></th>
                            <th>진열순서</th>
                            <th>이미지</th>
                            <th>상품명</th>
                            <th>판매가</th>
                            <th>공급사</th>
                            <th>재고</th>
                            <th>품절여부</th>
                        </tr>
                        </thead>
                    </table>
                    <div id="goodsChoice_registerdOutlineDiv">
                        <table cellpadding="0" cellpadding="0" width="100%" id="tbl_add_goods_result" class="table table-rows">
                        <tbody contents-length="<?=is_null($setGoodsList) ? 0 : strlen(trim($setGoodsList))?>">
                        <?php if($setGoodsList) { echo $setGoodsList; } ?>
                        </tbody>
                        </table>
                    </div>
                    </form>
                </td>
                </tr>
                <tr>
                    <td class="goodsChoice_outlineSort">
                        <table cellpadding="0" cellspacing="0" width="100%" height="30">
                            <tr>
                                <td>
                                    <table cellpadding="0" cellspacing="0" width="100%">
                                        <tr>
                                            <td width="150">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-white btn-icon-bottom js-moverow goodsChoice_downArrowMore" data-direction="bottom">
                                                        맨아래
                                                    </button>
                                                    <button type="button" class="btn btn-white btn-icon-down js-moverow goodsChoice_downArrow" data-direction="down">
                                                        아래
                                                    </button>
                                                    <button type="button" class="btn btn-white btn-icon-up js-moverow goodsChoice_upArrow" data-direction="up">
                                                        위
                                                    </button>

                                                    <button type="button" class="btn btn-white btn-icon-top js-moverow goodsChoice_upArrowMore" data-direction="top">
                                                        맨위
                                                    </button>
                                                </div>

                                            </td>
                                            <td colspan="3" class="right pdr10"><span class="action-title">선택한 상품을</span>
                                                <input type="text" name="goodsChoice_sortText" class="goodsChoice_sortText"/> 번 위치로&nbsp;
                                                <input type="button" value="이동" class="btn btn-white goodsChoice_moveBtn">
                                                <?php
                                                if ($relationFl != 'm') {
                                                    ?>
                                                    <input type="button" value="고정"
                                                           class="btn btn-white goodsChoice_fixBtn">
                                                <?php
                                                }
                                                ?>
                                            </td>

                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td></tr></table>
        </td>
        <?php }?>
        <!-- 등록상품 리스트-->
    </tr>
    </table>
    <div style="width: 100%;height: 140px;padding: 30px 0;" class="center">
        <input type="button" value="취소" id="goodsChoiceCancel" class="btn btn-lg btn-white" onclick="self.close();" style="font-weight: bold;margin-right: 10px"/>
        <?php if($checkCheckboxType) {?>
            <input type="button" value="선택완료" id="goodsChoiceConfirm" class="goodsChoiceConfirm btn btn-lg btn-black"/>
        <?php } else {?>
            <input type="button" value="확인" id="goodsChoiceConfirm" class="goodsRadioChoiceConfirm btn btn-lg btn-black"/>
        <?php }?>
    </div>

    <script type="text/javascript">
        <!--
        $(document).ready(function(){

            /**
             * [전체 삭제]인 경우를 체크하여 html() 강제 치환 - 2018.10.01 parkjs
             * selectedGoodsList 값이 none인 상태에서 넘겨받은 html의 length가 0일 경우 전체 삭제로 간주함.
             **/
            if ($('#selectedGoodsList').val() == "none" && $('#tbl_add_goods_result > tbody').attr('contents-length') == 0) {
                $('#tbl_add_goods_result > tbody').html("");
            }

            $('.goodsRadioChoiceConfirm').bind('click',function(){
                if($('input[type=radio][name="itemGoodsNo[]"]:checked').length<1) {
                    alert('상품을 선택해주세요.');
                    return;
                }

                var resultJson = {
                    "info": []
                };

                var checkedGoodsNo = $('input[type=radio][name="itemGoodsNo[]"]:checked').val();
                var imgSrc = $('#tbl_add_goods_'+checkedGoodsNo).find('.itemImage img').attr('src');
                var name = $('#tbl_add_goods_'+checkedGoodsNo).find('.itemName').text();
                var price = $('#tbl_add_goods_'+checkedGoodsNo).find('.itemPrice').text();

                resultJson.info.push({
                    "goodsNo": checkedGoodsNo,
                    "goodsImgageSrc": imgSrc,
                    "goodsName": name,
                    "goodsPrice": price,
                });
                opener.setAddGoods(resultJson);
                self.close();
            })

            $('input').keydown(function(e) {
                if (e.keyCode == 13) {
                    $("input[name='setGoodsList']").val( encodeURIComponent($("#tbl_add_goods_result tbody").html()));
                    $("#frmSearchBase").submit();
                    return false
                }
            });


            $('.search-goods-btn').click(function() {

                $("input[name='setGoodsList']").val( encodeURIComponent($("#tbl_add_goods_result tbody").html()));
                $("#frmSearchBase").submit();

            });

            $('.pagination li a').click(function() {

                $("input[name='page']").val($(this).data('page'));
                $('.search-goods-btn').click();
            });

        });

        $('select[name=\'pageNum\']').change(function () {
            $('.search-goods-btn').click();
        });

        $('select[name=\'sort\']').change(function () {
            $('.search-goods-btn').click();
        })


        function search_register() {
            $("#allCheck").click();

            $("#addGoods").click();

        }

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

            layer_add_info(typeStr,addParam);
        }

        //-->
    </script>
