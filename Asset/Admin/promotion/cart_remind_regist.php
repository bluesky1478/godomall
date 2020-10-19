<form id="frmCartRemind" name="frmCartRemind" action="cart_remind_ps.php" method="post" class="content_form" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $cartRemindData['mode']; ?>"/>
    <input type="hidden" name="cartRemindNo" value="<?= $cartRemindData['cartRemindNo']; ?>"/>
    <input type="hidden" name="cartRemindSendType" value="<?= $cartRemindData['cartRemindSendType']; ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./cart_remind_list.php');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <h5 class="table-title">장바구니 알림 조건 설정</h5>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">장바구니 알림명</th>
            <td>
                <input type="text" name="cartRemindNm" value="<?= $cartRemindData['cartRemindNm'] ?>" class="form-control width-xl" maxlength="30"/>
            </td>
        </tr>
        <tr>
            <th>발송유형</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="cartRemindType" value="manual" <?= $checked['cartRemindType']['manual']; ?> />수동발송
                </label>
                <label class="radio-inline">
                    <input type="radio" name="cartRemindType" value="auto" <?= $checked['cartRemindType']['auto']; ?> />자동발송
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">발송대상</th>
            <td class="cart-remind-manual form-inline">
                <select name="cartRemindPeriodManual" class="form-control width-lg">
                    <option value="" <?= $selected['cartRemindPeriod'][''] ?>>==선택==</option>
                    <option value="1" <?= $selected['cartRemindPeriod']['1'] ?>>최근1~3일전</option>
                    <option value="2" <?= $selected['cartRemindPeriod']['2'] ?>>최근3~5일전</option>
                    <option value="3" <?= $selected['cartRemindPeriod']['3'] ?>>최근5~7일전</option>
                    <option value="4" <?= $selected['cartRemindPeriod']['4'] ?>>최근3~7일전</option>
                    <option value="5" <?= $selected['cartRemindPeriod']['5'] ?>>직접선택</option>
                </select> 장바구니에 상품을 담은 회원 (발송일 기준)
                <div id="manual_direct_period" class="mgt10">
                    <input type="text" name="cartRemindPeriodStart" value="<?= $cartRemindData['cartRemindPeriodStart'] ?>" class="js-datepicker width-sm form-control" size="10"/> ~
                    <input type="text" name="cartRemindPeriodEnd" value="<?= $cartRemindData['cartRemindPeriodEnd'] ?>" class="js-datepicker width-sm form-control" size="10"/>
                </div>
                <p class="notice-info">장바구니에 담았던 상품을 구매 및 삭제한 회원들에게는 알림을 발송하지 않습니다.</p>
                <p class="notice-info">휴대폰번호 정보가 있고, SMS 수신 허용을 한 회원을 대상으로만 알림을 발송합니다.</p>
            </td>
            <td class="cart-remind-auto form-inline">
                <select name="cartRemindPeriodAuto" class="form-control width-lg">
                    <option value="" <?= $selected['cartRemindPeriod'][''] ?>>==선택==</option>
                    <option value="1" <?= $selected['cartRemindPeriod']['1'] ?>>1일전</option>
                    <option value="2" <?= $selected['cartRemindPeriod']['2'] ?>>2일전</option>
                    <option value="3" <?= $selected['cartRemindPeriod']['3'] ?>>3일전</option>
                    <option value="4" <?= $selected['cartRemindPeriod']['4'] ?>>4일전</option>
                    <option value="5" <?= $selected['cartRemindPeriod']['5'] ?>>5일전</option>
                    <option value="6" <?= $selected['cartRemindPeriod']['6'] ?>>6일전</option>
                    <option value="7" <?= $selected['cartRemindPeriod']['7'] ?>>7일전</option>
                </select> 장바구니에 상품을 담은 회원 (발송일 기준)
                <p class="notice-info">장바구니에 담았던 상품을 구매 및 삭제한 회원들에게는 알림을 발송하지 않습니다.</p>
                <p class="notice-info">휴대폰번호 정보가 있고, SMS 수신 허용을 한 회원을 대상으로만 알림을 발송합니다.</p>
                <p class="notice-info">알림을 설정한 다음 날부터 알림이 발송 됩니다.</p>
            </td>
        </tr>
        <tr class="cart-remind-auto">
            <th>발송시점</th>
            <td class="form-inline">
                <select name="cartRemindAutoSendTime" class="form-control width-lg">
                    <option value="" <?= $selected['cartRemindAutoSendTime'][''] ?>>==선택==</option>
                    <?php
                    for ($t = 9; $t < 21; $t++) {
                        ?>
                        <option value="<?= $t; ?>" <?= $selected['cartRemindAutoSendTime'][$t] ?>><?= $t; ?>시</option>
                        <?php
                    }
                    ?>
                </select>
                <p class="notice-info">영리 목적의 광고성 정보는 별도의 동의 없이 밤 9시~다음날 오전 8시까지 발송할 수 없습니다.</p>
                <!-- @todo 발송 대상 발송 시점은 스케줄러에 등록된 시간에 맞게 문구 변경 필요 -->
                <p class="notice-info">자동발송되는 장바구니 알림의 발송 대상은 새벽 3~4시에 추출되어 설정된 발송시점에 예약발송됩니다.</p>
                <p class="notice-info">발송대상 추출시간에 SMS잔여포인트가 부족한 경우 발송되지 않으며, 포인트 충전 시 다음 발송대상 추출시간 이후 발송 가능합니다.</p>
            </td>
        </tr>
        <tr>
            <th>발송제외 상품상태</th>
            <td>
                <label class="checkbox-inline">
                    <input type="checkbox" name="cartRemindGoodsSellFl" value="y" <?= $checked['cartRemindGoodsSellFl']['y'] ?> /> 판매안함
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="cartRemindGoodsDisplayFl" value="y" <?= $checked['cartRemindGoodsDisplayFl']['y'] ?> /> 노출안함
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="cartRemindGoodsSoldOutFl" value="y" <?= $checked['cartRemindGoodsSoldOutFl']['y'] ?> /> 품절
                </label>
            </td>
        </tr>
        <tr>
            <th>재고량에 따른 발송제한</th>
            <td class="form-inline">
                상품재고
                <input type="text" name="cartRemindGoodsStock" value="<?= $cartRemindData['cartRemindGoodsStock'] ?>" class="width-sm form-control">
                <select name="cartRemindGoodsStockSel" class="width-sm">
                    <option value="up" <?= $selected['cartRemindGoodsStockSel']['up'] ?>>이상</option>
                    <option value="down" <?= $selected['cartRemindGoodsStockSel']['down'] ?>>이하</option>
                </select>만 발송
                <p class="notice-info">빈칸으로 두면 재고량에 관계없이 알림 메시지가 발송됩니다.</p>
                <p class="notice-info">상품 재고 ~이하만 발송을 선택한 경우, 판매재고가 무한정판매로 설정된 상품은 장바구니 알림이 발송되지 않습니다.</p>
            </td>
        </tr>
        <tr>
            <th>발송 회원등급 선택</th>
            <td>
                <div class="form-inline">
                    <button type="button" class="btn btn-sm btn-gray js-member-group" class="btn btn-sm" title="발급 가능 회원등급을 선택해주세요.">회원등급선택</button>
                </div>
                <div id="cartRemindApplyMemberGroup" class="selected-btn-group <?= $cartRemindData['cartRemindApplyMemberGroup'] ? 'active' : '' ?>">
                    <h5>선택된 회원등급</h5>
                    <?php
                    if ($cartRemindData['cartRemindApplyMemberGroup']) {
                        foreach ($cartRemindData['cartRemindApplyMemberGroup'] as $k => $v) {
                            ?>
                            <span id="idmember_group_<?= $cartRemindData['cartRemindApplyMemberGroup'][$k]['no'] ?>" class="btn-group btn-group-xs">
                                <input type="hidden" name="cartRemindApplyMemberGroup[]" value="<?= $cartRemindData['cartRemindApplyMemberGroup'][$k]['no'] ?>"/>
                                <button type="button" class="btn btn-default" name="cartRemindApplyMemberGroupName[]"><?= $cartRemindData['cartRemindApplyMemberGroup'][$k]['name'] ?></button>
                                <button type="button" class="btn btn-danger" data-toggle="delete" data-target="#idmember_group_<?= $cartRemindData['cartRemindApplyMemberGroup'][$k]['no'] ?>">삭제</button>
                            </span>
                            <?php
                        }
                    }
                    ?>
                </div>
            </td>
        </tr>
        <tr>
            <th>지급 쿠폰 선택</th>
            <td class="form-inline">
                <select name="cartRemindCoupon" class="form-control width-lg">
                    <option value="" <?= $selected['cartRemindCoupon'][''] ?>>==선택==</option>
                    <?php
                    if (is_array($cartRemindCouponData)) {
                        foreach ($cartRemindCouponData as $couponKey => $couponVal) {
                            $couponDisabled = ($couponVal['couponType'] == 'f') ? 'disabled' : '';
                            if ($couponVal['couponType'] != 'f' || $selected['cartRemindCoupon'][$couponVal['couponNo']]) {
                                ?>
                                <option value="<?= $couponVal['couponNo']; ?>" <?= $selected['cartRemindCoupon'][$couponVal['couponNo']] ?> <?=$couponDisabled?>><?= $couponDisabled ? '(발급종료)':''; ?><?= $couponVal['couponNm']; ?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
                <button type="button" class="btn btn-sm btn-gray js-coupon-regist" class="btn btn-sm" title="신규쿠폰 등록">신규쿠폰 등록</button>
                <div class="btn-link js-coupon-detail"><a href="#">선택쿠폰 상세보기></a></div>
            </td>
        </tr>
        <tr>
            <th>
                발송내용입력
                <button type="button" class="btn btn-xs btn-gray mgt5 js-replace-code">치환코드 보기</button>
            </th>
            <td class="form-inline">
                <div>
                    <div class="display-inline-block width-lg">
                        <span class="cart-remind-lms notice-danger">LMS : 발송 건당 <?php echo $lmsPoint; ?>포인트 차감</span>
                        <span class="cart-remind-sms notice-info">SMS : 발송 건당 1포인트 차감</span>
                    </div>
                </div>
                <div class="form-inline mgt10">
                    <textarea name="cartRemindSendMessage" rows="8" class="smsContents form-control width-xl"><?= $cartRemindData['cartRemindSendMessage']; ?></textarea>
                    <div class="mgt5">
                        <input type="text" id="cartRemindSendMessageCount" value="0" readonly="readonly" class="form-control width-3xs"> /
                        <span class="cart-remind-lms"><?php echo number_format($lmsStringLimit); ?></span>
                        <span class="cart-remind-sms"><?php echo $smsStringLimit; ?></span> Bytes
                    </div>
                </div>
                <div class="mgt10">
                    <p class="notice-danger">정통망법에 따른 광고성 정보 전송 준수사항을 꼭 확인해주세요.
                        <a href="https://www.godo.co.kr/customer/board-view.gd?type=notice&idx=1237" target="_blank" class="btn-link">자세히보기></a>
                    </p>
                    <p class="notice-info">장바구니 링크 치환코드는 LMS인 경우에만 정상적으로 발송됩니다</p>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#frmCartRemind").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                cartRemindNm: {
                    required: true,
                },
                cartRemindType: {
                    required: true,
                },
                cartRemindPeriodManual: {
                    required: function (input) {
                        var required = false;
                        if ($('input:radio[name=cartRemindType]:checked').val() == 'manual') {
                            required = true;
                        }
                        return required;
                    }
                },
                cartRemindPeriodAuto: {
                    required: function (input) {
                        var required = false;
                        if ($('input:radio[name=cartRemindType]:checked').val() == 'auto') {
                            required = true;
                        }
                        return required;
                    }
                },
                cartRemindSendType: {
                    required: true,
                },
                cartRemindSendMessage: {
                    required: true,
                },
                cartReminderPeriodStart: {
                    required: function (input) {
                        var required = false;
                        if (($('input:radio[name=cartRemindType]:checked').val() == 'manual') && ($('select[name=cartRemindPeriod]:selected').val() == '5')) {
                            required = true;
                        }
                        return required;
                    }
                },
                cartReminderPeriodEnd: {
                    required: function (input) {
                        var required = false;
                        if (($('input:radio[name=cartRemindType]:checked').val() == 'manual') && ($('select[name=cartRemindPeriod]:selected').val() == '5')) {
                            required = true;
                        }
                        return required;
                    }
                },
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                cartRemindNm: {
                    required: '장바구니 알림명을 입력하세요.',
                },
                cartRemindType: {
                    required: '장바구니 알림 발송유형을 선택하세요.',
                },
                cartRemindPeriodManual: {
                    required: '장바구니 알림 발송대상을 선택하세요.',
                },
                cartRemindPeriodAuto: {
                    required: '장바구니 알림 발송대상을 선택하세요.',
                },
                cartRemindSendType: {
                    required: '장바구니 알림 발송형식이 없습니다. 다시 시도해주세요.',
                },
                cartRemindSendMessage: {
                    required: '장바구니 알림 발송내용을 입력해주세요.',
                },
                cartReminderPeriodStart: {
                    required: '발송대상 시작일을 입력하세요.',
                },
                cartReminderPeriodEnd: {
                    required: '발송대상 종료일을 입력하세요.',
                },
            }
        });
        $('.js-replace-code').click(function (e) {
            e.preventDefault();
            var type = 'cartRemind';
            var default_code = ['{rc_mallNm}'];
            replace_code_popup(type, default_code);
        });
        // 발송유형 선택 시
        $('input:radio[name="cartRemindType"]').click(function (e) {
            changeCartRemindType();
        });
        // 발송대상 선택 시
        $('select[name="cartRemindPeriodManual"]').click(function (e) {
            changeCartRemindManualDirectPeriod();
        });
        $('.js-member-group').click(function (e) {
            layerCartRemindApplyMemberGroup();
        });

        $('.js-coupon-regist').click(function (e) {
            window.open('./coupon_regist.php');
        });
        // 지급쿠폰 선택 시
        $('select[name="cartRemindCoupon"]').click(function (e) {
            displayCartRemindCouponLink();
        });
        $('.js-coupon-detail').click(function (e) {
            window.open('./coupon_regist.php?couponNo='+$('select[name="cartRemindCoupon"]').val());
        });

        // 글자수 체크
        $('textarea[name=cartRemindSendMessage]').keyup(changeCartRemindContentsLength);
        displayCartRemindCouponLink();
        changeCartRemindType();
        changeCartRemindSendType();
        changeCartRemindManualDirectPeriod();
        changeCartRemindContentsLength();
    });

    function displayCartRemindCouponLink() {
        if ($('select[name="cartRemindCoupon"]').val() > 0) {
            $('.js-coupon-detail').show();
        } else {
            $('.js-coupon-detail').hide();
        }
    }

    /**
     * SMS 내용 길이 체크
     */
    function changeCartRemindContentsLength() {
        var contentsText = $('textarea[name=cartRemindSendMessage]').val();
        var textLength = stringToByte(contentsText);

        if ($('input:hidden[name="cartRemindSendType"]').val() == 'sms' && textLength > <?= $smsStringLimit; ?>) {
            alert('SMS는 최대 90자까지만 가능합니다.');
        } else if ($('input:hidden[name="cartRemindSendType"]').val() == 'lms' && textLength > <?= $lmsStringLimit; ?>) {
            alert('LMS는 최대 2000자까지만 가능합니다.');
        }

        if (textLength > 90) {
            $('input:hidden[name="cartRemindSendType"]').val('lms');
            $('#cartRemindSendMessageCount').addClass("text-red");
            changeCartRemindSendType();
        } else if (textLength <= 90) {
            $('input:hidden[name="cartRemindSendType"]').val('sms');
            $('#cartRemindSendMessageCount').removeClass("text-red");
            changeCartRemindSendType();
        }
        $('#cartRemindSendMessageCount').val(textLength);
    }
    // 발송유형 따른 폼 변경
    function changeCartRemindType() {
        if ($('input:radio[name="cartRemindType"]:checked').val() == 'manual') {
            // 수동발송
            $('.cart-remind-manual').show();
            $('.cart-remind-manual select').removeAttr('disabled');
            $('.cart-remind-manual input').removeAttr('disabled');
            // 자동발송
            $('.cart-remind-auto').hide();
            $('.cart-remind-auto select').attr('disabled', 'disabled');
            $('.cart-remind-auto input').attr('disabled', 'disabled');
        } else if ($('input:radio[name="cartRemindType"]:checked').val() == 'auto') {
            // 수동발송
            $('.cart-remind-manual').hide();
            $('.cart-remind-manual select').attr('disabled', 'disabled');
            $('.cart-remind-manual input').attr('disabled', 'disabled');
            // 자동발송
            $('.cart-remind-auto').show();
            $('.cart-remind-auto select').removeAttr('disabled');
            $('.cart-remind-auto input').removeAttr('disabled');
        }
    }
    // 문자 전송 방식 따른 폼 변경
    function changeCartRemindSendType() {
        // lms발송
        if ($('input:hidden[name="cartRemindSendType"]').val() == 'lms') {
            $('.cart-remind-lms').show();
            $('.cart-remind-lms select').removeAttr('disabled');
            $('.cart-remind-lms input').removeAttr('disabled');

            $('.cart-remind-sms').hide();
            $('.cart-remind-sms select').attr('disabled', 'disabled');
            $('.cart-remind-sms input').attr('disabled', 'disabled');
            // sms발송
        } else if ($('input:hidden[name="cartRemindSendType"]').val() == 'sms') {
            $('.cart-remind-lms').hide();
            $('.cart-remind-lms select').attr('disabled', 'disabled');
            $('.cart-remind-lms input').attr('disabled', 'disabled');

            $('.cart-remind-sms').show();
            $('.cart-remind-sms select').removeAttr('disabled');
            $('.cart-remind-sms input').removeAttr('disabled');
        }
    }
    // 발급방식에 따른 폼 변경
    function changeCartRemindManualDirectPeriod() {
        if ($('input:radio[name="cartRemindType"]:checked').val() == 'manual') {
            if ($('select[name="cartRemindPeriodManual"]').val() == '5') {
                $('#manual_direct_period').show();
                $('#manual_direct_period input').removeAttr('disabled');
            } else {
                $('#manual_direct_period').hide();
                $('#manual_direct_period input').attr('disabled', 'disabled');
            }
        }
    }
    /**
     * 회원등급 Ajax layer
     *
     * @param string codeStr 타입
     * @param string modeStr 예외 여부
     */
    function layerCartRemindApplyMemberGroup() {
        var layerFormID = 'cartRemindForm';
        var parentFormID = 'cartRemindApplyMemberGroup';
        var dataFormID = 'idMemberGroup';
        var dataInputNm = 'cartRemindApplyMemberGroup';
        var layerTitle = '장바구니 알림 회원등급 ';
        var addParam = '';
        var fileStr = 'member_group';
        var mode = 'search';
        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
//            "callFunc": "",
        };
        $("#cartRemindApplyMemberGroup thead").show();
        $("#cartRemindApplyMemberGroup tfoot").show();

        layer_add_info(fileStr, addParam);
    }
    /**
     * 문자열 Byte 체크 (한글 2byte)
     */
    function stringToByte(str) {
        var length = 0;
        for (var i = 0; i < str.length; i++) {
            if (escape(str.charAt(i)).length >= 4)
                length += 2;
            else if (escape(str.charAt(i)) != "%0D")
                length++;
        }
        return length;
    }
</script>
