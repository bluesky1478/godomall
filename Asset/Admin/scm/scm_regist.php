<style>
    .permission-item > td {
        width:100%;
    }

    .permission-item > td > table > td {
        width:50%;
        text-align:center;
    }
</style>
<form id="frmScm" name="frmScm" action="scm_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $getData['mode'] ?>"/>
    <input type="hidden" name="scmNo" value="<?= $getData['scmNo'] ?>"/>
    <input type="hidden" name="isProvider" value="n"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <?php if($popupMode != 'yes') {?>
                <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./scm_list.php');" />
            <?php }?>
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title">
        공급사 등록
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <?php
        if ($getData['mode'] == 'modifyScmModify') {
            ?>
            <tr>
                <th>
                    공급사 타입
                </th>
                <td colspan="3">
                    <?= $getData['scmKindName']; ?>
                </td>
            </tr>
            <?php
        } else {
            ?>
            <input type="hidden" name="scmKind" value="p" />
            <?php
        }
        ?>
        <tr>
            <th class="require">
                공급사명
            </th>
            <td>
                <input type="text" name="companyNm" value="<?= $getData['companyNm'] ?>" maxlength="20" class="form-control width-xl"/>
            </td>
            <th class="require">
                상태
            </th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="scmType" value="y" <?= $checked['scmType']['y'] ?> />운영
                </label>
                <label class="radio-inline">
                    <input type="radio" name="scmType" value="x" <?= $checked['scmType']['x'] ?> <?= $getData['scmTypeReadOnly'] ?> />탈퇴
                </label>
            </td>
        </tr>
        <tr>
            <th class="require">
                닉네임
            </th>
            <td class="form-inline">
                <?php
                if ($getData['mode'] == 'modifyScmModify') {
                    ?>
                    <div><?= $getData['managerNickNm'] ?></div>
                    운영자 닉네임 변경은 <a href="../policy/manage_list.php" class="btn-link">운영정책 > 관리 정책 > 운영자관리</a>에서 할 수 있습니다.
                    <?php
                } else {
                    ?>
                    <input type="text" name="managerNickNm" value="" maxlength="20" class="form-control width-sm"/>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="scmSameNick" value="Y"/>공급사명과 동일
                    </label>
                    <?php
                }
                ?>
            </td>
            <th>
                이미지 표시
            </th>
            <td class="form-inline">
                <?php
                if ($getData['dispImage']) {
                    echo '<div><img src="' . $getData['dispImage'] . '" width="50"/>';
                    echo '<label title="이미지 삭제시 체크해 주세요!" class="radio-inline"><input type="checkbox" name="isImageDelete" value="y"/> 삭제</label><div>';
                }
                if ($getData['mode'] == 'modifyScmModify') {
                    echo '운영자 이미지 변경은 <a href="../policy/manage_list.php" class="btn-link">운영정책 > 관리 정책 > 운영자관리</a>에서 할 수 있습니다.';
                } else {
                    echo '<input type="file" name="scmImage" class="form-control"/>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th class="require">
                공급사 아이디
            </th>
            <td class="form-inline">
                <?php
                if ($getData['mode'] == 'modifyScmModify') {
                    echo $getData['managerId'];
                } else {
                    ?>
                    <input type="text" name="managerId" value="" maxlength="<?= $policy['memId']['maxlen'] ?>" class="form-control width-sm"/>
                    <input type="hidden" name="managerDuplicateId" value=""/>
                    <button type="button" id="overlap_managerId" class="btn btn-gray btn-sm">중복확인</button>
                <?php } ?>
            </td>
            <th class="require">
                공급사비밀번호
            </th>
            <td>
                <?php
                if ($getData['mode'] == 'modifyScmModify') {
                    echo '비밀번호 변경은 <a href="../policy/manage_list.php" class="btn-link">운영정책 > 관리 정책 > 운영자관리</a>에서 할 수 있습니다.';
                } else {
                    ?>
                    <input type="password" name="managerPw" value="" maxlength="16" class="form-control width-sm"/>
                <?php } ?>
                <div class="text-blue">
                    * 영문,숫자,특수문자 중 2가지 조합 : 10자 이상
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">
                판매수수료
            </th>
            <td id="scmCommission" class="form-inline">
                <table id="scmCommissionTable"class="table-cols">
                    <tr>
                        <th class="">기본</th>
                        <td>
                            <div>
                                <input type="text" name="scmCommission" value="<?= $getData['scmCommission'] ?>" class="scmCommission form-control width-sm" <?= $getData['scmCommissionInput'] ?>/>%
                                <button type="button" id="btn_commission_add" class="btn btn-gray btn-sm">추가</button>
                            </div>
                        </td>
                    </tr>
                    <?php
                    if ($getData['addCommissionData']) {
                        $commissionCnt = 1;
                        foreach ($getData['addCommissionData'] as $key => $val) {
                            if ($val['commissionType'] == 'sell') {
                                if ($val['commissionValue'] < 0 || $val['commissionValue'] > 100) { continue; }
                                $buttonClass = 'btn-commission-del';
                                if (gd_isset($val['scmCommissionButtonClass'])) {
                                    $buttonClass = $val['scmCommissionButtonClass'];
                                }
                                $scmCommissionTh = '';
                                if ($commissionCnt == 1) {
                                    $scmCommissionTh = '<tr class="scmCommissionTrInDB"><th id="scmCommissionAddth" rowspan="">추가</th></tr>';
                                }
                                $commissionCnt++;
                                ?>
                                <?=$scmCommissionTh?>
                                <tr class="scmCommissionTrInDB">
                                    <td>
                                        <div>
                                            <input type="text" name="scmCommissionInDB[<?= $val['sno'] ?>]" value="<?= $val['commissionValue'] ?>" class="scmCommission form-control width-sm" <?= $val['scmCommissionInput'] ?>/>%
                                            <button type="button" id="btn_commission_del" class="<?= $buttonClass ?> btn btn-gray btn-sm" <?= $val['scmCommissionButton'] ?>>삭제</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    ?>
                </table>
            </td>
            <th class="require">
                배송비수수료
            </th>
            <td id="scmCommissionDelivery" class="form-inline">
                <label class="checkbox-inline">
                    <input type="checkbox" id="scmSameCommission" name="scmSameCommission" value="Y" <?= $checked['scmCommissionSame'] ?> <?= $getData['scmCommissionSameCheckBox'] ?>/>판매수수료 동일 적용
                </label>
                <table id="scmCommissionDeliveryTable"class="table-cols">
                    <tr>
                        <th>기본</th>
                        <td>
                            <div class="scmCommissionDeliveryDiv">
                                <input type="text" name="scmCommissionDelivery" value="<?= $getData['scmCommissionDelivery'] ?>" class="scmcommissionDelivery form-control width-sm commissionDeliveryRequired" <?= $getData['scmCommissionDeliveryInput'] ?>/>%
                                <button type="button" id="btn_commission_delivery_add" class="btn btn-gray btn-sm">추가</button>
                            </div>
                        </td>
                    </tr>
                    <?php
                    if ($getData['addCommissionData']) {
                        $commissionDeliveryCnt = 1;
                        foreach ($getData['addCommissionData'] as $key => $val) {
                            if ($val['commissionType'] == 'delivery') {
                                if ($val['commissionValue'] < 0 || $val['commissionValue'] > 100) { continue; }
                                $buttonClass = 'btn-commission-delivery-del';
                                if (gd_isset($val['scmCommissionDeliveryButtonClass'])) {
                                    $buttonClass = $val['scmCommissionDeliveryButtonClass'];
                                }
                                $scmCommissionDeliveryTh = '';
                                if ($commissionDeliveryCnt == 1) {
                                    $scmCommissionDeliveryTh = '<tr class="scmCommissionDeliveryTrInDB"><th id="scmCommissionDeliveryAddth" rowspan="">추가</th></tr>';
                                }
                                $commissionDeliveryCnt++;
                                ?>
                                <?=$scmCommissionDeliveryTh?>
                                <tr class="scmCommissionDeliveryTrInDB">
                                    <td>
                                        <div class="scmCommissionDeliveryDiv">
                                            <input type="text" id="scmcommissionDelivery" name="scmCommissionDeliveryInDB[<?= $val['sno'] ?>]" value="<?= $val['commissionValue'] ?>" class="scmcommissionDelivery form-control width-sm" <?= $val['scmCommissionDeliveryInput'] ?>/>%
                                            <button type="button" id="btn_commission_delivery_del" class="<?= $buttonClass ?> btn btn-gray btn-sm" <?= $val['scmCommissionDeliveryButton'] ?>>삭제</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        }
                    }
                    ?>
                </table>
            </td>
        </tr>
        <tr>
            <th>
                공급사코드
            </th>
            <td class="form-inline">
                <input type="text" name="scmCode" value="<?= $getData['scmCode'] ?>" class="form-control width-sm"/>
            </td>
            <th>
                이미지 저장소 기본값
            </th>
            <td class="form-inline">
                <?=gd_select_box('imageStorage', 'imageStorage', $conf['storage'], null, $getData['imageStorage'], null, null); ?>
            </td>
        </tr>
        <tr>
            <th>
                상품등록권한
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="scmPermissionInsert" value="a" <?= $checked['scmPermissionInsert']['a'] ?> />자동승인
                </label>
                <label class="radio-inline">
                    <input type="radio" name="scmPermissionInsert" value="c" <?= $checked['scmPermissionInsert']['c'] ?> />관리자승인
                </label>
            </td>
        </tr>
        <tr>
            <th>
                상품수정권한
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="scmPermissionModify" value="a" <?= $checked['scmPermissionModify']['a'] ?> />자동승인
                </label>
                <label class="radio-inline">
                    <input type="radio" name="scmPermissionModify" value="c" <?= $checked['scmPermissionModify']['c'] ?> />관리자승인
                </label>
            </td>
        </tr>
        <tr>
            <th>
                상품삭제권한
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="scmPermissionDelete" value="a" <?= $checked['scmPermissionDelete']['a'] ?> />자동승인
                </label>
                <label class="radio-inline">
                    <input type="radio" name="scmPermissionDelete" value="c" <?= $checked['scmPermissionDelete']['c'] ?> />관리자승인
                </label>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="notice-info">기본수수료는 공급사 상품 등록 시 기본적으로 적용되는 수수료이며 상품 등록 시 상품별로 수수료를 다르게 설정 할 수 있습니다.<br/>특정 기간에 상품에 등록된 수수료가 아닌 별도의 수수료 적용이 필요할 경우 추가수수료를 등록하시기 바랍니다.</div>
    <div class="notice-info">추가된 수수료는 <a href="./scm_commission_list.php" class="btn-link">[공급사 > 공급사 관리 > 공급사 수수료관리]</a>에서 기간별 설정이 가능합니다.<br/>배송비수수료의 “판매수수료 동일 적용“ 체크시 판매수수료에 등록된 수수료를 배송비수수료로 설정할 수 있으며,<br/>공급사 수수료관리에서 기간별 수수료 설정 시 판매수수료 기준으로 배송비수수료가 동일하게 적용됩니다.</div>
    <div class="table-title">
        공급사 정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">
                대표자명
            </th>
            <td>
                <input type="text" name="ceoNm" value="<?= $getData['ceoNm'] ?>" maxlength="20" class="form-control width-sm"/>
            </td>
            <th class="require">
                사업자등록번호
            </th>
            <td>
                <input type="text" name="businessNo" value="<?= $getData['businessNo'] ?>" maxlength="20" class="form-control width-sm"/>
                <div class="notice-info">※사업자등록번호를 정확하게 입력하지 않을 경우 세금계산서 발행이 제한될 수 있습니다.</div>
            </td>
        </tr>
        <tr>
            <th>
                사업자등록증
            </th>
            <td colspan="3" class="form-inline">
                <?php
                if ($getData['businessLicenseImage']) {
                    echo '<div><img src="' . $getData['businessLicenseImage'] . '" width="100"  />';
                    echo '<label title="이미지 삭제시 체크해 주세요!" class="radio-inline"><input type="checkbox" name="isBusinessImageDelete" value="y" /> 삭제</label><div>';
                    echo '<input type="hidden" name="oldBusinessLicenseImage" value="' . $getData['businessLicenseImage'] . '"/>';
                }
                ?>
                <input type="file" name="businessLicenseImage" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="require">
                업태
            </th>
            <td>
                <input type="text" name="service" value="<?= $getData['service'] ?>" class="form-control width-sm"/>
            </td>
            <th class="require">
                종목
            </th>
            <td>
                <input type="text" name="item" value="<?= $getData['item'] ?>" class="form-control width-sm"/>
            </td>
        </tr>
        <tr>
            <th class="require">
                사업장주소
            </th>
            <td colspan="3">
                <div class="form-inline mgb5">
                    <input type="text" name="zonecode" value="<?= gd_isset($getData['zonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="zipcode" value="<?= gd_isset($getData['zipcode']) ?>"/>
                    <span id="zipcodeText" class="number <?php if (strlen($getData['zipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $getData['zipcode']; ?>)</span>
                    <button type="button" id="btn_zipcode" class="btn btn-gray btn-sm">우편번호찾기</button>
                </div>
                <div class="form-inline">
                    <input type="text" name="address" class="form-control width-2xl" value="<?= gd_isset($getData['address']); ?>" readonly="readonly"/>
                    <input type="text" name="addressSub" class="form-control width-2xl" value="<?= gd_isset($getData['addressSub']); ?>"/>
                </div>
                <span id="zipcodeMsg" class="input_error_msg"></span>
                <span id="addressMsg" class="input_error_msg"></span>
                <span id="addressSubMsg" class="input_error_msg"></span>
            </td>
        </tr>
        <tr>
            <th>
                출고지 주소
            </th>
            <td colspan="3">
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="chkSameUnstoringAddr" value="y" <?= $checked['chkSameUnstoringAddr']['y'] ?>/>사업장주소와 동일
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="chkSameUnstoringAddr" value="n" <?= $checked['chkSameUnstoringAddr']['n'] ?>/>주소등록
                    </label>
                </div>
                <div class="form-inline mgb5 div_unstoring_addr">
                    <input type="text" name="unstoringZonecode" value="<?= gd_isset($getData['unstoringZonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="unstoringZipcode" value="<?= gd_isset($getData['unstoringZipcode']) ?>"/>
                    <span id="unstoringZipcodeText" class="number <?php if (strlen($getData['unstoringZipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $getData['unstoringZipcode']; ?>)</span>
                    <button type="button" id="btn_unstoring_zipcode" class="btn btn-gray btn-sm">우편번호찾기</button>
                </div>
                <div class="form-inline div_unstoring_addr">
                    <input type="text" name="unstoringAddress" class="form-control width-2xl" value="<?= gd_isset($getData['unstoringAddress']); ?>" readonly="readonly"/>
                    <input type="text" name="unstoringAddressSub" class="form-control width-2xl" value="<?= gd_isset($getData['unstoringAddressSub']); ?>"/>
                </div>
                <div class="div_unstoring_addr">
                    <span id="unstoringZipcodeMsg" class="input_error_msg"></span>
                    <span id="unstoringAddressMsg" class="input_error_msg"></span>
                    <span id="unstoringAddressSubMsg" class="input_error_msg"></span>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                반품/교환 주소
            </th>
            <td colspan="3">
                <div class="form-inline mgb5">
                    <label class="radio-inline">
                        <input type="radio" name="chkSameReturnAddr" value="y" <?= $checked['chkSameReturnAddr']['y'] ?>/>사업장주소와 동일
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="chkSameReturnAddr" value="x" <?= $checked['chkSameReturnAddr']['x'] ?>/>출고지주소와 동일
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="chkSameReturnAddr" value="n" <?= $checked['chkSameReturnAddr']['n'] ?>/>주소등록
                    </label>
                </div>
                <div class="form-inline mgb5 div_return_addr">
                    <input type="text" name="returnZonecode" value="<?= gd_isset($getData['returnZonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="returnZipcode" value="<?= gd_isset($getData['returnZipcode']) ?>"/>
                    <span id="returnZipcodeText" class="number <?php if (strlen($getData['returnZipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $getData['returnZipcode']; ?>)</span>
                    <button type="button" id="btn_return_zipcode" class="btn btn-gray btn-sm">우편번호찾기</button>
                </div>
                <div class="form-inline div_return_addr">
                    <input type="text" name="returnAddress" class="form-control width-2xl" value="<?= gd_isset($getData['returnAddress']); ?>" readonly="readonly"/>
                    <input type="text" name="returnAddressSub" class="form-control width-2xl" value="<?= gd_isset($getData['returnAddressSub']); ?>"/>
                </div>
                <div class="div_return_addr">
                    <span id="returnZipcodeMsg" class="input_error_msg"></span>
                    <span id="returnAddressMsg" class="input_error_msg"></span>
                    <span id="returnAddressSubMsg" class="input_error_msg"></span>
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">
                대표번호
            </th>
            <td>
                <input type="text" name="phone" value="<?= $getData['phone'] ?>" class="form-control width-sm"/>
            </td>
            <th class="require">
                고객센터
            </th>
            <td>
                <input type="text" name="centerPhone" value="<?= $getData['centerPhone'] ?>" class="form-control width-sm"/>
            </td>
        </tr>
        </tbody>
    </table>

<?php if ($getData['permissionSetupMode'] == 'managePermission') {?>
    <input type="hidden" name="permissionSetupMode" value="<?= $getData['permissionSetupMode'] ?>"/>
    <input type="hidden" name="superManagerSno" value="<?= $data['sno'] ?>"/>
    <div class="table-title gd-help-manual">
        권한 설정
    </div>
    <table class="table table-cols mgb30">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>메뉴 권한 설정</th>
            <td class="form-inline">
                <button type="button" class="btn btn-sm btn-gray manage-permission-btn" data-mode="<?= gd_isset($getData['mode']); ?>" data-issuper="y" >운영자 권한 설정</button>
            </td>
        </tr>
        </tbody>
    </table>

    <div id="permission_data">
        <input type="hidden" name="permissionFl" value="<?= gd_isset($data['permissionFl']); ?>"/>
        <?php
        foreach ($data['permissionMenu']['permission_1'] as $key => $val) {
            ?>
            <input type="hidden" name="permission_1[]" value="<?= $val ?>"/>
            <?php
        }
        ?>
        <?php
        foreach ($data['permissionMenu']['permission_2'] as $d1Key => $d1Val) {
            foreach ($d1Val as $d2Key => $d2Val) {
                ?>
                <input type="hidden" name="permission_2[<?= $d1Key ?>][]" data-item="<?= $d1Key ?>" value="<?= $d2Val ?>"/>
                <?php
            }
        }
        ?>
        <?php
        foreach ($data['permissionMenu']['permission_3'] as $d2Key => $d2Val) {
            foreach ($d2Val as $d3Key => $d3Val) {
                ?>
                <input type="hidden" name="permission_3[<?= $d2Key ?>][]" data-item="<?= $d2Key ?>" value="<?= $d3Val ?>"/>
                <?php
            }
        }
        ?>
        <?php
        foreach ($data['writeEnabledMenu'] as $d2Key => $d2Val) {
            foreach ($d2Val as $d3Key => $d3Val) {
                ?>
                <input type="hidden" name="writeEnabledMenu[<?= $d2Key ?>][]" data-item="<?= $d2Key ?>" value="<?= $d3Val ?>"/>
                <?php
            }
        }
        ?>
        <?php
        foreach ($data['functionAuth']['functionAuth'] as $key => $val) {
            ?>
            <input type="hidden" name="functionAuth[<?= $key ?>]" data-item="<?= $key ?>" value="<?= $val ?>"/>
            <?php
        }
        ?>
    </div>
<?php } else { ?>
    <?php
    if ($layoutFunctionAuth) {
        ?>
        <div class="table-title">
            공급사 기능 권한
        </div>
        <?php include $layoutFunctionAuth; ?>
    <?php } ?>
<?php } ?>

    <div class="table-title">
        담당자 정보
    </div>
    <table id="table_staff" class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-sm"/>
            <col class="width-sm"/>
            <col class="width-sm"/>
            <col class="width-sm"/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                담당
            </th>
            <th>
                이름
            </th>
            <th>
                전화번호
            </th>
            <th>
                휴대폰
            </th>
            <th>
                이메일
            </th>
            <th>
                추가/삭제
            </th>
        </tr>
        <?php
        if ($getData['staff']) {
            $staffNum = 2;
            foreach ($getData['staff'] as $key => $val) {
                $selected['staffType'][$key][$getData['staff'][$key]->staffType] = 'selected="selected"';
                $checked['chkSmsOrder'][$key][$getData['staff'][$key]->staffSmsOrder] = 'checked="checked"';
                $checked['chkSmsIncome'][$key][$getData['staff'][$key]->staffSmsIncome] = 'checked="checked"';
                ?>
                <tr id="tr_staff_<?= $staffNum ?>">
                    <td>
                        <?= gd_select_box('staffType[]', 'staffType[]', $department, null, gd_isset($getData['staff'][$key]->staffType), '=부서 선택='); ?>
                    </td>
                    <td>
                        <input type="text" name="staffName[]" value="<?= $getData['staff'][$key]->staffName ?>" class="form-control width-sm"/>
                    </td>
                    <td>
                        <input type="text" name="staffTel[]" value="<?= $getData['staff'][$key]->staffTel ?>" class="form-control width-sm"/>
                    </td>
                    <td>
                        <input type="text" name="staffPhone[]" value="<?= $getData['staff'][$key]->staffPhone ?>" class="form-control width-sm"/>
                    </td>
                    <td>
                        <input type="text" name="staffEmail[]" value="<?= $getData['staff'][$key]->staffEmail ?>" class="form-control width-sm"/>
                    </td>
                    <td>
                        <?php
                        if ($staffNum == 2) {
                            ?>
                            <button type="button" id="btn_staff_add" class="btn btn-gray btn-sm">추가</button>
                            <?php
                        } else {
                            ?>
                            <button type="button" class="btn_staff_del btn btn-gray btn-sm">삭제</button>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
                $staffNum++;
            }
        } else {
            ?>
            <tr id="tr_staff_2">
                <td>
                    <?= gd_select_box('staffType[]', 'staffType[]', $department, null, null, '=부서 선택='); ?>
                </td>
                <td>
                    <input type="text" name="staffName[]" value="" class="form-control width-sm"/>
                </td>
                <td>
                    <input type="text" name="staffTel[]" value="" class="form-control width-sm"/>
                </td>
                <td>
                    <input type="text" name="staffPhone[]" value="" class="form-control width-sm"/>
                </td>
                <td>
                    <input type="text" name="staffEmail[]" value="" class="form-control width-sm"/>
                </td>
                <td>
                    <button type="button" id="btn_staff_add" class="btn btn-gray btn-sm">추가</button>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-title">
        계좌 정보
    </div>
    <table id="table_account" class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-lg"/>
            <col class="width-sm"/>
            <col class="width-2xl"/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>
                은행
            </th>
            <th>
                계좌번호
            </th>
            <th>
                예금주
            </th>
            <th>
                메모
            </th>
            <th>
                추가/삭제
            </th>
        </tr>
        <?php
        if ($getData['account']) {
            $accountNum = 2;
            foreach ($getData['account'] as $key => $val) {
                $selected['accountType'][$key][$getData['account'][$key]->accountType] = 'selected="selected"';
                ?>
                <tr id="tr_account_<?= $accountNum ?>">
                    <td>
                        <?= gd_select_box('accountType[]', 'accountType[]', $account, null, gd_isset($getData['account'][$key]->accountType), '=은행 선택='); ?>
                    </td>
                    <td>
                        <input type="text" name="accountNum[]" value="<?= $getData['account'][$key]->accountNum ?>" class="form-control width100p" maxlength="30"/>
                    </td>
                    <td>
                        <input type="text" name="accountName[]" value="<?= $getData['account'][$key]->accountName ?>" class="form-control width100p" maxlength="30"/>
                    </td>
                    <td>
                        <textarea name="accountMemo[]" class="form-control width100p" maxlength="250"><?=$getData['account'][$key]->accountMemo;?></textarea>
                    </td>
                    <td>
                        <?php
                        if ($accountNum == 2) {
                            ?>
                            <button type="button" id="btn_account_add" class="btn btn-gray btn-sm">추가</button>
                            <?php
                        } else {
                            ?>
                            <button type="button" class="btn_account_del btn btn-gray btn-sm">삭제</button>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
                $accountNum++;
            }
        } else {
            ?>
            <tr id="tr_account_2">
                <td>
                    <?= gd_select_box('accountType[]', 'accountType[]', $account, null, null, '=은행 선택='); ?>
                </td>
                <td>
                    <input type="text" name="accountNum[]" value="" class="form-control width100p" maxlength="30"/>
                </td>
                <td>
                    <input type="text" name="accountName[]" value="" class="form-control width100p" maxlength="30"/>
                </td>
                <td>
                    <textarea name="accountMemo[]" class="form-control width100p" maxlength="250"></textarea>
                </td>
                <td>
                    <button type="button" id="btn_account_add" class="btn btn-gray btn-sm">추가</button>
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <?php
    if($popupMode == 'yes') {
        ?>
        <div id="gnbAnchor" style="position: fixed; bottom: 25px; right: 25px;">
            <div class="scrollTop" style="display:none;">
                <a href="#top"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/scroll_top_btn.png"></a>
            </div>
            <div class="scrollDown" style="display:block;">
                <a href="#down"><img src="<?= PATH_ADMIN_GD_SHARE ?>img/scroll_down_btn.png"></a>
            </div>
            <div class="scrollSave">
                <input type="submit" value="" class="save-btn"/>
            </div>
        </div>
        <?php
    }
    ?>
</form>

<script type="text/javascript">
    <!--
    var trCount = $('#table_staff tr').length;
    var trAccountCount = $('#table_account tr').length;
    var _select, _accNum, _accName, _memo;
    $(document).ready(function () {
        $("#frmScm").validate({
            dialog: false,
            submitHandler: function (form) {
                var checkAccount = false;
                var _trAccountCount = $('#table_account tr[id^=tr_account]').length;
                $('#table_account tr[id^=tr_account]').each( function( key ) {
                    _select = $(this).find('select').val();
                    _accNum = $.trim($(this).find('input[name^=accountNum]').val());
                    _accName = $.trim($(this).find('input[name^=accountName]').val());
                    _memo = $.trim($(this).find('textarea[name^=accountMemo]').val());
                    if ( ( _select == '' && _accNum == '' && _accName == '' && _memo == '') || ( _select != '' && _accNum != '' && _accName != '' ) ) {
                        if( key == _trAccountCount - 1 ) {
                            checkAccount = true;
                        }
                        return true;
                    } else {
                        if ( _select == '' ) {
                            alert("은행을 선택해 주세요.");
                        } else if ( _accNum == '' ) {
                            alert("계좌번호를 입력해 주세요.");
                        } else if ( _accName == '' ) {
                            alert("예금주를 입력해 주세요.");
                        }
                        checkAccount = false;
                        return false;
                    }
                });
                if ( !checkAccount ) {
                    return false;
                }

                if ($('input[name="permissionSetupMode"]').length == 1) {
                    if ($('input[name="permissionFl"]').length == 0) {
                        alert("메뉴 권한은 최소 1개 이상 '읽기' 또는 '읽기+쓰기'로 설정하셔야 합니다.");
                        return false;
                    } else {
                        if ($('input[name="permissionFl"]').val() == 'l' && $('input[name^="permission_3"]').length == 0) {
                            alert("메뉴 권한은 최소 1개 이상 '읽기' 또는 '읽기+쓰기'로 설정하셔야 합니다.");
                            return false;
                        }
                    }
                }

                <?php
                if ($getData['mode'] == 'modifyScmModify') {
                ?>
                if ($('input:radio[name="scmType"]:checked').val() == 'x') {
                    var scmNo = $('input:hidden[name="scmNo"]').val();
                    var chkMsg = '';
                    $.ajax({
                        method: "POST",
                        cache: false,
                        url: "../scm/scm_ps.php",
                        data: "mode=checkScmModify&scmNo=" + scmNo,
                    }).success(function (data) {
                        data = data + '<br/>탈퇴 처리 하시겠습니까?';
                        dialog_confirm(data, function (result) {
                            if (result) {
                                form.target = 'ifrmProcess';
                                form.submit();
                            } else {
                                return false;
                            }
                        });
                    }).error(function (e) {
                        alert(e.responseText);
                        return false;
                    });
                } else {
                    form.target = 'ifrmProcess';
                    form.submit();
                }
                <?php
                } else {
                ?>
                form.target = 'ifrmProcess';
                form.submit();
                <?php
                }
                ?>
            },
            rules: {
                companyNm: {
                    required: true,
                },
                scmType: {
                    required: true,
                },
                managerId: {
                    required: true,
                    minlength: 4,
                    maxlength: 50,
                    equalTo: 'input[name=managerDuplicateId]'
                },
                managerPw: {
                    required: true,
                    minlength: 10,
                    maxlength: 16,
                },
                scmKind: {
                    required: true,
                },
                scmPermissionInsert: {
                    required: true,
                },
                scmPermissionModify: {
                    required: true,
                },
                scmPermissionDelete: {
                    required: true,
                },
                ceoNm: {
                    required: true,
                    maxlength: 20,
                },
                businessNo: {
                    required: true,
                    maxlength: 20,
                },
                service: {
                    required: true,
                },
                item: {
                    required: true,
                },
                phone: {
                    required: true,
                },
                zonecode: {
                    required: true,
                },
                address: {
                    required: true,
                },
                addressSub: {
                    required: true,
                }
            },
            messages: {
                companyNm: {
                    required: '공급사명을 입력해주세요.',
                },
                scmType: {
                    required: '공급사 상태를 선택해주세요.',
                },
                managerId: {
                    required: '공급사 로그인ID를 입력해주세요.',
                    minlength: '공급사 로그인ID는 최소 {0}자 이상 입력해주세요.',
                    maxlength: '공급사 로그인ID는 최대 {0}자 이하 입력해주세요.',
                    equalTo: '공급사 로그인ID 중복체크 해주세요.',
                },
                managerPw: {
                    required: '공급사 로그인비밀번호를 입력해주세요.',
                    minlength: '공급사 로그인비밀번호는 최소 {0}자 이상 입력해주세요.',
                    maxlength: '공급사 로그인비밀번호는 최대 {0}자 이하 입력해주세요.',
                },
                scmKind: {
                    required: '공급사 타입을 선택해주세요.',
                },
                scmPermissionInsert: {
                    required: '상품등록권한을 선택해주세요.',
                },
                scmPermissionModify: {
                    required: '상품수정권한을 선택해주세요.',
                },
                scmPermissionDelete: {
                    required: '상품삭제권한을 선택해주세요.',
                },
                ceoNm: {
                    required: '대표자명을 입력해주세요.',
                    maxlength: '대표자명는 최대 {0}자 이하 입력해주세요.',
                },
                businessNo: {
                    required: '사업자등록번호를 입력해주세요.',
                    maxlength: '사업자등록번호는 최대 {0}자 이하 입력해주세요.',
                },
                service: {
                    required: '업태를 입력해주세요.',
                },
                item: {
                    required: '업종을 입력해주세요.',
                },
                phone: {
                    required: '대표번호를 입력해주세요.',
                },
                zonecode: {
                    required: '사업자 우편번호를 입력해주세요.',
                },
                address: {
                    required: '사업자 주소를 입력해주세요.',
                },
                addressSub: {
                    required: '사업자 주소를 입력해주세요.',
                }
            }
        });
        //판매 수수료, 배송비 수수료 체크
        jQuery.validator.addMethod("commissionRequired", jQuery.validator.methods.required,"판매 수수료를 입력해주세요.");
        $.validator.addMethod("commissionDeliveryRequired", jQuery.validator.methods.required,"배송비 수수료를 입력해주세요.");
        $.validator.addClassRules({
            scmCommission : { commissionRequired : true },
            commissionDeliveryRequired : { commissionDeliveryRequired : true }

        });
        if ( $("#scmSameCommission").is(":checked") == true ) {
            $('#scmCommissionDeliveryTable ').hide();
        }
        //추가 수수료부분 th 합치기
        commissionThMerge();
        commissionDeliveryThMerge();

        // 판매 수수료 동일 적용 및 배송비 수수료 보일 시에만 체크
        $("#scmSameCommission").click(function (e) {
            if ($(this).is(":checked") == true) {
                $('#scmCommissionDeliveryTable ').hide();
                $('.scmcommissionDelivery').removeClass('commissionDeliveryRequired');
            } else {
                $('#scmCommissionDeliveryTable ').show();
                $('.scmcommissionDelivery').addClass('commissionDeliveryRequired');
            }
        });
        // 닉네임 공급사명과 동일체크
        $('input:checkbox[name^="scmSameNick"]').click(function (e) {
            changeSameNickName();
        });
        // 관리자 아이디 중복확인
        $("#overlap_managerId").click(function (e) {
            checkManageId(e);
        });
        // 사업자 주소
        $("#btn_zipcode").click(function (e) {
            postcode_search('zonecode', 'address', 'zipcode');
        });
        // 출고지 주소
        $("#btn_unstoring_zipcode").click(function (e) {
            postcode_search('unstoringZonecode', 'unstoringAddress', 'unstoringZipcode');
        });
        // 반품/교환 주소
        $("#btn_return_zipcode").click(function (e) {
            postcode_search('returnZonecode', 'returnAddress', 'returnAddressSub');
        });
        $('input:radio[name="chkSameUnstoringAddr"],input:radio[name="chkSameReturnAddr"]').click(function (e) {
            changeAddress();
        });
        // 주소 입력창 노출
        changeAddress();
        // 담당자 정보 추가
        $("#btn_staff_add").click(function (e) {
            staffAdd();
        });
        // 담당자 정보 삭제(정적 담당자 삭제)
        $(".btn_staff_del").click(function (e) {
            $(this).closest('tr').remove();
        });
        $(document).on('keydown focusout', 'input[name^=accountNum]', function(e){
            $(this).val($(this).val().replace(/[^A-Za-z0-9\-]/g,""));
        });
        // 계좌 정보 추가
        $("#btn_account_add").click(function (e) {
            accountAdd();
        });
        // 계좌 정보 삭제
        $(".btn_account_del").click(function (e) {
            $(this).closest('tr').remove();
        });
        //판매 수수료 추가
        $("#btn_commission_add").click(function (e) {
            commissionAdd();
        });
        //판매 수수료 삭제
        $(".btn-commission-del").click(function (e) {
            if ($('.scmCommission').length == 2) {
                $('.scmCommissionTrInDB').remove();
                $('.scmCommissionAddTr').remove();
            } else {
                $(this).closest('tr').remove();
            }
        });
        //판매 수수료 수정 금지
        $(".scmCommission").click(function (e) {
            if ( $(this).attr('readonly') ) {
                alert('일정에 등록된 수수료는 수정 및 삭제를 할 수 없습니다.');
            }
        });
        //판매 수수료 삭제 금지
        $(".btn-commission-in-schedule").click(function (e) {
            alert('일정에 등록된 수수료는 수정 및 삭제를 할 수 없습니다.');
        });
        //배송비 수수료 추가
        $("#btn_commission_delivery_add").click(function (e) {
            commissionDeliveryAdd();
        });
        //배송비 수수료 삭제
        $(".btn-commission-delivery-del").click(function (e) {
            if ($('.scmcommissionDelivery').length == 2) {
                $('.scmCommissionDeliveryTrInDB').remove();
                $('.scmCommissionDeliveryAddTr').remove();
            } else {
                $(this).closest('tr').remove();
            }
        });
        //배송비 수수료 수정 금지
        $(".scmcommissionDelivery").click(function (e) {
            if ( $(this).attr('readonly') ) {
                alert('일정에 등록된 수수료는 수정 및 삭제를 할 수 없습니다.');
            }
        });
        //판매 수수료 삭제 금지
        $(".btn-commission-delivery-in-schedule").click(function (e) {
            alert('일정에 등록된 수수료는 수정 및 삭제를 할 수 없습니다.');
        });

        // input radio 값을 input text 에 넣기
        $(document).on('change', '#frmScm input:checkbox', function (e) {
            if ($(this).prop('checked') == true) {
                $(this).closest('td, th').children('input[type=hidden]').val('y');
            } else {
                $(this).closest('td, th').children('input[type=hidden]').val('');
            }
        });

        <?php
        if (gd_isset($popupMode) == 'yes') {
        ?>
        $(window).scroll(function () {
            $('#content').css('padding-top','52px');
            var height = $(document).scrollTop();

            if (height >= 1) {
                $('.scrollTop').css('display','block');
                $('.scrollDown').css('display','block');
            } else {
                $('.scrollTop').css('display','none');
            }

            if (Math.round($(window).scrollTop()) >= $(document).height() - $(window).height()) {
                $('.scrollDown').css('display','none');
            }
        });
        $(window).scroll();

        // 탑버튼 클릭
        $(document).on("click", "a[href=#top]", function(e) {
            $('html body').animate({scrollTop: 0}, 'fast');
            $('.scrollDown').css('display','block');
            $('.scrollTop').css('display','none');
        });

        // 다운버튼 클릭
        $(document).on("click", "a[href=#down]", function(e) {
            $('html body').animate({scrollTop: $(document).scrollTop($(document).height())}, 'fast');
            $('.scrollDown').css('display','none');
            $('.scrollTop').css('display','block');
        });

        $('#gnbAnchor').css('display','block');

        <?php }?>

        // 운영자 권한 설정 버튼 이벤트
        if ($('input[name="permissionSetupMode"]').length == 1) {
            if (typeof layer_manage_permission == 'undefined') {
                $('.manage-permission-btn').click(function () { dialog_alert("오류가 발생했습니다. 잠시 후 다시 시도해주세요.<br>문제가 지속될 경우 1:1문의 게시판에 문의해주세요."); });
            } else {
                $('.manage-permission-btn').click(layer_manage_permission);
            }
        }
    });
    function staffAdd() {
        var chkTrCount = ($('#table_staff tr').length) - 2; // + 추가 1개 - 기본 tr 2개
        trCount = trCount + 1;
        if ((chkTrCount + 1) >= <?php echo DEFAULT_LIMIT_SCM_STAFF;?>) {
            alert('담당자 정보는 <?php echo DEFAULT_LIMIT_SCM_STAFF;?>개가 제한 입니다.');
            return false;
        }
        var selectType = '<?= gd_select_box("staffType[]", "staffType[]", $department, null, null, "=부서 선택="); ?>';
        var addStaff = '<tr id="tr_staff_' + trCount + '">';
        addStaff += '<td>'+selectType+'</td>';
        addStaff += '<td><input type="text" name="staffName[]" value="" class="form-control width-sm"/></td>';
        addStaff += '<td><input type="text" name="staffTel[]" value="" class="form-control width-sm"/></td>';
        addStaff += '<td><input type="text" name="staffPhone[]" value="" class="form-control width-sm"/></td>';
        addStaff += '<td><input type="text" name="staffEmail[]" value="" class="form-control width-sm"/></td>';
        addStaff += '<td><button type="button" id="btn_staff_del_' + trCount + '" class="btn btn-gray btn-sm">삭제</button></td>';
        addStaff += '</tr>';
        $('#table_staff').append(addStaff);
        // 담당자 정보 삭제(동적 담당자 삭제)
        $('#btn_staff_del_' + trCount).on('click', function (e) {
            $(this).closest('tr').remove();
        });
    }
    function accountAdd() {
        var chkTrCount = ($('#table_account tr').length) - 2; // + 추가 1개 - 기본 tr 2개
        trAccountCount = trAccountCount + 1;
        if ((chkTrCount + 1) >= 5) {
            alert('계좌 정보는 5개가 제한 입니다.');
            return false;
        }
        var selectType = '<?= gd_select_box("accountType[]", "accountType[]", $account, null, null, "=은행 선택="); ?>';
        var addAccount = '<tr id="tr_account_' + trAccountCount + '">';
        addAccount += '<td>'+selectType+'</td>';
        addAccount += '<td><input type="text" name="accountNum[]" value="" class="form-control width100p" maxlength="30"/></td>';
        addAccount += '<td><input type="text" name="accountName[]" value="" class="form-control width100p" maxlength="30"/></td>';
        addAccount += '<td><textarea name="accountMemo[]" class="form-control width100p" maxlength="250"></textarea></td>';
        addAccount += '<td><button type="button" id="btn_account_del_' + trAccountCount + '" class="btn btn-gray btn-sm">삭제</button></td>';
        addAccount += '</tr>';
        $('#table_account').append(addAccount);
        // 계좌 정보 삭제
        $('#btn_account_del_' + trAccountCount).on('click', function (e) {
            $(this).closest('tr').remove();
        });
    }
    function commissionAdd() {
        var chkCommissionInput = $('.scmCommission').length; // + 추가 1개 - 기본 tr 2개
        if ((chkCommissionInput) >= 4) {
            alert('판매 수수료는 3개까지 추가할 수 있습니다.');
            return false;
        }
        var addCommissionTh = '';
        if (chkCommissionInput == 1) {//첫번째 추가 수수료
            addCommissionTh = '<tr class="scmCommissionTrInDB"><th id="scmCommissionAddth">추가</th></th>';
        }

        var addCommission = '';
        addCommission += addCommissionTh;
        addCommission += '<tr class="scmCommissionAddTr">';
        addCommission += '<td>';
        addCommission += '<div>';
        addCommission += '<input type="text" name="scmCommissionNew[]" value="" class="scmCommission form-control width-sm"/>% ';
        addCommission += '<button type="button" id="btn_commission_del_'+ chkCommissionInput + '" class="btn btn-gray btn-sm">삭제</button>';
        addCommission += '</div>';
        addCommission += '</td></tr>';

        $('#scmCommissionTable').append(addCommission);
        var rowspanValue = $('.scmCommission').length;
        $('#scmCommissionAddth').attr('rowspan',rowspanValue);
        // 수수료 정보 삭제
        $('#btn_commission_del_' + chkCommissionInput).on('click', function (e) {
            if ($('.scmCommission').length == 2) {
                $('.scmCommissionTrInDB').remove();
                $('.scmCommissionAddTr').remove();
            } else {
                $(this).closest('tr').remove();
            }
        });
    }

    function commissionThMerge() {
        var rowspanValue = $('.scmCommission').length;
        $('#scmCommissionAddth').attr('rowspan',rowspanValue);
    }

    function commissionDeliveryAdd() {
        var chkCommissionDeliveryInput = $('.scmcommissionDelivery').length;
        if ((chkCommissionDeliveryInput) >= 4) {
            alert('배송비 수수료는 3개까지 추가할 수 있습니다.');
            return false;
        }
        var addCommissionTh = '';
        if (chkCommissionDeliveryInput == 1) {//첫번째 추가 수수료
            addCommissionTh = '<tr class="scmCommissionDeliveryTrInDB"><th id="scmCommissionDeliveryAddth">추가</th></tr>';

        }
        var addCommissionDelivery = '';
        addCommissionDelivery += addCommissionTh;
        addCommissionDelivery += '<tr class="scmCommissionDeliveryAddTr">';
        addCommissionDelivery += '<td>';
        addCommissionDelivery += '<div>';
        addCommissionDelivery += '<input type="text" id="scmcommissionDelivery" name="scmCommissionDeliveryNew[]" value="" class="scmcommissionDelivery form-control width-sm commissionDeliveryRequired"/>% ';
        addCommissionDelivery += '<button type="button" id="btn_commission_delivery_del_'+ chkCommissionDeliveryInput + '" class="btn_commission_delivery_del btn btn-gray btn-sm">삭제</button>';
        addCommissionDelivery += '</div>';
        addCommissionDelivery += '</td></tr>';

        $('#scmCommissionDeliveryTable').append(addCommissionDelivery);
        var scmcommissionDeliveryTh = $('.scmcommissionDelivery').length;
        $('#scmCommissionDeliveryAddth').attr('rowspan',scmcommissionDeliveryTh);
        // 수수료 정보 삭제
        $('#btn_commission_delivery_del_' + chkCommissionDeliveryInput).on('click', function (e) {
            if ($('.scmcommissionDelivery').length == 2) {
                $('.scmCommissionDeliveryTrInDB').remove();
                $('.scmCommissionDeliveryAddTr').remove();
            } else {
                $(this).closest('tr').remove();
            }
        });
    }

    function commissionDeliveryThMerge() {
        var scmcommissionDeliveryTh = $('.scmcommissionDelivery').length;
        $('#scmCommissionDeliveryAddth').attr('rowspan',scmcommissionDeliveryTh);
    }

    function changeSameNickName(e) {
        if ($('input:checkbox[name="scmSameNick"]').prop("checked") == true) {
            $('input:text[name="managerNickNm"]').val($('input:text[name="companyNm"]').val());
        }
    }
    function checkManageId() {
        var managerId = $('input:text[name="managerId"]').val();
        $.ajax({
            method: "GET",
            cache: false,
            url: "../policy/manage_ps.php",
            data: "mode=overlapManagerId&managerId=" + managerId,
            dataType: 'json'
        }).success(function (data) {
            alert(data['msg']);
            if (data['result'] == 'ok') {
                $('input:hidden[name="managerDuplicateId"]').val(managerId);
            }
        }).error(function (e) {
            alert(e.responseText);
        });
    }
    function changeAddress() {
        if ($('input:radio[name="chkSameUnstoringAddr"]:checked').val() == 'n') {
            $('.div_unstoring_addr').show();
        } else {
            $('.div_unstoring_addr').hide();
            $('input:text[name="unstoringZonecode"]').val('');
            $('input:hidden[name="unstoringZipcode"]').val('');
            $('#unstoringZipcodeText').text('');
            $('input:text[name="unstoringAddress"]').val('');
            $('input:text[name="unstoringAddressSub"]').val('');
        }
        if ($('input:radio[name="chkSameReturnAddr"]:checked').val() == 'n') {
            $('.div_return_addr').show();
        } else {
            $('.div_return_addr').hide();
            $('input:text[name="returnZonecode"]').val('');
            $('input:hidden[name="returnZipcode"]').val('');
            $('#returnZipcodeText').text('');
            $('input:text[name="returnAddress"]').val('');
            $('input:text[name="returnAddressSub"]').val('');
        }
    }

    /**
     * 운영자 번호 리턴
     * @returns {string}
     */
    function get_manager_sno() {
        return $('#frmScm input:hidden[name="superManagerSno"]').val();
    }

    /**
     * 공급사 번호 리턴
     * @returns {string}
     */
    function get_scmno() {
        var scmNo = ''; // 기본값
        if ($('#frmScm input:hidden[name="mode"]').val() == 'modifyScmModify' && $('#frmScm input:hidden[name="scmNo"]').length) { // 공급사 사용 중이고 공급사 관리모드가 아닌 경우
            scmNo = $('#frmScm input:hidden[name="scmNo"]').val();
        }
        return scmNo;
    }

    /**
     * 공급사 구분 리턴
     * @returns {string}
     */
    function get_scmfl() {
        var scmFl = 'n'; // 기본값
        if (scmFl == 'n' && get_scmno() != <?= DEFAULT_CODE_SCMNO; ?>) { // 본사가 아닌 경우
            scmFl = 'y';
        } else if (scmFl == 'y' && get_scmno() == <?= DEFAULT_CODE_SCMNO; ?>) { // 본사 인 경우
            scmFl = 'n';
        }
        return scmFl;
    }
    //-->
</script>