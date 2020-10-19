<!-- //@formatter:off -->
<form name="frmSms" method="post" onsubmit="popupPay()">
    <input type="hidden" name="sno" value="<?php echo $shopSno?>">
    <input type="hidden" name="mode" value="sms">

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <?php if (gd_isset($guestCharge, false) !== true) { ?>
            <input type="button" value="SMS 충전 내역보기" class="btn btn-white js-sms-charge-list" />
            <?php } ?>
            <input type="submit" value="SMS 포인트 충전" class="btn btn-red" />
        </div>
    </div>

    <div class="table-title gd-help-manual">
        SMS 잔여 포인트
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>SMS 잔여 포인트</th>
            <td class="form-inline">
                <span class="number text-darkred bold"><?php echo number_format($smsPoint, 1)?></span> 포인트
                <?php if ($smsPoint === '0') { ?>
                <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 포인트 정보가 정상 출력됩니다.</div>
                <?php } ?>
            </td>
        </tr>
    </table>

    <div class="table-title gd-help-manual">
        충전 상품 선택
    </div>
    <table class="table table-rows mgb15">
        <colgroup>
            <col />
            <col />
            <col />
            <col />
            <col />
            <col />
        </colgroup>
        <thead>
        <tr>
            <th class="text-center">결제선택</th>
            <th class="text-center">발송 건/포인트</th>
            <th class="text-center">사용요금</th>
            <th class="text-center">SMS(건당 1포인트)</th>
            <th class="text-center">LMS(건당 <?php echo $lmsPoint;?>포인트)</th>
            <th class="text-center">알림톡(건당 <?php echo $kakaoPoint;?>포인트)</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $idx=0;
        if (empty($smsPriceList) === false) {
            foreach ($smsPriceList as $key => $val){
        ?>
        <tr height="30" class="text-center">
            <td><input type="radio" name="idx" value="<?php echo $idx++?>" <?php if ($idx == 1) echo ' checked="checked" '; ?>>
            <td><span class="font-num"><?php echo number_format($key)?></span> 포인트</td>
            <td><span class="font-num"><?php echo number_format($val['useFee'])?></span>원</td>
            <td><span class="font-num"><?php echo $val['unit']?></span>원/<span class="font-num">1</span>건</td>
            <td><span class="font-num"><?php echo ($val['unit'] * $lmsPoint)?></span>원/<span class="font-num">1</span>건</td>
            <td><span class="font-num"><?php echo round($val['unit'] * $kakaoPoint, 1)?></span>원/<span class="font-num">1</span>건</td>
        </tr>
        <?php
            }
        ?>
        <tr height="30" class="text-center">
            <td></td>
            <td><span class="font-num"><?php echo number_format($key)?></span> 포인트 이상</td>
            <td><span class="font-num">별도협의</td>
            <td><span class="font-num">-</td>
            <td><span class="font-num">-</td>
            <td><span class="font-num">-</td>
        </tr>
        <?php
        }
        ?>
        </tbody>
    </table>
    <div class="notice-danger mgb0 mgl15">충전한 SMS는 환불되지 않습니다. 위 사용요금과 단가는 <b>부가세 별도</b> 가격입니다.</div>
    <div class="notice-danger mgb0 mgl15 mgb15">1,000,000건 이상 대량으로 충전하실 경우 별도로 문의 부탁드립니다. (1688-7662)</div>
    <div class="linepd30"></div>
</form>
<!-- //@formatter:on -->
<script type="text/javascript">
    <!--
    // 충전 내역보기
    $('.js-sms-charge-list').click(function (e) {
        $.get('layer_sms_charge_list.php', '', function (data) {
            BootstrapDialog.show({
                title: 'SMS 충전 내역보기',
                size: BootstrapDialog.SIZE_WIDE,
                message: $(data),
                closable: true
            });
        });
    });

    function popupPay() {
        var fm = $("form[name=frmSms]");
        var popup_window = window.open("", "popupPay", "width=500,height=450");
        fm.attr("action", "https://www.godo.co.kr/userinterface/_godoConn/vaspay.php");
        fm.attr("target", "popupPay");
        var timer = setInterval(function () {
            if (popup_window.closed) {
                clearInterval(timer);
                window.location.location = '/';
            }
        }, 1000);
    }

    //-->
</script>
