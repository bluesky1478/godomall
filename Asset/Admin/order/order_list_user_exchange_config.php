<form id="frmOrderListUserExchangeConfig" name="frmOrderListUserExchangeConfig" action="./order_ps.php" target="ifrmProcess" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="orderListUserExchangeConfig">
    <div class="page-header js-affix">
        <h3><?=end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./order_list_user_exchange.php');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>
    <div class="table-title">자동 환불 상세설정</div>
    <div id="depth-toggle-layer-categoryLink" >
        <table class="table table-cols">
            <colgroup>
                <col class="width-lg">
                <col class="width-sm">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>고객 환불요청 건<br />자동 환불 설정</th>
                    <td colspan="2">
                        <div>
                            <label class="checkbox-inline">
                                <input type="radio" name="orderListUserExchangeConfigUse" value="n" <?= gd_isset($checked['orderListUserExchangeConfigUse']['n']); ?> /> 사용안함
                            </label>
                            <label class="checkbox-inline">
                                <input type="radio" name="orderListUserExchangeConfigUse" value="y" <?= gd_isset($checked['orderListUserExchangeConfigUse']['y']); ?> /> 사용함
                            </label>
                        </div>
                        <div class="notice-info">자동 환불: 고객이 환불요청 시 상세설정 조건에 해당하는 주문에 한하여, 자동으로 '환불완료'로 처리하는 기능입니다.<br />
                            상세설정 조건과 맞지 않는 주문은 '환불완료'로 변경되지 않으며, 수동으로 환불처리를 진행할 수 있습니다.</div></td>
                </tr>
                <tr>
                    <th rowspan="5">상세설정</th>
                    <td>환불 형태</td>
                    <td>전체 환불 건만 가능</td>
                </tr>
                <tr>
                    <td>환불 수단</td>
                    <td>전체 PG환불</td>
                </tr>
                <tr>
                    <td>주문상태</td>
                    <td>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="orderListUserExchangeConfigP" value="y" <?= gd_isset($checked['orderListUserExchangeConfigP']['y']); ?> /> 입금 상태
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="orderListUserExchangeConfigG" value="y" <?= gd_isset($checked['orderListUserExchangeConfigG']['y']); ?> /> 상품 상태
                        </label>
                        <div class="notice-info">고객 환불 요청이 가능한 주문상태 내에서만 자동 환불 설정이 가능합니다.</div>
                        <div class="notice-info">자동 환불이 가능한 주문 상태 설정은 <a href="../policy/order_status.php" target="_blank" class="desc_text_blue btn-link">'기본설정>주문 정책>주문 상태 설정'</a>의 상태 그룹을 기준으로 설정이 가능합니다.
                            <ul>
                                <li>· 입금 상태 : <?php echo $orderStep['payment']; ?></li>
                                <li>· 상품 상태 : <?php echo $orderStep['goods']; ?></li>
                            </ul></div>
                    </td>
                </tr>
                <tr>
                    <td>주문 상품</td>
                    <td>
                        <label class="checkbox-inline">
                            <input type="radio" name="orderListUserExchangeConfigOrderStatus" value="unlimited" <?= gd_isset($checked['orderListUserExchangeConfigOrderStatus']['unlimited']); ?> /> 제한 없음
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" name="orderListUserExchangeConfigOrderStatus" value="noProvider" <?= gd_isset($checked['orderListUserExchangeConfigOrderStatus']['noProvider']); ?> /> 공급사 상품 포함 시 자동 환불 불가
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>결제수단</td>
                    <td>
                        PG결제 :
                        <label class="checkbox-inline">
                            <input type="checkbox" name="settleType[]" value="c" <?= gd_isset($checked['settleType']['c']); ?> /> 신용카드
                        </label>
                        <br />
                        간편결제 :
                        <label class="checkbox-inline">
                            <?php
                                if($paycoAutoCancelable != true){
                                    $paycoAutoCancelDisabled = 'disabled';
                                    unset($checked['settleType']['p']);
                                }
                                if($kakaoAutoCancelable != true){
                                    $kakaoAutoCancelDisabled = 'disabled';
                                    unset($checked['settleType']['k']);
                                }
                            ?>
                            <input type="checkbox" name="settleType[]" value="p" <?= gd_isset($checked['settleType']['p']); ?> <?=$paycoAutoCancelDisabled?> /> 페이코
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="settleType[]" value="k" <?= gd_isset($checked['settleType']['k']); ?> <?=$kakaoAutoCancelDisabled?> /> 카카오페이
                        </label>
                        <div class="notice-info">무통장 입금/계좌이체/가상계좌/에스크로/휴대폰결제/네이버페이 로 결제한 주문은 자동 환불이 불가합니다.</div>
                    </td>
                </tr>
                <tr>
                    <th rowspan="2">추가 설정</th>
                    <td>재고 수량 복원 설정</td>
                    <td>
                        <label class="checkbox-inline">
                            <input type="radio" name="orderListUserExchangeConfigReStock" value="n" <?= gd_isset($checked['orderListUserExchangeConfigReStock']['n']); ?> /> 복원안함
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" name="orderListUserExchangeConfigReStock" value="y" <?= gd_isset($checked['orderListUserExchangeConfigReStock']['y']); ?> /> 복원함
                        </label>
                    </td>
                </tr>
                <tr>
                    <td>쿠폰 복원 설정</td>
                    <td>
                        <label class="checkbox-inline">
                            <input type="radio" name="orderListUserExchangeConfigReCoupon" value="n" <?= gd_isset($checked['orderListUserExchangeConfigReCoupon']['n']); ?> /> 복원안함
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" name="orderListUserExchangeConfigReCoupon" value="y" <?= gd_isset($checked['orderListUserExchangeConfigReCoupon']['y']); ?> /> 복원함
                        </label>
                    </td>
                </tr>
            </tbody>
        </table>
</form>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmOrderListUserExchangeConfig').validate({
            submitHandler: function (form) {
                form.submit();
            },
        });
    });

    //-->
</script>
