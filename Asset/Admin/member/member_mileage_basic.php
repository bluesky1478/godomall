<form id="frmMileageBasic" name="frmMileageBasic" action="member_ps.php" method="post">
    <input type="hidden" name="mode" value="mileage_basic"/>

    <div class="page-header js-affix">
        <h3><?= end($naviMenu->location); ?>
            <small>마일리지 기본 설정을 하실 수 있습니다.</small>
        </h3>
        <div class="btn-group">
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        마일리지 기본설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>사용설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="payUsableFl" value="y" <?php echo $checked['payUsableFl']['y'] ?>>
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="payUsableFl" value="n" <?php echo $checked['payUsableFl']['n'] ?>>
                    사용안함
                </label>
            </td>
        </tr>
        </tbody>
        <tbody id="mileageBasicUse">
        <tr>
            <th>쇼핑몰 노출 이름</th>
            <td>
                <input type="text" name="name" value="<?= $data['name']; ?>" class="form-control js-maxlength width-lg"/>
            </td>
        </tr>
        <tr>
            <th>쇼핑몰 노출 단위</th>
            <td>
                <input type="text" name="unit" value="<?= $data['unit']; ?>" class="form-control js-maxlength width-xs"/>
            </td>
        </tr>
        <tr>
            <th>마일리지 유효기간</th>
            <td>
                <div class="radio">
                    <label>
                        <input type="radio" name="expiryFl" value="n" <?= $checked['expiryFl']['n']; ?>>
                        유효기간 없음
                    </label>
                </div>
                <div class="radio form-inline js-validate-isexpiry">
                    <label>
                        <input type="radio" name="expiryFl" value="y" <?= $checked['expiryFl']['y']; ?>>
                        유효기간 있음
                    </label>
                    : 마일리지 적립일로부터
                    <input type="text" name="expiryDays" value="<?= $data['expiryDays']; ?>" maxlength="4" class="form-control js-number width-2xs" data-number="4,9999,365"/>
                    일 까지
                </div>
                <div class="checkbox form-inline js-validate-isexpiry">
                    유효기간 만료
                    <input type="text" name="expiryBeforeDays" value="<?= $data['expiryBeforeDays']; ?>" maxlength="3" class="form-control js-number width-2xs" data-number="3,365,30"/>
                    일 전 마일리지 소멸 자동안내
                    <label>
                        <input type="hidden" name="expirySms" value="<?= $smsMemberSend == 'y' ? '1' : ''; ?>">
                        <input type="checkbox" name="tmpExpirySms" value="1" <?= $smsMemberSend == 'y' ? 'checked="checked"' : ''; ?>>
                        SMS발송 <a href="../member/sms_auto.php#member" target="_blank" class="btn-link">상세설정</a>
                    </label>
                    <label>
                        <input type="hidden" name="expiryEmail" value="<?= $mailMemberSend == 'y' ? '1' : ''; ?>">
                        <input type="checkbox" name="tmpExpiryEmail" value="1" <?= $mailMemberSend == 'y' ? 'checked="checked"' : ''; ?>>
                        이메일발송 <a href="../member/mail_config_auto.php?category=point&type=deletemileage" target="_blank" class="btn-link">상세설정</a>
                    </label>
                </div>
                <span class="notice-info">※ SMS는 잔여포인트가 있어야 발송됩니다. (잔여포인트 :
                    <span class="text-darkred bold"><?= number_format($smsPoint, 1); ?></span>
                    ) <input type="button" class="btn btn-xs btn-gray mgl10 js-link-sms-charge" value="SMS포인트 충전하기" />
                </span>
            </td>
        </tr>
        <tr>
            <th>사용/적립시<br>구매금액 기준</th>
            <td>
                <div class="checkbox">
                    <label class="checkbox-inline">
                        <input type="checkbox" name="goodsPrice" value="1" disabled="disabled" checked="checked"/>
                        판매가
                    </label>
                    + (
                    <label class="checkbox-inline pdl0 mgl0">
                        <input type="checkbox" name="optionPrice" value="1" <?= $checked['optionPrice'][$data['optionPrice']]; ?> />
                        옵션가
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="addGoodsPrice" value="1" <?= $checked['addGoodsPrice'][$data['addGoodsPrice']]; ?> />
                        추가상품가
                    </label>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="textOptionPrice" value="1" <?= $checked['textOptionPrice'][$data['textOptionPrice']]; ?> />
                        텍스트옵션가
                    </label>
                    )
                </div>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>할인 금액 포함 여부</th>
                        <td>
                            <div class="checkbox">
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="goodsDcPrice" value="1" <?= $checked['goodsDcPrice'][$data['goodsDcPrice']]; ?> />
                                    상품 할인가 적용
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="memberDcPrice" value="1" <?= $checked['memberDcPrice'][$data['memberDcPrice']]; ?> />
                                    회원 할인가 적용
                                </label>
                                <label class="checkbox-inline">
                                    <input type="checkbox" name="couponDcPrice" value="1" <?= $checked['couponDcPrice'][$data['couponDcPrice']]; ?> />
                                    (상품+주문)쿠폰 할인가 적용
                                </label>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <th>정률(%) 사용/적립시<br>금액 끝자리 단위관리</th>
            <td>
                <div>
                    <span><?= gd_trunc_display('mileage'); ?></span>
                </div>
                <span class="notice-info">※ <a href="../policy/base_currency_unit.php" class="btn-link">기본설정>기본정책>금액/단위 기준설정</a>에서 설정한 기준에 따름
                </span>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 마일리지 기본 설정 저장
        $("#frmMileageBasic").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {},
            messages: {}
        });

        // 유효기간 없으면 인풋박스 비활성화 처리
        $('[name="expiryFl"]').click(function (e) {
            var $isEnable = $(this).val() != 'y';
            $('.js-validate-isexpiry input').each(function () {
                if ($(this).attr('type') != 'radio') {
                    $(this).prop('disabled', $isEnable);
                }
            });

            // SMS와 이메일 발송은 무조건 disabled (상세설정의 설정값을 가져와 처리)
            $('input[name=tmpExpirySms]:checkbox').prop('disabled', true);
            $('input[name=tmpExpiryEmail]:checkbox').prop('disabled', true);
        });
        $('[name="expiryFl"]:checked').trigger('click');

        <?php if ($data['payUsableFl'] == 'n') {?>
        display_toggle('mileageBasicUse', 'hide');
        <?php }?>

        $(':radio[name="payUsableFl"]').change(function () {
            if ($(this).val() == 'y') {
                display_toggle('mileageBasicUse', 'show');
            } else {
                display_toggle('mileageBasicUse', 'hide');
            }
        });
    });

    /**
     * 출력 여부
     * @param thisID 해당 ID
     * @param modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr) {
        if (modeStr == 'show') {
            $('#' + thisID).show();
            $('.notice-danger').hide();
        } else if (modeStr == 'hide') {
            $('#' + thisID).hide();
            $('.notice-danger').show();
        }
    }
    //-->
</script>
