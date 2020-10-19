<?php
/**
 * @date        2017-01-02
 * @author      yjwee
 * @usage       Asset/Admin/share/popup_add_member2.php
 * @description 해당 스킨은 구버전입니다. usage 의 스킨을 사용하세요. 해당 스킨은 글로벌 기능을 지원합니다.
 */
?>
<style>
    #iframeSearch { height:600px; border:0px; width:650px; border:1px solid #d6d6d6; }

    #tdSelectList, #divSelectList { width:650px; border-bottom:0px }

    .table > thead > tr > th { background:#fff; border:0px; }

    .addMemberDisplay { height:565px; width:648px; overflow-y:scroll; overflow-x:hidden; border:1px solid #d6d6d6; }

    #divSelectList .table-rows > tbody > tr > td { word-break:break-all; padding:15px 0 }

    #divSelectList .table > thead > tr > th:nth-child(1),
    #divSelectList .table-rows > tbody > tr > td:nth-child(1) { width:35px; }

    #divSelectList .table > thead > tr > th:nth-child(2), #divSelectList .table-rows > tbody > tr > td:nth-child(2) { width:39px; }

    #divSelectList .table > thead > tr > th:nth-child(3), #divSelectList .table-rows > tbody > tr > td:nth-child(3) { width:133px; }

    #divSelectList .table > thead > tr > th:nth-child(4), #divSelectList .table-rows > tbody > tr > td:nth-child(4) { width:90px; }

    #divSelectList .table > thead > tr > th:nth-child(5), #divSelectList .table-rows > tbody > tr > td:nth-child(5) { width:90px; }

    #divSelectList .table > thead > tr > th:nth-child(6), #divSelectList .table-rows > tbody > tr > td:nth-child(6) { width:139px; }

    #divSelectList .table > thead > tr > th:nth-child(7) { width:123px; }

    #divSelectList .table-rows > tbody > tr > td:nth-child(7) { width:103px; }

    .addMember_title {
        margin:0px 0px 0px 3px;
        padding:10px 5px 0;
        display:block;
        font-weight:bold;
        font-size:18px;
        letter-spacing:-1px;
        color:#222;
    }

    .btn-icon-check-bottom {
        background:url(<?=PATH_ADMIN_GD_SHARE;?>img/btn_icon_check_off.png) no-repeat 50% 40px;
        height:60px;
    }

    .btn-icon-check-bottom:hover {
        background:#f91d11 url(<?=PATH_ADMIN_GD_SHARE;?>img/btn_icon_check_on.png) no-repeat 50% 40px;
    }

    .addDelButtonArea { text-align:center; height:300px; width:100%; }
</style>
<table class="table table-cols">
    <thead>
    <tr>
        <th style="text-align: left">
            <span class="addMember_title">회원선택</span>
        </th>
        <th></th>
        <th style="text-align: left">
            <span class="addMember_title" style="margin-left: -5px">선택 회원 리스트</span>
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="width: 650px;padding-left: 20px;border-bottom: 0px">
            <iframe id="iframeSearch" name="iframeSearch" src="../share/ifrme_add_member_search.php?sendMode=<?php echo $sendMode; ?>"></iframe>
        </td>
        <td style="width: 60px;border-bottom: 0px">
            <table cellpadding="0" cellspacing="0" class="addDelButtonArea">
                <td>
                    <p style="margin: 0px">
                        <input type="button" class="btn btn-9 btn-white btn-icon-plus-bottom" value="추가" id="btnAddList"/>
                    </p>
                    <p style="margin: 20px 0">
                        <button class="btn btn-9 btn-icon-check-bottom btnSelected">선택<br>완료</span></button>
                    </p>
                    <p style="margin: 0px">
                        <input type="button" class="btn btn-9 btn-white btn-icon-minus-bottom" value="삭제" id="btnRemoveList"/>
                    </p>
                </td>
            </table>
        </td>
        <td id="tdSelectList">
            <div id="divSelectList">
                <form id="formSelectList" action="" role="form">
                    <table class="table table-rows" style="width:648px;margin: 0">
                        <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="selectChk"/>
                            </th>
                            <th>번호</th>
                            <th>아이디/닉네임</th>
                            <th>이름</th>
                            <th>등급</th>
                            <th>이메일</th>
                            <th>휴대폰번호</th>
                        </tr>
                        </thead>
                    </table>
                    <div class="addMemberDisplay">
                        <table class="table table-rows">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="3" class="center" style="padding-top: 25px;border-bottom: 0px">
            <input type="button" value="취소" id="btnCancel" class="btn btn-lg btn-white" style="font-weight: bold;margin-right: 10px"/>
            <input type="button" value="선택완료" class="btn btn-lg btn-black btnSelected"/>
        </td>
    </tr>
    </tbody>
</table>

<script type="text/javascript">
    <?php
    if ($sendMode == 'mail') {
        echo 'var openerDivList = \'divMaillingList\';';
    } elseif ($sendMode == 'sms') {
        echo 'var openerDivList = \'divSmsList\';';
    }
    ?>
    var selectedCount = {
        total: 0, accept: 0, reject: 0
    };
    $(document).ready(function () {
        var $formList = $('#formSearchList');
        var $formSearch = $('#frmSearchBase');
        var $formSelectList = $('#formSelectList');

        var $openerSelectList = top.opener.$('#' + openerDivList);
        if ($openerSelectList.find('input[name="selectChk[]"]').length > 0) {
            $formSelectList.html($openerSelectList.html());
            $('.js-checkall').click(function () {
                if ($(this).data('target-name')) {
                    $('input:checkbox[name*=\'' + $(this).data('target-name') + '\']:not(:disabled)').prop('checked', this.checked);
                } else {
                    // 테이블에서만 사용 가능
                    var name = $(this).closest('table').find('thead input:checkbox').data('target-name');
                    if (!_.isUndefined(name)) {
                        $('input:checkbox[name*=\'' + name + '\']:not(:disabled)').prop('checked', this.checked);
                    }
                }
                backgroundShow();
            });
        }

        // 정렬&출력수
        $('select[name=\'sort\']', $formList).change({targetForm: '#frmSearchBase'}, member.page_sort);
        $('select[name=\'pageNum\']', $formList).change({targetForm: '#frmSearchBase'}, member.page_number);

        $('.btnSelected').click(function (e) {
            e.preventDefault();

            var $tr = $('input[name="selectChk[]"]', $formSelectList).closest('tr');
            selectedCount.total = $tr.length;

            // 수신 여부 관련 카운트
            $.each($tr, function (idx, item) {
                var receiveFl;
                var $item = $(item);
                <?php if ($sendMode == 'mail') { ?>
                receiveFl = $item.find('input[name="selectChk[]"]').data('maillingfl');
                <?php } else if ($sendMode == 'sms') {?>
                receiveFl = $item.find('input[name="selectChk[]"]').data('smsfl');
                <?php } ?>

                if (receiveFl === 'y') {
                    selectedCount.accept++;
                } else {
                    selectedCount.reject++;
                }
            });

            top.opener.$('#' + openerDivList).data({
                total: selectedCount.total,
                accept: selectedCount.accept,
                reject: selectedCount.reject
            }).html($formSelectList.html());

            top.opener.$('#divSearchCount').removeClass('display-none');
            top.opener.$('#divSearchCount').find('#receiveTotal').text(selectedCount.total);
            top.opener.$('#divSearchCount').find('#rejectCount').text(selectedCount.reject);

            self.close();
        });

        $('#btnCancel').click(function (e) {
            e.preventDefault();
            self.close();
        });

        $('#btnAddList').click(function (e) {
            e.preventDefault();

            var $checked = $(':checkbox:checked', $('#iframeSearch').contents());
            var totalLength = $('input[name="selectChk[]"]', $formSelectList).length + 1;

            $.each($checked, function (idx, item) {
                var $item = $(item);

                if (item.id == 'chk_all' || $(':checkbox[value=' + $item.val() + ']', $formSelectList).length > 0) {
                    return true;
                }
                var $tr = $item.closest('tr');
                var $trClone = $tr.clone();

                $trClone.find('td:eq(1)').text(totalLength);
                $trClone.find(':checkbox').attr({checked: false, name: "selectChk[]"});
                $('tbody', $formSelectList).prepend($trClone);

                totalLength++;
            });
            backgroundShow();
        });
        $('#btnRemoveList').click(function (e) {
            e.preventDefault();

            var $checkbox = $(':checkbox', $formSelectList);

            $.each($checkbox, function (idx, item) {
                if (item.id == 'chk_all') {
                    return true;
                }

                var $tr = $(item).closest('tr');

                if (item.checked === true) {
                    $tr.remove();
                }
            });

            $checkbox = $(':checkbox', $formSelectList);
            var total = $checkbox.length;

            $.each($checkbox, function (idx, item) {
                var $tr = $(item).closest('tr');
                $tr.find('td:eq(1)').text(total);
                total--;
            });

            $checkbox.prop('checked', false);
        });

        backgroundShow();
    });

    function backgroundShow() {
        $('input[name="selectChk[]"]').each(function () {
            $(this).click(function () {
                if ($(this).is(":checked")) {
                    $(this).parent().parent().css('background-color', '#f7f7f7');
                } else {
                    $(this).parent().parent().css('background-color', '');
                }
            });
            if ($(this).is(":checked")) {
                $(this).parent().parent().css('background-color', '#f7f7f7');
            } else {
                $(this).parent().parent().css('background-color', '');
            }
        });
    }
</script>
