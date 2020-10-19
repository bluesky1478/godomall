<script type="text/javascript">
    <!--
    $(document).ready(function(){

        document.title = "현금영수증 재발행";
        tax_check('a');

        // 현금영수증 발급신청
        $('.js-receipt-request').click(function(){
            $('input[name="mode"]').attr('value', 'cash_receipt_reissue_request');

            var formData = $('form[name="frmCashReceiptReissue"]').serialize();

            $.ajax({
                method: 'POST',
                cache: false,
                url: './cash_receipt_ps.php',
                data: formData,
            }).success(function (res){
                alert(res);
                $(document).on('click', '.bootstrap-dialog-footer-buttons>button', function(){
                    opener.location.reload(true);
                    window.close();
                });
                setTimeout(function(){
                    opener.location.reload(true);
                    window.close();
                }, 1500);

            }).error(function (e){
                alert(e.responseText);
                return false;
            });
        });

        // 현금영수증 즉시발급
        $('.js-receipt-immediately').click(function(){
            $('input[name="mode"]').prop('value', 'cash_receipt_reissue_immediately');

            var formData = $('form[name="frmCashReceiptReissue"]').serialize();

            dialog_confirm('변경된 금액으로 현금영수증을 발급하시겠습니까?', function (result) {
                if (result) {
                    $.ajax({
                        method: 'POST',
                        cache: false,
                        url: './cash_receipt_ps.php',
                        data: formData,
                    }).success(function (res) {
                        alert(res);
                        $(document).on('click', '.bootstrap-dialog-footer-buttons>button', function(){
                            opener.location.reload(true);
                            window.close();
                        });
                        setTimeout(function(){
                            opener.location.reload(true);
                            window.close();
                        }, 1500);
                    }).error(function (e) {
                        alert(e.responseText);
                        return false;
                    });
                }
            });
        });

    });

    /**
     * 과세/면세 여부에 따른 금액 설정
     *
     * @param string thisValue 해당값
     */
    function tax_check(thisValue) {
        // 복합과세인경우
        if( thisValue == 'y') {
            $('input[name=\'supplyPrice\']').prop('readonly',true);
            $('input[name=\'taxPrice\']').prop('readonly',true);
            $('input[name=\'freePrice\']').prop('readonly',false);
            $('input[name=\'servicePrice\']').prop('readonly',false);
        }
        // 과세나 면세인 경우
        else {
            $('input[name=\'supplyPrice\']').prop('readonly',true);
            $('input[name=\'taxPrice\']').prop('readonly',true);
            $('input[name=\'freePrice\']').prop('readonly',true);
            $('input[name=\'servicePrice\']').prop('readonly',false);
        }

        // 공급액,부가세 계산
        autoPrice();
    }

    /**
     * 공급액,부가세 계산
     */
    function autoPrice()
    {
        // 발행액
        var settlePrice = $('input[name=\'settlePrice\']').val();
        if (settlePrice == '') {
            settlePrice = 0;
        }
        settlePrice = parseInt(settlePrice);

        // 봉사료 (발행액의 최대 20%, 이상일 경우 세금신고대상)
        var servicePrice = $('input[name=\'servicePrice\']').val();
        if (servicePrice == '') {
            servicePrice = 0;
        }
        if ((settlePrice * 0.2) < servicePrice) {
            servicePrice = (settlePrice * 0.2);
        }
        servicePrice = parseInt(servicePrice);

        // 과세
        if ($('input[name=\'taxFl\']:checked').val() == 'a') {

            var taxPrice = Math.floor((settlePrice - servicePrice) * <?php echo ($tax['taxPercent']/($tax['taxPercent']+100));?>);
            var supplyPrice = settlePrice - servicePrice - taxPrice;
            var freePrice = 0;

            // 복합과세
        } else if ($('input[name=\'taxFl\']:checked').val() == 'y') {
            // 면세금액
            var freePrice = $('input[name=\'freePrice\']').val();
            if (freePrice == '') {
                freePrice = 0;
            }
            if (freePrice > (settlePrice - servicePrice)) {
                freePrice = (settlePrice - servicePrice);
            }
            freePrice = parseInt(freePrice);

            var taxPrice = Math.floor((settlePrice  - freePrice  - servicePrice) * <?php echo ($tax['taxPercent']/($tax['taxPercent']+100));?>);
            var supplyPrice = settlePrice - freePrice - servicePrice - taxPrice;

            // 면세
        } else {
            var supplyPrice = 0;
            var taxPrice = 0;
            var freePrice = settlePrice;
            var servicePrice = 0;
        }

        $('input[name=\'supplyPrice\']').val(supplyPrice);
        $('input[name=\'taxPrice\']').val(taxPrice);
        $('input[name=\'freePrice\']').val(freePrice);
        $('input[name=\'servicePrice\']').val(servicePrice);
    }
    //-->
</script>
<form id="frmCashReceiptReissue" name="frmCashReceiptReissue" action="cash_receipt_ps.php" method="post">
    <input type="hidden" name="mode" value="">
    <input type="hidden" name="popupMode" value="y">
    <input type="hidden" name="issueMode" value="a">
    <input type="hidden" name="orderNo" value="<?=$data['orderNo'];?>">
    <input type="hidden" name="requestNm" value="<?=$data['requestNm'];?>">
    <input type="hidden" name="requestGoodsNm" value="<?=$data['requestGoodsNm'];?>">
    <input type="hidden" name="requestEmail" value="<?=$data['requestEmail'];?>">
    <input type="hidden" name="requestCellPhone" value="<?=$data['requestCellPhone'];?>">
    <input type="hidden" name="useFl" value="<?=$data['useFl'];?>">
    <input type="hidden" name="certFl" value="<?=$data['certFl'];?>">
    <input type="hidden" name="certNo" value="<?=$certNo;?>">
    <input type="hidden" name="pgName" value="<?=$data['pgName'];?>">
    <input type="hidden" name="statusFl" value="<?=$data['statusFl'];?>">
    <input type="hidden" name="adminMemo" value="<?=$data['adminMemo'];?>">
    <div>
        <div class="table-title pdt10">현금영수증 정보</div>

        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-2xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>주문번호</th>
                <td class="form-inline"><?php echo $data['orderNo'];?></td>
            </tr>
            <tr>
                <th>신청자명</th>
                <td class="form-inline"><?php echo $data['requestNm'];?></td>
            </tr>
            <tr>
                <th>과세/면세여부</th>
                <td class="form-inline">
                    <label class="radio-inline"><input type="radio" name="taxFl" value="a" onclick="tax_check(this.value);" <?=gd_isset($checked['taxInfo']['t']); ?>/>과세</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="n" onclick="tax_check(this.value);" <?=gd_isset($checked['taxInfo']['f']); ?>/>면세</label>
                    <label class="radio-inline"><input type="radio" name="taxFl" value="y" onclick="tax_check(this.value);" <?=gd_isset($checked['taxInfo']['mix']); ?>/>복합과세</label>
                </td>
            </tr>
            <tr>
                <th>신청 금액</th>
                <td class="form-inline">
                    <div>
                        <span class="display-inline-block width-3xs">발행액</span> :
                        <input type="text" name="settlePrice" value="<?=gd_money_format($data['settlePrice'], false);?>" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                    </div>
                    <div class="mgt10">
                        <span class="display-inline-block width-3xs">공급액</span> :
                        <input type="text" name="supplyPrice" value="<?php echo gd_money_format($ordData['realTaxSupplyPrice'], false);?>" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                    </div>
                    <div class="mgt10">
                        <span class="display-inline-block width-3xs">부가세</span> :
                        <input type="text" name="taxPrice" value="<?php echo gd_money_format($ordData['realTaxVatPrice'], false);?>" class="form-control width-sm" />
                    </div>
                    <div class="mgt10">
                        <span class="display-inline-block width-3xs">면세</span> :
                        <input type="text" name="freePrice" value="<?php echo gd_money_format($ordData['realTaxFreePrice'], false);?>" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                    </div>
                    <div class="mgt10 display-none">
                        <span class="display-inline-block width-3xs">봉사료</span> :
                        <input type="text" name="servicePrice" value="<?php echo gd_money_format($ordData['servicePrice'], false);?>" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="flo-left pdb10 width100p">
            <div class="pull-left notice-danger notice-info width100p">클레임 처리에 의해 변경된 금액을 확인해주세요.</div>
            <div class="pull-left notice-info width100p">기존에 발급된 현금영수증은 자동으로 취소됩니다.<br>취소를 원하지 않을 경우 "현금영수증 개별발급"을 이용해 주세요.</div>
            <div class="pull-left notice-danger notice-info width100p">[즉시발급]은 현금영수증 발급신청 단계 없이 바로 발급완료되므로 주의하시기 바랍니다.</div>
        </div>
        <div class="center">
            <input type="button" value="발급신청" class="btn btn-m btn-gray js-receipt-request" />
            <input type="button" value="즉시발급" class="btn btn-m btn-red js-receipt-immediately" />
            <input type="button" value="취소" class="btn btn-m btn-white js-cancel" onclick="self.close();" />
        </div>
    </div>
</form>
