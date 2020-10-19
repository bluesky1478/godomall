<form id="frmPurchase" name="frmPurchase" action="purchase_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="<?= $data['mode'] ?>"/>
    <input type="hidden" name="purchaseNo" value="<?= $data['purchaseNo'] ?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./purchase_list.php');" />
            <input type="submit" value="저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title">
        기본 정보
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
                매입처명
            </th>
            <td colspan="3">
                <input type="text" name="purchaseNm" value="<?= $data['purchaseNm'] ?>" maxlength="30" class="form-control width-xl js-maxlength"/>
            </td>
        </tr>
        <tr>
            <th>
                매입처 코드
            </th>
            <td>
                <p class="width-xl"><?php if($data['purchaseNo'] ) { ?><?=$data['purchaseNo'] ?><?php } else { ?>매입처 등록시 자동 생성됩니다.<?php } ?></p>
            </td>
            <th>
                매입처 자체코드
            </th>
            <td>
                <input type="text" name="purchaseCd" value="<?= $data['purchaseCd'] ?>" maxlength="30" class="form-control width-xl js-maxlength"/>
            </td>
        </tr>
        <tr>
            <th>
                사용상태
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="y" <?= $checked['useFl']['y'] ?> />사용
                </label>
                <label class="radio-inline">
                    <input type="radio" name="useFl" value="n" <?= $checked['useFl']['n'] ?> />사용안함
                </label>
            </td>
        </tr>
        <tr>
            <th>
               거래상태
            </th>
            <td colspan="3">
                <label class="radio-inline">
                    <input type="radio" name="businessFl" value="y" <?= $checked['businessFl']['y'] ?> />거래중
                </label>
                <label class="radio-inline">
                    <input type="radio" name="businessFl" value="n" <?= $checked['businessFl']['n'] ?> />거래중지
                </label>
                <label class="radio-inline">
                    <input type="radio" name="businessFl" value="x" <?= $checked['businessFl']['x'] ?> />거래해지
                </label>
            </td>
        </tr>
        <tr>
            <th>
                상품유형
            </th>
            <td colspan="3">
                <input type="text" name="category" value="<?= $data['category'] ?>" placeholder="예시)의류 " maxlength="60" class="form-control width-2xl js-maxlength"/>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="table-title">
        상세 정보
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
                계좌정보
            </th>
            <td colspan="3">
                <dl class="dl-horizontal">
                    <dt class="width-2xs">은행</dt>
                    <dd class="mgl0"> <?= gd_select_box(null, 'bankName', $data['bankList'], null, $data['bankName'], '= 은행 선택 =',null,'width-lg') ?></dd>
                    <dt class="mgt5 width-2xs">계좌번호</dt>
                    <dd class="mgl0 mgt5"><input type="text" name="accountNumber" value="<?=$data['accountNumber']?>" class="form-control width-lg js-type-valid" maxlength="30" data-regex="[^a-z0-9\.-]*"/></dd>
                    <dt class="mgt5 width-2xs">예금주</dt>
                    <dd class="mgl0 mgt5"><input type="text" name="depositor" value="<?= $data['depositor'] ?>" class="form-control width-lg" maxlength="30"/></dd>
                </dl>
            </td>
        </tr>
        <tr>
            <th>
                전화번호
            </th>
            <td>
                <input type="text" name="phone" value="<?= $data['phone'] ?>" class="form-control width-sm js-number-only" maxlength="12"/>
            </td>
            <th>
                팩스번호
            </th>
            <td>
                <input type="text" name="fax" value="<?= $data['fax'] ?>" class="form-control width-sm js-number-only" maxlength="12"/>
            </td>
        </tr>
        <tr>
            <th>
                사업장 주소
            </th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="zonecode" value="<?= gd_isset($data['zonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="zipcode" value="<?= gd_isset($data['zipcode']) ?>"/>
                    <span id="zipcodeText" class="number <?php if (strlen($data['zipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $data['zipcode']; ?>)</span>
                    <button type="button" id="btn_zipcode" class="btn btn-gray btn-sm">우편번호찾기</button>
                </div>
                <div>
                    <input type="text" name="address" class="form-control width-2xl mgt5" value="<?= gd_isset($data['address']); ?>" readonly="readonly"/>
                    <input type="text" name="addressSub" class="form-control width-2xl mgt5" value="<?= gd_isset($data['addressSub']); ?>"/>
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
                <label class="radio-inline">
                    <input type="radio" name="chkSameUnstoringAddr" value="y" <?= $checked['chkSameUnstoringAddr']['y'] ?>/>사업장주소와 동일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="chkSameUnstoringAddr" value="n" <?= $checked['chkSameUnstoringAddr']['n'] ?>/>주소등록
                </label>
                <div class="form-inline div_unstoring_addr mgt5">
                    <input type="text" name="unstoringZonecode" value="<?= gd_isset($data['unstoringZonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="unstoringZipcode" value="<?= gd_isset($data['unstoringZipcode']) ?>"/>
                    <span id="unstoringZipcodeText" class="number <?php if (strlen($data['unstoringZipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $data['unstoringZipcode']; ?>)</span>
                    <button type="button" id="btn_unstoring_zipcode" class="btn btn-gray btn-sm">우편번호찾기</button>
                </div>
                <div class="div_unstoring_addr">
                    <input type="text" name="unstoringAddress" class="form-control width-2xl mgt5" value="<?= gd_isset($data['unstoringAddress']); ?>" readonly="readonly"/>
                    <input type="text" name="unstoringAddressSub" class="form-control width-2xl mgt5" value="<?= gd_isset($data['unstoringAddressSub']); ?>"/>
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
                <label class="radio-inline">
                    <input type="radio" name="chkSameReturnAddr" value="y" <?= $checked['chkSameReturnAddr']['y'] ?>/>사업장주소와 동일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="chkSameReturnAddr" value="x" <?= $checked['chkSameReturnAddr']['x'] ?>/>출고지주소와 동일
                </label>
                <label class="radio-inline">
                    <input type="radio" name="chkSameReturnAddr" value="n" <?= $checked['chkSameReturnAddr']['n'] ?>/>주소등록
                </label>
                <div class="form-inline div_return_addr mgt5">
                    <input type="text" name="returnZonecode" value="<?= gd_isset($data['returnZonecode']) ?>" class="form-control width-sm" readonly="readonly"/>
                    <input type="hidden" name="returnZipcode" value="<?= gd_isset($data['returnZipcode']) ?>"/>
                    <span id="returnZipcodeText" class="number <?php if (strlen($data['returnZipcode']) != 7) {
                        echo 'display-none';
                    } ?>">(<?php echo $data['returnZipcode']; ?>)</span>
                    <button type="button" id="btn_return_zipcode" class="btn btn-gray btn-sm">우편번호찾기</button>
                </div>
                <div class="div_return_addr">
                    <input type="text" name="returnAddress" class="form-control width-2xl mgt5" value="<?= gd_isset($data['returnAddress']); ?>" readonly="readonly"/>
                    <input type="text" name="returnAddressSub" class="form-control width-2xl mgt5" value="<?= gd_isset($data['returnAddressSub']); ?>"/>
                </div>
                <div class="div_return_addr">
                    <span id="returnZipcodeMsg" class="input_error_msg"></span>
                    <span id="returnAddressMsg" class="input_error_msg"></span>
                    <span id="returnAddressSubMsg" class="input_error_msg"></span>
                </div>
            </td>
        </tr>
        <tr>
            <th>
                메모
            </th>
            <td colspan="3">
                <textarea name="memo" rows="3" maxlength="250" class="form-control js-maxlength"><?=$data['memo']; ?></textarea>
            </td>
        </tr>
        </tbody>
    </table>

    <div class="table-title">
        사업자 정보
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
                업체명
            </th>
            <td>
                <input type="text" name="companyNm" value="<?= $data['companyNm'] ?>" maxlength="20" class="form-control width-sm"/>
            </td>
            <th>
                사업자등록번호
            </th>
            <td>
                <input type="text" name="businessNo" value="<?= $data['businessNo'] ?>" maxlength="20" class="form-control width-sm js-type-valid" data-regex="[^0-9\.-]*"/>
            </td>
        </tr>
        <tr>
            <th>
                대표자명
            </th>
            <td colspan="3">
                <input type="text" name="ceoNm" value="<?= $data['ceoNm'] ?>" maxlength="20" class="form-control width-sm"/>
            </td>
        </tr>
        <tr>
            <th>
                업태
            </th>
            <td>
                <input type="text" name="service" value="<?= $data['service'] ?>" maxlength="20" class="form-control width-sm"/>
            </td>
            <th>
                종목
            </th>
            <td>
                <input type="text" name="item" value="<?= $data['item'] ?>" maxlength="20" class="form-control width-sm"/>
            </td>
        </tr>
        </tbody>
    </table>


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
                메모
            </th>
            <th>
                추가/삭제
            </th>
        </tr>
        <?php
            foreach ($data['staff'] as $key => $val) {
                ?>
                <tr id="tr_staff_<?= $staffNum ?>">
                    <td>
                        <input type="text" name="staffName[]" maxlength="20" value="<?= $data['staff'][$key]->staffName ?>" class="form-control width-sm"/>
                    </td>
                    <td>
                        <input type="text" name="staffTel[]" value="<?= $data['staff'][$key]->staffTel ?>" maxlength="12" class="form-control width-sm js-number-only"/>
                    </td>
                    <td>
                        <input type="text" name="staffPhone[]" value="<?= $data['staff'][$key]->staffPhone ?>" maxlength="12" class="form-control width-sm js-number-only"/>
                    </td>
                    <td>
                        <input type="text" name="staffEmail[]" value="<?= $data['staff'][$key]->staffEmail ?>" maxlength="250" class="form-control width-sm"/>
                    </td>
                    <td>
                        <input type="text" name="staffMemo[]" value="<?= $data['staff'][$key]->staffMemo ?>" maxlength="250" class="form-control width-lg"/>
                    </td>
                    <td>
                        <?php
                        if ($key > 0 ) {
                            ?>
                            <button type="button"  class="btn_staff_del btn btn-gray btn-sm">삭제</button>
                            <?php
                        } else {
                            ?>
                            <button type="button" id="btn_staff_add" class="btn btn-gray btn-sm">추가</button>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            }
        ?>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    <!--
    var trCount = $('#table_staff tr').length;

    $(document).ready(function () {

        $("#frmPurchase").validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                purchaseNm: {
                    required: true,
                }
            },
            messages: {
                purchaseNm: {
                    required: '매입처명을 입력해주세요.',
                }
            }
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
            postcode_search('returnZonecode', 'returnAddress', 'returnZipcode');
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
        $(document).on('change', '#frmPurchase input:checkbox', function (e) {
            if ($(this).prop('checked') == true) {
                $(this).closest('td, th').children('input[type=hidden]').val('y');
            } else {
                $(this).closest('td, th').children('input[type=hidden]').val('');
            }
        });
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
        addStaff += '<td><input type="text" name="staffName[]" value="" maxlength="20" class="form-control width-sm"/></td>';
        addStaff += '<td><input type="text" name="staffTel[]" value="" maxlength="12" class="form-control width-sm js-number-only"/></td>';
        addStaff += '<td><input type="text" name="staffPhone[]" value="" maxlength="12" class="form-control width-sm js-number-only"/></td>';
        addStaff += '<td><input type="text" name="staffEmail[]" value="" maxlength="250" class="form-control width-sm"/></td>';
        addStaff += '<td><input type="text" name="staffMemo[]" value="" maxlength="250" class="form-control width-lg"/></td>';
        addStaff += '<td><button type="button" id="btn_staff_del_' + trCount + '" class="btn btn-gray btn-sm">삭제</button></td>';
        addStaff += '</tr>';
        $('#table_staff').append(addStaff);
        // 담당자 정보 삭제(동적 담당자 삭제)
        $('#btn_staff_del_' + trCount).on('click', function (e) {
            $(this).closest('tr').remove();
        });

        if ($('input.js-number-only').length > 0) {
            $('input.js-number-only').each(function () {
                $(this).number_only("d");
            });
        }
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
