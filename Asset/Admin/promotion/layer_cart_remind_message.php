<form id="frmCartRemindMessage" name="frmCartRemindMessage" action="cart_remind_ps.php" method="post" class="content_form">
    <input type="hidden" name="mode" value="modifyCartRemindMessage"/>
    <input type="hidden" name="cartRemindNo" value="<?= $cartRemindData['cartRemindNo']; ?>"/>
    <input type="hidden" name="cartRemindSendType" value="<?= $cartRemindData['cartRemindSendType']; ?>"/>
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>발송내용입력</th>
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
    <div class="text-center">
        <button type="submit" class="btn btn-white">수정</button>
        <button type="button" class="btn btn-white js-layer-close">닫기</button>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        $("#frmCartRemindMessage").validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                mode: {
                    required: true,
                },
                cartRemindSendType: {
                    required: true,
                },
                cartRemindSendMessage: {
                    required: true,
                },
            },
            messages: {
                mode: {
                    required: '정상 접속이 아닙니다.(mode)',
                },
                cartRemindSendType: {
                    required: '장바구니 알림 발송형식이 없습니다. 다시 시도해주세요.',
                },
                cartRemindSendMessage: {
                    required: '장바구니 알림 발송내용을 입력해주세요.',
                },
            }
        });

        // 글자수 체크
        $('textarea[name=cartRemindSendMessage]').keyup(changeCartRemindContentsLength);
        changeCartRemindSendType();
        changeCartRemindContentsLength();
    });
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

