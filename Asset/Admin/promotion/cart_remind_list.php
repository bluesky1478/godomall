<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <button type="button" class="btn btn-red-line js-register">장바구니 알림 등록</button>
    </div>
</div>
<form id="frmSmsAuto" name="frmSmsAuto" method="post" action="sms_ps.php">
    <input type="hidden" name="mode" value="smsAuto" />
    <div class="table-title">
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
                <?php if ($smsPoint === '0') { ?>
                <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 발신번호 정보가 정상 출력됩니다.</div>
                <?php } ?>
            </td>
        </tr>
    </table>
</form>

<form id="frmCartRemind" name="frmCartRemind" method="post" action="cart_remind_ps.php">
    <input type="hidden" name="mode" value="deleteCartRemind"/>
    <table class="table table-rows promotion-coupon-list">
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="chkCartRemind[]"/></th>
            <th>번호</th>
            <th>장바구니알림명</th>
            <th>등록일</th>
            <th>등록자</th>
            <th>발송유형</th>
            <th>발송대상</th>
            <th>발송회원등급</th>
            <th>재고량발송제한</th>
            <th>쿠폰</th>
            <th>발송내용</th>
            <th>수정</th>
            <th>발송관리</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($cartRemindData) === false && is_array($cartRemindData)) {
            foreach ($cartRemindData as $key => $val) {
                ?>
                <tr class="text-center">
                    <td><input type="checkbox" name="chkCartRemind[]" value="<?= $val['cartRemindNo'] ?>"/></td>
                    <td><?= number_format($page->idx--); ?></td>
                    <td><?= $val['cartRemindNm'] ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']) ?></td>
                    <td><?= $val['cartRemindInsertAdminId'].$val['deleteText'] ?></td>
                    <td><?= $convertArrData[$key]['cartRemindType'] ?></td>
                    <td><?= $convertArrData[$key]['cartRemindPeriod'] ?></td>
                    <td>
                        <?php
                        if ($convertArrData[$key]['cartRemindApplyMemberGroup']) {
                            foreach ($convertArrData[$key]['cartRemindApplyMemberGroup'] as $memKey => $memVal) {
                                echo $memVal['name'] . '<br/>';
                            }
                        } else {
                            echo "전체";
                        }
                        ?>
                    </td>
                    <td><?= $convertArrData[$key]['cartRemindGoodsStock'] ?></td>
                    <td><?= $convertArrData[$key]['cartRemindCoupon']; ?></td>
                    <td>
                        <button type="button" class="btn btn-sm btn-white js-layer-message" data-no="<?= $val['cartRemindNo'] ?>">상세보기</button>
                    </td>
                    <td>
                        <a href="./cart_remind_regist.php?cartRemindNo=<?= $val['cartRemindNo'] ?>" class="btn btn-sm btn-white">수정</a>
                    </td>
                    <td><?= $convertArrData[$key]['sendMemberCount'] ?><br/><?= $convertArrData[$key]['sendPoint'] ?><br/><?= $convertArrData[$key]['sendAction'] ?></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="13" class="no-data">
                    검색된 장바구니 알림이 없습니다.
                </td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="submit" class="btn btn-sm btn-white">선택 삭제</button>
        </div>
    </div>
</form>

<script type="text/javascript">
    <!--
    var cartRemindNo;
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

        $('#frmCartRemind').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'chkCartRemind[]': 'required',
            },
            messages: {
                'chkCartRemind[]': {
                    required: "삭제할 장바구니 알림을 선택해 주세요.",
                }
            }
        });

        // 등록
        $('.js-register').click(function () {
            location.href = './cart_remind_regist.php';
        });
        // 쿠폰 정보 보기
        $('.js-coupon-detail').click(function () {
            window.open('./coupon_regist.php?couponNo=' + $(this).data('couponno'));
        });
        // 수동 발송 하기
        $('.js-manual-send').click(function () {
            cartRemindNo = $(this).data('no');
            var cnt = $(this).data('cnt');
            dialog = BootstrapDialog.confirm({
                name: "layer_cart_remind_manual_send",
                title: "수동 발송",
                size: BootstrapDialog.SIZE_WIDE,
                message: '장바구니 알림을 발송하시겠습니까?',
                callback: function (result) {
                    // 확인 버튼 클릭시
                    if (result) {
                        if(cnt >= 100) {
                            $.get('../share/layer_sms_password.php?mode=input', function () {
                                BootstrapDialog.show({
                                    title: 'SMS 인증번호 입력',
                                    size: BootstrapDialog.SIZE_WIDE,
                                    message: arguments[0]
                                });
                            });
                        } else {
                            menual_send()
                        }
                    }
                }
            });


        });
        // 자동 발송 중지하기
        $('.js-auto-state').click(function () {
            var params = {
                mode: 'setCartRemindAutoState',
                type: $(this).data('type'),
                cartRemindNo: $(this).data('no'),
            };
            $.ajax({
                method: "POST",
                cache: false,
                url: "./cart_remind_ps.php",
                data: params,
                success: function (data) {
                    result = JSON.parse(data);
                    dialog_alert(result['msg'], '알림', {isReload: true});
                },
                error: function (data) {
                    alert('변경 실패');

                }
            });
        });

        // 발송내용 보기
        $('.js-layer-message').click(function () {
            var loadChk = $('div#formCartRemindMessage').length;
            $.get('./layer_cart_remind_message.php?cartRemindNo=' + $(this).data('no'), {}, function (data) {
                if (loadChk === 0) {
                    data = '<div id="#formCartRemindMessage">' + data + '</div>';
                }

                dialog = BootstrapDialog.show({
                    name: "layer_cart_remind_message",
                    title: "발송내용 상세보기",
                    size: BootstrapDialog.SIZE_WIDE,
                    message: $(data),
                    closable: true
                });
            });
        });
    });
    function sms_password_callback() {
        menual_send();
    }
    function menual_send() {
        var params = {
            mode: 'sendCartRemindManual',
            cartRemindNo: cartRemindNo,
        };
        $.ajax({
            method: "POST",
            cache: false,
            url: "./cart_remind_ps.php",
            data: params,
            success: function (data) {
                result = JSON.parse(data);
                alert(result['msg']);
            },
            error: function (data) {
                alert('전송 실패');

            }
        });
    }
    //-->
</script>
