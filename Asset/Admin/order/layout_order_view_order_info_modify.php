<?php
/**
 * 주문상세 - 주문자 정보 수정
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
    <tr>
        <th>주문자명</th>
        <td>
            <input type="text" name="info[orderName]" value="<?= gd_isset($data['orderName']); ?>" class="form-control width-sm"/>
        </td>
    </tr>
    <tr>
        <th>전화번호</th>
        <td>
            <?php if (empty($data['isDefaultMall']) === true) { ?>
                <!-- 멀티몰 -->
                <p>
                    <?= gd_select_box('orderPhonePrefixCode', 'info[orderPhonePrefixCode]', $countryPhone, null, $data['orderPhonePrefixCode'], null, null, 'form-control'); ?>
                </p>
                <input type="text" name="info[orderPhone]" value="<?= gd_isset($data['orderPhone']); ?>" maxlength="20" class="form-control js-number-only width-md"/>

            <?php } else { ?>
                <!-- 기준몰 -->
                <input type="text" name="info[orderPhone]" value="<?= gd_isset($data['orderPhone']); ?>" maxlength="20" class="form-control js-number-only width-md"/>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th>휴대폰번호</th>
        <td>
            <?php if (empty($data['isDefaultMall']) === true) { ?>
                <!-- 멀티몰 -->
                <p>
                    <?= gd_select_box('orderCellPhonePrefixCode', 'info[orderCellPhonePrefixCode]', $countryPhone, null, $data['orderCellPhonePrefixCode'], null, null, 'form-control'); ?>
                </p>
                <input type="text" name="info[orderCellPhone]" value="<?= gd_isset($data['orderCellPhone']); ?>" maxlength="20" class="form-control js-number-only width-md"/>

            <?php } else { ?>
                <!-- 기준몰 -->
                <input type="text" name="info[orderCellPhone]" value="<?= gd_isset($data['orderCellPhone']); ?>" maxlength="20" class="form-control js-number-only width-md"/>
            <?php } ?>

        </td>
    </tr>
    <tr>
        <th>이메일</th>
        <td>
            <div class="form-inline">
                <input type="text" name="info[orderEmail]" value="<?= gd_isset($data['orderEmail']); ?>" class="form-control width-md" />

                <select id="emailDomain" class="form-control" style="width: 120px;">
                    <?php foreach($emailDomain as $key => $value){ ?>
                        <option value="<?=$key?>"><?=$value?></option>
                    <?php } ?>
                </select>
            </div>
        </td>
    </tr>
    <tr <?php echo $styleDisplayNone; ?>>
        <th>주소</th>
        <td>
            <?php if (empty($data['isDefaultMall']) === true) { ?>
                <!-- 멀티몰 -->
                <div class="form-inline">
                    <input type="text" name="info[orderZonecode]" value="<?= gd_isset($data['orderZonecode']); ?>" size="5" class="form-control"/>
                    <input type="hidden" name="info[orderZipcode]" value="<?= gd_isset($data['orderZipcode']); ?>"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[orderCity]" value="<?= gd_isset($data['orderCity']); ?>" class="form-control"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[orderState]" value="<?= gd_isset($data['orderState']); ?>" class="form-control"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[orderAddress]" value="<?= gd_isset($data['orderAddress']); ?>" class="form-control"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[orderAddressSub]" value="<?= gd_isset($data['orderAddressSub']); ?>" class="form-control"/>
                </div>
                <!-- 멀티몰 -->
            <?php } else { ?>
                <!-- 기준몰 -->
                <div class="form-inline">
                    <input type="text" name="info[orderZonecode]" value="<?= gd_isset($data['orderZonecode']); ?>" size="5" class="form-control"/>
                    <input type="hidden" name="info[orderZipcode]" value="<?= gd_isset($data['orderZipcode']); ?>"/>
                    <span id="infoorderZipcodeText" class="number">
                        <?php
                        if(strlen(trim($data['orderZipcode'])) == 7){
                            echo '('.$data['orderZipcode'].')';
                        }
                        ?>
                    </span>
                    <input type="button" onclick="postcode_search('info[orderZonecode]', 'info[orderAddress]', 'info[orderZipcode]');" value="우편번호찾기" class="btn btn-sm btn-gray"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[orderAddress]" value="<?= gd_isset($data['orderAddress']); ?>" class="form-control"/>
                </div>
                <div class="mgt5">
                    <input type="text" name="info[orderAddressSub]" value="<?= gd_isset($data['orderAddressSub']); ?>" class="form-control"/>
                </div>
                <!-- 기준몰 -->
            <?php } ?>
        </td>
    </tr>
</table>
