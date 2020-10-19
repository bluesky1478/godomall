<table class="table table-rows">
    <thead>
    <tr>
        <th><input type="checkbox" id="allCheck" value="y" class="js-checkall" data-target-name="memberCouponNo[]"/></th>
        <th>쿠폰명</th>
        <th>사용구분</th>
        <th>혜택구분</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($orderCoupon) === false) {
        $sumCouponPrice = 0;
        $sumCouponMileage = 0;
        foreach ($orderCoupon as $key => $val) {
            $sumCouponPrice = $sumCouponPrice + $val['couponPrice'];
            $sumCouponMileage = $sumCouponMileage + $val['couponMileage'];
            switch ($val['couponUseType']) {
                case 'product':
                    $couponUseType = '상품쿠폰';
                    break;
                case 'order':
                    $couponUseType = '주문쿠폰';
                    break;
                case 'delivery':
                    $couponUseType = '배송쿠폰';
                    break;
            }
            switch ($val['couponKindType']) {
                case 'sale':
                    $couponKindType = '상품할인';
                    break;
                case 'add':
                    $couponKindType = '마일리지적립';
                    break;
                case 'delivery':
                    $couponKindType = '배송비할인';
                    break;
            }
        ?>
        <tr class="text-center">
            <td><input type="checkbox" name="memberCouponNo[]" value="<?=$val['memberCouponNo']?>"></td>
            <td>
                <input type="hidden" id="couponNm_<?php echo $val['memberCouponNo'];?>" value="<?php echo gd_htmlspecialchars($val['couponNm']);?>" />
                <?= $val['couponNm']; ?>
            </td>
            <td><?= $couponUseType; ?></td>
            <td>
                <?= $couponKindType; ?><br>
                (<?= number_format($val['couponBenefit']); ?><?= $val['couponBenefitType'] == 'fix' ? gd_currency_string() : '%'; ?>)
            </td>
        </tr>
        <?php
        }
    } else {
    ?>
        <tr>
            <td colspan="5" class="no-data">복원 할 쿠폰이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="text-center">
    <button type="button" class="btn btn-white btn-lg js-layer-close">취소</button>
    <button type="submit" class="btn btn-black btn-lg js-select-restore-coupon">확인</button>
</div>

<script type="text/javascript">
    $(function(){
        $('.js-checkall').click(function () {
            if ($(this).data('target-name')) {
                $('input:checkbox[name*=\'' + $(this).data('target-name') + '\']:not(:disabled)').prop('checked', this.checked);
            } else {
                // 테이블에서만 사용 가능
                var name = $(this).closest('table').find('thead input:checkbox').data('target-name');
                if (!_.isUndefined(name)) {
                    $('input:checkbox[name*=\'' + name + '\']:not(:disabled)').prop('checked', this.checked);
                }
            }
        });

        // 쿠폰 저장하기 클릭시
        $('.js-select-restore-coupon').click(function(e){
            if ($('input[name="memberCouponNo[]"]:checked').length == 0) {
                alert('복원 할 쿠폰을 선택해 주세요.');
                return false;
            }

            var applyGoodsCnt = 0,
                chkGoodsCnt = 0,
                resultJson = {
                    info: []
                };

            $('input[name="memberCouponNo[]"]:checked').each(function() {
                var memberCouponNo = $(this).val();
                var couponNm = $('#couponNm_' + memberCouponNo).val();

                if ($('#restoreCoupon_' + memberCouponNo).length == 0) {
                    resultJson.info.push({
                        memberCouponNo: memberCouponNo,
                        couponNm: couponNm
                    });
                    applyGoodsCnt++;
                }
                chkGoodsCnt++;
            });

            if (applyGoodsCnt > 0) {
                displayTemplate(resultJson);

                if (applyGoodsCnt == chkGoodsCnt) {
                    alert(applyGoodsCnt+'개의 쿠폰이 추가 되었습니다.');
                } else {
                    alert('선택한 '+chkGoodsCnt+'개의 쿠폰 중 '+applyGoodsCnt+'개의 쿠폰이 추가 되었습니다.');
                }

                // 선택된 버튼 div 토글
                if (chkGoodsCnt > 0) {
                    $('#selectedCoupon').addClass('active');
                } else {
                    $('#selectedCoupon').removeClass('active');
                }

                $('div.bootstrap-dialog-close-button').click();
            } else {
                alert('동일한 쿠폰이 이미 존재합니다.');
                return false;
            }
        });
    });

    // 화면출력
    function displayTemplate(data) {
        $('#selectedCoupon').html('');

        if (data.info.length > 0 && !$('#' + data.parentFormID).children().is('h5')) {
            $('#selectedCoupon').prepend('<h5>선택된 쿠폰</h5>');
        }

        $.each(data.info, function (key,val) {
            var addHtml = '';
            var complied = _.template($('#restoreCouponTemplate').html());
            addHtml += complied({
                memberCouponNo: val.memberCouponNo,
                couponNm: val.couponNm,
            });
            $('#selectedCoupon').append(addHtml);
        });
    }
</script>
<script type="text/html" id="restoreCouponTemplate">
    <div id="restoreCoupon_<%=memberCouponNo%>" class="btn-group btn-group-xs">
        <input type="hidden" name="tmp[memberCouponNo][]" value="<%=memberCouponNo%>">
        <input type="hidden" name="tmp[couponNm][]" value="<%=couponNm%>">
        <span class="btn"><%=couponNm%></span>
        <button type="button" class="btn btn-icon-delete" data-toggle="delete" data-target="#restoreCoupon_<%=memberCouponNo%>">삭제</button>
    </div>
</script>
