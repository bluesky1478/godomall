<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="submit" value="회원등급 등록" class="btn btn-red-line" id="btnAdd"/>
</div>
<form id="frmSetup" action="../member/member_group_ps.php" method="post">
    <!-- 등급명칭/가입등급설정 -->
    <div class="table-title gd-help-manual">
        회원등급 노출이름 변경
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
        </colgroup>
        <tr>
            <th>쇼핑몰페이지 노출이름</th>
            <td class="form-inline">
                <input type="text" name="grpLabel" value="<?= gd_isset($groupData['grpLabel']); ?>" class="form-control"/>
                <button type="submit" class="btn btn-white btn-sm">변경</button>
            </td>
        </tr>
    </table>
</form>
<form id="frmSetupCoupon" action="../member/member_group_ps.php" method="post">
    <div>
    <div class="table-title gd-help-manual" style="float:left; width:50%">
        회원등급 쿠폰 혜택 설정
    </div>
    <div style="text-align:right; float:left; width:50%">
        <input type="submit" value="저장" class="btn btn-red">
    </div>
    </div>
    <table class="table table-cols" style="margin-bottom: 0px;">
        <colgroup>
            <col class="width-lg"/>
        </colgroup>
        <tr>
            <th>쿠폰 혜택 지급 시<br />쿠폰 발급 시점 설정</th>
            <td class="form-inline">
                <div><label><input type="checkbox" name="couponConditionComplete" value="y" <?=$checked['couponConditionComplete']?>>회원등급 평가 완료 시 발급</label>(<label><input type="checkbox" name="couponConditionCompleteChange" value="y" <?=$checked['couponConditionCompleteChange']?>>등급 변경 시에만 발급</label>)</div>
                <div><label><input type="checkbox" name="couponConditionManual" value="y" <?=$checked['couponConditionManual']?>>회원등급을 직접 수정 시 발급</label></div>
                <div><label><input type="checkbox" name="couponConditionExcel" value="y" <?=$checked['couponConditionExcel']?>>회원 엑셀 업로드로 회원등급 업데이트 시 발급</label>(<label><input type="checkbox" name="couponConditionExcelChange" value="y" <?=$checked['couponConditionExcelChange']?>>등급변경 시에만 발급</label>)</div>
                <div class="notice-info">'회원등급 평가 완료 시 발급' 설정 시 수동 발급은 [회원등급 수동평가] 버튼 클릭 시, 자동 발급은 등급 평가일에 쿠폰이 발급됩니다.</div>
                <div class="notice-info">'등급 변경 시에만 발급' 체크를 하면, 해당 이벤트 발생 시 회원등급이 변경된 회원들에게만 쿠폰이 발급됩니다.</div>
            </td>
        </tr>
    </table>
    <div class="notice-info"  style="margin-bottom: 20px;">회원 등급 변경으로 인한 쿠폰 혜택 지급 시 자동 SMS 항목인 '회원등급 변경안내와  '수동쿠폰 발급 안내'가 모두 발송될 수 있으니, 발송 설정을 확인해주세요. <a href="/member/sms_auto.php#promotion" target="_blank" class="btn-link-underline">자동 SMS 설정 ></a></div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value=""/>
    <input type="hidden" id="sno" name="sno" value=""/>

    <div class="table-title gd-help-manual">회원등급 리스트</div>
    <div class="table-header form-inline">
    </div>
    <div class="table-action mgb0 mgt0 bg-clear">
        <div class="pull-left">
            <div class="btn-group">
                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                    맨아래
                </button>
                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                    아래
                </button>
                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                    위
                </button>

                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                    맨위
                </button>
            </div>
            <button type="button" class="btn btn-white" id="btnSaveOrder">순서저장</button>
        </div>
    </div>
    <table class="table table-rows" id="tblGroupList">
        <colgroup>
            <col class="width-3xs"/>
            <col class="width-3xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col class="width-xs"/>
            <col class="width-xs"/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input type="checkbox" id="chk_all" class="js-checkall"
                       data-target-name="chk"/>
            </th>
            <th>등급순서</th>
            <th>회원등급명</th>
            <th>회원수</th>
            <th>등급혜택</th>
            <th>이용결제수단</th>
            <th>등록일</th>
            <th>등록자</th>
            <th>정보수정</th>
        </tr>
        </thead>
        <tbody id="listId">
        <?php
        if (gd_isset($data) && is_array($data)) {
            $cnt = count($data);
            $defaultGroupSno = gd_get_default_group();
            foreach ($data as $idx => $val) {
                $isDefault = $defaultGroupSno == $val['sno'];
                gd_isset($val['groupIcon'], 'ico_noimg_16.gif');
                $groupIcon = sprintf('<img src="%s/%s" alt="아이콘" class="img-thumbnail" />', UserFilePath::data('commonimg')->www(), 'ico_noimg_16.gif');
                if ($val['groupMarkGb'] == 'icon' && $val['groupIcon'] != 'ico_noimg_16.gif') {
                    $groupIcon = sprintf('<img src="%s/%s" alt="아이콘" class="img-thumbnail" />', UserFilePath::icon('group_icon')->www(), $val['groupIcon']);
                } elseif ($val['groupMarkGb'] == 'upload') {
                    $groupIcon = sprintf('<img src="%s/%s" alt="아이콘" class="img-thumbnail" />', UserFilePath::icon('group_icon')->www(), $val['groupIconUpload']);
                }

                // 등급 혜택
                $benefitStr = '';
                if (in_array($val['fixedOrderTypeDc'], ['goods', 'order', 'option']) === true) {
                    if ($val['dcType'] == 'percent' && empty($val['dcPercent']) === false && $val['dcPercent'] > 0) {
                        $benefitStr .= sprintf(__('추가 %s 할인 '), $val['dcPercent'] . '%');
                    }
                } else if ($val['fixedOrderTypeDc'] == 'brand') {
                    array_push($tmp, sprintf(__('추가 브랜드별 할인')) . "\n");
                    $benefitStr .= '추가 브랜드별 할인 <input type="button" data-sno="' . $val['sno'] . '" value="보기" class="btn btn-sm btn-gray js-dc-brand"/>' . '<br />';
                }

                $tmp = gd_get_benefit_string($val);
                $benefitStr .= '<ul class="list-inline"><li>' . str_replace("\n", '</li><li>', (empty($tmp) ? '없음' : $tmp)) . '</li></ul>';
                if(!empty($val['coupon'])){
                    $couponStr = '<br />'.$val['coupon'].' 쿠폰 발급';
                }else{
                    $couponStr = '';
                }

                $checkboxSno = '<input type="checkbox" name="chk[]" value="' . $val['sno'] . '"/>';
                $groupIconWithName = $groupIcon . $val['groupNm'];
                if ($isDefault) {
                    $checkboxSno = '<input type="checkbox" name="chk[]" value="' . $val['sno'] . '" disabled="disabled"/>';
                    $checkboxSno .= '<input type="hidden" name="chk[]" value="' . $val['sno'] . '"/>';
                    $groupIconWithName = $groupIcon . $val['groupNm'] . '<div style="padding-top:5px"><span class="notice-ref notice-sm">[가입회원등급]</span></div>';
                }
                ?>
                <tr class="center formRow">
                    <td>
                        <?= $checkboxSno ?>
                    </td>
                    <td>
                        <?= $cnt-- ?>
                    </td>
                    <td>
                        <?= $groupIconWithName ?>
                    </td>
                    <td><?= number_format($val['memCnt'] == null ? 0 : $val['memCnt']) ?></td>
                    <td><?= $benefitStr ?><?=$couponStr?></td>
                    <td><?= $settleGbs[$val['settleGb']] ?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']) ?></td>
                    <td>관리자<br/>(<?= $val['regId'] ?>)<?= $val['deleteText'] ?></td>
                    <td>
                        <button class="btn btn-white btn-sm btn-modify" data-sno="<?= $val['sno']; ?>">수정</button>
                    </td>
                </tr>
                <?php
            }
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white" id="btnDelete">선택 삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white" id="btnAppraisal" data-system="<?= strtoupper($groupData['apprSystem']) ?>">회원등급 수동평가</button>
        </div>
    </div>
</form>
<script type="text/javascript">
    (function ($, _, window, document, undefined) {
        var $frmSetup, $frmSetupCoupon, $frmList, $moveCheckbox, $moveChecked, $moveUnChecked;

        var gd_group_list = {
            layerRule: null,
            message: {
                FIGURE: "현재 설정된 평가방법은 실적수치제이며, 회원등급별 평가기준에 따라 주문금액, 상품주문건수, 주문상품후기횟수를 종합하여 회원등급이 평가됩니다. 회원등급 평가를 진행하시겠습니까?",
                POINT: "현재 설정된 평가방법은 실적점수제이며, 주문금액, 상품주문건수, 주문상품후기횟수, 로그인횟수를 점수로 환산하여 회원등급이 평가됩니다. 회원등급 평가를 진행하시겠습니까?"
            }
        };

        var save_order = function () {
            var $checkbox = $(':checkbox', '#listId');
            var snoArray = [$checkbox.length];
            $.each($checkbox, function (idx, item) {
                var $item = $(item);
                snoArray[idx] = $item.val();
            });
            var params = [];
            params.push({name: "mode", value: "sort"});
            params.push({name: "snoArray", value: snoArray});
            post_with_reload('../member/member_group_ps.php', params);
        };

        var move_row = {
            up: function () {
                var $checkbox = $('#tblGroupList').find(':checkbox:checked');
                $checkbox.each(function (idx, item) {
                    var $row = $(item).parents('tr');
                    logger.debug($row.prev('tr'), $row);
                    $row.insertBefore($row.prev());
                });
            }, down: function () {
                var $checkbox = $('#tblGroupList').find(':checkbox:checked');
                $($checkbox.get().reverse()).each(function (idx, item) {
                    var $row = $(item).parents('tr');
                    var $next = $row.next();
                    var enableCheckboxLength = $next.find(':checkbox:enabled').length;
                    logger.debug('enableCheckboxLength: ' + enableCheckboxLength);
                    if (enableCheckboxLength > 0) {
                        $row.insertAfter($next);
                    }
                });
            }, top: function () {
                $moveCheckbox = $(':checkbox:enabled', '#listId');
                var $row = $moveChecked.parents('tr');
                var $targetRow = $moveCheckbox.first().parents('tr');
                $row.insertBefore($targetRow);
            }, bottom: function () {
                $moveCheckbox = $(':checkbox:enabled', '#listId');
                var $row = $moveChecked.parents('tr');
                var $targetRow = $moveCheckbox.last().parents('tr');
                $row.insertAfter($targetRow);
            }
        };

        var init = function () {
            $frmSetup = $('#frmSetup');
            $frmSetupCoupon = $('#frmSetupCoupon');
            $frmList = $('#frmList');
            $frmSetup.validate({
                submitHandler: function (form) {
                    var params = [{name: 'mode', value: 'modifyLabel'}, {
                        name: 'grpLabel',
                        value: $('input[name=grpLabel]', form).val()
                    }];
                    post_with_reload(form.action, params);
                }
            });
            $frmSetupCoupon.validate({
                submitHandler: function (form) {
                    var params = [{name: 'mode', value: 'modifyCoupon'}, {
                        name: 'couponConditionComplete',
                        value: $('input[name=couponConditionComplete]', form).is(":checked")
                    },{
                        name: 'couponConditionCompleteChange',
                        value: $('input[name=couponConditionCompleteChange]', form).is(":checked")
                    },{
                        name: 'couponConditionManual',
                        value: $('input[name=couponConditionManual]', form).is(":checked")
                    },{
                        name: 'couponConditionExcel',
                        value: $('input[name=couponConditionExcel]', form).is(":checked")
                    },{
                        name: 'couponConditionExcelChange',
                        value: $('input[name=couponConditionExcelChange]', form).is(":checked")
                    },];
                    post_with_reload(form.action, params);
                }
            });

            $('#btnAdd').click(function (e) {
                e.preventDefault();
                location.href = '../member/member_group_register.php';
            });

            // 순서저장
            $('#btnSaveOrder').click(function (e) {
                e.preventDefault();
                save_order();
            });

            // 추가할인 브랜드 할인율 레이어
            $('.js-dc-brand').click(function (e) {
                var params = {
                    sno: $(this).data('sno')
                };
                $.get('../member/layer_dc_brand.php', params, function (data) {
                    BootstrapDialog.show({
                        title: '브랜드별 추가할인',
                        message: $(data),
                        closable: true
                    });
                });
            });

            $('.js-moverow').click(function (e) {
                var $target = $(e.target);
                $moveChecked = $(':checked', '#listId');
                $moveUnChecked = $(':checkbox:not(:checked)', 'tbody');
                logger.debug('checked group length: ' + $moveChecked.length);
                if ($moveChecked.length > 0) {
                    var direction = $target.data('direction');
                    if (_.isUndefined(direction)) {
                        direction = $target.closest('button').data('direction');
                    }
                    logger.debug('click direction: ' + direction);
                    switch (direction) {
                        case 'up':
                            move_row.up();
                            break;
                        case 'down':
                            move_row.down();
                            break;
                        case 'top':
                            move_row.top();
                            break;
                        case 'bottom':
                            move_row.bottom();
                            break;
                    }
                } else {
                    alert("선택된 등급이 없습니다.");
                }
            });

            $('.btn-modify', $frmList).click(function (e) {
                $('#sno').val($(e.target).data('sno'));
                $frmList.submit(function () {
                    $frmList.attr('action', '../member/member_group_modify.php');
                    $frmList.attr('method', 'post');
                    $frmList.attr('target', '');
                });
            });

            $('#btnDelete', $frmList).click(function (e) {
                if (member.alert_check($frmList)) {
                    post_with_reload('../member/member_group_ps.php', {
                        mode: "delete",
                        chk: $frmList.find('input:checkbox:checked').not('#chk_all').map(function () {
                            return $(this).val();
                        }).get()
                    });
                }
            });


            $frmList.on('click', '#btnAppraisal', function (e) {
                BootstrapDialog.confirm({
                    title: $(e.target).text(),
                    message: gd_group_list.message[this.dataset.system],
                    callback: function (result) {
                        if (result) {
                            var data = {
                                'mode': 'checkSendSms',
                                'checkType': 'group'
                            }
                            var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: data});
                            $ajax.done(function (data, textStatus, jqXHR) {
                                if (data === true) {
                                    $.get('../share/layer_sms_password.php?mode=input', function () {
                                        BootstrapDialog.show({
                                            title: 'SMS 인증번호 입력',
                                            size: BootstrapDialog.SIZE_WIDE,
                                            message: arguments[0]
                                        });
                                    });
                                } else {
                                    post_with_reload('../member/member_group_ps.php', {
                                        mode: "appraisal",
                                        passwordCheckFl: 'n'
                                    });
                                }
                            });
                        }
                    }
                });
            }).on('click', '#btnAppraisalRule', function (e) {
                $.post('../member/layer_member_group_appraisal_rule.php', function (data) {
                    gd_group_list.layerRule = BootstrapDialog.show({
                        title: "회원등급 평가방법 설정",
                        message: $(data),
                        size: BootstrapDialog.SIZE_WIDE_XLARGE
                    });
                });
            });
        };

        $(document).ready(function () {
            init();
        });
    })($, _, window, document);

    function sms_password_callback(password) {
        post_with_reload('../member/member_group_ps.php', {
            mode: "appraisal",
            password: password
        });
    }
</script>
