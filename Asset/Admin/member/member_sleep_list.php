<!-- //@formatter:off -->
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>

<form id="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10) ?>"/>
    <input type="hidden" name="searchFl" value="y"/>

    <div class="table-title gd-help-manual">휴면회원 검색</div>
    <div class="search-detail-box form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <?php if ($gGlobal['isUse']) { ?>
                <tr>
                    <th>상점</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="mallSno"
                                   value="" <?= gd_isset($checked['mallSno']['']); ?>/>
                            전체
                        </label>
                        <?php foreach ($gGlobal['useMallList'] as $item) { ?>
                            <label class="radio-inline">
                                <input type="radio" name="mallSno"
                                       value="<?= $item['sno']; ?>" <?= gd_isset($checked['mallSno'][$item['sno']]); ?>/>
                                <span class="flag flag-16 flag-<?= $item['domainFl']; ?>"></span><?= $item['mallName']; ?>
                            </label>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>검색어</th>
                <td>
                    <?= gd_select_box('key', 'key', $combineSearch, null, gd_isset($search['key'])); ?>
                    <input type="text" name="keyword" value="<?= gd_isset($search['keyword']); ?>"
                           class=""/>
                </td>
            </tr>
            <tr>
                <th>휴면회원 전환일</th>
                <td>
                    <div class="input-group js-datepicker">
                        <input type="text" class="" placeholder="" name="sleepDt[]"
                               value="<?= gd_isset($search['sleepDt'][0]); ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    <div class="input-group js-datepicker">
                        <input type="text" class="" placeholder="" name="sleepDt[]"
                               value="<?= gd_isset($search['sleepDt'][1]); ?>">
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black js-search-button"/>
    </div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">

    <div class="table-header">
        <div class="pull-left">
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '명'); ?>
        </div>
        <div class="pull-right">
            <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
        </div>
    </div>

    <div class="form-inline">
        <table class="table table-rows">
            <colgroup>
                <col class="width-xs"/>
                <col class="width-xs"/>
                <?php if ($gGlobal['isUse']) { ?>
                    <col/>
                <?php } ?>
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
                <th class="width-2xs">
                    <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
                </th>
                <th>휴면회원 전환일</th>
                <?php if ($gGlobal['isUse']) { ?>
                    <th>상점 구분</th>
                <?php } ?>
                <th>아이디</th>
                <th>이름</th>
                <th>회원등급</th>
                <th>마일리지</th>
                <th>예치금</th>
                <th>회원가입일</th>
                <th>휴면해제</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (gd_isset($data)) {
                foreach ($data as $val) {
                    $sleepDt = (substr($val['sleepDt'], 2, 8) != date('y-m-d')) ? substr($val['sleepDt'], 2, 8) : '<span class="">' . substr($val['sleepDt'], 11) . '</span>';
                    ?>
                    <tr class="center">
                        <td>
                            <input type="checkbox" name="chk[]" value="<?= $val['sleepNo']; ?>"
                                   data-mem-no="<?= $val['memNo']; ?>"
                                   data-deposit="<?= $val['deposit'] ?>"
                                   data-mileage="<?= $val['mileage'] ?>"
                                   data-couponcount="<?= $val['couponcount'] ?>"/>
                        </td>
                        <td class="font-date"><?= substr($val['sleepDt'], 2, 8); ?></td>
                        <?php if ($gGlobal['isUse']) { ?>
                            <td class="">
                                <span class="flag flag-16 flag-<?= gd_isset($gGlobal['mallList'][$val['mallSno']]['domainFl'], 'kr'); ?>"></span><?= gd_isset($gGlobal['mallList'][$val['mallSno']]['mallName'], '기준몰'); ?>
                            </td>
                        <?php } ?>
                        <td><?= $val['memId']; ?></td>
                        <td><?= $val['memNm']; ?></td>
                        <td><?= gd_isset($groups[$val['groupSno']]); ?></td>
                        <td class="font-num"><?= number_format($val['mileage']); ?></td>
                        <td class="font-num"><?= number_format($val['deposit']); ?></td>
                        <td class="font-date"><?= substr($val['entryDt'], 2, 8); ?></td>
                        <td>
                            <button type="button" class="btn btn-gray btn-sm btnWake" data-no="<?= $val['sleepNo']; ?>">해제</button>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                if ($page->recode['amount'] == 0) {
                    echo '<tr class="center">';
                    echo '<td colspan="12" class="no-data">휴면회원으로 전환된 회원이 없습니다.</td>';
                    echo '</tr>';
                } else {
                    echo '<tr class="center">';
                    echo '<td colspan="12" class="no-data">검색된 정보가 없습니다.</td>';
                    echo '</tr>';
                }
            }
            ?>
            </tbody>
        </table>
    </div>

    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white " id="checkDelete">선택 탈퇴처리</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white " id="checkAllDelete">전체 탈퇴처리</button>
        </div>
    </div>

    <div class="center"><?= $page->getPage(); ?></div>
</form>
<!-- //@formatter:on -->


<script type="text/javascript">
    var $formList = $('#frmList');
    var msg = {
        DEL_MEM: "휴면회원을 탈퇴처리 하시겠습니까?<br/>해당 회원은 즉시 탈퇴 처리되며, 탈퇴완료 시 취소할 수 없습니다.",
        DEL_ALL_MEM: "전체 <?= $page->recode['amount']; ?>명의 휴면회원을 탈퇴처리하시겠습니까?<br/>해당 회원은 즉시 탈퇴 처리되며, 탈퇴완료 시 취소할 수 없습니다.",
        COUPON_MILEAGE: "사용가능한 쿠폰/마일리지를 보유중인 휴면회원이 포함되어있습니다. 탈퇴처리 시 보유중인 회원혜택은 모두 삭제되고 즉시 탈퇴처리되며, 탈퇴완료 시 취소하실 수 없습니다.\n선택한 휴면회원을 탈퇴처리하시겠습니까?",
        DEPOSIT: "예치금을 보유중인 휴면회원이 포함되어있습니다. 예치금을 보유중인 휴면회원은 탈퇴처리할 수 없습니다."
    };

    $(document).ready(function () {
        // 출력수
        $('select[name=\'pageNum\']').change(function () {
            $('input[name=\'pageNum\']').val($(this).val());
            $('#frmSearchBase').submit();
        });

        // 해제
        $('.btnWake', $formList).on('click', function (e) {
            BootstrapDialog.confirm({
                title: "휴면해제",
                message: "선택한 회원을 휴면회원 상태에서 해제하시겠습니까? 해제 시 해당 회원은 다시 서비스를 정상적으로 이용하실 수 있습니다.",
                btnOKLabel: "해제",
                callback: function (result) {
                    if (result) {
                        var data = $formList.serializeArray();
                        data.push({name: "mode", value: "wake_member"});
                        data.push({name: "sleepNo", value: member.get_member_attribute(e)});
                        post_with_reload('../member/member_sleep_ps.php', data);
                    }
                }
            });
            e.preventDefault();
        });

        // 선택 삭제
        $('#checkDelete', $formList).on('click', check_delete);

        // 전체 삭제
        $('#checkAllDelete', $formList).on('click', check_all_delete);
    });

    function check_delete() {
        var $checkList = $formList.find(':checkbox[name="chk[]"]:checked');
        var hasDeposit = false; // 예치금 보유 여부
        var hasMileage = false; // 마일리지, 쿠폰 보유 여부

        if ($(':checkbox:checked').length == 0) {
            alert('선택된 회원이 없습니다.');
            return;
        }

        $checkList.each(function (idx, item) {
            var $item = $(item);
            if ($item.data('deposit') > 0) {
                hasDeposit = true;
                return false;
            }
            if (hasMileage === false && ($item.data('mileage') > 0 || $item.data('couponcount') > 0)) {
                hasMileage = true;
            }
        });

        if (hasDeposit == true) {
            alert(msg.DEPOSIT);
            return false;
        }

        if (hasMileage == true) {
            dialog_confirm(msg.COUPON_MILEAGE, function (result) {
                if (result) {
                    delete_sleep_member(msg.DEL_MEM);
                }
            });
            return false;
        }

        delete_sleep_member(msg.DEL_MEM);
    }

    function check_all_delete() {
        $.ajax({
            'url': '../member/member_sleep_ps.php',
            'type': 'post',
            'data': [{name: "mode", value: "check_all_delete"}],
            'success': function(data) {
                if(data == 'DEPOSIT') {
                    alert(msg.DEPOSIT);
                } else if(data == 'COUPON_MILEAGE') {
                    dialog_confirm(msg.COUPON_MILEAGE, function (result) {
                        if (result) {
                            delete_all_sleep_member(msg.DEL_ALL_MEM);
                        }
                    });
                } else {
                    delete_all_sleep_member(msg.DEL_ALL_MEM);
                }
            }
        });
    }

    function delete_sleep_member(message) {
        BootstrapDialog.confirm({
            title: "휴면회원 선택삭제",
            message: message,
            btnOKLabel: "탈퇴처리",
            callback: function (result) {
                if (result) {
                    var data = $formList.serializeArray();
                    data.push({name: "mode", value: "delete_sleep_member"});
                    post_with_reload('../member/member_sleep_ps.php', data);
                }
            }
        });
    }

    function delete_all_sleep_member(message) {
        BootstrapDialog.confirm({
            title: "휴면회원 전체삭제",
            message: message,
            btnOKLabel: "탈퇴처리",
            callback: function (result) {
                if (result) {
                    var data = $formList.serializeArray();
                    data.push({name: "mode", value: "delete_sleep_member_all"});
                    post_with_reload('../member/member_sleep_ps.php', data);
                }
            }
        });
    }

    function dialog_confirm(message, callback, title, btnText) {
        if (_.isUndefined(title)) {
            title = '확인';
        }

        if (_.isUndefined(btnText)) {
            cancelLabel = "취소";
            confirmLabel = "확인";
        } else {
            cancelLabel = btnText.cancelLabel;
            confirmLabel = btnText.confirmLabel;
        }

        BootstrapDialog.show({
            title: title,
            message: message,
            buttons: [{
                label: cancelLabel,
                hotkey: 32,
                size: BootstrapDialog.SIZE_LARGE,
                action: function (dialog) {
                    if (typeof callback == 'function') {
                        callback(false);
                    }
                    dialog.close();
                }
            }, {
                label: confirmLabel,
                cssClass: 'btn-white',
                size: BootstrapDialog.SIZE_LARGE,
                action: function (dialog) {
                    if (typeof callback == 'function') {
                        callback(true);
                    }
                    dialog.close();
                }
            }
            ]
        });
    }
</script>
