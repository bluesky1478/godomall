<script type="text/javascript">
    <!--
    /**
     * 문자열 Byte 체크 (한글 2byte)
     */
    function stringToByte(str) {
        var length = 0;
        for (var i = 0; i < str.length; i++) {
            if (escape(str.charAt(i)).length >= 4)
                length += 2;
            else if (escape(str.charAt(i)) != "%0D")
                length++;
        }
        return length;
    }

    /**
     * SMS 내용 길이 체크
     */
    function setContentsLength(contentsNm, countId) {
        var textarea = $('textarea[name=' + contentsNm + ']');
        var contentsText = textarea.val();
        var textLength = stringToByte(contentsText);
        if (textLength > <?php echo $smsStringLimit;?>) {
            if (textLength > <?php echo $lmsStringLimit;?>) {
                if (textarea.data('close')) {
                    textarea.data('close', false);
                    BootstrapDialog.show({
                        message: 'LMS 전송은 최대 2,000 Byte 까지 가능합니다.',
                        onhidden: function () {
                            textarea.data('close', true);
                        }
                    });

                }
            }
            $('#' + countId).css("color", "#FF0000");
            $('.sms-type').hide();
            $('.lms-type').show();
            $('input[name=sendFl]').val('lms');
        } else {
            $('#' + countId).css("color", "");
            $('.sms-type').show();
            $('.lms-type').hide();
            $('input[name=sendFl]').val('sms');
        }
        $('#' + countId).val(textLength);
    }

    function setSendLength() {
        setContentsLength('smsContents', 'smsStringCount');
    }

    function setReceiverCount() {
        var checkedType = $('[name=receiverType]:checked').val();
        var receiverCount = 0, agreeCount = 0, rejectCount = 0;
        var agreeCheck = 'n';
        var $receive_total = $('.js-receive-total');
        var $reject_count = $('.js-reject-count');
        var $agree_check = $('input[id=agree_group]:checked');
        if ($agree_check.length > 0) {
            agreeCheck = $agree_check.val();
        }
        // 대상 회원 타입에 따른 발송 건수 세팅
        if ((checkedType === 'group' || checkedType === 'each' || checkedType === 'all') && $receive_total.data('receive-total') !== '') {
            receiverCount = $receive_total.data('receive-total');
            rejectCount = $reject_count.data('reject-count');
            agreeCount = receiverCount - rejectCount;
            if (agreeCheck === 'y') {
                rejectCount = 0;
            }
            // 수신동의 체크 한 경우, 동의회원만 카운팅
            if ($('.js-cb-agree').length > 0 && $('.js-cb-agree').prop('checked')) {
                receiverCount = agreeCount;
            }
        } else if (checkedType === 'direct') {
            receiverCount = $('.js-direct-list .list-group-item :hidden').length;
        } else if (checkedType === 'excel') {
            receiverCount = $('#formExcel').data('receiver-count');
            if (receiverCount == null)
                receiverCount = 0;
        } else {
            var $target = $('.sms-target-area');
            var $popupData = $('input[name="popupData[]"]');
            receiverCount = $target.data('total');
            agreeCount = 0;
            if ($popupData.length > 0 && $popupData.data('smsfl') === 'n') {
                rejectCount = 1;
            } else {
                if($target.data('reject-count') > 0) {
                    rejectCount = $target.data('reject-count');
                    agreeCount = receiverCount - rejectCount;
                } else {
                    rejectCount = 0;
                }
            }
        }

        // 발송 타입에 따른 SMS 차감 포인트 결정
        var pointCnt = 1;
        if ($('input[name=sendFl]').val() === 'lms') {
            pointCnt = <?php echo $lmsPoint;?>;
        }
        console.log('receiverCount: ' + receiverCount, 'agreeCount: ' + agreeCount, 'rejectCount: ' + rejectCount);
        if ($('.js-cb-agree').prop('checked')) {
            $('input[name=receiverCnt]').val(agreeCount * pointCnt);
        } else {
            $('input[name=receiverCnt]').val(receiverCount * pointCnt);
        }
        $('input[name=receiverCnt]').val(receiverCount * pointCnt);
        $('input[name=agreeCnt]').val(agreeCount * pointCnt);
        $('input[name=rejectCnt]').val(rejectCount * pointCnt);
    }

    $(document).ready(function () {
        gd_sms_send.set_form($('#formSendSms'));
        gd_sms_send.set_pagination($('.js-pagination'));
        gd_sms_send.set_target_area($('.sms-target-area'));
        gd_sms_send.set_search_word($('input[name="searchWord"]'));
        gd_sms_send.set_contents_list($('.sms-contents-area'));
        gd_sms_send.set_sms_contents_search_area($('.js-sms-contents-search-area'));
        gd_sms_send.set_sms_receiver_list($('#divSmsList'));
        gd_sms_send.set_submit_excel('#formExcel');
        gd_sms_send.set_on_click_select_target('#btnSearchMember, #linkSearchMember');
        gd_sms_send.set_on_click_delete_group('#member_groupLayer .btn-icon-delete');
        gd_sms_send.set_on_click_add_cellphone('.js-btn-add-cell-phone');
        gd_sms_send.set_on_click_direct_list_item('.js-direct-list .list-group-item');
        gd_sms_send.set_on_click_delete_cell_phone_select('.js-btn-delete-selected');
        gd_sms_send.set_on_click_delete_cell_phone_all('.js-btn-delete-all');
        gd_sms_send.set_on_click_pagination('.page_navi_number');
        gd_sms_send.set_on_click_check_contents_view_all('.js-btn-select-sms-all');
        gd_sms_send.set_on_click_delete_sms_selected('.js-btn-delete-sms-selected');
        gd_sms_send.set_on_click_save_contents_by_dialog('.js-btn-dialog-save');
        gd_sms_send.set_click_sms080_contents(':checkbox[name="sms080Reject"]');
        gd_sms_send.set_click_save_contents('.js-btn-save-contents');
        gd_sms_send.set_click_more_contents('.js-btn-sms-contents');
        gd_sms_send.set_click_receiver_type(':radio[name=receiverType]');
        gd_sms_send.set_click_replace_code_insert('.replace-code-area .js-btn-insert');
        gd_sms_send.set_click_excel_upload('.js-btn-excel-upload');
        gd_sms_send.set_click_contents_search('.js-btn-search-contents');
        gd_sms_send.set_change_replace_code_group('select[name=replaceCodeGroup]');
        gd_sms_send.set_change_focus_contents('textarea[name="smsContents"]');
        gd_sms_send.set_change_auto_code_selector($('select[name="smsAutoCode"]'));

        $('.js-toggle-replace-code').click(function () {
            var $this = $(this);
            var $target = $($this.data('target'));
            console.log('toggle-replace-code', $target, open);
            $target.toggleClass('display-none');
            var text = $this.data('text');
            if (text) {
                $this.data('text', $this.text());
                $this.text(text);
            }
            var $sms = $('.sms-replace-code-area select[name=replaceCodeGroup]');
            var $notice = $sms.next();
            if ($target.hasClass('display-none')) {
                $notice.addClass('display-none');
            } else if ($sms.val() !== 'member') {
                $notice.removeClass('display-none');
            }
            if (open) {
                $notice.addClass('display-none');
                $sms.addClass('display-none');
                console.log('replace code table has display-none', $target.filter('div').hasClass('display-none'));
                if ($target.filter('div').hasClass('display-none')) {
                    $sms.next().next().addClass('display-none');
                } else {
                    $sms.next().next().removeClass('display-none');
                }
            }
        });

        var open = false;
        try {
            open = gd_sms_send.get_popup().is_open();
        } catch (e) {
            open = false;
        }
        gd_sms_send.set_is_default_mode(open === false);
        gd_sms_send.init();
        if (open) {
            gd_sms_send.popup_init();
        }
        $('.btn-icon-excel').click(function (e) {
            console.log(e);
            var $form = $('<form>').attr({
                method: 'post',
                action: '../member/sms_send_ps.php',
                name: 'formSmsSampleDownload'
            }).hide();
            $form.append($('<input>').attr({
                target: '_blank',
                type: 'hidden',
                name: 'mode',
                value: 'excelSampleDown'
            }));
            $('body').append($form);
            $form.submit();
            $form.remove();
        });

        var $formSendSms = gd_sms_send.get_form();

        // 폼 체크
        $formSendSms.validate({
            dialog: false,
            debug: true,
            submitHandler: function (form) {
                console.log('submitHan', form);
                var divisionInt = 1;
                if ($('input[name=sendFl]').val() === 'lms') {
                    divisionInt = <?php echo $lmsPoint;?>;
                }
                var $receiver_type = $('[name=receiverType]');
                var receiverCnt = parseInt($('input[name=receiverCnt]').val()) / divisionInt;
                var agreeCnt = parseInt($('input[name=agreeCnt]').val()) / divisionInt;
                var rejectCnt = parseInt($('input[name=rejectCnt]').val()) / divisionInt;
                var type = $receiver_type.filter(':checked').val();
                if (_.isUndefined(type)) {
                    type = $receiver_type.val();
                }
                var popup_params = {};
                if (type === 'popup') {
                    popup_params = gd_sms_send.get_popup().get_popup_params();
                    $(form).append('<input type="hidden" name="opener" value="' + popup_params.opener + '"/>');
                    $(form).append('<input type="hidden" name="searchType" value="' + popup_params.searchType + '"/>');
                    if (popup_params.searchType === 'search') {
                        $(form).find(':hidden[name="receiverSearch[]"]').remove();
                        $.each(popup_params.receiverSearch, function (idx, item) {
                            if (_.isArray(item)) {
                                $.each(item, function (idx2, item2) {
                                    $(form).append('<input type="hidden" name="receiverSearch[' + idx + '][' + idx2 + ']" value="' + item2 + '"/>');
                                });
                            } else {
                                $(form).append('<input type="hidden" name="receiverSearch[' + idx + ']" value="' + item + '"/>');
                            }
                        });
                    } else {
                        $(form).find(':hidden[name="receiverKeys[]"]').remove();
                        $(popup_params.receiverKeys).each(function (idx, item) {
                            $(form).append('<input type="hidden" name="receiverKeys[]" value="' + item + '"/>');
                        });
                    }
                } else if (type === 'direct') {
                    $(form).append('.js-direct-list .list-group-item :hidden');
                } else if (type === 'excel') {
                    $(form).append($('<input>').attr({
                        name: 'uploadKey',
                        value: $('#formExcel').data('uploadKey')
                    }));
                } else if (type === 'each') {
                    var receiverList = [];
                    $.each($('tbody tr', '#divSmsList'), function (idx, item) {
                        var $item = $(item);
                        receiverList.push($item.find('input[name="selectChk[]"]').val());
                    });
                    $('input[name=receiverList]').val(receiverList);
                } else {
                    $('#divSmsList').remove();
                }

                if (popup_params.opener === 'promotion') {
                    $(form).find(':hidden[name="receiverKeys[]"]').remove();
                    $(popup_params.receiverKeys).each(function (idx, item) {
                        $(form).append('<input type="hidden" name="receiverKeys[]" value="' + item + '"/>');
                    });
                }
                form.target = 'ifrmProcess';

                if (rejectCnt > 0 && ($('.js-cb-agree').length == 0 || ($('.js-cb-agree').length > 0 && $('.js-cb-agree').prop('checked') == false))) {
                    var dialogMessage = '발송대상 ' + receiverCnt + '명 중 수신거부회원이 ' + rejectCnt + '명 포함되어 있습니다.<br/>' +
                        '수신거부회원을 제외하고 SMS 발송하시겠습니까?<br/>' +
                        '수신거부한 회원에게 광고성 정보를 발송하는 경우 과태료가 부과될 수 있습니다.<br/>';
                    var titleInclude = '포함하고 발송 (' + receiverCnt + '명)';
                    var titleExcept = '제외하고 발송 (' + agreeCnt + '명)';
                    if (receiverCnt === rejectCnt) {
                        dialogMessage = '발송대상 ' + receiverCnt + '명 전체가 수신거부회원(' + rejectCnt + '명) 입니다.<br/>' +
                            '수신거부회원에게 SMS 발송하시겠습니까?<br/>' +
                            '수신거부한 회원에게 광고성 정보를 발송하는 경우 과태료가 부과될 수 있습니다.<br/>';
                        titleInclude = '수신거부회원에게 발송';
                        titleExcept = '제외하고 발송';
                    }

                    BootstrapDialog.show({
                        type: BootstrapDialog.TYPE_WARNING,
                        title: '수신거부회원 포함 안내',
                        message: dialogMessage,
                        onshow: function (dialog) {
                            if (receiverCnt === rejectCnt) {
                                dialog.getButton('btn-except').disable();
                            }
                        },
                        buttons: [
                            {
                                id: 'btn-cancel',
                                label: '발송 취소',
                                action: function (dialog) {
                                    dialog.close();
                                    return false;
                                }
                            },
                            {
                                id: 'btn-include',
                                label: titleInclude,
                                cssClass: 'btn-danger',
                                action: function (dialog) {
                                    var $exceptButton = dialog.getButton('btn-except');
                                    var $includeButton = this;
                                    var $cancelButton = dialog.getButton('btn-cancel');
                                    $exceptButton.disable();
                                    $includeButton.disable();
                                    $cancelButton.disable();
                                    $includeButton.spin();
                                    dialog.setClosable(false);
                                    dialog.setMessage('발송대상 ' + receiverCnt + '명 (수신거부회원 포함) 에게 발송중입니다.');

                                    $('input[name=rejectSend]').val('y');
                                    form.submit();
                                }
                            },
                            {
                                id: 'btn-except',
                                label: titleExcept,
                                cssClass: 'btn-red',
                                action: function (dialog) {
                                    var $exceptButton = this;
                                    var $includeButton = dialog.getButton('btn-include');
                                    var $cancelButton = dialog.getButton('btn-cancel');
                                    $exceptButton.disable();
                                    $includeButton.disable();
                                    $cancelButton.disable();
                                    $exceptButton.spin();
                                    dialog.setClosable(false);
                                    dialog.setMessage('발송대상 ' + agreeCnt + '명 (수신회원) 에게 발송중입니다.');

                                    $('input[name=rejectSend]').val('n');
                                    form.submit();
                                }
                            }
                        ]
                    });
                } else {
                    BootstrapDialog.show({
                        title: 'SMS 발송',
                        message: '발송대상 ' + receiverCnt + '명 에게 발송 하시겠습니까?',
                        closable: false,
                        buttons: [{
                            id: 'btn-close',
                            label: '취소',
                            action: function(dialogItself){
                                dialogItself.close();
                            }
                        }, {
                            id: 'btn-confirm',
                            label: '<span>확인</span>',
                            cssClass: 'btn-black',
                            action: function(dialog) {
                                var $confirmButton = this;
                                var $closeButton = dialog.getButton('btn-close');
                                $confirmButton.disable();
                                $closeButton.disable();
                                $confirmButton.spin();
                                dialog.setClosable(false);
                                $closeButton.hide();
                                $confirmButton.children().last().text('발송중');
                                $('input[name=rejectSend]').val('n');
                                form.submit();
                            }
                        }]
                    });
                }
            },
            rules: {
                password: {required: true, rangelength: [10, 16]},
                smsContents: {required: true},
                smsPoint: {required: true, min: 1},
                receiverCnt: {
                    required: function (input) {
                        var required = true;
                        setReceiverCount(); // 발송 건수 입력
                        return required;
                    },
                    max: parseInt($('input[name=smsPoint]').val()),
                    min: 1
                }
            },
            messages: {
                password: {
                    required: 'SMS 인증번호를 입력해주세요.',
                    minlength: 'SMS 인증번호는 10 ~ 16자리로만 사용하실 수 있습니다.<br/>[마이고도 > 쇼핑몰 관리]와 SMS 수정 페이지에서 SMS 인증번호 변경 후 다시 시도해주세요.',
                    maxlength: 'SMS 인증번호는 10 ~ 16자리로만 사용하실 수 있습니다.<br/>[마이고도 > 쇼핑몰 관리]와 SMS 수정 페이지에서 SMS 인증번호 변경 후 다시 시도해주세요.'
                },
                smsContents: {required: 'SMS 발송 내용을 입력해 주세요.'},
                smsPoint: {
                    required: 'SMS 포인트가 없습니다. SMS 포인트 충전하기를 통해 충전 후 발송을 하시기 바랍니다.',
                    min: 'SMS 포인트가 없습니다. SMS 포인트 충전하기를 통해 충전 후 발송을 하시기 바랍니다.'
                },
                receiverCnt: {
                    required: 'SMS 수신 회원이 없습니다. 발송할 회원을 확인해 주세요.',
                    max: 'SMS 잔여 포인트가 부족 합니다. SMS 포인트 충전하기를 통해 충전 후 발송을 하시기 바랍니다.',
                    min: 'SMS 수신 회원이 없습니다. 발송할 회원을 확인해 주세요.'
                }
            }
        });

        // SMS 사전 등록 발신번호 선택하기
        $('.js-sms-call-number').click(function (e) {
            var params = {returnInput: 'smsCallNum'};
            $.get('../member/layer_sms_call_number_select.php', params, function (data) {
                BootstrapDialog.show({
                    title: 'SMS 발신번호 목록',
                    message: $(data),
                    closable: true
                });
            });
        });

        // 발송설정에 따른 예약 설정 화면
        $('input[name=smsSendType]').click(function (e) {
            if ($(this).val() == 'reserve') {
                $('input[name=smsSendReserveDate]').attr('disabled', false);
            } else {
                $('input[name=smsSendReserveDate]').attr('disabled', true);
                $('input[name=smsSendReserveDate]').val('');
            }
        });

        // SMS 예약 발송을 할수 있는 시간을 설정
        $('.js-datetimepicker').click(function (e) {
            $(this).data('DateTimePicker').disabledHours([<?php echo implode(',', $smsForbidTime);?>]);
        });

        // 글자수 체크
        $('textarea[name=smsContents]').keyup(setSendLength).change(setSendLength);

        <?php if(empty($reSenderData) === false || empty($smsContents) === false) { ?>
        setSendLength();
        <?php } ?>
    });

    function limit_password_callback() {
        $(':password[name=\'password\']').prop('disabled', true);
    }

    //-->
</script>
<!-- //@formatter:off -->
<form id="formSendSms" name="formSendSms" action="sms_ps.php" method="post">
    <input type="hidden" name="mode" value="smsSend"/>
    <input type="hidden" name="smsPoint" value="<?php echo $smsPoint; ?>"/>
    <input type="hidden" name="receiverCnt" value=""/>
    <input type="hidden" name="agreeCnt" value=""/>
    <input type="hidden" name="rejectCnt" value="<?=$countSmsFlN?>"/>
    <input type="hidden" name="sendFl" value="sms"/>
    <input type="hidden" name="rejectSend" value="n"/>
    <input type="hidden" name="receiverList" value=""/>
    <?php if ($popupMode === false && gd_isset($smsLogSno, 0) > 0) { ?>
        <input type="hidden" name="smsLogSno" value="<?= $smsLogSno ?>"/>
        <?php if (count(gd_isset($arrSmsSendListSno, [])) > 0) {
            foreach ($arrSmsSendListSno as $index => $sno) { ?>
                <input type="hidden" name="arrSmsSendListSno[]" value="<?= $sno ?>"/>
            <?php }
        } ?>
    <?php } ?>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="submit" value="SMS 발송" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual">
        SMS 발송 정보 설정
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tr>
            <th>SMS 잔여 포인트</th>
            <td class="form-inline">
                <strong class="font-num text-red"><?php echo number_format($smsPoint, 1); ?></strong> 포인트
                <?php if (gd_is_provider() === false) { ?>
                    <button type="button" class="btn btn-gray btn-sm" onclick="show_popup('./sms_charge.php?popupMode=yes')">SMS 포인트 충전하기</button>
                <?php } ?>
                <?php if ($smsPoint === '0') { ?>
                <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 포인트 정보가 정상 출력됩니다.</div>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th class="require">SMS 인증번호</th>
            <td class="form-inline">
                <div class="mgb10">
                    <input type="password" class="form-control width-lg" name="password" minlength="10" maxlength="16"/>
                </div>
                <div class="notice-danger">무단으로 SMS 포인트가 사용되지 않도록 SMS 인증번호로 인증 후 발송할 수 있습니다.</div>
                <div class="notice-info">SMS 인증번호는
                    <a class="btn-link" href="javascript:gotoGodomall('main');">[마이고도 > 쇼핑몰 관리]</a> 에서 확인할 수 있습니다.
                </div>
            </td>
        </tr>
        <tr>
            <th class="require">SMS 발신번호</th>
            <td class="form-inline">
                <?php if ($smsPreRegister === false) { ?>
                    <div>
                        <input type="hidden" name="smsCallNum" value=""/>
                        <span class="smsCallNumText">
                            <span class="text-darkred">등록된 SMS 발신번호가 없습니다.</span>
                        </span>
                        <?php if (gd_is_provider() === false) { ?>
                            <a href="https://www.godo.co.kr/mygodo/sms/intro.gd" target="_blank" class="btn btn-gray btn-sm">발신번호 등록하기</a>
                        <?php } ?>
                    </div>
                    <div class="notice-info">
                        발신번호 사전등록제 : (전기통신사업법 제 84조의 2) 거짓으로 표시된 전화번호로 인한 이용자 피해 예방을 위해 사전 등록한 발신번호로만 SMS를 발송하실 수 있습니다. <a href="https://www.godo.co.kr/customer/board-view.gd?type=notice&idx=1247" target="_blank"
                                                                                                                           class="snote bold btn-link">자세히보기 ></a>
                    </div>
                <?php } elseif ($smsPreRegister === 'reset') { ?>
                    <div>
                        <input type="hidden" name="smsCallNum" value=""/>
                        <span class="smsCallNumText">
                            <span class="text-darkred">기 설정된 SMS 발신번호는 사전 등록된 번호가 아닙니다.</span>
                        </span>
                        <?php if (gd_is_provider() === false) { ?>
                            <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 선택하기</button>
                        <?php } ?>
                    </div>
                <?php } elseif ($smsPreRegister === 'empty') { ?>
                    <div>
                        <input type="hidden" name="smsCallNum" value=""/>
                        <span class="smsCallNumText">
                            <span class="text-darkred">SMS 발신번호를 선택해주세요.</span>
                        </span>
                        <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 선택하기</button>
                    </div>
                <?php } elseif ($smsPreRegister === true) { ?>
                    <input type="hidden" name="smsCallNum" value="<?php echo gd_isset($smsCallNum) ?>"/>
                    <span class="smsCallNumText number text-darkblue bold"><?php echo gd_number_to_phone($smsCallNum) ?></span>
                    <?php if (gd_is_provider() === false) { ?>
                        <button type="button" class="btn btn-white btn-sm js-sms-call-number">발신번호 변경하기</button>
                    <?php } ?>
                <?php } ?>
                <?php if ($smsPreRegister !== true) { ?>
                <div class="notice-danger">[SMS 설정]에 설정된 인증번호와 <a href="javascript:gotoGodomall('main');" class="btn-link text-red"><b>[마이고도 > 쇼핑몰 관리]</b></a>에 설정된 인증번호가 일치해야 발신번호 정보가 정상 출력됩니다.</div>
                <?php } ?>
            </td>
        </tr>

        <?php if ($receiverMode === 'select' && gd_isset($opener, '') !== 'order' && gd_isset($opener, '') !== 'goods') { ?>
            <tr>
                <th class="require">대상 회원 선택</th>
                <td class="form-inline">
                    <label for="receiverType1" class="radio-inline">
                        <input type="radio" name="receiverType" id="receiverType1" value="each" checked="checked"/>
                        회원 직접 선택
                    </label>
                    <label for="receiverType2" class="radio-inline">
                        <input type="radio" name="receiverType" id="receiverType2" value="group"/>
                        회원 등급 선택
                    </label>
                    <label for="receiverType3" class="radio-inline">
                        <input type="radio" name="receiverType" id="receiverType3" value="all"/>
                        전체 회원 발송
                    </label>
                    <label for="receiverType4" class="radio-inline">
                        <input type="radio" name="receiverType" id="receiverType4" value="direct"/>
                        직접 입력
                    </label>
                    <label for="receiverType5" class="radio-inline">
                        <input type="radio" name="receiverType" id="receiverType5" value="excel"/>
                        엑셀 업로드
                    </label>
                    <div class="sms-target-area mgt10">
                        <div class="display-none target-area-excel">
                            <!-- 2017-03-10 yjwee undercore template 으로 처리할 경우 bootstrap 적용에 시간이 소요되어서 엑셀만 예외처리하며 첫번째 form 이 사라지는 현상 때문에 공백 폼을 추가함 -->
                            <input type="file" name="excel" value="" class="form-control js-file-excel"/>
                            <input type="button" class="btn btn-sm btn-white js-btn-excel-upload" value="엑셀업로드"/>
                            <input type="button" value="엑셀 샘플 다운로드" class="btn btn-sm btn-white btn-icon-excel" data-link=""/>
                        </div>
                    </div>
                    <div id="member_groupLayer" class="selected-btn-group"></div>
                    <div class="notice-info mgt5">
                        정보통신망법에 따라 수신거부한 회원에게는 광고성정보를 발송할 수 없으며, 위반시 과태료가 부과됩니다.
                    </div>
                    <?php if ($gGlobal['isUse']) { ?>
                        <div class="notice-info">
                            해외몰 회원에게는 SMS 발송이 제한되며, 국내몰 회원만 검색이 가능합니다.
                        </div>
                    <?php } ?>
                    <div class="notice-danger display-none js-sleep-notice">
                        휴면회원의 정보를 포함하지 않도록 주의하시기 바랍니다.
                    </div>
                    <div class="notice-info display-none target-area-excel">
                        엑셀 파일 저장은 반드시 "Excel 97-2003 통합문서"로 저장을 하셔야 합니다. 그외 csv 나 xlsx파일 등은 지원이 되지 않습니다.
                    </div>
                </td>
            </tr>
        <?php } else { ?>
            <tr>
                <th>발송 대상</th>
                <td class="form-inline">
                    <div class="display-inline-block">
                        발송인원 총
                        <span class="text-red sms-target-area" data-total="<?= $receiverCount ?>" data-reject-count="<?= $countSmsFlN ?>"><?= $receiverCount ?></span>
                        명
                        <?php if($countSmsFlN > 0) { ?>
                            <span class="text-red js-reject-count" data-reject-count="<?= $countSmsFlN ?>">(수신거부 대상자 <?= $countSmsFlN ?> 명 포함)</span>
                        <?php } ?>
                    </div>
                    <input type="hidden" name="receiverType" value=<?= $popupMode ? "popup" : "reSend" ?>>
                    <?php foreach ($receiverData as $key => $val) { ?>
                        <input type="hidden" name="popupData[]" value="<?php echo implode(STR_DIVISION, $val); ?>" data-mode="<?php echo $receiverMode; ?>" data-phone="<?php echo $val['memNm']; ?>"
                               data-smsfl="<?php echo $val['smsFl']; ?>"/>
                        <?php
                        if ($val['memNo'] === '0') $tmpTxt[] = '<span class="text-green">비회원</span>';
                        if ($val['smsFl'] === 'n') $tmpTxt[] = '<span class="text-red">수신거부 대상자</span>';
                        if(empty($tmpTxt) === false) echo '('.implode(', ', $tmpTxt).')';
                        unset($tmpTxt);
                        ?>
                    <?php } ?>
                    <?php if(empty($receiverData)) { ?>
                        <div class="">
                            <label class="checkbox-inline">
                                <input type="checkbox" class="js-cb-agree" value="y" data-receive-total="<?= $receiverCount ?>" data-reject-count="<?= $countSmsFlN ?>"/>
                                수신동의한 대상에게만 발송
                            </label>
                        </div>
                    <?php } ?>
                    <div class="notice-info">정보통신망법에 따라 수신 거부한 대상에게는 광고성 정보를 발송할 수 없으며, 위반 시 과태료가 부과됩니다.</div>
                </td>
            </tr>
        <?php } ?>
        <tr>
            <th>발송 설정</th>
            <td class="form-inline">
                <label title="즉시 발송" class="radio-inline">
                    <input type="radio" name="smsSendType" value="now" checked="checked"/>
                    즉시 발송
                </label>
                <label title="예약 발송" class="radio-inline">
                    <input type="radio" name="smsSendType" value="reserve"/>
                    예약 발송
                </label>
                <div class="input-group js-datetimepicker">
                    <input type="text" name="smsSendReserveDate" value="" class="form-control width-md" placeholder="예약 일자 선택" disabled="disabled">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                </div>
                부터 순차 발송
            </td>
        </tr>
        <tr>
            <th>광고성 문자</th>
            <td class="form-inline">
                <label title="광고성 문구 추가" class="checkbox-inline">
                    <input type="checkbox" name="sms080Reject" value="y" <?= ($sms080Policy['status'] == 'O' && $sms080Policy['use'] == 'y') ? '' : 'disabled' ?> data-reject-number="<?= gd_isset($sms080Policy['rejectNumber'], '') ?>"/>
                    광고성 문구 추가
                </label>
                <div class="notice-info">광고성 문구를 추가하려면 <b><a href="../service/service_info.php?menu=convenience_refusal_info" target="_blank" class="text-blue">[080 수신거부 사용신청]</a></b>을 먼저 해주시기 바랍니다.</div>
            </td>
        </tr>
        <tr>
            <th class="require">발송 내용 입력</th>
            <td class="form-inline sms-replace-code-area">
                <div class="row">
                    <div class="col-xs-3 pdr0">
                        <span class="sms-type notice-info">SMS : 건당 1포인트 차감</span>
                        <span class="lms-type notice-danger display-none">LMS : 건당 <?php echo $lmsPoint; ?>포인트 차감</span>
                        <button type="button" class="btn btn-white btn-sm pull-right js-toggle-replace-code" data-target=".replace_code_area" data-text="치환코드 닫기">치환코드 보기</button>
                    </div>
                    <div class="col-xs-9"><?= gd_select_box('replaceCodeGroup', 'replaceCodeGroup', $replaceCodeGroup, null, $replaceCodeGroupKey, null, null, 'pull-left replace_code_area display-none') ?>
                        <div class="notice-danger pull-left mgl15 display-none">회원 외 치환코드는 정상 적용되지 않으니 저장된 메시지 수정 시에만 이용바랍니다.</div>
                    </div>
                </div>
                <div class="row pdt5">
                    <div class="col-xs-3 pdr0">
                        <label class="width100p">
                            <textarea name="smsContents" rows="13" class="smsContents form-control width100p" data-close="true"><?= $smsContents ?></textarea>
                        </label>
                    </div>
                    <div class="col-xs-9 display-none replace_code_area">
                        <div class="table-scroll">
                            <table class="table table-bordered table-rows mgb0 js-table-replace-code">
                                <colgroup>
                                    <col class="width-sm">
                                    <col class="width-2xl">
                                    <col class="width-3xs">
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>치환코드</th>
                                    <th>설명</th>
                                    <th>삽입</th>
                                </tr>
                                </thead>
                                <tbody class="replace-code-area display-none" data-type="goods">
                                <tr> <td class="center">[{rc_mallNm}]</td> <td>쇼핑몰 명, 상점명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{restockName}</td> <td>(재입고 알림) 신청자명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{restockGoodsNm}</td> <td>(재입고 알림) 상품명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{restockOptionName}</td> <td>(재입고 알림) 상품옵션</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{restockGoodsUrl}</td> <td>(재입고 알림) 상품 URL</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                </tbody>
                                <tbody class="replace-code-area display-none" data-type="order">
                                <tr> <td class="center">[{rc_mallNm}]</td> <td>쇼핑몰 명, 상점명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{orderName}</td> <td>주문자 이름</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{orderNo}</td> <td>주문번호</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{settlePrice}</td> <td>총 주문 금액</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{cancelPrice}</td> <td>취소금액</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{gbRefundPrice}</td> <td>실 환불금액</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{depositNm}</td> <td>입금자명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{bankAccount}</td> <td>입금계좌번호</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{bankInfo}</td> <td>은행명/계좌번호/예금주</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{orderDate}</td> <td>주문일</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                </tbody>
                                <tbody class="replace-code-area display-none" data-type="member">
                                <tr> <td class="center">[{rc_mallNm}]</td> <td>쇼핑몰 명, 상점명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{memId}</td> <td>회원 아이디</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{memNm}</td> <td>회원명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{sleepScheduleDt}</td> <td>휴면회원전환예정일</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{smsAgreementDt}</td> <td>SMS 수신동의일</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{mailAgreementDt}</td> <td>메일 수신동의일</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{groupNm}</td> <td>회원등급</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{mileage}</td> <td>보유한 마일리지</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{deposit}</td> <td>보유한 예치금</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                </tbody>
                                <tbody class="replace-code-area display-none" data-type="promotion">
                                <tr> <td class="center">[{rc_mallNm}]</td> <td>쇼핑몰 명, 상점명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{memNm}</td> <td>회원명 (없는 경우 공백으로 치환)</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{eventNm}</td> <td>(기획전 홍보) 기획전명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{eventDt}</td> <td>(기획전 홍보) 기획전 기간</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{eventUrl}</td> <td>(기획전 홍보) 기획전 url</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                </tbody>
                                <tbody class="replace-code-area display-none" data-type="board">
                                <tr> <td class="center">[{rc_mallNm}]</td> <td>쇼핑몰 명, 상점명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                <tr> <td class="center">{memNm}</td> <td>회원명</td> <td class="center"> <button class="btn btn-sm btn-white js-btn-insert" type="button">삽입</button> </td> </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row pdt10">
                    <div class="col-xs-12">
                        <input type="text" name="smsStringCount" id="smsStringCount" value="0" readonly="readonly" class="form-control width-3xs"> / <span class="sms-type"><?php echo $smsStringLimit; ?></span><span class="lms-type display-none"><?php echo number_format($lmsStringLimit); ?></span> Bytes
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <div class="notice-info">URL 삽입 시 플러스샵 [단축 URL] 앱을 이용하시면 짧고 간단하게 URL을 사용하실 수 있습니다. <a href="http://plus.godo.co.kr/goods/view.gd?idx=12" class="btn-link" target="_blank">바로가기</a></div>
                        <div class="notice-info">치환코드 사용 시 LMS로 발송될 수 있으며 일부 문자가 포인트 부족으로 발송되지 않을 수 있습니다.<br/>- 포인트 충전 후 <a href="../member/sms_log.php" class="btn-link" target="_blank">SMS 발송 내역 보기</a>에서 재발송하실 수 있습니다.</div>
                    </div>
                </div>
                <div class="row pdt10">
                    <div class="col-xs-12">
                        <button type="button" class="btn btn-white js-btn-sms-contents">발송 내용 관리<span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span></button>
                        <button type="button" class="btn btn-icon-plus-red-box js-btn-save-contents">현재 내용 저장</button>
                    </div>
                </div>
                <div class="row pdt10 js-sms-contents-search-area display-none">
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="col-xs-6 pdr0">
                                <label>
                                    <?= gd_select_box('smsAutoCode', 'smsAutoCode', $smsContentsGroupCode, null, $smsAutoCode, '전체') ?>
                                </label>
                                <input type="text" class="form-control width-xl" placeholder="제목이나 내용을 입력하여 주세요." name="searchWord">
                                <button type="button" class="btn btn-sm btn-gray js-btn-search-contents">검색</button>
                            </div>
                            <div class="col-xs-6">
                                <div class="pull-right">
                                    <button type="button" class="btn btn-white mgr5 js-btn-select-sms-all">전체선택</button>
                                    <button type="button" class="btn btn-white js-btn-delete-sms-selected">선택삭제</button>
                                </div>
                            </div>
                        </div>
                        <div class="row pdt10">
                            <div class="col-xs-12">
                                <div class="sms-contents-area row"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="center js-pagination"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-12">
                                <div class="notice-danger">치환코드는 지정된 페이지에서만 사용하실 수 있습니다.</div>
                                <div class="notice-info">- 예) 주문 관련 치환코드는 주문리스트에서 SMS 발송 시에만 사용하실 수 있습니다.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </table>
    <div class="display-none" id="divSmsList">
        <?php if (empty($reSenderData) === false) { ?>
            <table class="table table-rows">
                <colgroup>
                    <col class="width2p">
                    <col class="width3p">
                    <col class="width20p">
                    <col class="width20p">
                    <col class="width15p">
                    <col class="width20p">
                    <col class="width20p">
                </colgroup>
                <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="selectChk">
                    </th>
                    <th>번호</th>
                    <th>아이디/닉네임</th>
                    <th>이름</th>
                    <th>등급</th>
                    <th>이메일</th>
                    <th>휴대폰번호</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($reSenderData as $idx => $resender) { ?>
                    <tr class="text-center" data-member-no="<?= $resender['memNo'] ?>">
                        <td>
                            <input type="checkbox" name="selectChk[]" value="<?= $resender['memNo'] ?>" data-appfl="<?= $resender['appFl'] ?>" data-maillingfl="<?= $resender['maillingFl'] ?>" data-smsfl="<?= $resender['smsFl'] ?>">
                        </td>
                        <td><?= ($idx + 1) ?></td>
                        <td>
                            <span class="font-eng js-layer-crm hand"><?= $resender['memId'] ?></span>
                            <?= gd_get_third_party_icon_web_path($resender['snsTypeFl']); ?>
                            <?php if ($resender['nickNm']) { ?>
                                <div class="notice-ref notice-sm"><?= $resender['nickNm'] ?></div>
                            <?php } ?>
                        </td>
                        <td>
                            <span class="js-layer-crm hand"><?= $resender['memNm'] ?></span>
                        </td>
                        <td>
                            <span class="js-layer-crm hand"><?= $resender['groupNm'] ?></span>
                        </td>
                        <td>
                            <span class="font-eng js-layer-crm hand"><?= $resender['email'] ?></span>
                            <div class="notice-ref notice-sm">(<?= $resender['maillingFlText'] ?>)</div>
                        </td>
                        <td>
                            <span class="font-num js-layer-crm hand"><?= $resender['cellPhone'] ?></span>
                            <div class="notice-ref notice-sm">(<?= $resender['smsFlText'] ?>)</div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</form>
<form id="formExcel" name="formExcel" action="sms_send_ps.php" method="post" enctype="multipart/form-data" class="display-none">
    <input type="hidden" name="mode" value="excelUpload"/>
    <input type="file" name="excel" value=""/>
</form>
<script type="text/html" id="templateEach">
    <button type="button" class="btn btn-white btn-xs mgr10" id="btnSearchMember">선택하기</button>
    <div class="display-inline-block" id="divSearchCount">
        <a href="#" id="linkSearchMember"> 발송인원 총
            <span class="text-red js-receive-total" data-receive-total="<%= receiveTotal %>"><%= receiveTotal %></span>
            명
            <span class="text-red">(수신거부 대상자</span>
            <span class="text-red js-reject-count" data-reject-count="<%= rejectCount %>"><%= rejectCount %></span>
            <span class="text-red">명 포함)</span>
        </a>
    </div>
    <div class="">
        <label class="checkbox-inline">
            <input type="checkbox" class="js-cb-agree" value="y" checked="checked"/>
            수신동의한 회원에게만 발송
        </label>
    </div>
</script>
<script type="text/html" id="templateGroup">
    <button type="button" class="btn btn-white btn-xs mgr10" id="btnSearchMember">선택하기</button>
    <div class="display-inline-block">
        발송인원 총
        <span class="text-red js-receive-total" data-receive-total="<%= receiveTotal %>"><%= receiveTotal %></span>
        명
        <span class="text-red js-reject-count" data-reject-count="<%= rejectCount %>">(수신거부 대상자 <%= rejectCount %> 명 포함)</span>
    </div>
    <div class="">
        <label class="checkbox-inline">
            <input type="checkbox" class="js-cb-agree" value="y" checked="checked" data-receive-total="<%= receiveTotal %>" data-reject-count="<%= rejectCount %>"/>
            수신동의한 회원에게만 발송
        </label>
    </div>
</script>
<script type="text/html" id="templateAll">
    <div class="display-inline-block">
        발송인원 총
        <span class="text-red js-receive-total" data-receive-total="<%= receiveTotal %>"><%= receiveTotal %></span>
        명
        <span class="text-red js-reject-count" data-reject-count="<%= rejectCount %>">(수신거부 대상자 <%= rejectCount %> 명 포함)</span>
    </div>
    <div class="">
        <label class="checkbox-inline">
            <input type="checkbox" class="js-cb-agree" value="y" checked="checked" data-receive-total="<%= receiveTotal %>" data-reject-count="<%= rejectCount %>"/>
            수신동의한 회원에게만 발송
        </label>
    </div>
</script>
<script type="text/html" id="templateDirect">
    <div class="width-xl pdt5 form-inline">
        <label class="width70p">
            <input type="text" class="width100p form-control" placeholder="예) 010-1111-2222" name="directCellPhone"/>
        </label>
        <button type="button" class="btn btn-sm btn-white btn-icon-plus pull-right mgr0 js-btn-add-cell-phone">추가</button>
        <div class="mgt5 form-control ta-r width100p bgc-gray">
            <strong class="">발송인원 총
                <span class="text-red js-receive-total" data-receive-total="<%= receiveTotal %>"><%= total %></span>
                명
            </strong>
        </div>
        <% if (scroll) { %>
        <ul class="list-group js-direct-list form-control width-xl" style="overflow-y:scroll;">
        <% } else { %>
        <ul class="list-group js-direct-list form-control width-xl">
            <% } %>
            <% if (cell_phone != '') { %>
            <li class="list-group-item"><%= cell_phone %><input type="hidden" name="directReceiverNumbers[]" value="<%- cell_phone %>"></li>
            <% } %> <% direct_list_group_item.each(function(idx, item) { %> <%= item.outerHTML %> <% }); %>
        </ul>
        <div class="right">
            <button type="button" class="btn btn-xs btn-gray js-btn-delete-selected">선택삭제</button>
            <button type="button" class="btn btn-xs btn-gray js-btn-delete-all">전체삭제</button>
        </div>
    </div>
</script>
<script type="text/html" id="templateSmsContents">
    <div class="col-xs-4">
        <div class="sms-contents-view pd10">
            <%= checkbox %> <%= subject %>
            <button type="button" class="btn btn-sm btn-white pull-right js-btn-save" data-text="저장">수정</button>
            <button type="button" class="btn btn-sm btn-white pull-right js-btn-insert mgr5">삽입</button>
            <label class="width100p pdt5">
                <textarea rows="13" class="form-control width100p" name="contents"><%= contents %></textarea>
            </label>
        </div>
    </div>
</script>
<script type="text/html" id="templateEmptySmsContents">
    <div class="col-xs-12 center mgl30t">
        <span>검색된 정보가 없습니다.</span>
    </div>
</script>
<script type="text/template" id="templatePagination">
    <nav>
        <ul class="pagination pagination-sm">
            <% for (var i = page.start; i <= page.end; i++) { %> <% if (i === (page.now *1)) { %>
            <li class="active">
                <span><%- i %></span>
            </li>
            <% } else { %>
            <li><a href="#" class="page_navi_number"><%- i %></a></li>
            <% } %> <% } %>
        </ul>
    </nav>
</script>
<script type="text/html" id="templateSaveContentsDialog">
    <div class="layer_wrap">
        <div class="notice-info">작성한 내용을 저장할 그룹과 제목을 선택하여 주시기 바랍니다.</div>
        <div class="table-cols form-inline top-border-clear pdt10">
            <?= gd_select_box('dialogSmsAutoCode', 'dialogSmsAutoCode', $smsContentsGroupCode, null, null, '그룹 선택') ?>
            <input type="text" class="form-control width-xl js-maxlength" placeholder="제목을 입력하여 주세요." name="dialogSubject" maxlength="10">
        </div>
        <div class="pdt30 center">
            <button type="button" class="btn btn-red btn-lg js-btn-dialog-save">저장</button>
        </div>
    </div>
</script>
<!-- //@formatter:on -->
