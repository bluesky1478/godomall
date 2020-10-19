<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?></h3>
    <div class="btn-group">
        <a href="comeback_coupon_regist.php" class="btn btn-red-line">컴백쿠폰 발송 등록</a>
    </div>
</div>

<div class="mgt10 design-notice-box mgb20">
    주문 이력이 있는 고객 중 한동안 주문을 하지 않았던 고객에게 컴백 쿠폰을 발행하고 SMS를 발송해 재 방문을 유도하세요!<br />
    쿠폰만 발급하거나 SMS만 발송 할 수도 있습니다.
</div>

<div class="table-title gd-help-manual">
    SMS 발송 정보
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tr>
        <th>SMS 잔여 포인트</th>
        <td class="form-inline">
            <span class="number text-darkred bold"><?php echo number_format($smsPoint, 1) ?></span> 포인트
            <input type="hidden" id="sms-point" value="<?php echo $smsPoint ?>">
            <button type="button" class="btn btn-gray btn-sm" onclick="show_popup('../member/sms_charge.php?popupMode=yes')">SMS 포인트 충전하기</button>
            <?php if ($smsPoint === '0') { ?>
            <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 포인트 정보가 정상 출력됩니다.</div>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th>SMS 발신번호</th>
        <td class="form-inline">
            <?php if ($smsPreRegister === false) { ?>
                <div>
                    <input type="hidden" name="smsCallNum" value=""/>
                    <span class="smsCallNumText"><span class="text-darkred">등록된 SMS 발신번호가 없습니다.</span></span>
                    <?php if (gd_is_provider() === false) {?>
                        <a href="https://www.godo.co.kr/mygodo/sms/intro.gd" target="_blank" class="btn btn-gray btn-sm">발신번호 등록하기</a>
                    <?php }?>
                </div>
                <div class="notice-info">
                    발신번호 사전등록제 : (전기통신사업법 제 84조의 2) 거짓으로 표시된 전화번호로 인한 이용자 피해 예방을 위해 사전 등록한 발신번호로만 SMS를 발송하실 수 있습니다.
                    <a href="https://www.godo.co.kr/customer/board-view.gd?type=notice&idx=1247" target="_blank" class="snote bold">자세히보기 ></a>
                </div>
            <?php } elseif ($smsPreRegister === 'reset') { ?>
                <div>
                    <input type="hidden" name="smsCallNum" value=""/>
                    <span class="smsCallNumText"><span class="text-darkred">기 설정된 SMS 발신번호는 사전 등록된 번호가 아닙니다.</span></span>
                    <?php if (gd_is_provider() === false) {?>
                        <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 선택하기</button>
                    <?php }?>
                </div>
            <?php } elseif ($smsPreRegister === 'empty') { ?>
                <div>
                    <input type="hidden" name="smsCallNum" value=""/>
                    <span class="smsCallNumText"><span class="text-darkred">SMS 발신번호를 선택해주세요.</span></span>
                    <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 선택하기</button>
                </div>
            <?php } elseif ($smsPreRegister === true) { ?>
                <input type="hidden" name="smsCallNum" value="<?php echo gd_isset($smsAutoData['smsCallNum']) ?>"/>
                <span class="smsCallNumText number text-blue bold"><?php echo gd_number_to_phone($smsAutoData['smsCallNum']) ?></span>
                <?php if (gd_is_provider() === false) {?>
                    <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 변경하기</button>
                <?php }?>
            <?php } ?>
            <?php if ($smsPreRegister !== true) { ?>
            <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 발신번호 정보가 정상 출력됩니다.</div>
            <?php } ?>
        </td>
    </tr>
</table>

<form id="frmSearchCoupon" method="get" class="js-form-enter-submit">
    <div class="table-title">
        컴백쿠폰 발송 리스트
        <div class="pull-right form-inline">
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null, 'onchange="this.form.submit();"'); ?>
        </div>
    </div>
</form>

<form id="frmCouponList" action="../promotion/coupon_ps.php" method="post">
    <input type="hidden" name="mode" value="deleteComebackCouponList"/>
    <table class="table table-rows promotion-coupon-list">
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="chkCoupon[]"/></th>
            <th>번호</th>
            <th>제목</th>
            <th>쿠폰</th>
            <th>발송내용</th>
            <th>발송정보</th>
            <th>발급/발송</th>
            <th>등록자</th>
            <th>등록일</th>
            <th>관리</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false && is_array($data)) {
            foreach ($data as $key => $val) {
                ?>
                <tr class="text-center">
                    <td>
                        <input type="checkbox" name="chkCoupon[]" value="<?= $val['sno'] ?>" />
                    </td>
                    <td><?= number_format($page->idx--); ?></td>
                    <td><?= $val['title'] ?></td>
                    <td><?= $val['couponSet'] ?></td>
                    <td><?= $val['sendContents'] ?></td>
                    <td><?= $val['sendInfo'] ?></td>
                    <td><?= $val['sendAction'] ?></td>
                    <td><?= $val['managerId'] ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']) ?></td>
                    <td><?= $val['btnAction'] ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="10" class="no-data">
                    검색된 쿠폰이 없습니다.
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white js-copy-coupon">선택 복사</button>
            <button type="button" class="btn btn-white js-delete-coupon">선택 삭제</button>
        </div>
    </div>
</form>

<div class="center"><?= $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // SMS 사전 등록 발신번호 선택하기
        $('.js-sms-call-number').click(function (e) {
            var params = {
                returnInput: 'smsCallNum'
            };
            $.get('../member/layer_sms_call_number_select.php', params, function (data) {
                BootstrapDialog.show({
                    title: 'SMS 발신번호 목록',
                    message: $(data),
                    closable: true
                });
            });
        });

        $('#frmCouponList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chkCoupon[]': 'required',
            },
            messages: {
                'chkCoupon[]': {
                    required: "쿠폰을 선택해 주세요.",
                }
            }
        });

        $('.js-delete-coupon').click(function (e) {
            if ($('#frmCouponList').valid(e)) {
                var tempSendCount = 0;
                $('input[name="chkCoupon[]"]:checked').each(function (i) {
                    if ($('#dataInfo' + $(this).val()).attr('data-send') == 'y') {
                        tempSendCount++;
                    }
                });
                if (tempSendCount > 0) {
                    var message = '선택된 ' + $('input[name*=chkCoupon]:checked').length + '개의 컴백쿠폰을 정말로 삭제 하시겠습니까?<br />삭제시 쿠폰 발급내역 및 SMS 발송결과를 확인할 수 없으며 삭제된 정보는 복구되지 않습니다.'
                } else {
                    var message = '선택된 ' + $('input[name*=chkCoupon]:checked').length + '개의 컴백쿠폰을 정말로 삭제 하시겠습니까?<br />삭제시 정보는 복구 되지 않습니다.'
                }
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: '쿠폰삭제',
                    message: message,
                    closable: false,
                    callback: function (result) {
                        if (result) {
                            $('input[name=mode]').val('deleteComebackCouponList');
                            $('#frmCouponList').submit();
                        }
                    }
                });
            }
        });

        $('.js-copy-coupon').click(function (e) {
            if ($('#frmCouponList').valid()) {
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: '쿠폰복사',
                    message: "동일한 발송조건으로 새로운 컴백쿠폰이 등록됩니다.<br /><br /><p class='notice-danger'>이미 동일한 쿠폰을 보유하고 있는 회원에게는 쿠폰이 발급되지 않으며,<br/>사용기간이 지난 쿠폰은 사용할 수 없으므로 수정 후 발급하시기 바랍니다.</p>",
                    closable: false,
                    callback: function (result) {
                        if (result) {
                            $('input[name=mode]').val('copyComebackCouponList');
                            $('#frmCouponList').submit();
                        }
                    }
                });
            }
        });

        function sendAction(sno, couponNo) {
            var params = {
                mode : 'ajaxComebackCouponActionCount',
                sno: sno,
                couponNo: couponNo
            };
            var smsCount = $('#sms-point').val();
            var smsFl = $('#dataInfo' + sno).attr('data-smsFl');

            $.ajax({
                method: "POST",
                cache: false,
                url: "coupon_ps.php",
                data: params,
                success: function (data) {
                    if (data.count > 0) {
                        if (data.checkCoupon) {
                             var message = '';
                            var is_sms_fl = smsFl === 'y';
                            if (is_sms_fl) {
                                if (smsCount < data.count) {
                                    BootstrapDialog.alert({
                                        title: '',
                                        message: 'SMS 잔여 포인트가 부족하여 SMS를 발송할 수 없습니다.<br/>SMS 포인트 충전 후 발송하시기 바랍니다.',
                                        closable: true,
                                    });
                                    return false;
                                }
                                if ($('input[name=smsCallNum]').length == 0 || $('input[name=smsCallNum]').val() == '') {
                                    BootstrapDialog.alert({
                                        title: '',
                                        message: '등록된 SMS 발신번호가 없습니다. SMS 발신번호를 등록해주세요.<br>발신번호 사전등록제에 따라 거짓으로 표시된 전화번호로 인한 이용자 피해 예방을 위해 사전 등록한 발신번호로만 SMS를 발송하실 수 있습니다.',
                                        closable: true,
                                    });
                                    return false;
                                }
                                message = data.count + '명의 회원에게 컴백 쿠폰을 정말로 발급 하시겠습니까?<br /><br /><p class="notice-danger">SMS 잔여 포인트가 부족한 경우 부족한 포인트만큼 문자발송이 되지않습니다.<br/><span style="color:#2a2a2a">[회원>SMS 관리>SMS 발송 내역 보기]에서 발송결과를 꼭 확인하시기 바랍니다.</span></p>';
                            } else {
                                message = data.count + '명의 회원에게 컴백 쿠폰을 정말로 발급 하시겠습니까?';
                            }
                            BootstrapDialog.confirm({
                                title: '',
                                message: message,
                                closable: false,
                                callback: function (result) {
                                    if (result) {
                                        $('input[name="chkCoupon[]"][value=' + sno + ']').prop('checked', true);
                                        $('input[name="chkCoupon[]"]:checked').each(function (i) {
                                            if ($(this).val() != sno) {
                                                $(this).prop('checked', false);
                                            }
                                        });
                                        $('input[name=mode]').val('sendComebackCoupon');
                                        if (is_sms_fl) {
                                            if(data.count >= 100) {
                                                $.get('../share/layer_sms_password.php?mode=input', function () {
                                                    console.log(arguments);
                                                    BootstrapDialog.show({
                                                        title: 'SMS 인증번호 변경하기',
                                                        size: BootstrapDialog.SIZE_WIDE,
                                                        message: arguments[0]
                                                    });
                                                });
                                            } else {
                                                $('#frmCouponList').append("<input type='hidden' name='passwordCheckFl' value='n' />");
                                                $('#frmCouponList').submit();
                                            }
                                        } else {
                                            $('#frmCouponList').submit();
                                        }
                                    }
                                }
                            });
                         } else {
                            var message = '';
                            if (smsCount > data.count) {
                                message = '등록하신 쿠폰의 사용기간이 만료되어 쿠폰을 발급할 수 없습니다.<br/>수정/등록 후 발송하시기 바랍니다.';
                            } else {
                                message = '등록하신 쿠폰의 사용기간이 만료. SMS 포인트 부족으로 발송할 수 없습니다.<br/>쿠폰 수정 및 SMS 포인트 충전 후 발송하시기 바랍니다.';
                            }
                            BootstrapDialog.alert({
                                title: '',
                                message: message,
                                closable: true,
                            });
                        }
                    } else {
                        var message = '컴백 쿠폰 조건에 해당하는 발송대상이 없습니다.';
                        if (!data.checkCoupon) {
                            message = '발급하고자 하는 쿠폰이 발급종료 상태입니다.<br/>선택된 쿠폰을 수정한 후 다시 시도해주세요.';
                        }
                        BootstrapDialog.alert({
                            title: '',
                            message: message,
                            closable: true,
                        });
                    }
                },
                error: function (data) {
                    alert('error');
                }
            });
            return false;
        };

        // 쿠폰 적용 해당 버튼 선택 시
        $('[id^=view]').click(function (e) {
            var code = (this.id).split('view');
            code = code[1];
            if (code.substr(0, 1) == 'A') {
                sendAction($('#view' + code).attr('data-sno'), $('#view' + code).attr('data-couponNo'));
            } else {
                layer_register(code);
            }
        });
    });

    /**
     * 구매 상품 범위 등록 / 예외 등록 Ajax layer
     *
     * @param string codeStr 타입
     * @param string modeStr 예외 여부
     */
    function layer_register(codeStr, isDisabled) {
        var layerFormID = 'couponRangeForm';
        var addParam = '';
        var fileStr = '';
        var code = codeStr.substr(0, 1);

        if (code == 'C') {
            var parentFormID = 'target' + codeStr;
            var dataFormID = 'id' + codeStr;
            var dataInputNm = 'coupon' + codeStr;
            var layerTitle = '쿠폰 상세보기';
            var dataSno = $('#view' + codeStr).attr('data-sno');
            var dataCouponNo = $('#view' + codeStr).attr('data-coupon-no');
            var searchMode = '';
            var searchValue = '';
            var useType = '';
            fileStr = 'comeback_coupon_coupon';
            mode = 'simple';
        } else if (code == 'S') {
            var parentFormID = 'target' + codeStr;
            var dataFormID = 'id' + codeStr;
            var dataInputNm = 'coupon' + codeStr;
            var dataSno = $('#view' + codeStr).attr('data-sno');
            var dataCouponNo = '';
            var searchMode = '';
            var searchValue = '';
            var useType = '';
            var layerTitle = '발송내용 상세보기';
            fileStr = 'comeback_coupon_sms';
            mode = 'simple';
        } else if (code == 'M') {
            var parentFormID = 'target' + codeStr;
            var dataFormID = 'id' + codeStr;
            var dataInputNm = 'coupon' + codeStr;
            var dataSno = $('#view' + codeStr).attr('data-sno');
            var dataCouponNo = '';
            var searchMode = '';
            var searchValue = '';
            var useType = '';
            var dataCouponNo = '';
            var layerTitle = '컴백쿠폰 발송대상';
            fileStr = 'comeback_coupon_member';
            mode = 'simple';
        } else if (code == 'R') {
            var parentFormID = 'target' + codeStr;
            var dataFormID = 'id' + codeStr;
            var dataInputNm = 'coupon' + codeStr;
            var dataSno = $('#view' + codeStr).attr('data-sno');
            var searchMode = 'all';
            var searchValue = '';
            var issueCouponFl = 'all';
            var sendSmsFl = 'all';
            var useType = 'all';
            var dataCouponNo = '';
            var layerTitle = '컴백쿠폰 발송 및 사용내역';
            fileStr = 'comeback_coupon_result';
            mode = 'simple';
        }

        var addParam = {
            "mode": mode,
            "layerFormID": layerFormID,
            "parentFormID": parentFormID,
            "dataFormID": dataFormID,
            "dataInputNm": dataInputNm,
            "layerTitle": layerTitle,
            "dataSno": dataSno,
            "dataCouponNo": dataCouponNo,
            "searchMode": searchMode,
            "searchValue": searchValue,
            "issueCouponFl": issueCouponFl,
            "sendSmsFl": sendSmsFl,
            "useType": useType,
            "dataCouponNo": dataCouponNo,
            "disabled": isDisabled,
//            "callFunc": "",
        };

        layer_add_info(fileStr, addParam);
    }

    function sms_password_callback(password) {
        var $frm = $('#frmCouponList');
        $frm.append('<input type="hidden" name="password" value="' + password + '"/>');
        $frm.submit();
    }
    //-->
</script>
