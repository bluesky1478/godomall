<form id="frmCoupon" name="frmCoupon" action="coupon_ps.php" method="post" class="content_form">
    <input type="hidden" name="mode" value="<?= $mode; ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <h5 class="table-title gd-help-manual">쿠폰 기본설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>쿠폰사용설정</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="y" <?= $checked['couponUseType']['y']; ?> />사용함
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="couponUseType" value="n" <?= $checked['couponUseType']['n']; ?> />사용안함
                        <span class="notice-info mgl10">사용하지 않음으로 설정 시 발급된 쿠폰이 있더라도 쇼핑몰에서 쿠폰사용을 할 수 없으니 유의하시기 바랍니다.</span>
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>쿠폰/회원혜택<br>중복적용 여부</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="chooseCouponMemberUseType" value="all" <?= $checked['chooseCouponMemberUseType']['all']; ?> />쿠폰+회원 혜택 동시 사용
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="chooseCouponMemberUseType" value="coupon" <?= $checked['chooseCouponMemberUseType']['coupon']; ?> />쿠폰만 사용
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="chooseCouponMemberUseType" value="member" <?= $checked['chooseCouponMemberUseType']['member']; ?> />회원혜택만 사용
                    </label>
                </div>
                <div class="notice-info">
                    쿠폰(할인/적립)과 회원등급에 따른 혜택(할인/적립) 두 가지를 중복적용 또는 둘중 하나만 적용가능 하도록 설정하는 기능입니다.<br/>
                    쿠폰만 사용은 쿠폰 적용시 회원혜택(할인/적립)이 0원처리 되며, 쿠폰을 적용하지 않을 경우 회원혜택이 적용됩니다.<br/>
                    회원혜택만 사용은 쿠폰 기능을 사용함으로 하였더라도 쿠폰을 선택할 수 있는 버튼이 나타나지 않습니다.<br/>
                    회원등급별 혜택은 <a href="/member/member_group_list.php" target="_blank" class="btn-link-underline">[회원>회원 관리>회원 등급 관리]</a>에서 설정할 수 있습니다.
                </div>
            </td>
        </tr>
        <tr>
            <th>쿠폰가 노출 대상 설정</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="couponDisplayType" value="all" <?= $checked['couponDisplayType']['all']; ?> />비회원+회원
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="couponDisplayType" value="member" <?= $checked['couponDisplayType']['member']; ?> />회원만
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>금액 계산시<br>기준 구매금액</th>
            <td>
                판매가 + (
                <label class="checkbox-inline mgt-3">
                    <input type="checkbox" name="couponOptPriceType" value="y" <?= $checked['couponOptPriceType']['y'] ?> />옵션가
                </label>
                <label class="checkbox-inline mgt-3">
                    <input type="checkbox" name="couponAddPriceType" value="y" <?= $checked['couponAddPriceType']['y'] ?> />추가상품가
                </label>
                <label class="checkbox-inline mgt-3">
                    <input type="checkbox" name="couponTextPriceType" value="y" <?= $checked['couponTextPriceType']['y'] ?> />텍스트옵션가
                </label>
                )
            </td>
        </tr>
        <tr>
            <th>결제완료 전 취소 시<br>쿠폰복원설정</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="couponAutoRecoverType" value="y" <?= $checked['couponAutoRecoverType']['y'] ?> />복원함
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="couponAutoRecoverType" value="n" <?= $checked['couponAutoRecoverType']['n'] ?> />복원안함
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <th>상품쿠폰 주문서페이지<br>사용 여부 설정</th>
            <td>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="productCouponChangeLimitType" value="y" <?= $checked['productCouponChangeLimitType']['y'] ?> />제한함
                    </label>
                </div>
                <div class="radio">
                    <label class="radio-inline">
                        <input type="radio" name="productCouponChangeLimitType" value="n" <?= $checked['productCouponChangeLimitType']['n'] ?> />제한안함
                    </label>
                    <div class="notice-info">상품쿠폰을 주문서페이지에서 사용하도록 허용시 쇼핑몰 내 쿠폰적용 속도가 느려질 수 있습니다.</div>
                    <?php
                    if ($productCouponChangeLimitVersionFl == true) {
                    ?>
                    <div class="notice-info">
                        <span class="c-gdred">
                            해당 기능은 반드시 스킨패치를 적용하셔야 합니다. (미적용 시 일부 기능이 정상적이지 않을 수 있습니다.)
                        </span>
                    </div>
                    <?php
                    }
                    ?>
                </div>
            </td>
        </tr>
        <?php
        if ($offlineCouponUse) {
            ?>
            <tr>
                <th>페이퍼쿠폰<br>쇼핑몰 등록 설정</th>
                <td>
                    <div class="radio">
                        <label class="radio-inline">
                            <input type="radio" name="couponOfflineDisplayType" value="y" <?= $checked['couponOfflineDisplayType']['y'] ?> />등록가능
                        </label>
                    </div>
                    <div class="radio">
                        <label class="radio-inline">
                            <input type="radio" name="couponOfflineDisplayType" value="n" <?= $checked['couponOfflineDisplayType']['n'] ?> />등록불가
                        </label>
                        <span class="notice-info mgl10">등록불가 설정시 쇼핑몰에서 회원이 페이퍼쿠폰인증번호를 등록 할 수 없습니다.</span>
                    </div>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <h5 class="table-title gd-help-manual">자동발급 쿠폰 기본설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>생일 축하 쿠폰<br/>발급시점 설정</th>
            <td>
                <div class="radio form-inline">
                    <input type="radio" name="birthdayCouponReserveType" value="days" <?= $checked['birthdayCouponReserveType']['days']; ?> /> 생일
                    <?= gd_select_box(null, 'birthdayCouponReserveDays', $birthdayCouponReserveDaysList, null, $couponConfig['birthdayCouponReserveDays']); ?> 발급
                </div>
                <div class="radio form-inline">
                    <input type="radio" name="birthdayCouponReserveType" value="month" <?= $checked['birthdayCouponReserveType']['month']; ?> /> 월별 발급 : 생일
                    <?= gd_select_box(null, 'birthdayCouponReserveMonth', $birthdayCouponReserveMonthList, null, $couponConfig['birthdayCouponReserveMonth']); ?>
                    <input type="text" name="birthdayCouponReserveDate" value="<?= $couponConfig['birthdayCouponReserveDate'] ?>" class="form-control js-number-only width-3xs mgl5" maxlength="2" data-number="2,31,31">
                    일 발급
                </div>
                <div class="notice-info">
                    <a href="../promotion/coupon_regist.php" target="_blank" class="btn-link-underline">[프로모션 > 쿠폰 관리 > 쿠폰 등록]</a>에서 자동발급으로 등록한 "생일 축하 쿠폰"에 적용되는 설정입니다.
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#frmCoupon").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                couponDisplayType: {
                    required: true,
                },
                couponAutoRecoverType: {
                    required: true,
                },
                birthdayCouponReserveType: {
                    required: true,
                },
                birthdayCouponReserveDate: {
                    required: function () {
                        var required = false;
                        if ($('[name="birthdayCouponReserveType"]:checked').val() == 'month') {
                            required = true;
                        }
                        return required;
                    },
                    range: [1,31],
                    maxlength: 2
                }
            },
            messages: {
                birthdayCouponReserveType: {
                    required: '생일 축하 쿠폰 발급시점 설정을 선택하세요.'
                },
                birthdayCouponReserveDate: {
                    required: '생일 축하 쿠폰 월별 발급일을 입력하세요.',
                    range: '생일 축하 쿠폰 월별 발급일은 1~31 사이로 입력하세요.',
                    maxlength: '생일 축하 쿠폰 월별 발급일은 2자를 넘을 수 없습니다.'
                }
            }
        });
    });
</script>
