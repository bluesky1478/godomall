<script type="text/javascript">
<!--
$(document).ready(function(){
    $("#frmCashReceipt").validate({
        dialog: false,
        submitHandler: function (form) {
            <?php if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] === 'y') { ?>
            // 인증 체크
            var certObj = {'b':'사업자번호','c':'휴대폰번호'};
            var certFl = $('input[name=\'certFl\']:checked').val();
            var certCnt = $('input[name=\'certNo['+certFl+'][]\']').length
            for (var i = 0; i < certCnt; i++) {
                if ($('input[name=\'certNo['+certFl+'][]\']').eq(i).val() == '') {
                    alert('인증 체크 : ' + certObj[certFl] + '를 입력해 주세요!');
                    return false;
                }
            }
            BootstrapDialog.show({
                title: '현금영수증 개별발급 요청',
                message: '작성된 내용으로 현금영수증 개별 발급 요청을 진행중입니다.',
                closable: true
            });
            form.target = 'ifrmProcess';
            form.submit();
            <?php } else {?>
            BootstrapDialog.show({
                title: '현금영수증 발급 불가',
                message: 'PG를 사용중이 아니거나, 현금영수증 미사용인경우 발급이 불가능합니다.',
                closable: true
            });
            return false;
            <?php }?>
        },
        rules: {
            'requestNm': 'required',
            'requestCellPhone[]': 'required',
            'requestEmail[]': 'required',
            'requestGoodsNm': 'required',
            'settlePrice': {
                required: true,
                min: 1
            },
            'supplyPrice': 'required'
        },
        messages: {
            'requestNm': {
                required: '신청자명을 입력해 주세요.'
            },
            'requestCellPhone[]': {
                required: '휴대폰 번호를 입력해 주세요.'
            },
            'requestEmail[]': {
                required: '이메일 주소를 입력해 주세요.'
            },
            'requestGoodsNm': {
                required: '상품명을 입력해 주세요.'
            },
            'settlePrice': {
                required: '발행액을 입력해 주세요.',
                min: '현금영주증 발행액은 1원 이상 입력해 주세요.'
            },
            'supplyPrice': {
                required: '공급가액을 입력해 주세요.'
            }
        }
    });

    // 유효성 검사
    $('.js-order-check').click(function (e) {
        var orderNo = $('input[name=\'checkOrderNo\']').val();
        if (orderNo == '') {
            alert('주문번호를 입력해 주세요.');
            return false;
        }
        if (orderNo.length != 16) {
            alert('주문번호는 16자리 숫자로 입력해 주세요.');
            return false;
        }

        var params = {
            mode: 'cash_receipt_order_check',
            orderNo: orderNo
        };
        $.post('cash_receipt_ps.php', params, function (data) {
            if (data.returnCode == 'success') {
                BootstrapDialog.show({
                    title: '유효성 검사 성공',
                    message: '[' + orderNo + '] 유효한 주문번호 입니다.<br/> 해당 주문의 주문자 정보를 신청자 정보로 등록하시겠습니까?',
                    closable: false,
                    buttons: [
                        {
                            id: 'btn-close',
                            label: '취소',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        },
                        {
                            id: 'btn-confirm',
                            label: '확인',
                            cssClass: 'btn-black',
                            action: function(dialogItself){
                                $('input[name=\'requestNm\']').val(data.requestNm);
                                $('input[name=\'requestGoodsNm\']').val(data.requestGoodsNm);
                                $('input[name=\'requestCellPhone\']').val(data.requestCellPhone);
                                $('input[name=\'requestEmail[]\']').eq(0).val(data.requestEmail[0]);
                                $('input[name=\'requestEmail[]\']').eq(1).val(data.requestEmail[1]);
                                dialogItself.close();

                                // 글자수 체크
                                $('.js-maxlength').trigger('input');
                            }
                        },
                    ]
                });
            } else {
                var errMsg = '';
                if (data.returnMsg =='ERROR_ORDERNO') {
                    errMsg = ' - 주문번호를 입력해 주세요.';
                }
                else if (data.returnMsg =='ERROR_DATA') {
                    errMsg = ' - 주문 정보가 없습니다. 주문번호를 확인해 주세요..';
                }
                else if (data.returnMsg =='ERROR_STATUS') {
                    errMsg = ' - 해당 주문단계에서는 현금영수증 발급이 불가능합니다.';
                }
                else if (data.returnMsg =='ERROR_SETTLE') {
                    errMsg = ' - 해당 주문의 결제수단으로는 현금영수증 발급이 불가능합니다.';
                }
                else if (data.returnMsg =='ERROR_RECEIPT') {
                    errMsg = ' - 이미 현금영수증 또는 세금계산서가 발급이 되었습니다.';
                }
                else {
                    errMsg = '';
                }
                BootstrapDialog.show({
                    title: '유효성 검사 실패',
                    message: '[' + orderNo + '] 유효하지 않은 주문번호이므로 현금영수증 발급이 불가능합니다.<br/>' + errMsg,
                    closable: false,
                    buttons: [
                        {
                            id: 'btn-close',
                            label: '닫기',
                            action: function(dialogItself){
                                $('input[name=\'checkOrderNo\']').val('');
                                dialogItself.close();
                            }
                        }
                    ]
                });
            }
        }, 'json');
    });

    // 이메일 도메인 대입
    $('#email_domain').each(function () {put_email_domain('requestEmail')});
    $('#email_domain').change(function () {put_email_domain('requestEmail')});

    // 숫자 체크
    $('input[name*=\'checkOrderNo\']').number_only();
    $('input[name*=\'requestCellPhone\']').number_only();
    $('input[name*=\'Price\']').number_only();

    tax_check('a');
    display_toggle('c');
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
        //var supplyPrice = Math.round( (settlePrice - servicePrice) / <?php echo (1 + ($tax['taxPercent'] / 100));?> );
        //var taxPrice = settlePrice - supplyPrice - servicePrice;
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

        // 공급가액
        //var supplyPrice = Math.round( (settlePrice - freePrice - servicePrice) / <?php echo (1 + ($tax['taxPercent'] / 100));?> );
        // 부가세
        //var taxPrice = settlePrice - supplyPrice - freePrice - servicePrice;

    // 면세
    } else {
        var supplyPrice = 0;
        var taxPrice = 0;
        var freePrice = settlePrice;
        var servicePrice = 0;
    }

    $('input[name=\'supplyPrice\']').val(supplyPrice)
    $('input[name=\'taxPrice\']').val(taxPrice)
    $('input[name=\'freePrice\']').val(freePrice)
    $('input[name=\'servicePrice\']').val(servicePrice)
}

/**
 * 출력 여부
 *
 * @param string arrayID 해당 ID
 * @param string modeStr 출력 여부 (show or hide)
 */
function display_toggle(thisID) {
    $('#certNo_b').hide();
    $('#certNo_c').hide();
    $('#certNo_'+thisID).show();

    // 사업자 번호는 지출 증빙용으로만
    if (thisID == 'b') {
        $('input[name=\'useFl\']').eq(0).prop('disabled',true);
        $('input[name=\'useFl\']').eq(1).prop('disabled',false);
        $('input[name=\'useFl\']').eq(1).prop('checked',true);
    } else {
        $('input[name=\'useFl\']').eq(0).prop('disabled',false);
        $('input[name=\'useFl\']').eq(1).prop('disabled',true);
        $('input[name=\'useFl\']').eq(0).prop('checked',true);
    }
}
//-->
</script>

<form id="frmCashReceipt" name="frmCashReceipt" action="cash_receipt_ps.php" method="post">
<input type="hidden" name="mode" value="cash_receipt_register" />
<input type="hidden" name="pgName" value="<?php echo $pgConf['pgName'];?>" />
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>현금영수증을 주문서가 아닌 개별적으로 발급요청이 가능합니다.</small>
        </h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./cash_receipt_list.php');" />
            <input type="submit" value="현금영수증 개별발급" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">
        현금영수증 정보
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col class="width-2xl"/><col class="width-sm" /><col /></colgroup>
        <tr>
            <th>PG 업체</th>
            <td class="form-inline">
                <?php
                if (empty($pgConf['pgName'])) {
                    echo '전자결제 서비스 업체와 계약이 필요합니다.';
                } else {
                    echo $pgConf['pgNm'];
                }
                ?>
            </td>
            <th>신청 가능 여부</th>
            <td class="form-inline">
                <?php
                if (empty($pgConf['pgName']) === false && $pgConf['cashReceiptFl'] == 'y') {
                    echo '<span class="text-blue">신청 가능</span>';
                } else {
                    if (empty($pgConf['pgName'])) {
                        echo '<span class="text-red">신청 불가</span>';
                    } else {
                        echo '<span class="text-red">신청 불가</span> - &quot;'.$pgConf['pgNm'].'&quot;에서 현금영수증 신청을 해주시기 바랍니다.';
                    }
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>주문번호</th>
            <td class="form-inline" colspan="3">
                <input type="text" name="checkOrderNo" value="" maxlength="16" class="form-control" />
                <button type="button" class="btn btn-sm btn-gray js-order-check">유효성 검사</button>
            </td>
        </tr>
        <tr>
            <th class="require">신청자명</th>
            <td class="form-inline"><input type="text" name="requestNm" value="" class="form-control" /></td>
            <th class="require">상품명</th>
            <td class="form-inline">
                <input type="text" name="requestGoodsNm" value="" maxlength="250" class="form-control js-maxlength" style="width:300px" />
            </td>
        </tr>
        <tr>
            <th class="require">휴대폰 번호</th>
            <td class="form-inline">
                <input type="text" name="requestCellPhone" value="" maxlength="12" class="form-control js-number-only width-md"/>
            </td>
            <th class="require">이메일</th>
            <td class="form-inline">
                <input type="text" name="requestEmail[]" value="" class="form-control width-sm"/> @
                <input type="text" id="requestEmail" name="requestEmail[]" value="" class="form-control width-sm"/>
                <?php echo gd_select_box('email_domain',null,$emailDomain,null,null,null);?>
            </td>
        </tr>
        <tr>
            <th class="require">과세/면세 여부</th>
            <td class="form-inline">
                <label class="radio-inline"><input type="radio" name="taxFl" value="a" onclick="tax_check(this.value);" checked="checked" /> 과세</label>
                <label class="radio-inline"><input type="radio" name="taxFl" value="n" onclick="tax_check(this.value);" /> 면세</label>
                <label class="radio-inline"><input type="radio" name="taxFl" value="y" onclick="tax_check(this.value);" />  복합과세</label>
            </td>
            <th class="require">신청 금액</th>
            <td class="form-inline">
                <div>
                    <span class="display-inline-block width-3xs">발행액</span> :
                    <input type="text" name="settlePrice" value="" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                </div>
                <div class="mgt10">
                    <span class="display-inline-block width-3xs">공급액</span> :
                    <input type="text" name="supplyPrice" value="" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                </div>
                <div class="mgt10">
                    <span class="display-inline-block width-3xs">부가세</span> :
                    <input type="text" name="taxPrice" value="" class="form-control width-sm" />
                </div>
                <div class="mgt10">
                    <span class="display-inline-block width-3xs">면세</span> :
                    <input type="text" name="freePrice" value="" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                </div>
                <div class="mgt10 display-none">
                    <span class="display-inline-block width-3xs">봉사료</span> :
                    <input type="text" name="servicePrice" value="0" class="form-control width-sm" onchange="autoPrice();" onblur="autoPrice();" onkeyup="autoPrice();" />
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">발행 용도</th>
            <td class="form-inline" colspan="3">
                <label class="radio-inline"><input type="radio" name="useFl" value="d" checked="checked" /> 소득공제용</label>
                <label class="radio-inline"><input type="radio" name="useFl" value="e" /> 지출증빙용</label>
            </td>
        </tr>
        <tr>
            <th class="require">인증 종류</th>
            <td class="form-inline" colspan="3">
                <div class="mgb10">
                    <label class="radio-inline"><input type="radio" name="certFl" value="c" onclick="display_toggle('c');" checked="checked" /> 휴대폰번호</label>
                    <label class="radio-inline"><input type="radio" name="certFl" value="b" onclick="display_toggle('b');" /> 사업자번호</label>
                </div>
                <div id="certNo_c">
                    휴대폰번호 :
                    <input type="text" name="certNo[c][]" value="" maxlength="12" class="form-control js-number-only width-md"/>
                </div>
                <div id="certNo_b">
                    사업자번호 :
                    <input type="text" name="certNo[b][]" maxlength="3" value="" class="form-control width-2xs" /> -
                    <input type="text" name="certNo[b][]" maxlength="2" value="" class="form-control width-3xs" /> -
                    <input type="text" name="certNo[b][]" maxlength="5" value="" class="form-control width-2xs" />
                </div>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        관리자 메모 <span class="notice-info">관리자 메모를 작성합니다.</span>
    </div>
    <table class="table table-cols">
        <colgroup><col class="width-sm" /><col/></colgroup>
        <tr>
            <th>관리자 메모</th>
            <td class="form-inline">
                <textarea name="adminMemo" cols="100" rows="3" class="form-control" style="width:915px;"></textarea>
            </td>
        </tr>
    </table>
</form>
