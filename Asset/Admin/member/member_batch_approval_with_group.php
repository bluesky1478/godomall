<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
    <input type="button" value="가입승인/등급변경 처리" class="btn btn-red btn-register"/>
</div>

<form id="formSearch" method="get" class="content-form js-search-form js-form-enter-submit">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10); ?>"/>
    <div class="table-title gd-help-manual">
        대상회원 선택
    </div>
    <?php include('member_detail_search.php'); ?>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <div class="table-header form-inline">
        <div class="pull-left">
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '명'); ?>
        </div>
        <div class="pull-right">
            <div>
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum')); ?>
            </div>
        </div>
    </div>

    <table class="table table-rows">
        <colgroup>
            <col class="width-xs"/>
            <col class="width-xs"/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>
                <input type="checkbox" id="chk_all" class="js-checkall"
                       data-target-name="chk"/>
            </th>
            <th>번호</th>
            <?php if ($gGlobal['isUse']) { ?>
                <th>상점 구분</th>
            <?php } ?>
            <th>아이디/닉네임</th>
            <th>이름</th>
            <th>등급</th>
            <th>주문금액</th>
            <th>마일리지</th>
            <th>예치금</th>
            <th>회원가입일</th>
            <th>최종로그인</th>
            <th>가입승인</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $val) {
                $lastLoginDt = (substr($val['lastLoginDt'], 2, 8) != date('y-m-d')) ? substr($val['lastLoginDt'], 2, 8) : '<span class="">' . substr($val['lastLoginDt'], 11) . '</span>';
                $txtAppFl = ($val['appFl'] == 'y' ? '승인' : '미승인');
                ?>
                <tr class="center" data-member-no="<?= $val['memNo']; ?>">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?= $val['memNo']; ?>"
                               data-appFl="<?= ($val['appFl'] == 'y' ? 'y' : '') ?>"
                               data-maillingFl="<?= ($val['maillingFl'] == 'y' ? 'y' : '') ?>"
                               data-smsFl="<?= ($val['smsFl'] == 'y' ? 'y' : '') ?>"/>
                    </td>
                    <td class="number js-layer-crm hand"><?= $page->idx--; ?></td>
                    <?php if ($gGlobal['isUse']) { ?>
                        <td class="">
                            <span class="flag flag-16 flag-<?= gd_isset($gGlobal['mallList'][$val['mallSno']]['domainFl'], 'kr'); ?>"></span><?= gd_isset($gGlobal['mallList'][$val['mallSno']]['mallName'], '기준몰'); ?>
                        </td>
                    <?php } ?>
                    <td class="js-layer-crm hand">
                        <span class="font-eng">
                            <?= $val['memId']; ?>
                        </span>
                        <?= gd_get_third_party_icon_web_path($val['snsTypeFl']); ?>
                        <?php if ($val['nickNm']) { ?>
                            <div class="notice-ref notice-sm"><?= $val['nickNm']; ?></div><?php } ?>
                    </td>
                    <td class="js-layer-crm hand">
                        <?= $val['memNm']; ?>
                    </td>
                    <td class="js-layer-crm hand"><?= gd_isset($groups[$val['groupSno']]); ?></td>
                    <td class="number js-layer-crm hand"><?= number_format($val['saleAmt']); ?></td>
                    <td class="number js-layer-crm hand"><?= number_format($val['mileage']); ?></td>
                    <td class="number js-layer-crm hand"><?= number_format($val['deposit']); ?></td>
                    <td class="date js-layer-crm hand"><?= substr($val['entryDt'], 2, 8); ?></td>
                    <td class="date js-layer-crm hand"><?= $lastLoginDt; ?></td>
                    <td class="js-layer-crm hand"><?= $txtAppFl; ?></td>
                </tr>
                <?php
            }
        } elseif ($isSkip) {
            echo '<tr><td class="center" colspan="13">검색기능을 이용해주세요.</td></tr>';
        } else {
            echo '<tr><td class="center" colspan="13">검색된 정보가 없습니다.</td></tr>';
        }
        ?>
        </tbody>
    </table>

    <div class="center"><?= $page->getPage(); ?></div>
</form>

<form name="setupForm" id="setupForm" method="post" class="content-form js-setup-form">
    <input type="hidden" name="batchTarget[]" value=""/>
    <input type="hidden" name="memberType" value=""/>
    <input type="hidden" name="searchJson" value="<?= $searchJson; ?>"/>

    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-md"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>처리항목</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="mode"
                               value="batch_app" <?= gd_isset($checked['mode']['batch_app']); ?>
                               data-target=".tr-apply"/>
                        가입승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="mode"
                               value="batch_group" <?= gd_isset($checked['mode']['batch_group']); ?>
                               data-target=".tr-group"/>
                        등급변경
                    </label>
                </td>
            </tr>
            <tr class="tr-apply">
                <th>변경상태선택</th>
                <td>
                    <label class="radio-inline">
                        <input type="radio" name="approvalStatus"
                               value="y" <?= gd_isset($checked['approvalStatus']['y']); ?> />
                        승인
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="approvalStatus"
                               value="n" <?= gd_isset($checked['approvalStatus']['n']); ?>/>
                        미승인
                    </label>
                </td>
            </tr>
            <tr class="tr-group">
                <th>변경등급선택</th>
                <td>
                    <?= gd_select_box('newGroupSno', 'newGroupSno', $groups, null, Request::get()->get('newGroupSno'), '등급선택'); ?>
                    <a href="../member/member_group_list.php" target="_blank">
                        <span
                                class="notice-ref notice-sm">회원등급 설정 내용 확인 및 추가&gt;
                        </span>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="notice-danger">자동발송 설정에 따라 회원상태 변경 시 회원에게 SMS/메일로 안내메시지가 발송되므로 주의하시기 바랍니다.</p>
    </div>
</form>
<script type="text/javascript">
    var $formList = $('#frmList');
    var $formSearch = $('.js-search-form');
    var $formSetup = $('.js-setup-form');
    var search_total = '<?= $page->recode['total'] ?>';
    var data;

    $(document).ready(function () {
        $('.search-detail-box tbody:eq(0)').prepend(_.template($('#templateAddDetailSearch').html()));

        member.toggleEventApplyQuery();

        $(':radio[name=mode]').on('change', function (e) {
            member.target_display_switch(e);
        }).filter(':checked').trigger('change');

        $('select[name=\'pageNum\']').change({targetForm: '.js-search-form'}, member.page_number);

        $formSetup.validate({
            ignore: [],
            dialog: false,
            rules: {
                groupSno: {
                    required: function () {
                        return $(':radio[name=mode]:checked', $formSetup).val() == 'batch_group';
                    }
                },
                "batchTarget[]": {
                    required: function () {
                        return $(':radio[name=memberType]:checked', $formSearch).val() == 'select' && $(':checkbox:checked', $formList).length == 0;
                    }
                }
            }, messages: {
                groupSno: "변경하실 등급을 선택해 주세요.",
                "batchTarget[]": {
                    required: function () {
                        if ($(':radio[name=mode]:checked', $formSetup).val() == 'batch_group') {
                            return '등급을 변경할 대상 회원을 선택해주세요.';
                        } else {
                            return '가입승인상태를 변경할  대상 회원을 선택해주세요.';
                        }
                    }
                }
            }, submitHandler: function (form) {
                var $form = $(form);
                data = $form.serializeArray();

                var mode = $(':radio[name=mode]:checked', $formSetup).val();
                var approval_fl = $(':radio[name=approvalStatus]:checked', $form).val();
                var member_type = $(':radio[name=memberType]:checked', $formSearch).val();

                try {
                    if (member_type === 'select') {
                        $("input[name='chk[]']:checked", $formList).each(function () {
                            data.push({name: "chk[]", value: $(this).val()});
                        });
                    }

                    var is_batch_app_mode = (mode === 'batch_app');
                    var is_batch_group_mode = (mode === 'batch_group');
                    var is_member_type_query = (member_type === 'query');
                    var is_member_type_select = (member_type === 'select');
                    var is_approval_flag = (approval_fl === 'y');
                    var is_disapproval_flag = (approval_fl === 'n');
                    if (is_batch_app_mode && is_member_type_query && is_approval_flag) {
                        data.push({name: "mode", value: "all_approval_join"});
                    } else if (is_batch_app_mode && is_member_type_query && is_disapproval_flag) {
                        data.push({name: "mode", value: "all_disapproval_join"});
                    } else if (is_batch_app_mode && is_member_type_select && is_approval_flag) {
                        data.push({name: "mode", value: "approval_join"});
                    } else if (is_batch_app_mode && is_member_type_select && is_disapproval_flag) {
                        data.push({name: "mode", value: "disapproval_join"});
                    } else if (is_batch_group_mode && is_member_type_query) {
                        if ($('#newGroupSno').val() < 1) {
                            alert('회원등급을 선택해 주시기 바랍니다.');
                            return false;
                        }
                        data.push({name: "mode", value: "all_apply_group_grade"});
                    } else if (is_batch_group_mode && is_member_type_select) {
                        if ($('#newGroupSno').val() < 1) {
                            alert('회원등급을 선택해 주시기 바랍니다.');
                            return false;
                        }
                        data.push({name: "mode", value: "apply_group_grade"});
                    } else {
                        alert("가입승인/등급변경 조건이 올바르지 않습니다.");
                    }

                } catch (error) {
                    alert("가입승인/등급변경 처리 중 오류가 발생하였습니다.");
                }

                if (is_approval_flag) {
                    var new_group_name = $('#newGroupSno option:selected').text();
                    var message = '검색된 ' + search_total + '명의 가입을 승인하시겠습니까?<br/>(자동발송 설정에 따라 회원에게 SMS/메일이 발송됩니다.)';
                    if (is_member_type_select && is_batch_app_mode) {
                        message = '선택한 ' + $(':checkbox:checked', $formList).not('.js-checkall').length + '명의 가입을 승인하시겠습니까?<br/>(자동발송 설정에 따라 회원에게 SMS/메일이 발송됩니다.)';
                    } else if (is_member_type_query && is_batch_group_mode) {
                        message = '검색된 ' + search_total + '명의 회원등급을 ' + new_group_name + '으로 변경하시겠습니까?<br/>(자동발송 설정에 따라 회원에게 SMS/메일이 발송됩니다.)';
                    } else if (is_member_type_select && is_batch_group_mode) {
                        message = '선택한 ' + $(':checkbox:checked', $formList).not('.js-checkall').length + '명의 회원등급이 ' + new_group_name + '으로 변경됩니다.<br/>(자동발송 설정에 따라 회원에게 SMS/메일이 발송됩니다.)';
                    }

                    dialog_confirm(message, function (result) {
                        if (result) {
                            if((is_member_type_query && search_total >= 100) || (is_member_type_select && $(':checkbox:checked', $formList).not('.js-checkall').length >= 100)) {
                                var ckeckData = {
                                    'mode': 'checkSendSms'
                                }
                                if(is_batch_app_mode) {
                                    ckeckData.checkType = 'join';
                                } else {
                                    ckeckData.checkType = 'group';
                                    ckeckData.groupSno = $('#newGroupSno').val();
                                }
                                var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: ckeckData});
                                $ajax.done(function (ret, textStatus, jqXHR) {
                                    if(ret === true) {
                                        $.get('../share/layer_sms_password.php?mode=input', function () {
                                            BootstrapDialog.show({
                                                title: 'SMS 인증번호 입력',
                                                size: BootstrapDialog.SIZE_WIDE,
                                                message: arguments[0]
                                            });
                                        });
                                    } else {
                                        data.push({name: "passwordCheckFl", value: "n"});
                                        post_with_reload('../member/member_batch_ps.php', data, window.location.pathname);
                                    }
                                });
                            } else {
                                data.push({name: "passwordCheckFl", value: "n"});
                                post_with_reload('../member/member_batch_ps.php', data, window.location.pathname);
                            }
                        }
                    });
                } else {
                    data.push({name: "passwordCheckFl", value: "n"});
                    post_with_reload('../member/member_batch_ps.php', data, window.location.pathname);
                }


            }
        });

        $formSearch.validate({
            submitHandler: function (form) {
                var queryString1 = $(form).serialize();
                var queryString2 = $formSetup.find(':checked, :text, select').serialize();
                window.location.href = '../member/member_batch_approval_with_group.php?' + queryString1 + '&' + queryString2;
            }
        });

        $('.btn-register').click(function () {
            $('#setupForm').submit();
        });
    });
    function sms_password_callback(password) {
        data.push({name: "password", value: password});
        post_with_reload('../member/member_batch_ps.php', data, window.location.pathname);
    }
</script>
<script type="text/html" id="templateAddDetailSearch">
    <tr>
        <th>대상회원 선택</th>
        <td colspan="3">
            <label class="radio-inline">
                <input type="radio" name="memberType" class="js-apply-query"
                       value="query" <?= gd_isset($checked['memberType']['query']); ?>/>
                검색회원 전체적용
            </label>
            <label class="radio-inline">
                <input type="radio" name="memberType" class="js-apply-query"
                       value="select" <?= gd_isset($checked['memberType']['select']); ?>/>
                회원선택 적용
            </label>
        </td>
    </tr>
</script>
