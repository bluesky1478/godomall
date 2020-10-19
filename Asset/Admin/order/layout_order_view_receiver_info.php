<?php
/**
 * 주문상세 - 수령자 정보
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <?php if ($data['deliveryVisit'] != 'y') { ?>
    <tr>
        <th>수령자명</th>
        <td>
            <?= gd_isset($data['receiverName']); ?>
        </td>
    </tr>
    <tr>
        <th>전화번호</th>
        <td><?= ((empty($data['receiverPhone']) === false && count($data['receiverPhone']) > 1 && empty($data['isDefaultMall'])) ? '(' . gd_isset($data['receiverPhonePrefix']) . ') ' : '') . gd_isset(implode("-",$data['receiverPhone'])); ?></td>
    </tr>
    <tr>
        <th>휴대폰번호</th>
        <td>
            <?= ((empty($data['receiverCellPhone']) === false && count($data['receiverCellPhone']) > 1 && empty($data['isDefaultMall'])) ? '(' . gd_isset($data['receiverCellPhonePrefix']) . ') ' : '') . gd_isset(implode("-",$data['receiverCellPhone'])); ?>
            <?php if (empty($data['receiverCellPhone'][1]) === false && empty($data['isDefaultMall']) === false) { ?>
                <a class="btn btn-sm btn-gray" onclick="member_sms('','<?= urlencode($data['receiverName']); ?>','<?= implode('-', $data['receiverCellPhone']); ?>')">SMS 보내기</a>
            <?php } ?>
        </td>
    </tr>
    <?php if (gd_isset($safeNumberFl) && $data['receiverUseSafeNumberFl'] != 'n') { ?>
        <tr>
            <th>안심번호</th>
            <td>
                <?php if ($data['receiverUseSafeNumberFl'] == 'w') {
                        if ($safeNumberFl != 'off') { ?>
                            결제완료 시 발급됩니다.
                        <?php } else { ?>
                            <span class="text-danger">안심번호 서비스를 일시적으로 사용할 수 없습니다.</span>
                        <?php }
                    } else {
                        echo $data['receiverSafeNumber'];
                        if ($data['receiverUseSafeNumberFl'] == 'y') {
                            if (empty($data['receiverSafeNumber'])) {
                                if ($safeNumberFl != 'off') { ?>
                                    (발급대기)
                                    <input type="button" class="btn btn-sm btn-gray js-reset-safeNumber" value="수동발급"
                                           data-order-info-sno="<?= $data['infoSno'] ?>"
                                           data-receiver-cellphone="<?= implode("", $data['receiverCellPhone']); ?>"
                                           data-use-safenumber-fl="<?= $data['receiverUseSafeNumberFl']; ?>">
                                <?php } else { ?>
                                    <span class="text-danger">안심번호 서비스를 일시적으로 사용할 수 없습니다.</span>
                                <?php }
                            } else {
                                if ($safeNumberFl != 'off') {?>
                        <input type="button" class="btn btn-sm btn-gray js-cancel-safeNumber" value="사용해지" data-order-info-sno="<?=$data['infoSno']?>" data-receiver-cellphone="<?=implode("",$data['receiverCellPhone']);?>" data-safenumber="<?=$data['receiverSafeNumber'];?>">
                            <?php }
                            }
                        } else if ($data['receiverUseSafeNumberFl'] == 'c') { ?>
                        (사용해지)
                        <?php } else if ($data['receiverUseSafeNumberFl'] == 'e') { ?>
                        (기간만료)
                    <?php }
                    } ?>
            </td>
        </tr>
    <?php } ?>
    <tr>
        <th>주소</th>
        <td>
            <?php if (empty($data['isDefaultMall']) === true) { ?>
                <!-- 멀티몰 -->
                    <div>
                        [<?= gd_isset($data['receiverZonecode']); ?>]
                    </div>
                    <div>
                        <?= gd_isset($data['receiverAddressSub']); ?>,
                        <?= gd_isset($data['receiverAddress']); ?>,
                        <?= gd_isset($data['receiverState']); ?>,
                        <?= gd_isset($data['receiverCity']); ?>,
                        <?= gd_isset($data['receiverCountry']); ?>
                    </div>
                <!-- 멀티몰 -->
            <?php } else { ?>
                <!-- 기준몰 -->
                <div>
                    [<?= gd_isset($data['receiverZonecode']); ?>]
                    <?php if (strlen($data['receiverZipcode']) == 7) {
                        echo '(' . gd_isset($data['receiverZipcode']) . ')';
                    } ?>
                </div>
                <div><?= gd_isset($data['receiverAddress']); ?><br /><?= gd_isset($data['receiverAddressSub']); ?></div>
                <!-- 기준몰 -->
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th>배송 메세지</th>
        <td>
            <?php
            if($data['orderChannelFl'] == 'naverpay') {
                if(empty($naverPayMemo)===false) {
                    foreach($naverPayMemo as $memo) {
                        echo $memo['optionName']."<br />";
                        echo gd_isset($memo['memo'])."<br />";
                    }
                }
            }
            else {
                echo gd_isset(nl2br($data['orderMemo']));
            }
            ?>
        </td>
    </tr>
    <?php } ?>
    <?php if ($data['deliveryVisit'] == 'a' || $data['deliveryVisit'] == 'y') { ?>
        <tr>
            <th>방문수령 정보</th>
            <td>
                <table class="table table-cols">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tr>
                        <th>방문 수령지 주소</th>
                        <td><?php echo $data['visitDeliveryInfo']['address'][$data['infoSno']][0] . (count($data['visitDeliveryInfo']['address'][$data['infoSno']]) > 1 ? '외 ' . (count($data['visitDeliveryInfo']['address'][$data['infoSno']]) - 1) . '건 ' : ''); ?> <button type="button" class="btn btn-black btn-sm js-visit-address mgb5" data-order-no="<?php echo $data['orderNo']; ?>" data-goods-sno='<?php echo json_encode($data['visitDeliveryInfo']['goodsSno'][$data['infoSno']]);?>'>상품정보</button></td>
                    </tr>
                    <tr>
                        <th>방문자 정보</th>
                        <td><?php echo $data['visitName'] . ' / ' . $data['visitPhone']; ?></td>
                    </tr>
                    <tr>
                        <th>메모</th>
                        <td><?php echo $data['visitMemo']; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php } ?>
</table>
