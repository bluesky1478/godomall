<form id="frmScm" name="frmScm" action="scm_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $getData['mode']; ?>"/>
    <input type="hidden" name="scmNo" value="<?= $getData['scmNo']; ?>"/>
    <input type="hidden" name="zipcode" value="<?= $getData['zipcode']; ?>"/>
    <input type="hidden" name="zonecode" value="<?= $getData['zonecode']; ?>"/>
    <input type="hidden" name="address" value="<?= $getData['address']; ?>"/>
    <input type="hidden" name="addressSub" value="<?= $getData['addressSub']; ?>"/>
    <input type="hidden" name="isProvider" value="y"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <input type="submit" value="저장" class="btn btn-red"/>
    </div>

    <div class="table-title gd-help-manual">
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
        <tr>
            <th>
                공급사명
            </th>
            <td colspan="3">
                <?= $getData['companyNm']; ?>
            </td>
        </tr>
        <tr>
            <th>
                판매수수료
            </th>
            <td class="form-inline">
                <?= $getData['scmCommission']; ?>
            </td>
            <th>
                배송비수수료
            </th>
            <td>
                <?= $getData['scmCommissionDelivery']; ?>
            </td>
        </tr>
        <tr>
            <th>
                공급사코드
            </th>
            <td colspan="3"><?= $getData['scmCode']; ?></td>
        </tr>
        <tr>
            <th>
                상품등록권한
            </th>
            <td colspan="3"><?= $getData['scmPermissionInsert']; ?></td>
        </tr>
        <tr>
            <th>
                상품수정권한
            </th>
            <td colspan="3"><?= $getData['scmPermissionModify']; ?></td>
        </tr>
        <tr>
            <th>
                상품삭제권한
            </th>
            <td colspan="3"><?= $getData['scmPermissionDelete']; ?></td>
        </tr>
        </tbody>
    </table>
    <div class="notice-info">* 대표운영자정보 (ID, 비밀번호, 닉네임, 이미지)는 운영자관리에서 확인/수정하실 수 있습니다. <a href="../policy/manage_list.php" class="btn-link-underline">운영자관리 바로가기></a><br/>기본수수료는 공급사 상품 등록 시 기본적으로 적용되는 수수료이며 상품 등록 시 상품별로 수수료를 다르게 설정 할 수 있습니다.<br/>본사 담당자는 특정 기간에 상품에 등록된 수수료가 아닌 별도의 수수료를 추가 수수료를 이용하여 적용 할 수 있습니다.<br/>수수료 일정 관리에서 일별 수수료 정보를 확인할 수 있습니다. <a href="../policy/scm_commission_list.php" class="btn-link-underline">수수료 일정 관리 바로가기 ></a></div>
    <div class="table-title gd-help-manual">
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
            <th>
                대표자명
            </th>
            <td><?= $getData['ceoNm']; ?></td>
            <th>
                사업자등록번호
            </th>
            <td><?= $getData['businessNo']; ?></td>
        </tr>
        <tr>
            <th>
                사업자등록증
            </th>
            <td colspan="3" class="form-inline">
                <?php
                if ($getData['businessLicenseImage']) {
                    ?>
                    <img src="<?= $getData['businessLicenseImage']; ?>" width="100"/>
                    <?php
                }
                ?>
            </td>
        </tr>
        <tr>
            <th>
                업태
            </th>
            <td><?= $getData['service']; ?></td>
            <th>
                종목
            </th>
            <td><?= $getData['item']; ?></td>
        </tr>
        <tr>
            <th>
                사업자 주소
            </th>
            <td colspan="3"><?= gd_isset($getData['zonecode']) ?>
                <?php if (strlen($getData['zipcode']) == 7) {
                    echo '(' . $getData['zipcode'] . ')';
                } ?>
                </div>
                <div><?= gd_isset($getData['address']); ?> <?= gd_isset($getData['addressSub']); ?></div>
            </td>
        </tr>
        <tr>
            <th>
                출고지 주소
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="chkSameUnstoringAddr" value="y" <?= $checked['chkSameUnstoringAddr']['y']; ?>/>사업장 주소와 동일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="chkSameUnstoringAddr" value="n" <?= $checked['chkSameUnstoringAddr']['n']; ?>/>주소등록
                </label>
                <div class="form-inline div_unstoring_addr">
                    <input type="text" name="unstoringZonecode" value="<?= gd_isset($getData['unstoringZonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="unstoringZipcode" value="<?= gd_isset($getData['unstoringZipcode']) ?>"/>
                    <span id="unstoringZipcodeText" class="number <?php if (strlen($getData['unstoringZipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $getData['unstoringZipcode']; ?>)</span>
                    <button type="button" id="btn_unstoring_zipcode">우편번호찾기</button>
                </div>
                <div class="div_unstoring_addr">
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
                반품/교환지 주소
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="chkSameReturnAddr" value="y" <?= $checked['chkSameReturnAddr']['y']; ?>/>사업장 주소와 동일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="chkSameReturnAddr" value="x" <?= $checked['chkSameReturnAddr']['x']; ?>/>출고지 주소와 동일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="chkSameReturnAddr" value="n" <?= $checked['chkSameReturnAddr']['n']; ?>/>주소등록
                </label>
                <div class="form-inline div_return_addr">
                    <input type="text" name="returnZonecode" value="<?= gd_isset($getData['returnZonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="returnZipcode" value="<?= gd_isset($getData['returnZipcode']) ?>"/>
                    <span id="returnZipcodeText" class="number <?php if (strlen($getData['returnZipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $getData['returnZipcode']; ?>)</span>
                    <button type="button" id="btn_return_zipcode">우편번호찾기</button>
                </div>
                <div class="div_return_addr">
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
                <input type="text" name="phone" value="<?= $getData['phone']; ?>" class="form-control width-sm"/>
            </td>
            <th class="require">
                고객센터
            </th>
            <td>
                <input type="text" name="centerPhone" value="<?= $getData['centerPhone']; ?>" class="form-control width-sm"/>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="table-title gd-help-manual">
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
                            <button type="button" id="btn_staff_add">추가</button>
                            <?php
                        } else {
                            ?>
                            <button type="button" class="btn_staff_del">삭제</button>
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
                    <button type="button" id="btn_staff_add">추가</button>
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
        </tr>
        <?php
        if ($getData['account']) {
            foreach ($getData['account'] as $key => $val) {
                ?>
                <tr>
                    <td><?= $account[$getData['account'][$key]->accountType] ?></td>
                    <td><?= $getData['account'][$key]->accountNum ?></td>
                    <td><?= $getData['account'][$key]->accountName ?></td>
                    <td><?= nl2br($getData['account'][$key]->accountMemo); ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="4"></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <?php
    if($naverPay['useApi'] == 'y') {?>
    <div class="table-title">네이버페이 배송정보 설정</div>
    <table class="table table-cols" >
        <tr>
            <th>반품 배송비</th>
            <td>
                <input type="text" name="returnPrice" class="form-control js-number" data-number="6,200000,0"  value="<?= $naverPay['deliveryData'][$scmNo]['returnPrice'] ?>">
                <p class="notice-info">반품 배송비를 입력하지 않을 경우 본사의 네이버페이 반품 배송비를 따릅니다.
                    <br>
                    무료 배송 상품 반품 시 & 상품 교환 시 반품 배송비의 2배를 청구합니다. <a href="https://admin.pay.naver.com" target="_blank">네이버페이센터 바로가기></a></p>
            </td>
        </tr>


        <tr>
            <th>지역별 배송비 설정</th>
            <td>
                <div class="in-form"><label><input type="radio" class="form-control" name="areaDelivery" value="n" <?= gd_isset($checked['areaDelivery']['n']) ?>/>사용안함</label></div>
                <div class="in-form"><label><input type="radio" class="form-control" name="areaDelivery" value="2" <?= gd_isset($checked['areaDelivery']['2']) ?>/>2권역 지역별 배송비 사용
                        <span class="notice-info">제주권을 포함한 도서산간 지역에 같은 지역별 배송비를 부과합니다. </span></label>
                    <div class="form-inline pdl15">지역별 배송비 : <input type="text" name="area22Price" class="form-control js-number" data-number="6,200000,0" value="<?= $naverPay['area22Price'] ?>" />원</div>
                </div>
                <div class="in-form"><label><input type="radio" class="form-control" name="areaDelivery" value="3" <?= gd_isset($checked['areaDelivery']['3']) ?>/>3권역 지역별 배송비 사용
                        <span class="notice-info">제주권과 도서산간 지역에 각각 다른 지역별 배송비를 부과합니다 </span></label>
                    <div class="form-inline pdl15">2권역(제주권) 지역별 배송비 : <input type="text" name="area32Price" class="form-control js-number" value="<?= $naverPay['area32Price'] ?>" data-number="6,200000,0" />원</div>
                    <div class="form-inline pdl15">3권역(제주권 외 도서산간) 지역별 배송비 : <input type="text" name="area33Price" class="form-control js-number" value="<?= $naverPay['area33Price'] ?>" data-number="6,200000,0" />원</div>
                </div>
                <div class="notice-danger">네이버페이로 결제 시 현재 페이지에서 설정한 네이버페이 지역별 배송비를 따릅니다.([기본설정>배송정책>지역별추가배송비관리]에서 설정한 지역별 배송비는 따르지 않습니다.)
                </div>


            </td>
        </tr>
    </table>
    <?php }?>

</form>
<script type="text/javascript">
    <!--
    var trCount = $('#table_staff tr').length;
    $(document).ready(function () {
        $("#frmScm").validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                phone: {
                    required: true,
                },
            },
            messages: {
                phone: {
                    required: '대표번호를 입력해주세요.',
                },
            }
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
        // input radio 값을 input text 에 넣기
        $(document).on('change', '#frmScm input:checkbox', function (e) {
            if ($(this).prop('checked') == true) {
                $(this).closest('td, th').children('input[type=hidden]').val('y');
            } else {
                $(this).closest('td, th').children('input[type=hidden]').val('');
            }
        });

        $('input[name=areaDelivery]').bind('click', function () {
            val = $(this).val();
            $('[name=area22Price]').prop('disabled', false);
            $('[name=area32Price]').prop('disabled', false);
            $('[name=area33Price]').prop('disabled', false);
            switch (val) {
                case 'n' :
                    $('[name=area22Price]').prop('disabled', true);
                    $('[name=area32Price]').prop('disabled', true);
                    $('[name=area33Price]').prop('disabled', true);
                    break;
                case '2' :
                    $('[name=area32Price]').prop('disabled', true);
                    $('[name=area33Price]').prop('disabled', true);
                    break;
                case '3' :
                    $('[name=area22Price]').prop('disabled', true);
                    break
            }
        })
        $('input[name=areaDelivery]:checked').trigger('click');

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
        addStaff += '<td><button type="button" id="btn_staff_del_' + trCount + '">삭제</button></td>';
        addStaff += '</tr>';
        $('#table_staff').append(addStaff);
        // 담당자 정보 삭제(동적 담당자 삭제)
        $('#btn_staff_del_' + trCount).on('click', function (e) {
            $(this).closest('tr').remove();
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
    //-->
</script>
