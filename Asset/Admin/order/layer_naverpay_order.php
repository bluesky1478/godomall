<form name="formNaverpay" id="formNaverpay" action="order_naverpay_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="<?=$mode?>">
    <input type="hidden" name="orderNo" value="<?=$orderNo?>" >
    <input type="hidden" name="orderGoodsNos" >
    <?php include($includeFile)?>

    <div class="text-center">
        <button type="button" class="btn btn-white btn-lg js-layer-close">취소</button>
        <?php if($layerMode != 'view') {?>
            <input type="button" value="저장"  class="btn btn-lg btn-black js-btn-naverpay-save"/>
        <?php }?>
    </div>
</form>

<script type="text/javascript">
    $(function () {
        $('.js-btn-naverpay-save').bind('click',function () {
            if($('[name=date]').length>0){
                if(_.isEmpty($('[name=date]').val())){
                    alert('기한을 입력해주세요.');
                    return false;
                }
            }

            if($('#formNaverpay [name=extraData]').length>0 && $('[name=extraData]').is(':visible')){
                if(_.isEmpty($('#formNaverpay [name=extraData]').val())){
                    alert('금액을 입력해주세요.');
                    return false;
                }
            }
            else {
                $('#formNaverpay [name=extraData]').val('');
            }

            var snoList = [];
            $('[name^="bundle[statusCheck]"]:checked').each(function () {
                if($(this).is(':visible')){
                    snoList.push($(this).val());
                }
            });
            if(snoList.length<1){
                alert('주문을 선택해주세요.');
                return;
            }


            $('#formNaverpay [name=orderGoodsNos]').val(snoList.join(','));

            $('#formNaverpay').submit();
        })

        $('#formNaverpay textarea[name=contents]').keyup(function () {
            var maxLength = 500;
            var stringToByte = function (str) {
                return str.length;
            };

            var cutString = function (str, length) {
                return str.substring(0, length);
            };

            var contentsText = $(this).val();
            var countId = '.js-contents-length';
            var textLength = stringToByte(contentsText);
            if (textLength > maxLength) {
                var _length = cutString(contentsText, maxLength);
                $(this).val(_length);
                textLength = stringToByte($(this).val());
            }
            $(countId).text(textLength);
        })

        $('.js-datetimepicker').datetimepicker({
            locale: 'ko',
            format: 'YYYY-MM-DD',
            dayViewHeaderFormat: 'YYYY년 MM월',
            viewMode: 'days',
            ignoreReadonly: true
        });
    });
</script>
