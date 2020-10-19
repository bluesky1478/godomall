<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="저장" class="btn btn-red" id="batchSubmit"/>
    </div>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmBatchPrice" name="frmBatchPrice" action="./goods_ps.php"    target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="batch_mileage" />
    <input type="hidden" name="modDtUse" value="" />
    <?php
    foreach ($batchAll as $key => $val) {
        echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
    }
    ?>
    <div  class="table-responsive">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width-2xs center"><input type="checkbox" class="js-checkall" data-target-name="arrGoodsNo[]"></th>
                <th class="width-2xs center">번호</th>
                <th class="width-xs center">상품코드</th>
                <th class="width-xs">이미지</th>
                <th class="width-lg center">상품명</th>
                <th class="width-xs center">공급사</th>
                <th class="width-xs center">노출상태</th>
                <th class="width-xs center">판매상태</th>
                <th class="width-md center">판매가</th>
                <th class="width-md center">마일리지</th>
                <th class="width-xs center">상품할인</th>
                <th class="width-xl center">상품혜택</th>
                <th class="width-2xs center">상세보기</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && count($data) > 0 ) {
                $arrGoodsDisplay = ['y' => '노출함', 'n' => '노출안함'];
                $arrGoodsSell = ['y' => '판매함', 'n' => '판매안함'];
                // 상품혜택관련 치환 배열
                $arrDiscountGroup        = array('all' => '전체', 'member' => '회원전용', 'group' => '특정회원등급');
                $arrNewGoodsReg        = array('regDt' => '등록일', 'modDt' => '수정일');
                $arrNewGoodsDate      = array('day' => '일', 'hour' => '시간');

                // --- 상품  설정 config 불러오기
                $goodsConfig = (gd_policy('goods.display'));
                $goodsConfig['goodsModDtTypeAll'] = gd_isset($goodsConfig['goodsModDtTypeAll'], 'y');
                $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');

                foreach ($data as $key => $val) {
                    $goodsBenefitStr = '';
                    $goodsDiscount = '사용안함';
                    if ($val['goodsDiscountFl'] == 'y' || $val['goodsBenefitSetFl'] == 'y') {
                        if (gd_isset($val['goodsDiscountGroup'], 'all') == 'all') {
                            $goodsDiscount = '사용함<br />(전체회원)';
                        } else if (gd_isset($val['goodsDiscountGroup'], 'all') == 'member') {
                            $goodsDiscount = '사용함<br />(회원전용)';
                        } else if (gd_isset($val['goodsDiscountGroup'], 'all') == 'group') {
                            $goodsDiscount = '사용함<br />(특정회원등급)';
                        }
                    }
                    // 혜택유형
                    if ($val['benefitUseType'] == 'nonLimit') {
                        $benefitPeriod = '<span style="font-size: 11px;font-weight: normal;">제한없음</span>';
                    } else if ($val['benefitUseType'] == 'newGoodsDiscount') {
                        $benefitPeriod = '<span style="font-size: 11px;font-weight: normal;">상품 ' . $arrNewGoodsReg[$val['newGoodsRegFl']] . '부터 ' . $val['newGoodsDate'] . $arrNewGoodsDate[$val['newGoodsDateFl']] . '까지</span>';
                    } else {
                        $benefitPeriod = '<span style="font-size: 11px;font-weight: normal;">' . gd_date_format("Y-m-d H:i", $val['periodDiscountStart']) . ' ~ ' . gd_date_format("Y-m-d H:i", $val['periodDiscountEnd']) . '</span>';
                    }
                    // 혜택금액단위
                    if ($val['goodsDiscountUnit'] == 'percent') {
                        $goodsDiscountUnit = $val['goodsDiscount'] . '% - ';
                    } else {
                        $goodsDiscountUnit = gd_money_format($val['goodsDiscount']) . gd_currency_default() . ' - ';
                    }
                    if (gd_isset($val['goodsDiscountGroup'], 'all') == 'group') {
                        $goodsDiscountUnit = '';
                    }
                    // 상품혜택관리 사용 일 경우
                    if($val['goodsBenefitSetFl'] == 'y') {
                        $goodsBenefitStr = $val['benefitNm'] . '<br/>(' . $goodsDiscountUnit . $arrDiscountGroup[$val['goodsDiscountGroup']] . ')<br/>'. $benefitPeriod;
                    } else if($val['goodsDiscountFl'] == 'y' && $val['goodsBenefitSetFl'] == 'n') { // 개별설정만 사용일 경우
                        $goodsBenefitStr = '개별설정<br/>(' . $goodsDiscountUnit . $arrDiscountGroup[$val['goodsDiscountGroup']] . ')<br/>' . $benefitPeriod;
                    } else if($val['goodsDiscountFl'] == 'n' && $val['goodsBenefitSetFl'] == 'n') { // 둘 다 미사용일 경우
                        $goodsBenefitStr = '개별설정<br/>(사용안함)';
                    }

                    $mileageFlText = $mileageFl[gd_isset($val['mileageFl'], 'c')];
                    $mileageFlText .= '<br />(' . $mileageGroup[gd_isset($val['mileageGroup'], 'all')] . ')';
                    ?>
                    <tr>
                        <td class="center number">
                            <input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo']; ?>"/>
                        </td>
                        <td class="center"><?=number_format($page->idx--); ?></td>
                        <td class="center number"><?=$val['goodsNo']; ?></td>
                        <td class="center">
                            <div class="width-2xs">
                                <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?>
                            </div>
                        </td>
                        <td>
                            <a href="./goods_register.php?goodsNo=<?=$val['goodsNo']; ?>" target="_blank"><span class="emphasis_text"><?=$val['goodsNm']; ?></span></a>

                        </td>
                        <td class="center"><?= $val['scmNm'] ?></td>
                        <td class="center lmenu"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                        <td class="center lmenu"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                        <td class="center number">
                            <div class="form-inline"><?=gd_currency_symbol(); ?><?=gd_money_format($val['goodsPrice']); ?><?=gd_currency_string(); ?></div>
                        </td>
                        <td class="center"><?= $mileageFlText ?></td>
                        <td class="center"><?php echo $goodsDiscount; ?></td>
                        <td class="left"><?=$goodsBenefitStr; ?></td>
                        <td class="center"><button type="button" class="btn btn-sm btn-white js-benefit-detail" data-goods-no="<?php echo $val['goodsNo']; ?>">보기</button></td>
                    </tr>
                    <?php
                }
            }  else {

                ?>
                <tr><td class="no-data" colspan="12">검색된 정보가 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>


    </div>
    <div class="center"><?=$page->getPage();?></div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th class="center">
                    <select name="type" onchange="view_terms(this.value)" class="form-control">
                        <option value="mileage">마일리지</option>
                        <option value="discount">상품할인/제외혜택</option>
                    </select><br/>
                    조건설정
                </th>
                <td id="display_set">
                    <label class="checkbox-inline">
                        <input type="checkbox" id="batchAll" name="batchAll" value="y" />검색된 상품 전체(<?=number_format($page->recode['total']);?>개 상품)를 수정합니다.
                    </label>
                    <p class="notice-danger mgt5">상품수가 많은 경우 비권장합니다. 가능하면 한 페이지씩 선택하여 수정하세요.</p>
                    <table class="table table-cols" id="tbl_set_mileage">
                        <colgroup>
                            <col class="width-sm">
                            <col>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>지급방법 선택</th>
                            <td>
                                <label class="radio-inline"><input type="radio" name="mileageFl" value="c" checked="checked" onclick="display_mileage_set();"/>통합설정</label>
                                <label class="radio-inline"><input type="radio" name="mileageFl" value="g" onclick="display_mileage_set();"/>개별설정</label>
                            </td>
                        </tr>
                        <tr>
                            <th>대상 선택</th>
                            <td class="form-inline">
                                <label class="radio-inline"><input type="radio" name="mileageGroup" value="all" checked="checked" onclick="display_mileage_set();">전체회원</label>
                                <label class="radio-inline"><input type="radio" name="mileageGroup" value="group" onclick="display_mileage_set();layer_register('mileage_group','search')">특정회원등급</label>
                                <label>
                                    <button type="button" class="btn btn-sm btn-gray js-mileage-group-select">회원등급 선택</button>
                                </label>

                                <div id="mileage_group" class="selected-btn-group"></div>
                            </td>
                        </tr>
                        <tr>
                            <th>금액 설정</th>
                            <td>
                                <div class="mileage-set-c">
                                    <?php if ($conf['mileage']['giveType'] == 'price') { ?>
                                        구매 금액의 <span><?=$conf['mileage']['goods']; ?>%</span>를  마일리지로 지급
                                    <?php } else if ($conf['mileage']['giveType'] == 'priceUnit') { ?>
                                        구매금액으로 <?=number_format($conf['mileage']['goodsPriceUnit']); ?>원 단위로 <?=number_format($conf['mileage']['goodsMileage']); ?> 마일리지 지급
                                    <?php } else if ($conf['mileage']['giveType'] == 'cntUnit') { ?>
                                        구매금액과 상관없이 구매상품 1개 단위로 <?=number_format($conf['mileage']['cntMileage']); ?> 마일리지 지급
                                    <?php } ?>
                                </div>
                                <div class="mileage-set-g-all display-none form-inline">
                                    <span class="goods-title">구매금액의</span>
                                    <input type="text" name="mileageGoods" class="form-control width-sm">
                                    <select name="mileageGoodsUnit" class="goods-unit form-control width-2xs">
                                        <option value="percent" selected="selected">%</option>
                                        <option value="mileage"><?=Globals::get('gSite.member.mileageBasic.unit'); ?></option>
                                    </select>
                                </div>
                                <div class="mileage-set-g-group display-none">
                                    <table class="table table-rows" style="width:auto;">
                                        <thead>
                                        <tr>
                                            <th>회원등급</th>
                                            <th>지급금액</th>
                                        </tr>
                                        </thead>
                                        <tr>
                                            <td><?php echo gd_select_box(null, "mileageGroupMemberInfo['groupSno'][]", $groupList, null, null, '=회원등급 선택='); ?></td>
                                            <td class="form-inline">
                                                <span class="goods-title">구매금액의</span>
                                                <input type="text" name="mileageGroupMemberInfo['mileageGoods'][]" value="" class="form-control width-sm">
                                                <select name="mileageGroupMemberInfo['mileageGoodsUnit'][]" class="goods-unit form-control width-2xs">
                                                    <option value="percent" checked="checked">%</option>
                                                    <option value="mileage"><?=Globals::get('gSite.member.mileageBasic.unit'); ?></option>
                                                </select>
                                                <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus add-groupSno" data-target="mileage">
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                        <tfoot>
                        <tr>
                            <td colspan="2">
                                <div class="notice-info">
                                    통합설정 <a href="../member/member_mileage_give.php" target="_blank" class="btn-link">회원>마일리지/예치금 관리>마일리지 지급설정</a>에서 설정한 기준에 따름 :
                                    <?php if ($conf['mileage']['giveType'] == 'price') { ?>
                                        구매 금액의 <span><?=$conf['mileage']['goods']; ?>%</span>를  마일리지로 지급
                                    <?php } else if ($conf['mileage']['giveType'] == 'priceUnit') { ?>
                                        구매금액으로 <?=number_format($conf['mileage']['goodsPriceUnit']); ?>원 단위로 <?=number_format($conf['mileage']['goodsMileage']); ?> 마일리지 지급
                                    <?php } else if ($conf['mileage']['giveType'] == 'cntUnit') { ?>
                                        구매금액과 상관없이 구매상품 1개 단위로 <?=number_format($conf['mileage']['cntMileage']); ?> 마일리지 지급
                                    <?php } ?><br />
                                    구매금액 <a href="../member/member_mileage_basic.php" target="_blank" class="btn-link">회원>마일리지/예치금 관리>마일리지 기본설정</a>에서 설정한 기준에 따름 : <?php echo $conf['mileageBasic']['mileageText']; ?><br />
                                    절사기준 <a href="../policy/base_currency_unit.php" target="_blank" class="btn-link">기본설정>기본정책>금액/단위 기준설정</a>에서 설정한 기준에 따름</span> : <?=gd_trunc_display('mileage'); ?>
                                </div>
                            </td>
                        </tr>
                        </tfoot>
                    </table>

                    <div class="pdl5 pdb5 display-none" id="tbl_set_goodsBenefit">
                        <label class="radio-inline">
                            <input type="radio" name="goodsBenefitSetFl" value="n"  onclick="display_benefit_set('n', 'goods_benefit_group');" checked />개별설정
                        </label>
                        <?php if($goodsBenefitUse == 'y'){?>
                            <label class="radio-inline">
                                <input type="radio" name="goodsBenefitSetFl" value="y"  onclick="display_benefit_set('y', 'goods_benefit_group');layer_register('goods_benefit','layer')" />상품 혜택 적용</label>
                            <label>
                                <button type="button" class="btn btn-sm btn-gray js-goods-benefit-select">혜택 선택</button>
                            </label>
                            <span class="notice-info"> <a href="../goods/goods_benefit_list.php" class="btn-link" target="_blank">상품>상품관리>상품 혜택관리</a>에서 등록한 상품 혜택정보를 적용할 수 있습니다.</span>
                        <?php }?>
                    </div>

                    <div id="goods_benefit_group" class="selected-btn-group" >

                    </div>

                    <div id="goodsBenefitConfig">
                        <table class="table table-cols display-none" id="tbl_set_discount">
                            <colgroup>
                                <col class="width-md">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <th colspan="2">상품 할인 설정</th>
                            </tr>
                            <tr>
                                <th>사용여부</th>
                                <td>
                                    <div class="radio">
                                        <label class="radio-inline">
                                            <input type="radio" name="goodsDiscountFl" value="n" onclick="display_toggle_class('goodsDiscountFl', 'goodsDiscountConfig');" checked="checked">사용안함
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="goodsDiscountFl" value="y" onclick="display_toggle_class('goodsDiscountFl', 'goodsDiscountConfig');">사용함
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr class="goodsDiscountConfig display-none">
                                <th class="input_title">진행유형</th>
                                <td class="input_area">

                                    <div>
                                        <label class="radio-inline">
                                            <input type="radio" name="benefitUseType" value="nonLimit" checked>제한 없음
                                        </label>
                                    </div>

                                    <div class="pdt10">
                                        <label class="radio-inline">
                                            <input type="radio" name="benefitUseType" value="newGoodsDiscount">신상품 할인
                                        </label>
                                        <div class="form-inline pdt10 pdl20">
                                            상품 등록일
                                            <select name="newGoodsRegFl" class="form-control" style="display: none">
                                                <option value="regDt" checked>등록일</option>
                                                <!--<option value="modDt">수정일</option>-->
                                            </select>
                                            부터
                                            <input type="text" name="newGoodsDate" value="" class="form-control width-2xs" maxlength="5">
                                            <select name="newGoodsDateFl" class="form-control">
                                                <option value="day" selected>일</option>
                                                <option value="hour">시간</option>
                                            </select>
                                            까지
                                        </div>
                                    </div>

                                    <div class="pdt10">
                                        <label class="radio-inline">
                                            <input type="radio" name="benefitUseType" value="periodDiscount">특정기간 할인
                                        </label>

                                        <div class="form-inline pdt10 pdl20">
                                            <div class="input-group js-datetimepicker">
                                                <input type="text" class="form-control width-sm" name="periodDiscountStart" value="" >
                                                <span class="input-group-addon">
                                                        <span class="btn-icon-calendar">
                                                        </span>
                                                    </span>
                                            </div>
                                            ~
                                            <div class="input-group js-datetimepicker">
                                                <input type="text" class="form-control width-sm" name="periodDiscountEnd" value="" >
                                                <span class="input-group-addon">
                                                        <span class="btn-icon-calendar">
                                                        </span>
                                                    </span>
                                            </div>
                                        </div>
                                    </div>

                                </td>
                            </tr>
                            <tr class="goodsDiscountConfig display-none">
                                <th>할인금액 기준</th>
                                <td>
                                    <input type="checkbox" checked="checked" disabled="disabled">판매가&nbsp;+&nbsp;(&nbsp;
                                    <?= gd_check_box('fixedGoodsDiscount[]', $fixedGoodsDiscount, $data['fixedGoodsDiscount']); ?>
                                    &nbsp;)&nbsp;
                                </td>
                            </tr>
                            <tr class="goodsDiscountConfig display-none">
                                <th>대상 선택</th>
                                <td>
                                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroup" value="all" checked="checked" onclick="display_goods_discount_set();">전체(회원+비회원)</label>
                                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroup" value="member" onclick="display_goods_discount_set();">회원전용(비회원제외)</label>
                                    <label class="radio-inline"><input type="radio" name="goodsDiscountGroup" value="group" onclick="display_goods_discount_set();">특정회원등급</label>
                                </td>
                            </tr>
                            <tr class="goodsDiscountConfig display-none">
                                <th>금액 설정</th>
                                <td>
                                    <div class="goods-discount-all form-inline">
                                        <span class="goods-title">구매금액의</span>
                                        <input type="text" name="goodsDiscount" class="form-control width-sm">
                                        <select name="goodsDiscountUnit" class="goods-unit form-control width-2xs">
                                            <option value="percent" selected="selected">%</option>
                                            <option value="price"><?=gd_currency_default(); ?></option>
                                        </select>
                                    </div>
                                    <div class="goods-discount-group hide">
                                        <table class="table table-rows" style="width:auto;">
                                            <thead>
                                            <tr>
                                                <th>회원등급</th>
                                                <th>할인금액</th>
                                            </tr>
                                            </thead>
                                            <tr>
                                                <td><?php echo gd_select_box(null, "goodsDiscountGroupMemberInfo['groupSno'][]", $groupList, null, null, '=회원등급 선택='); ?></td>
                                                <td class="form-inline">
                                                    <span class="goods-title">구매금액의</span>
                                                    <input type="text" name="goodsDiscountGroupMemberInfo['goodsDiscount'][]" value="" class="form-control width-sm">
                                                    <select name="goodsDiscountGroupMemberInfo['goodsDiscountUnit'][]" class="goods-unit form-control width-2xs">
                                                        <option value="percent" selected="selected">%</option>
                                                        <option value="price"><?=gd_currency_default();?></option>
                                                    </select>
                                                    <input type="button" value="추가" class="btn btn-sm btn-white btn-icon-plus add-groupSno" data-target="discount">
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="2">
                                    <span class="notice-info">절사기준 <a href="../policy/base_currency_unit.php" class="btn-link" target="_blank">[기본설정>기본정책>금액/단위 기준설정]</a>에서 설정한 기준에 따름 : <?=gd_trunc_display('goods'); ?></span>
                                </td>
                            </tr>
                            </tfoot>
                        </table>

                        <table class="table table-cols display-none" id="tbl_set_except">
                            <colgroup>
                                <col class="width-sm">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <th colspan="2">혜택 제외 설정 <span class="notice-info">상품 할인 설정의 진행유형 및 기간과 상관없이 별도 설정이 가능합니다.</span></th>
                            </tr>
                            <tr>
                                <th>제외 혜택 선택</th>
                                <td>
                                    <?= gd_check_box('exceptBenefit[]', $exceptBenefit, $data['exceptBenefit'], 1); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>제외 대상 선택</th>
                                <td class="form-inline">
                                    <label class="radio-inline"><input type="radio" name="exceptBenefitGroup" value="all" checked="checked" onclick="display_group_member('all', 'except_benefit_group');">전체회원</label>
                                    <label class="radio-inline"><input type="radio" name="exceptBenefitGroup" value="group" onclick="display_group_member('group', 'except_benefit_group');layer_register('except_benefit_group','search')">특정회원등급</label>
                                    <label>
                                        <button type="button" class="btn btn-sm btn-gray js-except-benefit-group-select">회원등급 선택</button>
                                    </label>

                                    <div id="except_benefit_group" class="selected-btn-group"></div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</form>


<script type="text/javascript">
    <!--

    $(document).ready(function(){


        $( "#batchSubmit" ).click(function() {

            var msg = '';

            var type =  $('select[name="type"]').val();

            if ($('#batchAll:checked').length == 0) {
                if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                    $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                    return false;
                }

                msg += '선택된 상품을 ';
            } else {
                msg += '검색된 전체 상품을 ';
            }

            if(type =='discount')
            {

                if($('#display_set input[name="goodsDiscountFl"]:checked').val() == 'y' && $('input[name="goodsBenefitSetFl"]:checked').val() != 'y')
                {
                    if ($('#display_set input[name="goodsDiscountGroup"]:checked').val() == 'group') {
                        var discountFl = true;
                        var goodsDiscountFl = true;
                        $('#display_set input[name="goodsDiscountGroupMemberInfo[\'goodsDiscount\'][]"]').each(function(index){
                            if ($('#display_set select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]').eq(index).val() == '' || $(this).val() == '' || parseFloat($(this).val()) <= 0) {
                                discountFl = false;
                                return false;
                            }
                            if ($('#display_set select[name="goodsDiscountGroupMemberInfo[\'goodsDiscountUnit\'][]"]').eq(index).val() == 'percent' && parseFloat($(this).val()) > 100) {
                                goodsDiscountFl = false;
                                return false;
                            }
                        });
                        if (discountFl === false) {
                            alert('상품 할인 설정의 금액설정 항목을 입력하세요.');
                            return false;
                        }
                        if (goodsDiscountFl === false) {
                            alert('상품 할인 금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    } else {
                        if ($('#display_set select[name="goodsDiscountUnit"]').val() == 'percent' && parseFloat($('#display_set input[name="goodsDiscount"]').val()) > 100) {
                            alert('상품 할인 금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    }
                }

                if ($('input[name="exceptBenefit[]"]:checked').length > 0 && $('input[name="exceptBenefitGroup"]:checked').val() == 'group') {
                    if (!$('input[name="exceptBenefitGroupInfo[]"]').length) {
                        alert('상품 할인/적립 혜택 제외 회원등급을 선택해주세요');
                        return false;
                    }
                }

                if ($('input[name="benefitUseType"]:checked').val() == 'newGoodsDiscount' && $('input[name="goodsBenefitSetFl"]:checked').val() != 'y') {
                    if ($('input[name="newGoodsDate"]') == '' || $('input[name="newGoodsDate"]').val() < 1) {
                        alert('신상품 할인 기간을 입력해주세요');
                        return false;
                    }
                }

                if ($('input[name="goodsDiscountFl"]:checked').val() == 'y' && $('input[name="goodsBenefitSetFl"]:checked').val() != 'y' ) {
                    if ($('[name="goodsDiscountGroup"]:checked').val() != 'group' && $('input[name="goodsDiscount"]').val() < 1) {
                        alert('상품 할인 설정의 금액설정 항목을 입력하세요');
                        return false;
                    }
                }


                if ($('input[name="benefitUseType"]:checked').val() == 'periodDiscount' && $('input[name="goodsBenefitSetFl"]:checked').val() != 'y') {

                    var discountStart = $('input[name="periodDiscountStart"]').val();
                    var discountEnd = $('input[name="periodDiscountEnd"]').val()
                    if(discountStart == '' || discountEnd == ''){
                        alert('특정기간 할인의 시작일/종료일을 확인해 주세요');
                        return false;
                    }
                    var sdt = dateGetTime(discountStart);
                    var edt = dateGetTime(discountEnd);
                    if (sdt >= edt) {
                        alert('특정기간 할인의 시작일/종료일을 확인해 주세요');
                        return false;
                    }
                }

                if ($('input[name="goodsBenefitSetFl"]:checked').val() == 'y') {
                    var benefitSno = $('input[name="benefitSno"]').val();
                    if ( typeof benefitSno == "undefined" || benefitSno == "" ){
                        alert('상품 혜택을 선택해주세요');
                        return false;
                    }
                }

                if ($('#display_set input[name="exceptBenefit[]"]:checked').length > 0 && $('#display_set input[name="exceptBenefitGroup"]:checked').val() == 'group') {
                    if (!$('#display_set input[name="exceptBenefitGroupInfo[]"]').length) {
                        alert('상품 할인/적립 혜택 제외 회원등급을 선택해주세요');
                        return false;
                    }
                }

                msg += '설정하신 상품할인 조건으로 \n';
            }
            else
            {
                if($('#display_set input[name="mileageFl"]:checked').val() == 'g')
                {
                    if ($('#display_set input[name="mileageGroup"]:checked').val() == 'group') {
                        var mileageFl = true;
                        var mileageDiscountFl = true;
                        $('#display_set input[name="mileageGroupMemberInfo[\'mileageGoods\'][]"]').each(function(index){
                            if ($('#display_set select[name="mileageGroupMemberInfo[\'groupSno\'][]"]').eq(index).val() == '' || $(this).val() == '' || parseFloat($(this).val()) <= 0) {
                                mileageFl = false;
                                return false;
                            }
                            if ($('#display_set select[name="mileageGroupMemberInfo[\'mileageGoodsUnit\'][]"]').eq(index).val() == 'percent' && parseFloat($(this).val()) > 100) {
                                mileageDiscountFl = false;
                                return false;
                            }
                        });
                        if (mileageFl === false) {
                            alert('마일리지 설정의 금액설정 항목을 입력하세요.');
                            return false;
                        }
                        if (mileageDiscountFl === false) {
                            alert('마일리지 지급금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    } else {
                        if ($('#display_set select[name="mileageGoodsUnit"]').val() == 'percent' && parseFloat($('#display_set input[name="mileageGoods"]').val()) > 100) {
                            alert('마일리지 지급금액은 100%를 초과할 수 없습니다.');
                            return false;
                        }
                    }
                } else if ($('#display_set input[name="mileageFl"]:checked').val() == 'c' && $('#display_set input[name="mileageGroup"]:checked').val() == 'group') {
                    if (!$('#display_set input[name="mileageGroupInfo[]"]').length) {
                        alert('마일리지 지급 대상 회원등급을 선택해주세요');
                        return false;
                    }
                }
                msg += '설정하신 마일리지 조건으로 ';
            }

            msg += '일괄 수정하시겠습니까?<br />';
            msg += '[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';

            dialog_confirm(msg, function (result) {
                if (result) {
                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?", function (result2) {
                        if (result2) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $( "#frmBatchPrice").submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                        //상품 수정일 변경 범위설정 체크
                        <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                            $('input[name="modDtUse"]').val('y');
                        <?php } else { ?>
                            $('input[name="modDtUse"]').val('n');
                        <?php } ?>
                        $( "#frmBatchPrice").submit();
                    <?php } ?>
                }
            });

        });

        except_benefit_disabled();

        $('input[name="exceptBenefit[]"]').click(function () {
            except_benefit_disabled();
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $( ".js-search-toggle" ).click(function() {
            onlyDisplayArea();
        });

        $('.js-mileage-group-select, .js-except-benefit-group-select').bind('click', function () {
            $(this).closest('td').find('input[type="radio"][value="group"]').trigger('click');
        });

        $('.add-groupSno').click(function(){
            var target = $(this).data('target');
            switch (target) {
                case 'mileage':
                    var groupSnoName = 'select[name="mileageGroupMemberInfo[\'groupSno\'][]"]';
                    var goodsUnitName = 'select[name="mileageGroupMemberInfo[\'mileageGoodsUnit\'][]"]';
                    var inputName = 'mileageGroupMemberInfo[\'mileageGoods\'][]';
                    var appendClassName = 'mileage-set-g-group';
                    break;
                case 'discount':
                    var groupSnoName = 'select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]';
                    var goodsUnitName = 'select[name="goodsDiscountGroupMemberInfo[\'goodsDiscountUnit\'][]"]';
                    var inputName = 'goodsDiscountGroupMemberInfo[\'goodsDiscount\'][]';
                    var appendClassName = 'goods-discount-group';
                    break;
            }

            var groupCnt = '<?php echo $groupCnt; ?>';
            var length = $(groupSnoName).length;

            if (length >= groupCnt) {
                alert('');
                return;
            }

            var groupSnoInfo = $(this).closest('tr').find(groupSnoName)[0].outerHTML.replace('selected="selected"', '');
            var goodsUnitInfo = $(this).closest('tr').find(goodsUnitName)[0].outerHTML.replace('selected="selected"', '');

            var html = '<tr>' +
                '<td>' + groupSnoInfo + '</td>' +
                '<td class="form-inline"><span class="goods-title">구매금액의</span> <input type="text" name="' + inputName + '" value="" class="form-control width-sm"> ' + goodsUnitInfo + ' <input type="button" value="삭제" class="btn btn-sm btn-white btn-icon-minus del-groupSno"></td>' +
                '</tr>';
            $('.' + appendClassName + ' table').append(html);
        });

        $(document).on('click', '.del-groupSno', function(){
            $(this).closest('tr').remove();
        });

        $(document).on('change', 'select[name="mileageGroupMemberInfo[\'groupSno\'][]"], select[name="goodsDiscountGroupMemberInfo[\'groupSno\'][]"]', function(){
            var name = this.name;
            var value = this.value;
            var flagFl = true;
            var index = $('select[name="' + name + '"]').index(this);

            $('select[name="' + name + '"]').each(function(idx){
                if (index != idx && ($(this).val() && value == $(this).val())) {
                    flagFl = false;
                    return false;
                }
            });

            if (flagFl === false) {
                alert('이미 선택된 회원등급 입니다.');
                $(this).val('');
            }
        });

        $(document).on('change', '.goods-unit', function(){
            set_goods_title($(this));
        });

        $('.js-benefit-detail').click(function(){
            var goodsNo = $(this).data('goods-no');

            var title = "상품 마일리지/할인 혜택 상세보기";
            $.get('../goods/benefit_detail.php',{ goodsNo : goodsNo }, function(data){

                data = '<div id="viewInfoForm">'+data+'</div>';

                var layerForm = data;

                BootstrapDialog.show({
                    title:title,
                    size: get_layer_size('normal'),
                    message: $(layerForm),
                    closable: true
                });
            });
        });

        onlyDisplayArea();

        if($('input[name="goodsBenefitSetFl"]:checked').val() == 'y'){
            display_benefit_set('y', 'goods_benefit_group');
        }

        $('.js-goods-benefit-select').bind('click', function () {
            $(this).closest('td').find('input[name="goodsBenefitSetFl"][value="y"]').trigger('click');
        });

        $("input[name='newGoodsDate']").number_only(5, 99999, 1);
        $("input[name='goodsDiscount']").number_only();
    });

    function set_goods_title(e) {
        var goodsTitle = '구매금액의';
        switch (e.val()) {
            case 'mileage':
            case 'price':
                goodsTitle = '구매수량별';
                break;
        }
        e.closest('.form-inline').find('.goods-title').html(goodsTitle);
    }

    function onlyDisplayArea()
    {
        if($('input[name=detailSearch]').val() == 'y'){
            $('.js-search-detail .js-search-mileage').show();
        }
        else {
            $('.js-search-detail .js-search-mileage').hide();
        }
    }

    function resetEventSearchCondition()
    {
        $("input[name='event_text']").val('');
        $("input[name='eventThemeSno']").val('');
        $("#eventGroupSelectArea").addClass("display-none");
        $("#eventSearchResetArea").addClass("display-none");
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
        if (typeStr == 'mileage_group') {
            if ($('#frmBatchPrice input[name="mileageFl"]:checked').val() == 'g') {
                return;
            }
            addParam['layerFormID'] = 'mileageGroup';
            addParam['parentFormID'] = 'mileage_group';
            addParam['dataFormID'] = 'info_mileage_group';
            addParam['dataInputNm'] = 'mileageGroupInfo';
            typeStr = 'member_group';
        }
        if (typeStr == 'except_benefit_group') {
            addParam['layerFormID'] = 'exceptBenefitGroup';
            addParam['parentFormID'] = 'except_benefit_group';
            addParam['dataFormID'] = 'info_except_benefit_group';
            addParam['dataInputNm'] = 'exceptBenefitGroupInfo';
            typeStr = 'member_group';
        }
        if (typeStr == 'goods_benefit') {
            addParam['layerFormID'] = 'goodsBenefitGroup';
            addParam['parentFormID'] = 'goods_benefit_group';
            addParam['dataFormID'] = 'info_goods_benefit_group';
            addParam['dataInputNm'] = 'benefitSno';
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }



    /**
     * 출력 여부
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr)
    {
        if (modeStr == 'show') {
            $('#'+thisID).attr('class','display-block');
        } else if (modeStr == 'hide') {
            $('#'+thisID).attr('class','display-none');
        }
    }

    function display_toggle_class(thisName, thisClass) {
        var modeStr = $('input[name="' + thisName + '"]:checked').val();console.log(modeStr);
        if (modeStr == 'y') {
            $('.' + thisClass).removeClass('display-none');
            // !중요! 숨겨진 엘리먼트를 보여지게 할 경우 maxlength 표시 부분의 위치가 어긋난다. 이에 아래 트리거를 사용해 위치를 재 설정한다.
        } else if (modeStr == 'n') {
            $('.' + thisClass).addClass('display-none');
        }
    }


    function view_terms(id)
    {
        $("#display_set .table-cols").hide();
        if(id == 'discount'){
            $("#tbl_set_discount").show();
            $("#tbl_set_except").show();
            $("#tbl_set_goodsBenefit").show();
        }else{
            $("#tbl_set_"+id).show();
        }

    }

    function display_mileage_set() {
        $('#frmBatchPrice div[class^="mileage-set"]').addClass('display-none');

        var mileageFl = $('#frmBatchPrice input[name="mileageFl"]:checked').val();
        var mileageGroup = $('#frmBatchPrice input[name="mileageGroup"]:checked').val();console.log(mileageFl, mileageGroup);
        if (mileageFl == 'g') {
            $('#frmBatchPrice #mileage_group').removeClass('active').empty();
            $('#frmBatchPrice .js-mileage-group-select').closest('label').hide();
            $('#frmBatchPrice .mileage-set-' + mileageFl + '-' + mileageGroup).removeClass('display-none');
        } else {
            $('#frmBatchPrice .js-mileage-group-select').closest('label').show();
            $('#frmBatchPrice .mileage-set-' + mileageFl).removeClass('display-none');
        }
        display_group_member(mileageGroup, 'mileage_group');
    }

    function display_goods_discount_set() {
        $('div[class^="goods-discount"]').addClass('hide');

        var goodsDiscountGroup = $('input[name="goodsDiscountGroup"]:checked').val();
        switch (goodsDiscountGroup) {
            case 'all':
            case 'member':
                $('.goods-discount-all').removeClass('hide');
                break;
            case 'group':
                $('.goods-discount-group').removeClass('hide');
                break;
        }
    }

    function display_group_member(value, target) {
        if (value == 'all') {
            $('#frmBatchPrice #' + target).empty().removeClass('active');
        } else {
            $('#frmBatchPrice #' + target).addClass('active');
        }
    }

    /*
     * 상품할인 혜택 세팅
     */
    function display_benefit_set(value, target){
        if (value == 'n') {
            $('#' + target).empty().removeClass('active');
            display_toggle('goodsBenefitConfig','show');
        } else {
            $('#' + target).addClass('active');
            display_toggle('goodsBenefitConfig','hide')
        }
    }
    /*
     * 상품할인 혜택 수정 팝업
     */
    function goods_benefit_popup(sno) {

        var url = '/goods/goods_benefit_register.php?popupMode=yes&sno=' + sno;

        win = popup({
            url: url,
            target: '',
            width: 900,
            height: 600,
            scrollbars: 'yes',
            resizable: 'yes'
        });
        win.focus();
        return win;
    };

    function except_benefit_disabled() {

        var length = $('input[name="exceptBenefit[]"]:checked').length;

        if (length > 0) {
            $('input[name="exceptBenefitGroup"], .js-except-benefit-group-select').prop('disabled', false);
        } else {
            $('input[name="exceptBenefitGroup"], .js-except-benefit-group-select').prop('disabled', true);
        }
    }

    //-->
</script>
