<div class="table-responsive">
    <table class="table table-rows table-fixed">
        <thead>
            <tr>
                <th class="width-2xs center"><input type="checkbox" class="js-checkall" data-target-name="arrGoodsNo[]"></th>
                <th class="width-2xs center">번호</th>
                <th class="width-xs center">상품코드</th>
                <th class="width-xs">이미지</th>
                <th class="width-lg center">상품명</th>
                <th class="width-xs center">공급사</th>
                <th class="width-xs center">브랜드</th>
                <th class="width-xs center">노출상태</th>
                <th class="width-xs center">판매상태</th>
                <th class="width-xs center">품절여부</th>
                <th class="width-xs center">재고</th>
                <th class="width-md center">판매가</th>
                <th class="width-md center">마일리지</th>
                <th class="width-xs center">상품할인</th>
            </tr>
        </thead>
        <tbody>
        <?php
            if (gd_isset($data) && count($data) > 0 ) {
                foreach ($data as $key => $val) {

                    $arrGoodsDisplay = ['y' => '노출함', 'n' => '노출안함'];
                    $arrGoodsSell = ['y' => '판매함', 'n' => '판매안함'];

                    if ($val['goodsDiscountFl'] == 'y') {
                        if ($val['goodsDiscountUnit'] == 'price') $goodsDiscount = gd_currency_symbol() . gd_money_format($val['goodsDiscount']) . gd_currency_string();
                        else $goodsDiscount = (int)$val['goodsDiscount'] . '%';
                    } else $goodsDiscount = '사용안함';

                    if ($val['mileageFl'] == 'g') {
                        if ($val['mileageGoodsUnit'] == 'mileage') $mileageGoods = gd_money_format($val['mileageGoods']) . $conf['mileageBasic']['unit'];
                        else $mileageGoods = (int)$val['mileageGoods'] . '%';
                    } else $mileageGoods = $conf['mileage']['goods'] . '%';

                    list($totalStock,$stockText) = gd_is_goods_state($val['stockFl'],$val['totalStock'],$val['soldOutFl']);
        ?>
            <tr>
                <td class="center number"><input type="checkbox" name="arrGoodsNo[]" value="<?=$val['goodsNo']; ?>"/></td>
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
                <td class="center"><?= $val['brandNm'] ?></td>
                <td class="center lmenu"><?=$arrGoodsDisplay[$val['goodsDisplayFl']]; ?></td>
                <td class="center lmenu"><?=$arrGoodsSell[$val['goodsSellFl']]; ?></td>
                <td class="center lmenu"><?=$stockText?></td>
                <td class="center lmenu"><?=$totalStock?></td>
                <td class="center number">
                    <div class="form-inline"><?=gd_currency_symbol(); ?><?=gd_money_format($val['goodsPrice']); ?><?=gd_currency_string(); ?></div>
                </td>
                <td class="center"><?= $mileageGoods ?></td>
                <td class="center"><?= $goodsDiscount ?></td>
            </tr>
        <?php  }
            } else {
        ?>
            <tr><td class="no-data" colspan="11">검색된 정보가 없습니다.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<div class="center"><?=$page->getPage('#');?></div>
