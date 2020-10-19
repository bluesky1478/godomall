<?php
/**
 * 주문상세 - 수령자 정보 수정
 * 상품준비중 리스트 - 묶음배송 수령자 정보 수정
 * 
 * @author <bumyul2000@godo.co.kr>
 */
?>
<table class="table table-cols">
    <?php if (empty($data['isDefaultMall']) === true) { ?>
        <input type="hidden" name="info[mallSno]" value="<?= $data['mallSno']; ?>"
    <?php } ?>
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <?php if ($data['deliveryVisit'] != 'y') { ?>
    <tr>
        <th>수령자명</th>
        <td>
            <input type="text" name="info[receiverName]" value="<?= gd_isset($data['receiverName']); ?>" class="form-control width-sm"/>
        </td>
    </tr>
    <tr>
        <th>전화번호</th>
        <td>
            <div class="form-inline">
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    <!-- 멀티몰 -->
                    <p><?= gd_select_box('receiverPhonePrefixCode', 'info[receiverPhonePrefixCode]', $countryPhone, null, $data['receiverPhonePrefixCode'], null, null, 'form-control'); ?></p>
                <?php } ?>

                <input type="text" name="info[receiverPhone]" value="<?= gd_isset(implode("",$data['receiverPhone'])); ?>" maxlength="12" class="form-control js-number-only width-md"/>
            </div>
        </td>
    </tr>
    <tr>
        <th>휴대폰번호</th>
        <td>
            <div class="form-inline">
                <?php if (empty($data['isDefaultMall']) === true) { ?>
                    <!-- 멀티몰 -->
                    <p><?= gd_select_box('receiverCellPhonePrefixCode', 'info[receiverCellPhonePrefixCode]', $countryPhone, null, $data['receiverCellPhonePrefixCode'], null, null, 'form-control '); ?></p>
                <?php } else {
                    if (gd_isset($safeNumberFl) && $data['receiverUseSafeNumberFl'] == 'y') { ?>
                    <input type="hidden" name="info[receiverOriginCellPhone]" value="<?= gd_isset(implode("",$data['receiverCellPhone'])); ?>">
                    <input type="hidden" name="info[receiverSafeNumber]" value="<?= gd_isset($data['receiverSafeNumber']); ?>">
                    <?php } ?>
                <?php } ?>

                <input type="text" name="info[receiverCellPhone]" value="<?= gd_isset(implode("",$data['receiverCellPhone'])); ?>" maxlength="12" class="form-control js-number-only width-md"/>
            </div>
        </td>
    </tr>
    <tr>
        <th>주소</th>
        <td>
            <?php if (empty($data['isDefaultMall']) === true) { ?>
                <!-- 멀티몰 -->
                <div class="form-inline">
                    <p><?= gd_select_box('receiverCountrycode', 'info[receiverCountrycode]', $countryAddress, null, $data['receiverCountryCode'], null, null, 'form-control'); ?></p>
                    <input type="text" name="info[receiverZonecode]" value="<?= gd_isset($data['receiverZonecode']); ?>" size="5" class="form-control"/>
                    <input type="hidden" name="info[receiverZipcode]" value="<?= gd_isset($data['receiverZipcode']); ?>"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[receiverCity]" value="<?= gd_isset($data['receiverCity']); ?>" class="form-control"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[receiverState]" value="<?= gd_isset($data['receiverState']); ?>" class="form-control"/>
                </div>
                <!-- 멀티몰 -->
            <?php } else { ?>
                <!-- 기준몰 -->
                <div class="form-inline">
                    <input type="text" name="info[receiverZonecode]" value="<?= gd_isset($data['receiverZonecode']); ?>" size="5" class="form-control"/>
                    <input type="hidden" name="info[receiverZipcode]" value="<?= gd_isset($data['receiverZipcode']); ?>"/>
                    <span id="inforeceiverZipcodeText" class="number">
                        <?php
                        if(strlen(trim($data['receiverZipcode'])) == 7){
                            echo '('.$data['receiverZipcode'].')';
                        }
                        ?>
                    </span>
                    <input type="button" onclick="postcode_search('info[receiverZonecode]', 'info[receiverAddress]', 'info[receiverZipcode]');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                </div>
                <!-- 기준몰 -->
            <?php } ?>

            <div class="mgt5">
                <input type="text" name="info[receiverAddress]" value="<?= gd_isset($data['receiverAddress']); ?>" class="form-control"/>
            </div>
            <div class="mgt5">
                <input type="text" name="info[receiverAddressSub]" value="<?= gd_isset($data['receiverAddressSub']); ?>" class="form-control"/>
            </div>
        </td>
    </tr>
    <tr>
        <th>배송 메세지</th>
        <td>
            <?php
            if($data['orderChannelFl'] == 'naverpay') {
                if(empty($naverPayMemo)===false){
                    foreach($naverPayMemo as $memo){
            ?>
                        <?=$memo['optionName']?><br />
                        <textarea disabled  rows="3" class="form-control"><?= gd_isset($memo['memo']); ?></textarea>
                        <br />
            <?php
                    }
                }
            }
            else {
            ?>
                <textarea name="info[orderMemo]" rows="3" class="form-control"><?= gd_isset($data['orderMemo']); ?></textarea>
            <?php }?>
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
                        <td><?php echo $data['visitDeliveryInfo']['address'][$data['infoSno']][0] . (count($data['visitDeliveryInfo']['address'][$data['infoSno']]) > 1 ? '외 ' . (count($data['visitDeliveryInfo']['address'][$data['infoSno']]) - 1) . '건 ' : ''); ?></td>
                    </tr>
                    <tr>
                        <th>방문자 정보</th>
                        <td>
                            <div class="form-inline">
                                <input type="text" name="info[visitName]" value="<?= gd_isset($data['visitName']); ?>" class="form-control"/>
                                <input type="text" name="info[visitPhone]" value="<?= gd_isset($data['visitPhone']); ?>" class="form-control"/>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>메모</th>
                        <td><textarea name="info[visitMemo]" rows="3" class="form-control"><?= gd_isset($data['visitMemo']); ?></textarea></td>
                    </tr>
                </table>
            </td>
        </tr>
    <?php } ?>
</table>
