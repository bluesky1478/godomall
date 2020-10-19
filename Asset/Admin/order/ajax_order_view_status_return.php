<!-- 복원 설정 정보 -->
<div id="returnHtml">
    <div class="table-title">
        <span class="gd-help-manual mgt30">추가 설정</span>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <?php if($statusMode !== 'e'){ ?>
        <tr>
            <th>재고 수량 복원 설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="returnStockFl" value="y" <?=$checked['returnStockFl']['y']?> /> 복원함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="returnStockFl" value="n" <?=$checked['returnStockFl']['n']?> /> 복원안함
                </label>
            </td>
        </tr>
        <?php } ?>
        <?php if (count($couponData) > 0) { ?>
        <tr>
            <th>쿠폰 복원 설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="returnCouponFl" value="n" <?=$checked['returnCouponFl']['n']?> /> 복원안함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="returnCouponFl" value="y" <?=$checked['returnCouponFl']['y']?> /> 복원함
                </label>
            </td>
        </tr>
        <tr class="display-none js-detail-display">
            <td colspan="2">
                <table class="table table-rows">
                    <thead>
                    <tr>
                        <th>쿠폰명</th>
                        <th>쿠폰종류</th>
                        <th>할인금액</th>
                        <th>적립금액</th>
                        <th>복원여부</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($couponData as $key => $val) { ?>
                        <tr>
                            <td><?= $val['couponNm']; ?></td>
                            <td><?= $val['couponUseType']; ?></td>
                            <td><?= $val['couponPrice']; ?></td>
                            <td><?= $val['couponMileage']; ?></td>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="returnCoupon[<?= $val['memberCouponNo']; ?>]" value="n" <?=$checked['returnCouponFl']['n']?> /> 복원안함
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="returnCoupon[<?= $val['memberCouponNo']; ?>]" value="y" <?=$checked['returnCouponFl']['y']?> /> 복원함
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php
        } else {
            if($statusMode === 'e'){
        ?>
            <tr>
                <th>쿠폰 복원 설정</th>
                <td>
                    복원할 쿠폰이 없습니다.
                </td>
            </tr>
        <?php
            }
        }
        ?>
        <?php if(count($giftData) > 0) { ?>
        <tr>
            <th>사은품 지급 설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="returnGiftFl" value="y" <?=$checked['returnGiftFl']['y']?> /> 지급함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="returnGiftFl" value="n" <?=$checked['returnGiftFl']['n']?> /> 지급안함
                </label>
            </td>
        </tr>
        <tr class="display-none js-detail-display">
            <td colspan="2">
                <table class="table table-rows">
                    <thead>
                    <tr>
                        <th>사은품조건명</th>
                        <th>사은품</th>
                        <th>수량</th>
                        <th>지급여부</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($giftData as $key => $val) { ?>
                        <tr>
                            <td><?= $val['presentTitle']; ?></td>
                            <td><?= html_entity_decode($val['imageUrl']); ?><?= $val['giftNm']; ?></td>
                            <td><?= $val['giveCnt']; ?></td>
                            <td>
                                <label class="radio-inline">
                                    <input type="radio" name="returnGift[<?= $val['sno']; ?>]" value="y" <?=$checked['returnGiftFl']['y']?> /> 지급함
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="returnGift[<?= $val['sno']; ?>]" value="n" <?=$checked['returnGiftFl']['n']?> /> 지급안함
                                </label>
                            </td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- 교환 추가 상품 혜택 지급 -->
    <?php if($statusMode === 'e' && $exchangeMileageApplyFl === 'y'){ ?>
    <div class="table-title">
        <span class="gd-help-manual mgt30">
            교환추가 상품 마일리지 혜택 지급 여부
            <span class="mgl10 notice-info">지급함 선택시 주문당시의 "주문 상태 설정 > 혜택 지급 시점" 에 따라 지급</span>
        </span>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>마일리지</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="supplyMileage" value="y" <?=$checked['supplyMileage']['y']?> /> 지급함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="supplyMileage" value="n" <?=$checked['supplyMileage']['n']?> /> 지급안함
                </label>
            </td>
        </tr>
        <?php if($exchangeMode === 'sameExchange'){ ?>
        <tr>
            <th>쿠폰</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="supplyCouponMileage" value="y" <?=$checked['supplyCouponMileage']['y']?> /> 지급함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="supplyCouponMileage" value="n" <?=$checked['supplyCouponMileage']['n']?> /> 지급안함
                </label>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
    <!-- 교환 추가 상품 혜택 지급 -->
</div>
<!-- 복원 설정 정보 -->
<script>
    $(document).ready(function () {
        $('input[name="returnCouponFl"]').change(function (e) {
            if ($('input[name="returnCouponFl"]:checked').val() == 'y') {
                $(this).closest('tr').next('tr').removeClass('display-none');
            } else {
                $(this).closest('tr').next('tr').addClass('display-none');
            }
        });
        $('input[name="returnGiftFl"]').change(function (e) {
            if ($('input[name="returnGiftFl"]:checked').val() == 'n') {
                $(this).closest('tr').next('tr').removeClass('display-none');
            } else {
                $(this).closest('tr').next('tr').addClass('display-none');
            }
        });
        $('input[name="returnCouponFl"]').trigger('change');
        $('input[name="returnGiftFl"]').trigger('change');
    });
</script>
