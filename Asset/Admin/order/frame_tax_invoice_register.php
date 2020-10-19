<form id="frmTaxInvoice" name="frmTaxInvoice" action="tax_invoice_ps.php" method="post">
    <input type="hidden" name="mode" value="tax_invoice_register"/>
    <input type="hidden" name="sno" value="<?php echo gd_isset($orderData['sno']); ?>"/>
    <input type="hidden" name="taxMode" value="<?php echo gd_isset($orderData['mode']); ?>"/>
    <input type="hidden" name="orderNo" value="<?php echo gd_isset($orderData['orderNo']); ?>"/>
    <input type="hidden" name="processDt" value="<?php echo gd_isset($orderData['processDt']); ?>"/>
    <input type="hidden" name="cancelDt" value="<?php echo gd_isset($orderData['cancelDt']); ?>"/>
    <input type="hidden" name="taxPolicy" value="<?php echo gd_isset($orderData['taxPolicy']); ?>"/>
    <input type="hidden" name="requestNm" value="<?php echo gd_isset($orderData['requestNm']); ?>"/>
    <input type="hidden" name="memNo" value="<?php echo gd_isset($orderData['orderMemNo']); ?>"/>
    <input type="hidden" name="saveTaxInfoFl" value="">

    <div class="table-title gd-help-manual">
        세금계산서 정보
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-sm"/>
            <col class="width-md"/>
            <col class="width-sm"/>
            <col/>
        </colgroup>
        <tr>
            <th>발행 정보</th>
            <td colspan="3">
                <?php
                if ($orderData['tax']['paper'] == 'y' || $orderData['tax']['godobill'] == 'y') {
                    echo '<span class="text-primary">';
                    if ($orderData['tax']['paper'] == 'y') {
                        echo '<strong>[일반 세금계산서]</strong> ';
                    }
                    if ($orderData['tax']['godobill'] == 'y') {
                        echo '<strong>[전자 세금계산서(고도빌)]</strong> ';
                    }
                    echo '을(를) ' . ($orderData['tax']['step'] =='p' ? '결제완료일' : '배송완료일')  . ' 기준으로 발행이 가능합니다.</span>';
                } else {
                    echo '<span class="text-danger">[일반 세금계산서] 또는 [전자 세금계산서(고도빌)] 설정이 사용안함이여서 발행을 하실 수 없습니다.</span>';
                }
                ?>
            </td>
        </tr>
        <?php
        if ($orderData['mode'] == 'modify') {
            ?>
            <tr>
                <th>처리 일자</th>
                <td colspan="3">
                    <span class="notice-ref notice-sm">신청 : </span><span class="font-date"><?php echo gd_date_format('Y-m-d H:i', $orderData['regDt']); ?></span>
                    <?php
                    if (isset($orderData['statusFl']) == 'y' || $orderData['statusFl'] == 'c') {
                        echo '<br /><span class="notice-ref notice-sm">발행 : </span><span class="font-date">' . gd_date_format('Y-m-d H:i', $orderData['processDt']) . '</span>';
                    }
                    if (isset($orderData['statusFl']) == 'c') {
                        echo '<br /><span class="notice-ref notice-sm">취소 : </span><span class="font-date">' . gd_date_format('Y-m-d H:i', $orderData['cancelDt']) . '</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php
            if ($orderData['tax']['godobill'] == 'y' && empty($orderData['godobillCd']) === false) {
                ?>
                <tr>
                    <th>고도빌 전송 여부</th>
                    <td colspan="3">
                        <span class="notice-ref notice-sm">고도빌로 전송된 세금계산서 입니다. [고도빌 Code : <?php echo $orderData['godobillCd']; ?>]</span>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        <?php
        if ($orderData['mode'] == 'register') {
            ?>
            <tr>
                <th nowrap="nowrap">신청 가능 여부</th>
                <td colspan="3">
                    <?php
                    if ($orderData['tax']['paper'] == 'y' || $orderData['tax']['godobill'] == 'y') {
                        echo '<span class="text-primary">';
                        if ($orderData['tax']['paper'] == 'y') {
                            echo '[일반 세금계산서] ';
                        }
                        if ($orderData['tax']['godobill'] == 'y') {
                            echo ' [전자 세금계산서(고도빌)] ';
                        }
                        echo '신청 가능</span>';
                    } else {
                        echo '<span class="text-danger">신청 불가</span>';
                    }
                    ?>
                </td>
            </tr>
            <?php
        } else {
            ?>
            <tr>
                <th nowrap="nowrap">발행 여부</th>
                <td colspan="3">
                    <label>
                        <input type="radio" name="statusFl" value="r" <?php echo gd_isset($checked['statusFl']['r']); ?> />미발행
                    </label>
                    <label>
                        <input type="radio" name="statusFl" value="y" <?php echo gd_isset($checked['statusFl']['y']); ?> <?php if ($orderData['checkStatus'] === false && isset($orderData['statusFl']) != 'y') {
                            echo 'disabled="disabled"';
                        } ?> />발행
                    </label>
                    <label id="godobillSend"><?php if ($orderData['tax']['godobill'] == 'y') { ?>
                            <input type="checkbox" name="godobillSend" value="y">고도빌 전송<?php } ?></label>
                    <?php if (isset($orderData['statusFl']) != 'r') { ?>
                        <label>
                            <input type="radio" name="statusFl" value="c" <?php echo gd_isset($checked['statusFl']['c']); ?> />발행취소
                        </label>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        ?>
        <tr>
            <th class="require">상품명</th>
            <td colspan="3">
                <input type="text" name="requestGoodsNm" value="<?php echo gd_isset($orderData['orderGoodsNm'], gd_isset($orderData['requestGoodsNm'])); ?>" class="form-control width80p"/>
            </td>
        </tr>

        <tr>
            <th class="require">사업자번호</th>
            <td colspan="3">
                <div class="form-inline">
                    <input type="text" name="taxBusiNo" value="<?php echo gd_isset($orderData['taxBusiNo'], gd_remove_special_char($memberData['busiNo'])); ?>" maxlength="13" value="" class="form-control width-xs js-number" />
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">회사명</th>
            <td>
                <input type="text" name="taxCompany" value="<?php echo gd_isset($orderData['taxCompany'], $memberData['company']); ?>" class="form-control" maxlength="50"/>
            </td>
            <th class="require">대표자명</th>
            <td>
                <input type="text" name="taxCeoNm" value="<?php echo gd_isset($orderData['taxCeoNm'], $memberData['ceo']); ?>" class="form-control"/>
            </td>
        </tr>
        <tr>
            <th class="require">업태</th>
            <td>
                <input type="text" name="taxService" value="<?php echo gd_isset($orderData['taxService'], $memberData['service']); ?>" class="form-control"/>
            </td>
            <th class="require">종목</th>
            <td>
                <input type="text" name="taxItem" value="<?php echo gd_isset($orderData['taxItem'], $memberData['item']); ?>" class="form-control"/>
            </td>
        </tr>
        <?php
        if ($orderData['mode'] == 'modify') { ?>
            <tr>
                <th class="require">사업장 주소</th>
                <td colspan="3">
                    <div class="form-inline">
                        <input type="text" name="taxZonecode" value="<?php echo gd_isset($orderData['taxZonecode']); ?>" size="5" maxlength="5" class="form-control" readonly="readonly"/>
                        <input type="hidden" name="taxZipcode" value="<?php echo gd_isset($orderData['taxZipcode']); ?>"/>
                        <span id="taxZipcodeText" class="<?php if (strlen($orderData['taxZipcode']) != 7) {
                            echo 'display-none';
                        } ?>">(<?php echo $orderData['taxZipcode']; ?>)</span>
                        <input type="button" class="btn btn-sm btn-black" onclick="postcode_search('taxZonecode', 'taxAddress', 'taxZipcode');" value="우편번호찾기"/></span>
                    </div>
                    <div class="mgt5">
                        <input type="text" name="taxAddress" value="<?php echo gd_isset($orderData['taxAddress']); ?>" class="form-control" />
                    </div>
                    <div class="mgt5">
                        <input type="text" name="taxAddressSub" value="<?php echo gd_isset($orderData['taxAddressSub']); ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <th class="require">사업장 주소</th>
                <td colspan="3">
                    <div class="form-inline">
                        <input type="text" name="taxZonecode" value="<?php echo gd_isset($memberData['comZonecode']); ?>" size="5" maxlength="5" class="form-control" readonly="readonly"/>
                        <input type="hidden" name="taxZipcode" value="<?php echo gd_isset($memberData['comZipcode']); ?>"/>
                        <span id="taxZipcodeText" class="<?php if (strlen($memberData['comZipcode']) != 7) {
                            echo 'display-none';
                        } ?>">(<?php echo $memberData['comZipcode']; ?>)</span>
                        <input type="button" class="btn btn-sm btn-black" onclick="postcode_search('taxZonecode', 'taxAddress', 'taxZipcode');" value="우편번호찾기"/></span>
                    </div>
                    <div class="mgt5">
                        <input type="text" name="taxAddress" value="<?php echo gd_isset($memberData['comAddress']); ?>" class="form-control" readonly="readonly"/>
                    </div>
                    <div class="mgt5">
                        <input type="text" name="taxAddressSub" value="<?php echo gd_isset($memberData['comAddressSub']); ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th class="require">발행 이메일</th>
            <td colspan="3">
                <input type="text" name="taxEmail" value="<?php echo gd_isset($orderData['taxEmail']); ?>" class="form-control"/>
            </td>
        </tr>
        <?php   if ($orderData['mode'] == 'modify') { ?>
        <tr>
            <th class="require js-issue">발행 일자</th>
            <td colspan="3">
                <input type="text" name="issueDt" value="<?php echo gd_isset($orderData['issueDt']); ?>" class="form-control"/>
            </td>
        </tr>
        <?php } ?>
    </table>

    <div class="table-title gd-help-manual">
        관리자 메모 <small>관리자 메모를 작성합니다.</small>
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>관리자 메모</th>
            <td>
                <textarea name="adminMemo" rows="6" class="form-control"><?php echo gd_isset($orderData['adminMemo']); ?></textarea>
            </td>
        </tr>
    </table>

    <div class="table-btn">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <input type="submit" value="저장" class="btn btn-lg btn-black"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmTaxInvoice').validate({
            submitHandler: function(form) {
                if ($('input[name="statusFl"]:checked').val() == 'y') {
                    var msgTitle = '일반';
                    if ($('input[name="godobillSend"]').prop('checked') === true) {
                        msgTitle = '전자';
                    }
                    var msg = msgTitle + ' 세금계산서를 발행하시겠습니까?<br /><br /><label><input type="checkbox" id="saveTaxInfoFl" value="y" /> 현재 발행정보를 고객의 세금계산서 신청정보에 반영</label>';
                    if (!$('input[name="taxAddressSub"]').val()) {
                        msg += '<br /><br /><span class="notice-danger">사업장 주소가 모두 입력되지 않았습니다. 확인 후 발행하시기 바랍니다.</span>';
                    }
                    dialog_confirm(msg, function (result) {
                        if (result) {
                            <?php if ($orderData['tax']['paper'] == 'y' || $orderData['tax']['godobill'] == 'y') { ?>
                            form.submit();
                            <?php } else {?>
                            $.warnUI('신청여부 체크', '[일반 세금계산서] 또는 [고도빌] 설정이 사용안함이여서 신청을 하실 수 없습니다.');
                            return false;
                            <?php }?>
                        } else {
                            $('input[name="saveTaxInfoFl"]').val('');
                        }
                    });
                    return false;
                } else {
                    <?php if ($orderData['tax']['paper'] == 'y' || $orderData['tax']['godobill'] == 'y') { ?>
                    var msg = '선택한 세금계산서의 정보를 수정하시겠습니까?<br /><br /><label><input type="checkbox" id="saveTaxInfoFl" value="y" /> 수정된 정보를 고객의 세금계산서 신청정보에 반영</label>';
                    dialog_confirm(msg, function (result) {
                        if (result) {
                            form.submit();
                        } else {
                            $('input[name="saveTaxInfoFl"]').val('');
                        }
                    });
                    return false;
                    <?php } else {?>
                    $.warnUI('신청여부 체크', '[일반 세금계산서] 또는 [고도빌] 설정이 사용안함이여서 신청을 하실 수 없습니다.');
                    return false;
                    <?php }?>
                }
            },
            rules: {
                requestNm: {
                    required: true
                },
                requestGoodsNm: {
                    required: true
                },
                taxBusiNo: {
                    required: true
                },
                taxCompany: {
                    required: true
                },
                taxCeoNm: {
                    required: true
                },
                taxService: {
                    required: true
                },
                taxItem: {
                    required: true
                },
                taxEmail: {
                    required: true,
                    email: true
                },
                taxZonecode: {
                    required: true
                },
                <?php   if ($orderData['mode'] == 'modify') { ?>
                issueDt: {
                    required: function (input) {
                        var required = false;
                        if ($('input:radio[name="statusFl"]:checked').val() == 'y') {
                            required = true;
                        }
                        return required;
                    }
                },
                <?php } ?>
                taxAddress: {
                    required: true
                }
            },
            messages: {
                requestGoodsNm: {
                    required: '상품명을 입력하세요.'
                },
                taxBusiNo: {
                    required: '사업자번호를 입력하세요.'
                },
                taxCompany: {
                    required: '회사명을 입력하세요.'
                },
                taxCeoNm: {
                    required: '대표자명을 입력하세요.'
                },
                taxService: {
                    required: '업태를 입력하세요.'
                },
                taxItem: {
                    required: '종목을 입력하세요.'
                },
                taxEmail: {
                    required: '발행 이메일을 정확하게 입력하여 주세요.'
                },
                taxZonecode: {
                    required: '사업장 주소를 입력하세요.'
                },
                <?php   if ($orderData['mode'] == 'modify') { ?>
                issueDt: {
                    required: '발행일을 입력하세요.'
                },
                <?php } ?>
                taxAddress: {
                    required: '사업장 주소를 입력하세요.'
                }
            }
        });

        $(document).on('click', '#saveTaxInfoFl', function(){
            var value = this.checked === true ? 'y' : '';

            $('input[name="saveTaxInfoFl"]').val(value);
        });

        // 숫자 체크
        $('input[name=\'taxBusiNo[]\']').number_only();

        // 클릭 시 발행 여부에 따른 처리
        $('input:radio[name="statusFl"]').click(function (e) {
            setStatusFl();
        });

        // 로딩 시 발행 여부에 따른 처리
        setStatusFl();

    });

    // 발행 여부에 따른 처리
    function setStatusFl() {
        if ($('input:radio[name="statusFl"]:checked').val() == 'r') {
            $('#godobillSend').hide();
            $('.js-issue').removeClass('require');
        } else if ($('input:radio[name="statusFl"]:checked').val() == 'y') {
            $('#godobillSend').show();
            $('.js-issue').addClass('require');
        } else if ($('input:radio[name="statusFl"]:checked').val() == 'c') {
            $('#godobillSend').hide();
            $('.js-issue').removeClass('require');
        }
    }

    /**
     * 공급액,부가세 계산
     */
    function autoPrice() {
        // 발행액
        var settlePrice = $('input[name=\'settlePrice\']').val();
        if (settlePrice == '') {
            settlePrice = 0;
        }
        settlePrice = parseInt(settlePrice);

        // 과세(수동)
        var supplyPrice = $('input[name=\'supplyPrice\']').val();
        if (supplyPrice == '') {
            supplyPrice = 0;
        }
        supplyPrice = parseInt(supplyPrice);
        if (supplyPrice >= settlePrice) {
            supplyPrice = settlePrice;
        }
        var taxPrice = settlePrice - supplyPrice;

        $('input[name=\'supplyPrice\']').val(supplyPrice)
        $('input[name=\'taxPrice\']').val(taxPrice)
    }
    //-->
</script>
