<?php
/**
 * 주문상세 - 주문자 정보
 *
 * @author <bumyul2000@godo.co.kr>
 */
?>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tr <?php echo $styleDisplayNone; ?>>
        <th>구매자 IP</th>
        <td><span class="font-num"><?= gd_isset($data['orderIp']); ?></span></td>
    </tr>
    <tr>
        <th>주문자명</th>
        <td>
            <span class="text-primary"><?= gd_isset($data['orderName']); ?></span>
            <?php if (empty($memInfo) === true) { ?>
                <?php if (empty($data['memNo']) === true) { ?>
                    / <span class="text-primary">비회원</span>
                <?php } else { ?>
                    / <span class="text-primary">탈퇴회원</span>
                <?php } ?>
            <?php } else { ?>
                / <span class="text-primary"><?= $memInfo['memId'] ?></span>
                / <span class="text-primary"><?= $memInfo['groupNm'] ?></span>
                <?php if (!$isProvider) { ?>
                    <button type="button" class="btn btn-sm btn-gray js-layer-crm" data-member-no="<?= $data['memNo'] ?>">CRM 보기</button>
                <?php } ?>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th>전화번호</th>
        <td><span class="font-num"><?= ((empty($data['orderPhone']) === false && empty($data['isDefaultMall'])) ? '(' . gd_isset($data['orderPhonePrefix']) . ') ' : '') . gd_isset($data['orderPhone']); ?></span></td>
    </tr>
    <tr>
        <th>휴대폰번호</th>
        <td>
            <?php if (empty($data['orderCellPhone']) === false) { ?>
                <?= (empty($data['isDefaultMall']) ? '(' . gd_isset($data['orderCellPhonePrefix']) . ') ' : '') . gd_isset($data['orderCellPhone']) ?>
                <?php if (empty($data['isDefaultMall']) === false) { ?>
                    <a class="btn btn-sm btn-gray" onclick="member_sms('<?=gd_isset($data['memNo'])?>','<?= urlencode($data['orderName']); ?>','<?= $data['orderCellPhone']; ?>', '<?=$data['smsFl']?>')">SMS 보내기</a>
                <?php } ?>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th>이메일</th>
        <td><?= gd_isset($data['orderEmail']); ?></td>
    </tr>
    <?php if (empty($data['orderAddress']) === false) { ?>
        <?php if (empty($data['isDefaultMall']) === true) { ?>
            <tr>
                <th>주소</th>
                <td>
                    <div>
                        [<?= gd_isset($data['orderZonecode']); ?>]
                    </div>
                    <div>
                        <?= gd_isset($data['orderAddressSub']); ?>,
                        <?= gd_isset($data['orderAddress']); ?>,
                        <?= gd_isset($data['orderState']); ?>,
                        <?= gd_isset($data['orderCity']); ?>,
                        <?= gd_isset($data['orderCountry']); ?>
                    </div>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <th>주소</th>
                <td>
                    <div>
                        [<?= gd_isset($data['orderZonecode']); ?>]
                        <?php if (strlen($data['orderZipcode']) == 7) {
                            echo '(' . gd_isset($data['orderZipcode']) . ')';
                        } ?>
                    </div>
                    <div><?= gd_isset($data['orderAddress']); ?><br /><?= gd_isset($data['orderAddressSub']); ?></div>
                </td>
            </tr>
        <?php } ?>
    <?php } ?>
</table>
