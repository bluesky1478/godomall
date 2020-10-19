<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
?>
<form action="" method="post">
    <div class="row">
        <div class="col-xs-12">
            <div class="pull-left">
                <span class="sms-type text-darkblue">SMS : 발송 건당 1포인트 차감</span>
                <span class="lms-type text-orange-red display-none">LMS : 발송 건당 <?php echo $lmsPoint; ?>포인트 차감</span>
            </div>

            <div class="pull-right">
                <input type="text" id="smsStringCount" value="0" readonly="readonly" class="form-control width-3xs"> /
                <span class="sms-type"><?php echo $smsStringLimit; ?></span><span class="lms-type display-none"><?php echo number_format($lmsStringLimit); ?></span> Bytes
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <textarea name="contents" id="contents" rows="8" class="form-control"><?= $data['contents']; ?></textarea>
        </div>
    </div>
    <div class="row">
        <div class="center modal-footer">
            <button type="submit" id="modify" class="btn btn-red">수정</button>
            <button type="button" id="cancel" class="btn btn-gray">취소</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    $(document).ready(function () {
        var $textarea = $('textarea[name=contents]');
        $textarea.on('keyup change', setSendLength).trigger('change');
    });
    function setSendLength() {
        setContentsLength('contents', 'smsStringCount');
    }
    /**
     * SMS 내용 길이 체크
     */
    function setContentsLength(contentsNm, countId) {
        var contentsText = $('textarea[name=' + contentsNm + ']').val();
        var textLength = stringToByte(contentsText);

        if (textLength > <?php echo $smsStringLimit;?>) {
            if (textLength > <?php echo $lmsStringLimit;?>) {
                alert('LMS 전송은 최대 2,000 Byte 까지 가능합니다.');
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
        $('#' + countId).val(textLength);
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
