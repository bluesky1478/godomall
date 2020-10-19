<style>
    .packet-disable-info-area {
        padding: 10px;
        height: 40px;
        width: 100%;
        border: 1px solid #aeaeae;
        background-color: #FFF5F5;
    }
    .packet-info-area {
        padding: 10px;
        width: 100%;
        border: 1px solid #aeaeae;
        background-color: #F6F6F6;
    }
</style>

<div class="page-header">
    <h3>묶음배송 처리</h3>
</div>

<div class="mgt15">
    <div class="mgb5">주문 <?=count($orderData)?>건</div>
    <form name="packetForm" id="packetForm" method="post" action="./order_change_ps.php" target="ifrmProcess">
        <input type="hidden" name="mode" value="set_packet" />
        <input type="hidden" name="orderNoStr" value="<?=$orderNoStr?>" />
        <table class="table table-rows">
            <thead>
            <tr>
                <th>주문번호</th>
                <th>상품주문번호</th>
                <th>수령자명</th>
                <th>연락처</th>
                <th>주소</th>
                <th>처리여부</th>
            </tr>
            </thead>

            <tbody>
            <?php
            if(count($orderData) > 0){
                foreach($orderData as $key => $data){
            ?>
                <tr>
                    <td class="center">
                        <div onclick="javascript:order_view_popup('<?=$data['orderNo']?>', '<?=$isProvider?>');" class="hand" style="color:#117efa;"><?=$data['orderNo']?></div>
                        <?php if ($packetAbleFl === false) { ?>
                            <div><button type="button" class="btn btn-black btn-sm js-packet-receiver-info" data-order-no="<?=$data['orderNo']?>"/>수령자 정보변경</button></div>
                        <?php } ?>
                    </td>
                    <td class="center">
                        <?php
                        if(count($data['orderGoodsNoArr']) > 0){
                            foreach($data['orderGoodsNoArr'] as $odKey => $orderGoodsSno){
                                echo "<div style='color:#117efa;'>" . $orderGoodsSno . "</div>";
                            }
                        }
                        ?>
                    </td>
                    <td class="center"><?=$data['receiverName']?></td>
                    <td class="center">
                        <div><?php echo (count($data['receiverPhone']) > 0) ? implode("-", $data['receiverPhone']) : '-'; ?></div>
                        <div><?php echo (count($data['receiverCellPhone']) > 0) ? implode("-", $data['receiverCellPhone']) : '-'; ?></div>
                    </td>
                    <td class="center">
                        <?php if (empty($data['isDefaultMall']) === true) { ?>
                            (<?=$data['receiverZonecode']?>) <?=$data['receiverAddressSub']?> <?=$data['receiverState']?> <?=$data['receiverCity']?> <?=$data['receiverAddress']?> <?=$data['receiverCountry']?>
                        <?php } else { ?>
                            (<?=$data['receiverZonecode']?>) <?=$data['receiverAddress']?> <?=$data['receiverAddressSub']?>
                        <?php } ?>
                    </td>
                    <td class="center">
                        <?php if ($packetAbleFl === true) { ?>
                            <span style="color:#117efa;">묶음배송 가능</span>
                        <?php } else { ?>
                            <span class="c-gdred">묶음배송 불가</span>
                        <?php } ?>
                    </td>
                </tr>
            <?php
                }
            }
            ?>
            </tbody>
        </table>

        <?php if ($packetAbleFl === false) { ?>
            <div class="mgt30">
                <div class="table-title">
                    <span class="gd-help-manual">묶음배송 불가 사유</span>
                </div>

                <div class="packet-disable-info-area">
                    <span class="c-gdred bold">[묶음배송 불가]</span> - 수령자 정보가 일치하지 않아 묶음배송이 불가합니다.
                    <span class="c-gdred">(※ 상이한 정보를 변경하여 묶음배송처리를 하시면 됩니다.)</span>
                </div>
            </div>
        <?php } ?>

        <div class="mgt30 packet-info-area">
            <div class="bold">도움말</div>
            <div>묶음배송시 수령자명, 연락처, 우편번호, 기본주소, 상세주소 정보가 동일해야 합니다.</div>
            <div>묶음배송시 배송비가 착불인 주문에 대해 확인 후 묶음배송처리 바랍니다.</div>
            <div>동일 주문에 대해 부분배송 처리한 것을 묶음배송처리시 다시 합쳐지지만 묶음배송으로 구분하지 않습니다.</div>
            <div>수령자정보가 일치하지 않을 경우 '수령자 정보변경'을 클릭하여 정보 수정 후 처리하시기 바랍니다.</div>
            <div>페이코, 네이버 페이 주문은 묶음배송 처리가 불가능합니다.</div>
            <div>네이버 페이 주문의 상세한 정보는 네이버 페이 센터에서 관리하실 것을 권장합니다. <a href="https://admin.pay.naver.com" target="_blank" style="color:#117efa !important;">[네이버 페이 센터 바로가기 ▶]</a></div>
        </div>

        <div class="mgt20 center">
            <?php if ($packetAbleFl === true) { ?><input type="button" value="처리하기" class="btn btn-gray js-packet-act" /><?php } ?>
            <input type="button" value="닫기" class="btn btn-white js-close" />
        </div>
    </form>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        //닫기
        $('.js-close').click(function(){
            self.close();
        });

        //처리하기
        $('.js-packet-act').click(function(){
            dialog_confirm('묶음배송을 정말 처리하시겠습니까?', function (result) {
                if (result) {
                    $("#packetForm").submit();
                }
            });
        });

        //수령자 정보 변경
        $('.js-packet-receiver-info').click(function(){
            var params = {
                orderNo: $(this).data('order-no')
            };

            $.post('layer_receiver_info.php', params, function (data) {
                layer_popup(data, '수령자 정보변경');
            });
        });
    });
    //-->
</script>
