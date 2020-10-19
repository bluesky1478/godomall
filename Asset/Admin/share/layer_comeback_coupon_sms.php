
<table class="table table-rows">
    <colgroup>
        <col style="width:20%" />
        <col style="width:80%" />
    </colgroup>
    <tbody>
    <tr>
        <th>발송 내용</th>
        <td>
            <div>
                <div style="text-align:justify;" class="width100p">
                    <span class="sms-type notice-info">SMS : 건당 1포인트 차감</span>
                    <span class="lms-type notice-danger display-none">LMS : 건당 <?php echo $lmsPoint; ?>포인트 차감</span>
                    <span style="float: right" id="smsStringCount"> Bytes</span>
                </div>
            </div>
            <div class="pdt5">
                <div class="pdr0">
                    <textarea name="smsContents" rows="13" class="smsContents form-control width100p"  readonly="readonly" data-close="true"><?= $data['smsContents'] ?></textarea>
                </div>
            </div>
        </td>
    </tr>
    </tbody>
</table>

<div class="text-center">
    <button type="submit" class="btn btn-black btn-lg js-close">확인</button>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        // 확인 클릭시
        $('.js-close').click(function(e){
            $('div.bootstrap-dialog-close-button').click();
        });

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

        /**
         * SMS 내용 길이 체크
         */
        function setContentsLength(contentsNm, countId) {
            var textarea = $('textarea[name=' + contentsNm + ']');
            var contentsText = textarea.val();
            var textLength = stringToByte(contentsText);
            if (textLength > <?php echo $smsStringLimit;?>) {
                if (textLength > <?php echo $lmsStringLimit;?>) {
                    if (textarea.data('close')) {
                        textarea.data('close', false);
                        BootstrapDialog.show({
                            message: 'LMS 전송은 최대 2,000 Byte 까지 가능합니다.',
                            onhidden: function () {
                                textarea.data('close', true);
                            }
                        });
                    }
                }
                $('#' + countId).css("color", "#FF0000");
                $('.sms-type').hide();
                $('.lms-type').show();
                $('input[name=sendFl]').val('lms');
            } else {
                $('#' + countId).css("color", "");
                $('.sms-type').show();
                $('.lms-type').hide();
                $('input[name=sendFl]').val('sms');
            }
            $('#' + countId).text(textLength + ' Bytes');
        }

        function setSendLength() {
            setContentsLength('smsContents', 'smsStringCount');
        }

        setSendLength();
    });
</script>

