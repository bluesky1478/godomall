<div>
    <h5 class="table-title">제한조건 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <?php
        if ($couponData['couponApplyMemberGroup']) {
            ?>
            <tr>
                <th>발급 가능 회원등급</th>
                <td>
                    <?php
                    foreach ($couponData['couponApplyMemberGroup'] as $k => $v) {
                        ?>
                        <button type="button" class="btn btn-default" name="couponApplyMemberGroupName[]"><?= $couponData['couponApplyMemberGroup'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponApplyProductType'] == 'provider' && $couponData['couponApplyProvider']) {
            ?>
            <tr>
                <th>발급/사용 가능 특정 공급사</th>
                <td class="form-inline">
                    <?php
                    foreach ($couponData['couponApplyProvider'] as $k => $v) {
                        ?>
                        <button type="button" class="btn btn-default" name="couponApplyProviderName[]"><?= $couponData['couponApplyProvider'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponApplyProductType'] == 'category' && $couponData['couponApplyCategory']) {
            ?>
            <tr class="tr-apply-category tr-apply-use">
                <th>발급/사용 가능 특정 카테고리</th>
                <td class="form-inline">
                    <?php
                    foreach ($couponData['couponApplyCategory'] as $k => $v) {
                        ?>
                        <button type="button" class="btn btn-default" name="couponApplyCategoryName[]"><?= $couponData['couponApplyCategory'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponApplyProductType'] == 'brand' && $couponData['couponApplyBrand']) {
            ?>
            <tr class="tr-apply-brand tr-apply-use">
                <th>발급/사용 가능 특정 브랜드</th>
                <td class="form-inline">
                    <?php
                    foreach ($couponData['couponApplyBrand'] as $k => $v) { ?>
                        <button type="button" class="btn btn-default" name="couponApplyBrandName[]"><?= $couponData['couponApplyBrand'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponApplyProductType'] == 'goods' && $couponData['couponApplyGoods']) {
            ?>
            <tr class="tr-apply-goods tr-apply-use">
                <th>발급/사용 가능 특정 상품</th>
                <td class="form-inline">
                    <table class="table table-cols">
                        <thead>
                        <tr>
                            <th>이미지</th>
                            <th>상품명</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($couponData['couponApplyGoods'] as $key => $val) {
                            echo '<tr id="idGoods_' . $val['goodsNo'] . '">' . chr(10);
                            echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td>' . $val['goodsNm'] . '</td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                        ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponExceptProviderType'] == 'y' && $couponData['couponExceptProvider']) {
            ?>
            <tr class="tr-except-provider tr-except-use">
                <th>발급/사용 제외 특정 공급사</th>
                <td class="form-inline">
                    <?php
                    foreach ($couponData['couponExceptProvider'] as $k => $v) {
                        ?>
                        <button type="button" class="btn btn-default" name="couponExceptProviderName[]"><?= $couponData['couponExceptProvider'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponExceptCategoryType'] == 'y' && $couponData['couponExceptCategory']) {
            ?>
            <tr class="tr-except-category tr-except-use">
                <th>발급/사용 제외 특정 카테고리</th>
                <td class="form-inline">
                    <?php
                    foreach ($couponData['couponExceptCategory'] as $k => $v) {
                        ?>
                        <button type="button" class="btn btn-default" name="couponExceptCategoryName[]"><?= $couponData['couponExceptCategory'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponExceptBrandType'] == 'y' && $couponData['couponExceptBrand']) {
            ?>
            <tr class="tr-except-brand tr-except-use">
                <th>발급/사용 제외 특정 브랜드</th>
                <td class="form-inline">
                    <?php
                    foreach ($couponData['couponExceptBrand'] as $k => $v) {
                        ?>
                        <button type="button" class="btn btn-default" name="couponExceptBrandName[]"><?= $couponData['couponExceptBrand'][$k]['name'] ?></button>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponExceptGoodsType'] == 'y' && $couponData['couponExceptGoods']) {
            ?>
            <tr class="tr-except-goods tr-except-use">
                <th>발급/사용 제외 특정 상품</th>
                <td class="form-inline">
                    <table id="couponExceptGoods" class="table table-cols">
                        <thead>
                        <tr>
                            <th>이미지</th>
                            <th>상품명</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($couponData['couponExceptGoods'] as $key => $val) {
                            echo '<tr id="idExceptGoods_' . $val['goodsNo'] . '">' . chr(10);
                            echo '<td>' . gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank') . '</td>' . chr(10);
                            echo '<td>' . $val['goodsNm'] . '</td>' . chr(10);
                            echo '</tr>' . chr(10);
                        }
                        ?>
                        </tbody>
                    </table>
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponMinOrderPrice'] > 0) {
            ?>
            <tr>
                <th>최소 상품구매금액 제한</th>
                <td class="form-inline">
                    구매금액이 <?= gd_currency_symbol(); ?><?= gd_money_format($couponData['couponMinOrderPrice'], false); ?><?= gd_currency_string(); ?> 이상인 경우 결제 시 사용 가능
                </td>
            </tr>
            <?php
        }
        ?>
        <?php
        if ($couponData['couponUseType'] != 'gift' && $couponData['couponApplyDuplicateType']) {
            if ($couponData['couponApplyDuplicateType'] == 'y') {
                $couponApplyDuplicateType = '중복사용 가능';
            } else {
                $couponApplyDuplicateType = '중복사용 불가';
            }
            ?>
            <tr>
                <th>같은 유형의 쿠폰과 중복사용 여부</th>
                <td class="form-inline"><?= $couponApplyDuplicateType; ?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.js-close').click(function (e) {
            $('div.bootstrap-dialog-close-button').click();
        });
    });
    //-->
</script>
