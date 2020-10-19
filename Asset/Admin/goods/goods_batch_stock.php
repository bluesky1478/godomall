<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <input type="button" value="저장" class="btn btn-red" id="batchSubmit"/>
    </div>
</div>

<?php include($goodsSearchFrm); ?>

<form id="frmBatchStock" name="frmBatchStock" action="./goods_ps.php" target="ifrmProcess" method="post">
    <input type="hidden" name="mode" value="batch_stock" />
    <input type="hidden" name="modDtUse" value="" />
    <?php
    foreach ($batchAll as $key => $val) {
        echo '<input type="hidden" name="queryAll['.$key.']" value="'.$val.'" />'.chr(10);
    }
    ?>
    <div class="table-responsive">
        <table class="table table-rows table-fixed">
            <thead>
            <tr>
                <th class="width-2xs center"><input type="checkbox" class="js-checkall" data-target-name="arrGoodsNo[]"></th>
                <th class="width-2xs colOptionValueLast">번호</th>
                <th class="width-xs center colGoodsNo">상품코드</th>
                <th class="width-xs center colGoodsCd">자체상품코드</th>
                <th class="width-xs colGoodsImage">이미지</th>
                <th class="width-lg center colGoodsNm">상품명</th>
                <th class="width-xs center colTaxFreeFl">과세/면세</th>
                <th class="width-xs center colFixedPrice">정가</th>
                <th class="width-xs center colCostPrice">매입가</th>
                <th class="width-xs center colGoodsPrice">판매가</th>
                <th class="width-xs center colCommission">수수료</th>
                <th class="width-lg center colGoodsModelNo">모델번호</th>
                <th class="width-xs center colScmNo">공급사</th>
                <th class="width-md center colBrandCd">브랜드</th>
                <th class="width-md center colMakerNm">제조사</th>
                <th class="width-md center colGoodsDisplayFl">노출상태(PC)</th>
                <th class="width-md center colGoodsDisplayMobileFl">노출상태(모바일)</th>
                <th class="width-md center colGoodsSellFl">판매상태(PC)</th>
                <th class="width-md center colGoodsSellMobileFl">판매상태(모바일)</th>
                <th class="width-2xs center colSoldOutFl">품절상태</th>
                <th class="width-sm colTotalStock">상품재고</th>
                <th class="width-sm colOption">옵션</th>
                <th class="width-sm colOptionViewFl">옵션 노출상태</th>
                <th class="width-sm colOptionSellFl">옵션 품절상태</th>
                <?php
                if($optionType == 'y') {
                ?>
                <th class="width-sm center colOptionDeliveryFl">옵션 배송상태</th><?php
                }
                ?>
                <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                <!--<th class="width-lg center colOptionStopFl">판매중지수량</th>
                <th class="width-lg center colOptionRequestFl">확인요청수량</th>-->
                <th class="width-md colStockCnt">재고</th>
                <th class="width-lg center colOptionCostPrice">옵션매입가</th>
                <th class="width-lg center colOptionPrice">옵션가</th>
                <th class="width-xs center colMileageFl">마일리지</th>
                <th class="width-xs center colGoodsDiscountFl">상품할인</th>
                <th class="width-xs center colGoodsBenefit">상품혜택</th>
                <th class="width-md center colDeliverySno">배송정책</th>
                <th class="width-xs center colRegDt">등록일</th>
                <th class="width-xs center colModDt">수정일</th>
            </tr>
            <tr>
                <td><button type="button" class="btn btn-black btn-xs" onclick="set_display()" >선택상품 일괄적용</button> </td>
                <td class="colOptionValueLast"></td>
                <td class="colGoodsNo"></td>
                <td class="colGoodsCd"></td>
                <td class="colGoodsImage"></td>
                <td class="colGoodsNm"></td>
                <td class="colTaxFreeFl"></td>
                <td class="colFixedPrice"></td>
                <td class="colCostPrice"></td>
                <td class="colGoodsPrice"></td>
                <td class="colCommission"></td>
                <td class="colGoodsModelNo"></td>
                <td class="colScmNo"></td>
                <td class="colBrandCd"></td>
                <td class="colMakerNm"></td>
                <td class="colGoodsDisplayFl"><select class="form-control " id="setGoodsDisplayFl"><option value="">선택</option><option value="y">노출함</option><option value="n">노출안함</option></select></td>
                <td class="colGoodsDisplayMobileFl"><select class="form-control " id="setGoodsDisplayMobileFl"><option value="">선택</option><option value="y">노출함</option><option value="n">노출안함</option></select></td>
                <td class="colGoodsSellFl"><select class="form-control " id="setGoodsSellFl"><option  value="">선택</option><option value="y">판매함</option><option value="n">판매안함</option></select></td>
                <td class="colGoodsSellMobileFl"><select class="form-control " id="setGoodsSellMobileFl"><option  value="">선택</option><option value="y">판매함</option><option value="n">판매안함</option></select></td>
                <td class="colSoldOutFl"><select class="form-control " id="setSoldOutFl"><option  value="">선택</option><option value="n">정상</option><option value="y">품절</option></select></td>
                <td class="colTotalStock"></td>
                <td class="colOption"></td>
                <td class="colOptionViewFl"><select class="form-control " id="setOptionDisplayFl"><option  value="">선택</option><option value="y">노출함</option><option value="n">노출안함</option></select></td>
                <?php
                if($optionType == 'y') {
                ?>
                <td class="colOptionSellFl"><select class="form-control " id="setOptionSellFl">
                    <option value="">선택</option><?php
                    foreach ($stockReason as $key => $value) {
                        ?>
                        <option value="<?= $key ?>"><?= $value ?></option><?php
                    }
                    ?></select></td>
                <td class="colOptionDeliveryFl"><select class="form-control " id="setOptionDeliveryFl">
                    <option value="">선택</option><?php
                    foreach ($deliveryReason as $key => $value) {
                        ?>
                        <option value="<?= $key ?>"><?= $value ?></option><?php
                    }
                    ?></select></td>
                <?php
                }else{
                ?>
                <td class="colOptionSellFl"><select class="form-control " id="setOptionSellFl">
                        <option value="">선택</option>
                        <option value="y">정상</option>
                        <option value="n">품절</option>
                        </select></td><?php
                }
                ?>
                <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                <!--
                <td class="colOptionStopFl"><div class="form-inline"><select class="form-control "  id="setOptionSellStopFl"><option  value="">선택</option><option value="y">사용함</option><option value="n">사용안함</option></select> <input type="text" id="setOptionSellStopStock" class="form-control width-xs"></div></td>
                <td class="colOptionRequestFl"><div class="form-inline"><select class="form-control "  id="setOptionConfirmRequestFl"><option  value="">선택</option><option value="y">사용함</option><option value="n">사용안함</option></select> <input type="text" id="setOptionConfirmRequestStock" class="form-control width-xs"></div></td>
                -->
                <td class="colStockCnt"><div class="form-inline"><select id="setOptionStockFl" class="form-control "><option  value="">선택</option><option value="p">추가</option><option value="m">차감</option><option value="c">변경</option></select>
                        <input type="text" id="setOptionStockCnt" class="form-control width-2xs"></div>
                </td>
                <td class="colOptionCostPrice"></td>
                <td class="colOptionPrice"></td>
                <td class="colMileageFl"></td>
                <td class="colGoodsDiscountFl"></td>
                <td class="colGoodsBenefit"></td>
                <td class="colDeliverySno"></td>
                <td class="colRegDt"></td>
                <td class="colModDt"></td>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data) && count($data) > 0 ) {
                $i = 0;
                $goodsConfig = (gd_policy('goods.display')); //상품 설정 config 불러오기
                $goodsConfig['goodsModDtTypeAll'] = gd_isset($goodsConfig['goodsModDtTypeAll'], 'y');
                $goodsConfig['goodsModDtFl'] = gd_isset($goodsConfig['goodsModDtFl'], 'n');
                foreach ($data as $key => $val) {
                    $arrGoodsTax = array('t' => '과세', 'f' => '면세');

                    $tmp = [];
                    for ($i = 1; $i <= 5; $i++) {
                        if (!empty($val['optionValue' . $i])) {
                            $tmp[] = $val['optionValue' . $i];
                        }
                    }
                    $optionValue = implode(' / ', $tmp);

                    if($val['stockFl'] == 'y' &&($val['optionFl'] == 'y' && $val['sellStopFl'] == 'y' && $val['stockCnt'] <= $val['sellStopStock']) || $val['optionFl'] == 'y' && $val['confirmRequestFl'] == 'y' && $val['stockCnt'] <= $val['confirmRequestStock']){
                        $bgColor = '#f5f9fc';
                    }else{
                        $bgColor = '';
                    }
                    ?>
                    <tr bgcolor="<?=$bgColor?>">
                        <td class="center number">
                            <input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo'].'_'.$val['sno']; ?>"/>

                        </td>
                        <td class="center number"><?=number_format($page->idx--); ?></td>
                        <?php if ($val['optionNo'] == 1) { ?>
                            <td class="center number colGoodsNo"  style="border-bottom:0px;" ><?=$val['goodsNo']; ?> <input type="hidden" name="optionFl[<?=$val['goodsNo']?>]" value="<?=$val['optionFl']?>"></td>
                            <td class="center number colGoodsCd"  style="border-bottom:0px;" ><?=$val['goodsCd']; ?></td>
                            <td class="center colGoodsImage" style="border-bottom:0px;"  >
                                <?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank')?>
                            </td>
                            <td class="colGoodsNm" style="border-bottom:0px;"  >
                                <a href="./goods_register.php?goodsNo=<?=$val['goodsNo']; ?>" target="_blank"><span class="emphasis_text"><?=$val['goodsNm']; ?></span></a>

                            </td>
                            <td class="colTaxFreeFl"><?=$arrGoodsTax[$val['taxFreeFl']]?></td>
                            <td class="right colFixedPrice"><?=number_format($val['fixedPrice'])?></td>
                            <td class="right colCostPrice"><?=number_format($val['costPrice'])?></td>
                            <td class="right colGoodsPrice"><?=number_format($val['goodsPrice'])?></td>
                            <td class="right colCommission"><?=number_format($val['commission'])?>%</td>
                            <td class="colGoodsModelNo"><?=$val['goodsModelNo']?></td>
                            <td class="center colScmNo"style="border-bottom:0px;"  ><?=$val['scmNm']?></td>
                            <td class="colBrandCd"><?=$val['brandCd']?></td>
                            <td class="colMakerNm"><?=$val['makerNm']?></td>
                            <td class="center lmenu colGoodsDisplayFl" style="border-bottom:0px;">
                                <select class="form-control width-2xs  " name="goods[goodsDisplayFl][<?=$val['goodsNo']?>]">
                                    <option value="y" <?php if($val['goodsDisplayFl'] == 'y') { echo "selected='selected'"; } ?> >노출함</option>
                                    <option value="n" <?php if($val['goodsDisplayFl'] == 'n') { echo "selected='selected'"; } ?> >노출안함</option>
                                </select>
                            </td>
                            <td class="center lmenu colGoodsDisplayMobileFl" style="border-bottom:0px;">
                                <select class="form-control width-2xs " name="goods[goodsDisplayMobileFl][<?=$val['goodsNo']?>]">
                                    <option value="y" <?php if($val['goodsDisplayMobileFl'] == 'y') { echo "selected='selected'"; } ?> >노출함</option>
                                    <option value="n" <?php if($val['goodsDisplayMobileFl'] == 'n') { echo "selected='selected'"; } ?> >노출안함</option>
                                </select>
                            </td>
                            <td class="center lmenu colGoodsSellFl" style="border-bottom:0px;">
                                <select class="form-control width-2xs " name="goods[goodsSellFl][<?=$val['goodsNo']?>]">
                                    <option value="y" <?php if($val['goodsSellFl'] == 'y') { echo "selected='selected'"; } ?> >판매함</option>
                                    <option value="n" <?php if($val['goodsSellFl'] == 'n') { echo "selected='selected'"; } ?> >판매안함</option>
                                </select>
                            </td>
                            <td class="center lmenu colGoodsSellMobileFl" style="border-bottom:0px;">
                                <select class="form-control width-2xs " name="goods[goodsSellMobileFl][<?=$val['goodsNo']?>]">
                                    <option value="y" <?php if($val['goodsSellMobileFl'] == 'y') { echo "selected='selected'"; } ?> >판매함</option>
                                    <option value="n" <?php if($val['goodsSellMobileFl'] == 'n') { echo "selected='selected'"; } ?> >판매안함</option>
                                </select>
                            </td>
                            <td class="center lmenu colSoldOutFl" style="border-bottom:0px;">
                                <select class="form-control input-sm" name="goods[soldOutFl][<?=$val['goodsNo']?>]">
                                    <option value="n" <?php if($val['soldOutFl'] == 'n') { echo "selected='selected'"; } ?> >정상</option>
                                    <option value="y" <?php if($val['soldOutFl'] == 'y') { echo "selected='selected'"; } ?> >품절</option>
                                </select>
                            </td>
                            <td class="center colTotalStock" style="border-bottom:0px;">
                                <?= ($val['stockFl'] == 'n' ? '∞' : $val['totalStock']);?>
                            </td>
                        <?php } else { ?>
                            <td class="colGoodsNo" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsCd" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsImage" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsNm" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colTaxFreeFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colFixedPrice" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colCostPrice" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsPrice" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colCommission" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsModelNo" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colScmNo" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colBrandCd" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colMakerNm" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsDisplayFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsDisplayMobileFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsSellFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsSellMobileFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colSoldOutFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colTotalStock" style="border-top:0px;border-bottom:0px;" ></td>
                        <?php } ?>
                        <td class="center colOption">
                            <?=$optionValue; ?>
                        </td>
                        <td class="center colOptionViewFl">
                            <select class="form-control" name="option[optionViewFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>>
                                <option value="y" <?php if($val['optionViewFl'] == 'y') { echo "selected='selected'"; } ?> >노출함</option>
                                <option value="n" <?php if($val['optionViewFl'] == 'n') { echo "selected='selected'"; } ?> >노출안함</option>
                            </select>
                        </td>
                        <?php
                        if($optionType == 'y') {
                        ?>
                        <td class="colOptionSellFl"><select class="form-control" name="option[optionSellFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>><?php
                                foreach($stockReason as $key =>  $value){
                                    ?><option value="<?=$key?>" <?php if($val['optionSellFl'] == $key) { echo "selected='selected'"; } ?>><?=$value?></option><?php
                                }
                                ?></select></td>
                        <td class="colOptionDeliveryFl"><select class="form-control" name="option[deliveryFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>><?php
                                foreach($deliveryReason as $key =>  $value){
                                    ?><option value="<?=$key?>" <?php if($val['optionDeliveryFl'] == $key) { echo "selected='selected'"; } ?>><?=$value?></option><?php
                                }
                                ?></select></td>
                        <?php
                        } else {
                            ?>
                            <td class="colOptionSellFl"><select class="form-control" name="option[optionSellFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>>
                                    <option value="y" <?php if($val['optionSellFl'] == 'y') { echo "selected='selected'"; } ?> >정상</option>
                                    <option value="n" <?php if($val['optionSellFl'] == 'n') { echo "selected='selected'"; } ?> >품절</option>
                                    </select></td>
                            <?php
                        }
                        ?>
                        <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                        <!--
             <td class="colOptionStopFl"><div class="form-inline"><select class="form-control" name="option[sellStopFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>><option value="y" <?php if($val['sellStopFl'] == 'y') { echo "selected='selected'"; } ?>>사용함</option><option value="n" <?php if($val['sellStopFl'] == 'n') { echo "selected='selected'"; } ?>>사용안함</option></select> <input type="text" name="option[sellStopStock][<?=$val['goodsNo']?>][<?=$val['sno']?>]" value="<?=$val['sellStopStock']?>" class="form-control width-xs" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>></div></td>
             <td class="colOptionRequestFl"><div class="form-inline"><select class="form-control" name="option[confirmRequestFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>><option value="y" <?php if($val['confirmRequestFl'] == 'y') { echo "selected='selected'"; } ?>>사용함</option><option value="n" <?php if($val['confirmRequestFl'] == 'n') { echo "selected='selected'"; } ?>>사용안함</option></select> <input type="text" name="option[confirmRequestStock][<?=$val['goodsNo']?>][<?=$val['sno']?>]" value="<?=$val['confirmRequestStock']?>" class="form-control width-xs" <?php if($val['optionFl'] =='n') { echo "disabled = 'disabled'"; } ?>></div></td>
             -->
                        <td class="colStockCnt"><div class="form-inline"><select name="option[stockFl][<?=$val['goodsNo']?>][<?=$val['sno']?>]"  class="form-control"><option value="">선택</option><option value="p">추가</option><option value="m">차감</option><option value="c">변경</option></select>
                                <input type="hidden" name="option[stockFlOrg][<?=$val['goodsNo']?>][<?=$val['sno']?>]" value="<?=$val['stockFl']?>">
                                <input type="hidden" name="option[stockCntFix][<?=$val['goodsNo']?>][<?=$val['sno']?>]"  class="form-control width-2xs" value="<?=$val['stockCnt']?>">
                                <input type="text" name="option[stockCnt][<?=$val['goodsNo']?>][<?=$val['sno']?>]"  class="form-control width-2xs" value="<?=$val['stockCnt']?>"></div>
                        </td>
                        <td class="colOptionCostPrice"><?=number_format($val['optionCostPrice'])?></td>
                        <td class="colOptionPrice"><?=number_format($val['optionPrice'])?></td>
                        <?php if ($val['optionNo'] == 1) { ?>
                            <td class="colMileageFl"><?=($val['mileageFl'] == 'c') ? "통합설정" : "개별설정"; ?></td>
                            <td class="colGoodsDiscountFl"><?=$val['goodsDiscountFl'] == 'y' ? "사용함" : "사용안함"; ?></td>
                            <td class="colGoodsBenefit"><?=$val['goodsBenefitSetFl'] == 'y' ? "사용함" : "사용안함"; ?></td>
                            <td class="colDeliverySno"><?=$delivery[$val['deliverySno']]['description']?></td>
                            <td class="colRegDt"><?=$val['regDt']?></td>
                            <td class="colModDt"><?=$val['modDt']?></td>
                        <?php } else { ?>
                            <td class="colMileageFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsDiscountFl" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colGoodsBenefit" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colDeliverySno" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colRegDt" style="border-top:0px;border-bottom:0px;" ></td>
                            <td class="colModDt" style="border-top:0px;border-bottom:0px;" ></td>
                        <?php } ?>

                    </tr>
                    <?php
                    unset($arrDisplay);
                    $i++;
                }
            } else {
                ?>
                <tr><td class="no-data" colspan="13">검색된 정보가 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>


    </div>
    <div class="center"><?=$page->getPage();?></div>

    <div>

        <table class="table table-cols">
            <colgroup><col class="width-md" /><col /></colgroup>
            <tr>
                <th>조건설정</th>
                <td>
                    <div class="form-inline">
                        <label for="batchAll" class="checkbox-inline">
                            <input type="checkbox" id="batchAll" name="batchAll" value="y" />
                            검색된 상품 전체(<?=number_format($page->recode['total']);?>개 상품)를 수정합니다.
                        </label>
                        <p class="notice-danger mgt5">상품수가 많은 경우 비권장합니다. 가능하면 한 페이지씩 선택하여 수정하세요.</p>
                    </div>
                    <input type="hidden" name="termsFl" value="n" >
                    <table class="table-cols">
                        <tr>
                            <th class="center" colspan="2">노출상태</th>
                            <th class="center" colspan="2">판매상태</th>
                            <th class="width-sm center" rowspan="2">품절상태</th>
                            <th class="width-sm center" rowspan="2">옵션 노출상태</th>
                            <th class="width-sm center" rowspan="2">옵션 품절상태</th>
                            <?php
                            if($optionType == 'y') {
                                ?>
                                <th class="width-sm center colOptionDeliveryFl" rowspan="2">옵션 배송상태</th><?php
                            }
                            ?>
                            <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                            <!--<th class="width-sm center" rowspan="2">판매중지수량</th>
                            <th class="width-sm center" rowspan="2">확인요청수량</th>-->
                            <th class="width-xl center" rowspan="2">재고</th>
                        </tr>
                        <tr>
                            <th class="center">PC</th>
                            <th class="center">모바일</th>
                            <th class="center">PC</th>
                            <th class="center">모바일</th>
                        </tr>
                        <tr>
                            <td><select class="form-control input-sm" name="goodsDisplayFl" ><option value="">선택</option><option value="y">노출함</option><option value="n">노출안함</option></select></td>
                            <td><select class="form-control input-sm" name="goodsDisplayMobileFl" ><option value="">선택</option><option value="y">노출함</option><option value="n">노출안함</option></select></td>
                            <td><select class="form-control input-sm" name="goodsSellFl" ><option  value="">선택</option><option value="y">판매함</option><option value="n">판매안함</option></select></td>
                            <td><select class="form-control input-sm" name="goodsSellMobileFl" ><option  value="">선택</option><option value="y">판매함</option><option value="n">판매안함</option></select></td>
                            <td><select class="form-control input-sm" name="soldOutFl" ><option  value="">선택</option><option value="n">정상</option><option value="y">품절</option></select></td>
                            <td><select class="form-control input-sm" name="optionViewFl" ><option  value="">선택</option><option value="y">노출함</option><option value="n">노출안함</option></select></td>
                            <?php
                            if($optionType == 'y') {
                            ?>
                            <td><select class="form-control input-sm" name="optionSellFl">
                                    <option value="">선택</option><?php
                                    foreach ($stockReason as $key => $value) {
                                        ?>
                                        <option value="<?= $key ?>"><?= $value ?></option><?php
                                    }
                                    ?></select></td>
                            <td><select class="form-control" name="optionDeliveryFl">
                                    <option value="">선택</option><?php
                                    foreach ($deliveryReason as $key => $value) {
                                        ?>
                                        <option value="<?= $key ?>"><?= $value ?></option><?php
                                    }
                                    ?></select></td>
                            <?php
                            } else {
                            ?>
                            <td><select class="form-control " name="optionSellFl">
                                <option value="">선택</option>
                                <option value="y">정상</option>
                                <option value="n">품절</option>
                            </select></td>
                            <?php
                            }
                            ?>
                            <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
                            <!--<td><div class="form-inline"><select class="form-control "  name="optionSellStopFl"><option  value="">선택</option><option value="y">사용함</option><option value="n">사용안함</option></select> <input type="text" name="optionSellStopStock" class="form-control width-xs"></div></td>
                            <td><div class="form-inline"><select class="form-control "  name="optionConfirmRequestFl"><option  value="">선택</option><option value="y">사용함</option><option value="n">사용안함</option></select> <input type="text" name="optionConfirmRequestStock" class="form-control width-xs"></div></td>-->
                            <td><div class="form-inline"><select name="optionStockFl" class="form-control" ><option  value="">선택</option><option value="p">추가</option><option value="m">차감</option><option value="c">변경</option></select>
                                    <input type="text" name="optionStockCnt" class="form-control width-2xs"> <label class="checkbox-inline"><input type="checkbox" name="stockLimit" value="y">무한정 판매</label></div></td>
                        </tr>

                    </table>

                    <div class="notice-info">
                        재고는 "추가/차감/변경" 중 선택하여 수정 가능합니다.<br/>
                        "추가/차감"을 선택하고 저장하면, 현재의 상품 재고에 입력한 수량 만큼 "추가/차감"되고, "변경"을 선택하면 입력한 수량과 재고 수량이 동일하게 변경됩니다.<br/>
                        "무한정 판매"를 체크한 경우 입력한 재고 수량과 관계없이 무한정 판매 상품으로 변경됩니다.<br/>
                        수정 중 상품 주문으로 재고 수량에 변동이 생기면, 현재 재고의 수량과 관계없이 변동된 수량에서 "추가/차감"됩니다.<br/>
                    </div>

                </td>
            </tr>
        </table>


    </div>

</form>

<script type="text/javascript">
    <!--
    $(document).ready(function(){
        //$('#frmBatchStock').formProcess();




        $( "#batchSubmit" ).click(function() {

            var msg = "";

            var goodsDisplayFl = $('#frmBatchStock select[name="goodsDisplayFl"] option:selected').val();
            var goodsDisplayMobileFl = $('#frmBatchStock select[name="goodsDisplayMobileFl"] option:selected').val();
            var goodsSellFl = $('#frmBatchStock select[name="goodsSellFl"] option:selected').val();
            var goodsSellMobileFl = $('#frmBatchStock select[name="goodsSellMobileFl"] option:selected').val();
            var soldOutFl = $('#frmBatchStock select[name="soldOutFl"] option:selected').val();
            var optionViewFl = $('#frmBatchStock select[name="optionViewFl"] option:selected').val();
            var optionSellFl = $('#frmBatchStock select[name="optionSellFl"] option:selected').val();
            var optionDeliveryFl = $('#frmBatchStock select[name="optionDeliveryFl"] option:selected').val();
            if(optionDeliveryFl == undefined) optionDeliveryFl = "";
            //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
            //var optionSellStopFl = $('#frmBatchStock select[name="optionSellStopFl"] option:selected').val();
            //var optionSellStopStock = $('#frmBatchStock input[name="optionSellStopStock"]').val();
            //var optionConfirmRequestFl = $('#frmBatchStock select[name="optionConfirmRequestFl"] option:selected').val();
            //var optionConfirmRequestStock = $('#frmBatchStock input[name="optionConfirmRequestStock"]').val();
            var optionStockFl = $('#frmBatchStock select[name="optionStockFl"] option:selected').val();
            var optionStockCnt = $('#frmBatchStock input[name="optionStockCnt"]').val();
            var stockLimit = $('#frmBatchStock input[name="stockLimit"]').is(":checked");

            <!--현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.-->
            if(stockLimit == false &&  goodsDisplayFl == '' &&  goodsDisplayMobileFl == '' &&  goodsSellMobileFl == ''  && soldOutFl == '' &&  goodsSellFl == '' &&  optionViewFl == '' &&  optionSellFl == '' && optionDeliveryFl == '' && /*optionSellStopFl == '' &&  optionSellStopStock == '' && optionConfirmRequestFl == '' && optionConfirmRequestStock == ''&&*/ optionStockFl == '' &&  optionStockCnt == '')
            {
                if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                    $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                    return false;
                }

                $('#frmBatchStock input[name="termsFl"]').val('n');

                msg += "선택된 상품을 수정하시겠습니까?\n\n";
                msg += "[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.";
            }
            else
            {
                if ($('#batchAll:checked').length == 0) {
                    if ($('input[name="arrGoodsNo[]"]:checked').length == 0) {
                        $.warnUI('항목 체크', '선택된 항목이 없습니다.');
                        return false;
                    }

                    msg += '선택된 상품을 \n\n';
                } else {
                    msg += '검색된 전체 상품을 \n\n';
                }

                /*
                if(optionSellStopFl && optionSellStopStock =='')
                {
                    $.warnUI('항목 체크', '판매 중지 수량을 입력해주세요.');
                    return false;
                }
                if(optionConfirmRequestFl && optionConfirmRequestStock =='')
                {
                    $.warnUI('항목 체크', '확인 요청 수량을 입력해주세요.');
                    return false;
                }
                */
                if(optionStockFl && optionStockCnt =='')
                {
                    $.warnUI('항목 체크', '재고를 입력해주세요.');
                    return false;
                }


                $('#frmBatchStock input[name="termsFl"]').val('y');


                var goodsDisplayFlText = $('#frmBatchStock select[name="goodsDisplayFl"] option:selected').text();
                var goodsDisplayMobileFlText = $('#frmBatchStock select[name="goodsDisplayMobileFl"] option:selected').text();
                var goodsSellFlText = $('#frmBatchStock select[name="goodsSellFl"] option:selected').text();
                var goodsSellMobileFlText = $('#frmBatchStock select[name="goodsSellMobileFl"] option:selected').text();
                var soldOutFlText = $('#frmBatchStock select[name="soldOutFl"] option:selected').text();
                var optionViewFlText = $('#frmBatchStock select[name="optionViewFl"] option:selected').text();
                var optionSellFlText = $('#frmBatchStock select[name="optionSellFl"] option:selected').text();
                var optionDeliveryText = $('#frmBatchStock select[name="optionDeliveryFl"] option:selected').text();
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                /*var optionSellStopText = $('#frmBatchStock select[name="optionSellStopFl"] option:selected').text();
                var optionConfirmRequestText = $('#frmBatchStock select[name="optionConfirmRequestFl"] option:selected').text();*/
                var optionStockFlText = $('#frmBatchStock select[name="optionStockFl"] option:selected').text();

                if(goodsDisplayFl) msg += '상품 노출 상태[PC] : '+goodsDisplayFlText+' <br>';
                if(goodsDisplayMobileFl) msg += '상품 노출 상태[모바일] : '+goodsDisplayMobileFlText+' <br>';
                if(goodsSellFl) msg += '상품 판매 상태[PC] : '+goodsSellFlText+' <br>';
                if(goodsSellMobileFl) msg += '상품 판매 상태[모바일] : '+goodsSellMobileFlText+' <br>';
                if(soldOutFl) msg += '상품 판매 상태 : '+soldOutFlText+' <br>';
                if(optionViewFl) msg += '옵션 노출 상태 : '+optionViewFlText+' <br>';
                if(optionSellFl) msg += '옵션 판매 상태 : '+optionSellFlText+' <br>';
                if(optionDeliveryFl) msg += '옵션 배송 상태 : '+optionDeliveryText+' <br>';
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                /*
                if(optionSellStopFl) msg += '판매 중지 수량 : '+optionSellStopStock+'개 '+optionSellStopText+' <br>';
                if(optionConfirmRequestFl) msg += '확인 요청 수량 : '+optionConfirmRequestStock+'개 '+optionConfirmRequestText+' <br>';
                */
                if(optionStockFl) msg += '재고 : '+optionStockCnt+'개 '+optionStockFlText+' 상태 <br>';
                if(stockLimit) msg += '재고 : 무한정 상태 <br>';


                msg += '<br>으로 일괄적으로 수정하시겠습니까?<br><br>';
                msg += '[주의] 일괄적용 후에는 이전상태로 복원이 안되므로 신중하게 변경하시기 바랍니다.';
            }


            dialog_confirm(msg, function (result) {
                if (result) {
                    var msgAdd = ($('#frmBatchStock input[name="termsFl"]').val() == 'y') ? "<p class='notice-info'>옵션 정보만 수정한 경우, 상품수정일은 변경되지 않습니다.</p>" : "<p class='notice-info'>옵션 정보와 재고만 수정한 경우, 상품수정일은 변경되지 않습니다.</p>";
                    //상품수정일 변경 확인 팝업
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y' && $goodsConfig['goodsModDtFl'] == 'y') { ?>
                    dialog_confirm("상품수정일을 현재시간으로 변경하시겠습니까?" + msgAdd, function (result2) {
                        if (result2) {
                            $('input[name="modDtUse"]').val('y');
                        } else {
                            $('input[name="modDtUse"]').val('n');
                        }
                        $( "#frmBatchStock").submit();
                    }, '상품수정일 변경', {cancelLabel:'유지', 'confirmLabel':'변경'});
                    <?php } else { ?>
                    //상품 수정일 변경 범위설정 체크
                    <?php if ($goodsConfig['goodsModDtTypeAll'] == 'y') { ?>
                    $('input[name="modDtUse"]').val('y');
                    <?php } else { ?>
                    $('input[name="modDtUse"]').val('n');
                    <?php } ?>
                    $( "#frmBatchStock").submit();
                    <?php } ?>
                }
            });
        });



        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGoods').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGoods').submit();
        });
        setGridSetting();
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

        layer_add_info(typeStr,addParam);
    }



    function set_display ()
    {

        var cnt = $('input[name="arrGoodsNo[]"]:checked').length;

        if(cnt > 0 )
        {

            var setGoodsDisplayFl = $('#frmBatchStock select[id="setGoodsDisplayFl"] option:selected').val();
            var setGoodsSellFl = $('#frmBatchStock select[id="setGoodsSellFl"] option:selected').val();
            var setGoodsDisplayMobileFl = $('#frmBatchStock select[id="setGoodsDisplayMobileFl"] option:selected').val();
            var setGoodsSellMobileFl = $('#frmBatchStock select[id="setGoodsSellMobileFl"] option:selected').val();
            var setSoldOutFl = $('#frmBatchStock select[id="setSoldOutFl"] option:selected').val();
            var setOptionDisplayFl = $('#frmBatchStock select[id="setOptionDisplayFl"] option:selected').val();
            var setOptionSellFl = $('#frmBatchStock select[id="setOptionSellFl"] option:selected').val();
            var setOptionDeliveryFl = $('#frmBatchStock select[id="setOptionDeliveryFl"] option:selected').val();
            //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
            //var setSellStopFl = $('#frmBatchStock select[id="setSellStopFl"] option:selected').val();
            //var setSellStopStock = $('#frmBatchStock input[id="setSellStopStock"]').val();
            //var setConfirmRequestFl = $('#frmBatchStock select[id="setConfirmRequestFl"] option:selected').val();
            //var setConfirmRequestStock = $('#frmBatchStock input[id="setConfirmRequestStock"]').val();
            var setOptionStockFl = $('#frmBatchStock select[id="setOptionStockFl"] option:selected').val();

            var setOptionStockCnt = $('#frmBatchStock input[id="setOptionStockCnt"]').val();


            //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
            if(setGoodsDisplayFl!='' || setGoodsSellFl !='' || setGoodsDisplayMobileFl!='' || setGoodsSellMobileFl !=''  || setSoldOutFl !='' || setOptionDisplayFl !='' || setOptionSellFl !='' || setOptionDeliveryFl != '' || /*setSellStopFl != '' || setSellStopStock != '' || setConfirmRequestFl !=  '' || setConfirmRequestStock != '' || */setOptionStockFl !='' || setOptionStockCnt !='')
            {
                $('input[name="arrGoodsNo[]"]').each(function (i) {
                    if (this.checked) {

                        var tmp = $(this).val().split("_");

                        if(setGoodsDisplayFl) $('#frmBatchStock select[name="goods[goodsDisplayFl]['+tmp[0]+']"]').val(setGoodsDisplayFl);
                        if(setGoodsSellFl) $('#frmBatchStock select[name="goods[goodsSellFl]['+tmp[0]+']"]').val(setGoodsSellFl);
                        if(setGoodsDisplayMobileFl) $('#frmBatchStock select[name="goods[goodsDisplayMobileFl]['+tmp[0]+']"]').val(setGoodsDisplayMobileFl);
                        if(setGoodsSellMobileFl) $('#frmBatchStock select[name="goods[goodsSellMobileFl]['+tmp[0]+']"]').val(setGoodsSellMobileFl);
                        if(setSoldOutFl) $('#frmBatchStock select[name="goods[soldOutFl]['+tmp[0]+']"]').val(setSoldOutFl);
                        if(setOptionDisplayFl) $('#frmBatchStock select[name="option[optionViewFl]['+tmp[0]+']['+tmp[1]+']"]').val(setOptionDisplayFl);
                        if(setOptionSellFl) $('#frmBatchStock select[name="option[optionSellFl]['+tmp[0]+']['+tmp[1]+']"]').val(setOptionSellFl);
                        if(setOptionDeliveryFl) $('#frmBatchStock select[name="option[deliveryFl]['+tmp[0]+']['+tmp[1]+']"]').val(setOptionDeliveryFl);
                        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                        //if(setSellStopFl) $('#frmBatchStock select[name="option[sellStopFl]['+tmp[0]+']['+tmp[1]+']"]').val(setSellStopFl);
                        //if(setSellStopStock) $('#frmBatchStock input[name="option[sellStopStock]['+tmp[0]+']['+tmp[1]+']"]').val(setSellStopStock);
                        //if(setConfirmRequestFl) $('#frmBatchStock select[name="option[confirmRequestFl]['+tmp[0]+']['+tmp[1]+']"]').val(setConfirmRequestFl);
                        //if(setConfirmRequestStock) $('#frmBatchStock input[name="option[confirmRequestStock]['+tmp[0]+']['+tmp[1]+']"]').val(setConfirmRequestStock);
                        if(setOptionStockFl) $('#frmBatchStock select[name="option[stockFl]['+tmp[0]+']['+tmp[1]+']"]').val(setOptionStockFl);
                        if(setOptionStockCnt) $('#frmBatchStock input[name="option[stockCnt]['+tmp[0]+']['+tmp[1]+']"]').val(setOptionStockCnt);

                    }
                });

            }
            else
            {
                $.warnUI('항목 체크', "일괄적용변경할 항목을 입력해주세요.");
                return false;
            }



        }
        else
        {
            $.warnUI('항목 체크', "선택된 상품이 없습니다.");
            return false;
        }

    }

    /**
     * 조회항목 설정
     */
    function setGridSetting(cnt){
        if(cnt == 0 || cnt == undefined || cnt == '' || cnt == null){
            cnt = <?=count($data)?>;
        }
        //옵션 조회항목 설정에 따른 표기
        $('.colGoodsNo').hide();
        $('.colGoodsCd').hide();
        $('.colGoodsImage').hide();
        $('.colGoodsNm').hide();
        $('.colTaxFreeFl').hide();
        $('.colFixedPrice').hide();
        $('.colCostPrice').hide();
        $('.colGoodsPrice').hide();
        $('.colCommission').hide();
        $('.colGoodsModelNo').hide();
        $('.colScmNo').hide();
        $('.colBrandCd').hide();
        $('.colMakerNm').hide();
        $('.colGoodsDisplayFl').hide();
        $('.colGoodsDisplayMobileFl').hide();
        $('.colGoodsSellFl').hide();
        $('.colGoodsSellMobileFl').hide();
        $('.colSoldOutFl').hide();
        $('.colTotalStock').hide();
        $('.colOption').hide();
        $('.colOptionViewFl').hide();
        $('.colOptionSellFl').hide();
        $('.colOptionDeliveryFl').hide();
        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
        //$('.colOptionStopFl').hide();
        //$('.colOptionRequestFl').hide();
        $('.colStockCnt').hide();
        $('.colOptionCostPrice').hide();
        $('.colOptionPrice').hide();
        $('.colMileageFl').hide();
        $('.colGoodsDiscountFl').hide();
        $('.colGoodsBenefit').hide();
        $('.colDeliverySno').hide();
        $('.colRegDt').hide();
        $('.colModDt').hide();

        //보임/안보임 설정
        <?php
        foreach($goodsBatchStockGridConfigList as $key => $value){
        ?>$('.col<?=ucfirst($key)?>').show();
        <?php
        }
        ?>
        //순서 설정
            <?php
            $firstValue = true;
            foreach($goodsBatchStockGridConfigList as $key => $value){
            if($firstValue){
                $left = 'optionValueLast';
                $firstValue = false;
            }
            ?>for(i=0; i<cnt+2; i++){
            $('.col<?=ucfirst($key)?>:eq('+i+')').insertAfter($('.col<?=ucfirst($left)?>:eq('+i+')'));
        }
        <?php
        $left = $key;
        }
        ?>
    }



    //-->
</script>
