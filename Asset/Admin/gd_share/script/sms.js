/*global
 layer_add_info, alert, dialog_alert, dialog_alert, close_validate_process_dialog, opener, $, _, BootstrapDialog, window
 */
var gd_sms_send;
gd_sms_send = (function (dialog) {
    'use strict';

    var sms = {};
    sms.request_uri = '../member/sms_send_ps';

    var target_area;
    var $form, $pagination, $contents_list, $auto_code_selector, $search_word, $sms_receiver_list, $sms_contents_search_area;
    var $receiver_type, $replace_code_group, $send_contents, $button_more_contents;
    var is_default_mode = true;

    /**
     * 발송인원 계산 레이어 발생
     */
    function show_count_dialog() {
        dialog.show({message: '발송인원을 계산 중입니다'});
    }

    /**
     * 오류 레이어 발생
     */
    function show_error_dialog() {
        dialog.closeAll();
        dialog.show({message: '조회 중 오류가 발생하였습니다'});
    }

    /**
     * 발송대상 선택에 _.template 함수 처리
     *
     * @param {string} template_id
     * @param {*=} params
     * @return {string|object}
     */
    function print_target_area(template_id, params) {
        var compiled = _.template($(template_id).html());
        var default_params = {receiveTotal: 0, rejectCount: 0};
        if (typeof params === 'object') {
            compiled = compiled($.extend({}, default_params, params));
        } else {
            compiled = compiled(default_params);
        }
        return compiled;
    }

    /**
     * 발송내용 검색 조건 초기화
     */
    function init_search_settings() {
        var search_settings = {};

        /**
         * 검색 조건이 기존과 동일한지 여부를 체크
         */
        function equals(after) {
            console.log('search setting equals', search_settings, after);
            if (search_settings.cached === false) {
                return false;
            }
            function search_word() {
                return search_settings.searchWord === after.searchWord;
            }

            function sms_auto_code() {
                return search_settings.smsAutoCode === after.smsAutoCode;
            }

            function page() {
                return search_settings.page === after.page;
            }

            return (search_word() && sms_auto_code() && page());
        }

        search_settings = {
            cached: false,
            searchWord: '',
            smsAutoCode: '',
            page: 1,
            equals: equals
        };
        sms.search_settings = search_settings;
    }

    /**
     * 사용될 항목 요소 검증
     */
    function validate_elements() {
        var errors = [];
        if (!_.isElement(target_area[0])) {
            errors.push('undefined sms target area element.');
        }
        if (!_.isElement($form[0])) {
            errors.push('undefined sms send form element.');
        }
        if (!_.isElement($pagination[0])) {
            errors.push('undefined sms contents pagination elements');
        }
        if (!_.isElement($contents_list[0])) {
            errors.push('undefined content list elements');
        }
        if (!_.isElement($auto_code_selector[0])) {
            errors.push('undefined auto code selector elements');
        }
        if (!_.isElement($search_word[0])) {
            errors.push('undefined search word elements');
        }
        if (!_.isElement($receiver_type[0])) {
            errors.push('undefined sms receiver radio elements');
        }
        if (!_.isElement($replace_code_group[0])) {
            errors.push('undefined replace code group elements');
        }
        console.log('validate elements', errors);
    }

    /**
     * 선택된 회원 등급 sms 수신 대상 정보 조회
     */
    function count_target_group() {
        var memberGroupNo = [];
        $('input[name="memberGroupNo[]"]').each(function (idx, item) {
            memberGroupNo.push(item.value);
        });
        if (memberGroupNo.length > 0) {
            $.ajax(sms.request_uri, {
                method: 'post',
                data: {mode: 'countTargetGroup', memberGroupNo: memberGroupNo},
                beforeSend: show_count_dialog,
                success: function () {
                    var json_response = arguments[0];
                    var params = {
                        receiveTotal: json_response.total,
                        rejectCount: json_response.reject
                    };
                    console.log('count group success', json_response, params);
                    sms.remove_sms_target_area();
                    target_area.append(print_target_area('#templateGroup', params));
                    target_area.data('total', params.receiveTotal);
                    target_area.data('reject', params.rejectCount);
                },
                error: show_error_dialog,
                complete: dialog.closeAll
            });
        }
    }

    /**
     * sms 발송내용 영역 체크박스 태그 반환 함수
     *
     * @param {json} item
     * @returns {string}
     */
    function get_contents_view_checkbox(item) {
        var _item = item || {sno: '', smsAutoCode: '', subject: '', contents: '', isUserBasic: 'n'};
        var attr = [];
        attr.push('type="checkbox"');
        attr.push('name="checkSmsContents"');
        attr.push('value="' + _item.sno + '"');
        attr.push('data-code="' + _item.smsAutoCode + '"');
        attr.push('data-subject-length="' + _item.subject.length + '"');
        attr.push('data-contents-length="' + _item.contents.length + '"');
        if (_item.isUserBasic === 'y') {
            attr.push('disabled=true');
        }
        var html = [];
        html.push('<label class="checkbox-inline">');
        html.push('<input ' + attr.join(' ') + ' />');
        html.push('</label>');
        return html.join('');
    }

    /**
     * 발송내용 제목 태그 변환 함수
     *
     * @param {object} delegateTarget
     */
    function replace_with_subject(delegateTarget) {
        var span = delegateTarget.find('span');
        var attr = [];
        attr.push('type="text"');
        attr.push('name="subject"');
        attr.push('class="form-control width-xl js-maxlength"');
        attr.push('maxlength="10"');
        attr.push('value="' + span.text() + '"');
        var input = '<input ' + attr.join(' ') + '">';
        span.replaceWith(input);
    }

    function trigger_events() {
        $receiver_type.filter(':first').trigger('click');
        $replace_code_group.trigger('change');
    }

    /**
     * 팝업 창 모드이며 호출한 페이지의 조건에 따라 수신대상을 조회해야하는 경우
     */
    function count_target_popup() {
        var sms_send_popup = opener.gd_sms_send_popup;
        var params = {
            mode: 'countTargetPopup',
            receiver: sms_send_popup.get_popup_params()
        };
        $.ajax('../member/sms_send_ps.php', {
            data: params,
            method: 'post',
            beforeSend: show_count_dialog,
            success: function () {
                var json_response = arguments[0];
                var params = {receiveTotal: json_response.total};
                console.log('count popup success', json_response, params);
                target_area.text(params.receiveTotal);
                target_area.data('total', params.receiveTotal);
            },
            error: show_error_dialog,
            complete: dialog.closeAll
        });
    }

    /**
     * 발송내용 리스트 갱신
     */
    function reload_sms_contents_list() {
        var $active_page = $pagination.find('.active span');
        var params = {};
        if ($active_page.length) {
            var current = ($active_page.text() * 1);
            params = {page: current};
        }
        sms.print_sms_contents(params);
    }

    sms.init = function () {
        sms.last_selected_index = -1;
        sms.contents_focus_position = 0;
        init_search_settings();
        validate_elements();
        trigger_events();
    };

    sms.popup_init = function () {
        var sms_send_popup = this.get_popup();
        var opener_params = sms_send_popup.get_popup_params();
        console.log('popup init', opener_params);
        $replace_code_group.val(opener_params.opener);
        var $selected_option = $replace_code_group.find('option[value="' + opener_params.opener + '"]');
        var type_text = $selected_option.text();
        $selected_option.trigger('change');
        $replace_code_group.parent().append('<div class="notice-danger">' + type_text + '과 관련된 치환코드만 노출됩니다.</div>');
        $replace_code_group.removeAttr('style');
        $('div.replace_code_area').removeClass('display-none');
        var $toggle_replace_code = $('.js-toggle-replace-code');
        var text = $toggle_replace_code.data('text');
        if (text) {
            $toggle_replace_code.data('text', $toggle_replace_code.text());
            $toggle_replace_code.text(text);
        }
        if (opener_params.opener === 'promotion') {
            $auto_code_selector.val('01007504');
        } else if (opener_params.opener === 'order') {
            $auto_code_selector.val('01007502');
        } else if (opener_params.opener === 'member') {
            $auto_code_selector.val('01007503');
        } else if (opener_params.opener === 'goods') {
            $auto_code_selector.val('01007501');
        } else if (opener_params.opener === 'board') {
            $auto_code_selector.val('01007505');
        }
        $button_more_contents.trigger('click');

        var count_target_popup_use = false;
        if (opener_params.opener === 'order' && opener_params.searchType === 'search') {
            count_target_popup_use = true;
        }
        //상품재입고 알림 전체 신청자, 검색신청자
        if (opener_params.opener === 'goods' && opener_params.searchType === 'search') {
            count_target_popup_use = true;
        }
        if (count_target_popup_use === true) {
            count_target_popup();
        }
    };

    /**
     * 엑셀 업로드
     * @param selector
     */
    sms.set_submit_excel = function (selector) {
        var options = {
            dataType: 'json',
            beforeSubmit: function (params, $form) {
                $form.removeAttr('data-upload-key');
                $form.removeAttr('data-receiver-count');
                var is_validate = $form.data('submit');
                $(params).each(function (idx, item) {
                    if (item.name === 'excel' && item.value === '') {
                        is_validate = false;
                        return false;   // exit each
                    }
                });
                if (is_validate) {
                    $form.data('submit', false);
                }
                console.log('beforeSubmit.is_validate', is_validate);
                return is_validate;
            },
            success: function (data, status, xhr, $form) {
                var result = $.extend({}, {success: false, uploadKey: '', resultUrl: ''}, data);
                console.log('excel upload result', result);
                if (result.success) {
                    window.open(result.resultUrl, '_blank');    // excel download
                }
                $form.data('upload-key', result.uploadKey);
                $.ajax('../member/sms_send_ps.php', {
                    method: 'post',
                    data: {
                        uploadKey: $form.data('upload-key'),
                        mode: 'excelResult'
                    },
                    beforeSend: function () {
                        var upload_key = $form.data('upload-key');
                        if (!_.isString(upload_key)) {
                            console.log('excelResult.beforeSend.upload-key', upload_key);
                            return false;
                        }
                    },
                    success: function () {
                        var resp = {count: 0};
                        resp = $.extend({}, resp, arguments[0]);
                        console.log('excelResult.success.response', resp);
                        $form.data('receiver-count', resp.count);
                        var $target = $('.target-area-excel');
                        $target.find('.js-excel-count').remove();
                        $target.append('<p class="js-excel-count">발송인원 총 <span class="text-red">' + resp.count + '</span>명</p>');
                        dialog.closeAll();
                    }
                });
            }, complete: function (xhr, message, $form) {
                console.log('complete', arguments);
                $form.data('submit', true);
            }
        };
        var $selector = $(selector);
        $selector.data('submit', true).ajaxForm(options);
    };

    /**
     * 직접 선택, 회원 등급 선택 화면의 선택하기 버튼 이벤트
     *
     * @param {string} selector
     */
    sms.set_on_click_select_target = function (selector) {
        $form.on('click', selector, function () {
            var $radio = $receiver_type.filter(':checked');
            if ($radio.val() === 'each') {
                var url = '../share/popup_add_member.php?sendMode=sms';
                var options = 'width=1450, height=760, scrollbars=no';
                window.open(url, 'member_search', options);
            } else if ($radio.val() === 'group') {
                var events = {
                    onhidden: function (dialogRef) {
                        console.log(arguments);
                        if (!_.isUndefined(dialogRef)) {
                            if (dialogRef.options.selected_group) {
                                count_target_group();
                            } else {
                                console.log('not found checked box');
                            }
                        }
                    }
                };
                var addParam = {events: events};
                layer_add_info('member_group', addParam);
            } else {
                alert('선택된 발송 대상이 없습니다');
            }
        });
    };

    /**
     * 회원등급 삭제 시 인원 재계산
     *
     * @param {string} selector
     */
    sms.set_on_click_delete_group = function (selector) {
        $form.on('click', selector, function (e) {
            $(e.target.dataset.target).remove();
            count_target_group();
        });
    };

    /**
     * 전화번호 직접 입력 추가
     *
     * @param {string} selector
     */
    sms.set_on_click_add_cellphone = function (selector) {
        /**
         * 휴대폰 번호 배열로 처리
         *
         * @param {string} number
         * @returns {string}
         */
        function add_dash(number) {
            var snippet = [];
            if (number.indexOf('-') > -1) {
                snippet = number.split('-');
            } else {
                snippet.push(number.substring(0, 3));
                if (number.length === 10) {
                    snippet.push(number.substring(3, 6));
                    snippet.push(number.substring(6));
                } else {
                    snippet.push(number.substring(3, 7));
                    snippet.push(number.substring(7));
                }
            }
            return snippet.join('-');
        }

        $form.on('click', selector, function () {
            var $input_cell_phone = $(':text[name="directCellPhone"]');
            var cell_phone = $input_cell_phone.val();
            if (!cell_phone) {
                alert('휴대폰 번호를 입력하여 주시기 바랍니다.');
                return false;
            }
            cell_phone = add_dash(cell_phone);
            var pattern = /^(010|011|016|017|018|019)\-[0-9]{3}\-[0-9]{4}$/;
            if (cell_phone.length === 13) {
                pattern = /^(010|011|016|017|018|019)\-[0-9]{4}\-[0-9]{4}$/;
            }
            if (!pattern.test(cell_phone)) {
                alert('잘못된 휴대폰 번호입니다.');
                return false;
            }

            var $direct_list = $('.js-direct-list');
            if ($direct_list.find(':contains(' + cell_phone + ')').length > 0) {
                alert('중복된 전화번호입니다.');
                return false;
            }
            var $direct_list_group_item = $('.js-direct-list .list-group-item');
            var params = {
                cell_phone: cell_phone,
                direct_list_group_item: $direct_list_group_item,
                scroll: false,
                total: $direct_list_group_item.length + 1
            };
            if ($direct_list.find('li').length > 5) {
                params.scroll = true;
            }
            sms.remove_sms_target_area();
            target_area.append(print_target_area('#templateDirect', params));
            target_area.find(':text[name="directCellPhone"]').focus();
        });
    };

    /**
     * 직접 입력 리스트 로우
     *
     * @param {string} selector
     */
    sms.set_on_click_direct_list_item = function (selector) {
        $form.on('click', selector, function (e) {
            var $this = $(e.target);

            function get_min_index() {
                return Math.min(index, sms.last_selected_index);
            }

            function get_max_index() {
                return Math.max(index, sms.last_selected_index);
            }

            if (e.shiftKey && (sms.last_selected_index > -1)) {
                var $list = $('.js-direct-list');
                var $list_group_item = $list.find('.list-group-item');
                var index = $list_group_item.index($this);
                $list_group_item.slice(get_min_index(), get_max_index() + 1).addClass('selected');
            } else {
                if ($this.hasClass('selected')) {
                    $this.removeClass('selected');
                } else {
                    $this.addClass('selected');
                    sms.last_selected_index = $('.js-direct-list .list-group-item').index($this);
                }
            }
        });
    };

    /**
     * 직접입력 선택삭제
     *
     * @param {string} selector
     */
    sms.set_on_click_delete_cell_phone_select = function (selector) {
        $form.on('click', selector, function () {
            var $direct_list = $('.js-direct-list');
            var $selected = $direct_list.find('.selected');
            if ($selected.length < 1) {
                alert('선택된 전화번호가 없습니다.');
                return false;
            }
            $selected.remove();
            var $direct_list_group_item = $('.js-direct-list .list-group-item');
            var params = {
                cell_phone: '',
                direct_list_group_item: $direct_list_group_item,
                scroll: false,
                total: $direct_list_group_item.length
            };
            if ($direct_list.find('li').length > 5) {
                params.scroll = true;
            }
            sms.remove_sms_target_area();
            target_area.append(print_target_area('#templateDirect', params));
        });
    };

    /**
     * 직접 입력 전체 삭제 이벤트
     *
     * @param {string} selector
     */
    sms.set_on_click_delete_cell_phone_all = function (selector) {
        $form.on('click', selector, function () {
            if ($('.js-direct-list .list-group-item').length < 1) {
                alert('입력된 전화번호가 없습니다.');
                return false;
            }
            dialog_alert('전체 삭제하시겠습니까?', '경고', {
                callback: function () {
                    $('.js-direct-list .list-group-item').remove();
                }
            });
        });
    };

    /**
     * sms 내용 페이지 이동 이벤트
     *
     * @param {string} selector
     */
    sms.set_on_click_pagination = function (selector) {
        $form.on('click', selector, function (e) {
            e.preventDefault();
            var current = $('.pagination .active span').text();
            if ($(e.target).is('a')) {
                current = $(e.target).text();
            }
            current = (current * 1);
            var params = {page: current};
            console.log(e);
            sms.print_sms_contents(params);
        });
    };

    /**
     * sms 내용 전체 선택
     *
     * @param {string} selector
     */
    sms.set_on_click_check_contents_view_all = function (selector) {
        $form.on('click', selector, function () {
            $(':checkbox[name="checkSmsContents"]').each(function (idx, item) {
                console.log(idx);
                item.checked = true;
            });
        });
    };

    /**
     * 선택된 sms 내용 삭제
     *
     * @param {string} selector
     */
    sms.set_on_click_delete_sms_selected = function (selector) {
        $form.on('click', selector, function () {
            var params = {
                mode: 'deleteSmsContents',
                sno: []
            };
            var $checkbox = $(':checkbox[name="checkSmsContents"]:checked');
            $checkbox.each(function () {
                console.log(arguments);
                var checkbox = arguments[1];
                params.sno.push(checkbox.value);
            });
            if (params.sno.length === 0) {
                alert('삭제할 문자를 선택하여 주시기 바랍니다.');
                return false;
            }
            $.ajax('../member/sms_send_ps.php', {
                method: 'POST',
                data: params,
                beforeSend: alert('처리중'),
                success: function () {
                    var response = arguments[0];
                    console.log(response);
                    dialog.closeAll();
                    sms.search_settings.cached = false;
                    sms.print_sms_contents({page: 1});
                },
                error: show_error_dialog
            });
        });
    };

    /**
     * 현재 내용 저장 시 발생하는 레이어 저장 버튼 이벤트
     *
     * @param {string} selector
     */
    sms.set_on_click_save_contents_by_dialog = function (selector) {
        $('body').on('click', selector, function (e) {
            console.log(e.target);
            $.ajax(sms.request_uri, {
                method: 'post',
                data: {
                    mode: 'saveSmsContents',
                    subject: function () {
                        return $('input[name="dialogSubject"]').val();
                    },
                    contents: function () {
                        return $('textarea[name="smsContents"]').val();
                    },
                    smsAutoCode: function () {
                        return $('select[name="dialogSmsAutoCode"] option:selected').val();
                    }
                },
                beforeSend: alert('처리중'),
                success: function () {
                    close_validate_process_dialog();
                    var response = arguments[0];
                    console.log(response);
                    if (response.result === 'OK') {
                        dialog.closeAll();
                        sms.search_settings.cached = false;
                        if (!$sms_contents_search_area.hasClass('display-none')) {
                            reload_sms_contents_list();
                        }
                    }
                    alert(response.message);
                },
                error: show_error_dialog
            });
        });
    };
    sms.set_click_excel_upload = function (selector) {
        $(selector).click(function () {
            var $formExcel = $('#formExcel');
            $formExcel.find(':file').remove();
            var $file = $(':file[name=excel]:eq(0)');
            $formExcel.append($file);
            if ($file.val() === '') {
                alert('선택된 엑셀 파일이 없습니다.');
                return false;
            }
            show_count_dialog();
            $formExcel.submit();
        });
    };

    /**
     * 대상 회원 선택 라디오버튼 이벤트
     *
     * @param {string} selector
     */
    sms.set_click_receiver_type = function (selector) {
        $receiver_type = $(selector);
        $receiver_type.click(function (e) {
            var $this = $(e.target);
            $('#member_groupLayer').removeClass('active').html('');
            sms.remove_sms_target_area();
            var $sleep_notice = $('.js-sleep-notice');
            var template_id = '#template' + $this.val().toCamelCase();
            var $target_area_excel = $('.target-area-excel');
            $sleep_notice.addClass('display-none');
            $target_area_excel.addClass('display-none');
            if ($this.val() === 'each') {
                var receivers = $sms_receiver_list.find('tbody tr');
                var params = {receiveTotal: 0, rejectCount: 0};
                $.each(receivers, function (idx, item) {
                    params.receiveTotal++;
                    if ($(item).find('input[name="selectChk[]"]').data('smsfl') === 'n') {
                        params.rejectCount++;
                    }
                });
                target_area.append(print_target_area(template_id, params));
            } else if ($this.val() === 'group') {
                target_area.append(print_target_area(template_id));
            } else if ($this.val() === 'all') {
                $.ajax(sms.request_uri, {
                    method: 'post',
                    data: {mode: 'countTargetAll'},
                    beforeSend: show_count_dialog,
                    success: function () {
                        var json_response = arguments[0];
                        console.log('receiver type all success', json_response);
                        var params = {receiveTotal: json_response.total, rejectCount: json_response.reject};
                        if ($(':radio[value="all"]').prop('checked')) {
                            var template = print_target_area('#template' + e.target.value.toCamelCase(), params);
                            target_area.append(template);
                        }
                    },
                    error: show_error_dialog,
                    complete: dialog.closeAll
                });
            } else if ($this.val() === 'direct') {
                $sleep_notice.removeClass('display-none');
                var direct_list_params = {
                    cell_phone: '',
                    direct_list_group_item: $('.js-direct-list .list-group-item'),
                    scroll: false,
                    total: 0
                };
                target_area.append(print_target_area(template_id, direct_list_params));
                $form.off('keyup', 'input[name="directCellPhone"]').on('keyup', 'input[name="directCellPhone"]', function (e) {
                    if (e.keyCode === 13) {
                        $('.js-btn-add-cell-phone').trigger('click');
                    }
                });
            } else if ($this.val() === 'excel') {
                $sleep_notice.removeClass('display-none');
                $target_area_excel.removeClass('display-none');
            } else {
                alert('선택된 발송 대상이 없습니다.');
            }
        });
    };

    /**
     * 내 문자함 버튼 이벤트
     *
     * @param {string} selector
     */
    sms.set_click_more_contents = function (selector) {
        $button_more_contents = $(selector);
        $button_more_contents.click(function (e) {
            var $this = $(e.target);
            var $icon = $this.find('.glyphicon');
            if ($icon.hasClass('glyphicon-triangle-top')) {
                $icon.removeClass('glyphicon-triangle-top');
                $icon.addClass('glyphicon-triangle-bottom');
            } else {
                $icon.removeClass('glyphicon-triangle-bottom');
                $icon.addClass('glyphicon-triangle-top');
            }
            if ($icon.hasClass('glyphicon-triangle-bottom')) {
                $sms_contents_search_area.addClass('display-none');
            } else {
                $sms_contents_search_area.removeClass('display-none');
                reload_sms_contents_list();
            }
        });
    };

    /**
     * 문자 내용 저장 이벤트
     *
     * @param {string} selector
     */
    sms.set_click_save_contents = function (selector) {
        $(selector).click(function (e) {
            e.preventDefault();
            if ($('textarea[name=smsContents]').val() === '') {
                alert('내용을 입력하여 주세요.');
                return false;
            }
            dialog.show({
                message: function () {
                    return _.template($('#templateSaveContentsDialog').html());
                },
                onshown: function (dialogRef) {
                    var $input = dialogRef.getModalBody().find('input[name="dialogSubject"]');
                    $input.maxlength({
                        showOnReady: true,
                        alwaysShow: true
                    });
                },
                title: '현재 내용 저장'
            });
        });
    };

    /**
     * 발송내용 변경 시 마지막 포커스 위치 및 글자 체크 이벤트 갱신
     */
    function refresh_send_contents() {
        sms.contents_focus_position = $send_contents.val().length;
        $send_contents.focus();
        $send_contents.trigger('keyup');
    }

    /**
     * 치환코드 삽입 이벤트 설정
     * @param selector
     */
    sms.set_click_replace_code_insert = function (selector) {
        $(selector).click(function (e) {
            var $this = $(e.target);
            var code = $this.closest('tr').find('td:eq(0)').text();
            var input = $send_contents.val();
            var output = [input.slice(0, sms.contents_focus_position), code, input.slice(sms.contents_focus_position)];
            console.log(sms.contents_focus_position, code);
            $send_contents.val(output.join(''));
            $send_contents.get(0).selectionEnd = sms.contents_focus_position + code.length;
            refresh_send_contents();
        });
    };

    /**
     * 문자함 검색 버튼 이벤트
     * @param selector
     */
    sms.set_click_contents_search = function (selector) {
        $(selector).click(function () {
            sms.print_sms_contents({});
        });
        $(selector).siblings('input').keyup(function (e) {
            if (e.keyCode === 13) {
                sms.print_sms_contents({});
            }
        });
    };

    /**
     * 광고성 문구 추가
     * @param selector
     */
    sms.set_click_sms080_contents = function (selector) {
        $(selector).click(function (e) {
            $send_contents.val($send_contents.val().replace(/(\(광고\)\n)|(\n무료거부\s[0-9\-]*)/g, ''));
            if (e.target.checked) {
                $send_contents.val('(광고)\n' + $send_contents.val() + '\n무료거부 ' + $(selector).data('reject-number'));
            }
            refresh_send_contents();
        });
    };

    /**
     * 치환코드 셀렉터 변경 이벤트
     * @param {string} selector
     */
    sms.set_change_replace_code_group = function (selector) {
        $replace_code_group = $(selector);
        $replace_code_group.change(function () {
            var select_value = $replace_code_group.find('option:selected').val();
            var $replace_area = $('.replace-code-area');
            var $replace_guide_message = $replace_code_group.next();
            $replace_guide_message.addClass('display-none');
            if (select_value !== 'member' && is_default_mode) {
                $replace_guide_message.removeClass('display-none');
            }
            console.log('change replace code', select_value, $replace_area);
            $replace_area.each(function (area_idx, area) {
                var $area = $(area);
                if (!$area.hasClass('display-none')) {
                    $area.addClass('display-none');
                }
                if ($area.data('type') === select_value) {
                    console.log('type', $area.data('type'), select_value, $area);
                    $area.removeClass('display-none');
                    if (is_default_mode) {
                        if (select_value === 'member') {
                            $area.find('*').prop('disabled', false).removeAttr('style');
                        } else {
                            $area.find('*').prop('disabled', true).css('color', 'gray');
                        }
                    } else {
                        console.log('is not default mode');
                    }
                } else {
                    console.log('change replace code group.select_value', select_value);
                }
            });
        });
    };

    /**
     * 발송내용 분류 선택
     * @param element
     */
    sms.set_change_auto_code_selector = function (element) {
        $auto_code_selector = element;
        $auto_code_selector.change(function () {
            sms.print_sms_contents({});
        });
    };

    /**
     * 발송내용 포커스 아웃 이벤트
     * @param selector
     */
    sms.set_change_focus_contents = function (selector) {
        $send_contents = $(selector);
        $send_contents.focusout(function (e) {
            var $this = $(e.target);
            sms.contents_focus_position = $this.prop('selectionStart');
        });
    };

    /**
     * 발송대상 태그 설정
     * @param element
     */
    sms.set_target_area = function (element) {
        target_area = element;
    };
    /**
     * 폼 태그 설정
     * @param element
     */
    sms.set_form = function (element) {
        $form = element;
    };
    /**
     * 현재 설정된 폼 반환
     * @returns {*}
     */
    sms.get_form = function () {
        return $form;
    };
    /**
     * 발송내용 페이징 태그 설정
     * @param element
     */
    sms.set_pagination = function (element) {
        $pagination = element;
    };
    /**
     * 발송내용 리스트 태그 설정
     * @param element
     */
    sms.set_contents_list = function (element) {
        $contents_list = element;
    };
    /**
     * 검색어 태그 설정
     * @param element
     */
    sms.set_search_word = function (element) {
        $search_word = element;
    };
    /**
     * 수신대상 태그 설정
     * @param element
     */
    sms.set_sms_receiver_list = function (element) {
        $sms_receiver_list = element;
    };
    /**
     * 발송내용 리스트 영역
     * @param element
     */
    sms.set_sms_contents_search_area = function (element) {
        $sms_contents_search_area = element;
    };
    /**
     * 엑셀 업로드 영역을 제외한 발송대상 영역 초기화
     */
    sms.remove_sms_target_area = function () {
        target_area.children(':not(.target-area-excel)').remove();
    };

    /**
     * sms 내용 출력
     * @param params
     */
    sms.print_sms_contents = function (params) {
        var default_params = {
            mode: 'getSmsContentsBox',
            smsAutoCode: $auto_code_selector.find('option:selected').val(),
            searchWord: $search_word.val(),
            page: 1
        };
        var data = $.extend(true, default_params, params);
        if (sms.search_settings.equals(data) === false) {
            $.ajax(sms.request_uri, {
                method: 'post',
                data: data,
                beforeSend: function () {
                    $contents_list.html('').addClass('height400').addClass('loading');
                },
                success: function () {
                    var response_json = arguments[0] || {lists: [], page: {}};
                    console.log(response_json);
                    if (response_json.lists.length === 0) {
                        sms.search_settings.cached = false;
                        $contents_list.append(_.template($('#templateEmptySmsContents').html()));
                        $pagination.html('');
                        return false;
                    }
                    var compiled = _.template($('#templateSmsContents').html());
                    $contents_list.html('');
                    $.each(response_json.lists, function (idx, item) {
                        console.log('print sms contents view=>' + idx, item);
                        var checkbox = get_contents_view_checkbox(item);
                        var subject = '<span class="">' + item.subject + '</span>';
                        var view_params = {
                            checkbox: checkbox,
                            subject: subject,
                            contents: item.contents,
                            sno: item.sno
                        };
                        var view = $(compiled(view_params));
                        view.on('click', 'button.js-btn-save', function (e) {
                            var $delegateTarget = $(e.delegateTarget);
                            var $this = $(e.target);
                            var text = $this.data('text');
                            if (text) {
                                $this.data('text', $this.text());
                                $this.text(text);
                            }

                            /**
                             * 변경 사항 여부 체크 함수
                             *
                             * @returns {boolean}
                             */
                            function check_subject_with_contents() {
                                function subject() {
                                    return ($checkbox.data('subject-length') === $subjectInput.val().length);
                                }

                                function contents() {
                                    return ($checkbox.data('contents-length') === $textarea.val().length);
                                }

                                return subject() && contents();
                            }

                            if (text === '저장') {
                                console.log('change modify mode');
                                replace_with_subject($delegateTarget);
                            } else {
                                var $subjectInput = $delegateTarget.find('input[name="subject"]');
                                var $checkbox = $delegateTarget.find(':checkbox');
                                var $textarea = $delegateTarget.find('textarea');
                                if (check_subject_with_contents()) {
                                    $subjectInput.replaceWith('<span>' + $subjectInput.val() + '</span>');
                                    return false;
                                }

                                var contents_params = {
                                    mode: 'saveSmsContents',
                                    subject: $subjectInput.val(),
                                    contents: $textarea.val(),
                                    smsAutoCode: $checkbox.data('code'),
                                    sno: $checkbox.val()
                                };
                                $.ajax('../member/sms_send_ps.php', {
                                    method: 'POST',
                                    data: contents_params,
                                    beforeSend: function () {
                                        dialog.show({message: '저장 중입니다'});
                                    },
                                    success: function () {
                                        var response = arguments[0];
                                        if (response.result === 'OK') {
                                            $subjectInput.replaceWith('<span>' + $subjectInput.val() + '</span>');
                                            $checkbox.data('subject-length', $subjectInput.val().length);
                                            $checkbox.data('contents-length', $textarea.val().length);
                                        }
                                        dialog.closeAll();
                                        alert(response.message);
                                    },
                                    error: show_error_dialog
                                });
                            }
                        });
                        view.on('click', 'button.js-btn-insert', function (e) {
                            var $delegateTarget = $(e.delegateTarget);
                            var $textarea = $delegateTarget.find('textarea');
                            $send_contents.val($send_contents.val() + '' + $textarea.val());
                            refresh_send_contents();
                        });
                        $contents_list.append(view);
                    });
                    var pagination_compiled = _.template($('#templatePagination').html());
                    $pagination.html('');
                    $pagination.append(pagination_compiled(response_json.page));
                    sms.search_settings = $.extend({}, sms.search_settings, data);
                    sms.search_settings.cached = true;
                },
                error: show_error_dialog,
                complete: function () {
                    $contents_list.removeClass('loading').removeClass('height400');
                    dialog.closeAll();
                }
            });
        }
    };

    /**
     * 팝업모드가 아닌 경우 설정
     * @param flag
     */
    sms.set_is_default_mode = function (flag) {
        is_default_mode = flag;
    };

    sms.get_popup = function () {
        return _.isNull(opener) ? {
            is_open: function () {
                return false
            }
        } : opener.gd_sms_send_popup;
    };

    return sms;
}(BootstrapDialog));

var gd_sms_send_popup = (function () {
    "use strict";
    var $popup_button;
    var popup_params;
    init_popup_params();
    function init_popup_params() {
        popup_params = {opener: '', searchType: '', receiverKeys: [], receiverSearch: {mallFl: '1'}};
    }

    /**
     * 개별/전체 sms 발송 시 팝업을 발생시키는 화면에 따른 처리
     * @type {{order: order, promotion: promotion, member: member}}
     */
    var opener_action = {
        order: function () {
            var has_globals = false;
            if (popup_params.searchType === 'search') {
                popup_params.receiverSearch = $($popup_button.data('target-selector')).serializeObject();
                if (_.isString($popup_button.data('status-mode'))) {
                    popup_params.receiverSearch.statusMode = $popup_button.data('status-mode');
                }
                if (_.isString($popup_button.data('user-handle-mode'))) {
                    popup_params.receiverSearch.userHandleMode = $popup_button.data('user-handle-mode');
                }
                if (_.isString(popup_params.receiverSearch.mallFl)) {
                    has_globals = popup_params.receiverSearch.mallFl !== '1';
                    if (popup_params.receiverSearch.mallFl !== 'all' && popup_params.receiverSearch.mallFl > 1) {
                        dialog_alert('해외상점 구매자에게는 SMS를 발송하실 수 없습니다.');
                        return false;
                    }
                }
            } else if (popup_params.searchType === 'select') {
                var $targets = $($popup_button.data('target-selector')).closest('tr');
                if ($targets.length === 0) {
                    dialog_alert('선택된 주문 상품이 없습니다.');
                    return false;
                }
                $targets.each(function () {
                    var $this = $(this);
                    var $order = $this.find(':checkbox:checked');
                    var order_no = $order.val();
                    if (order_no.indexOf('||') > 0) {
                        order_no = order_no.split('||')[0];
                    }
                    console.log(_.contains(popup_params.receiverKeys, order_no), popup_params.receiverKeys, order_no);
                    if ($this.data('mall-sno') === 1 && !_.contains(popup_params.receiverKeys, order_no)) {
                        popup_params.receiverKeys.push(order_no);
                    } else {
                        has_globals = true;
                    }
                });
                if (popup_params.receiverKeys.length === 0) {
                    console.log('not found receivers', $targets);
                    dialog_alert('해외상점 구매자에게는 SMS를 발송하실 수 없습니다.');
                    return false;
                }
            }
            if (has_globals) {
                dialog_alert('해외상점 구매자에게는 SMS를 발송하실 수 없습니다. 해외상점 구매자는 SMS 발송 대상에서 제외됩니다.', undefined, {
                    callback: function () {
                        member_multi_sms(popup_params);
                    }
                });
            } else {
                member_multi_sms(popup_params);
            }
        },
        promotion: function () {
            var $targets = $($popup_button.data('target-selector'));
            if ($targets.length === 0) {
                dialog_alert('기획전을 선택해 주세요.');
                return false;
            }
            $targets.each(function (idx, item) {
                popup_params.receiverKeys.push(item.value);
            });
            member_multi_sms(popup_params);
        },
        member: function () {
            var $target = $popup_button.closest('tr').find($popup_button.data('target-selector'));
            var mem_no = $target.val();
            if ($popup_button.data('target-selector') === 'this') {
                $target = $popup_button;
                mem_no = $popup_button.data('memno');
            }
            popup_params.receiverKeys.push(mem_no);
            if ($target.length === 0) {
                dialog_alert('선택된 회원이 없습니다.');
                return false;
            }
            if ($target.data('cellphone') === '') {
                dialog_alert('SMS 정보가 없는 회원입니다.');
                return false;
            }
            if ($target.data('smsfl') === 'y') {
                member_multi_sms(popup_params);
            } else {
                dialog_confirm('수신거부한 회원 입니다. SMS 발송을 하시려면 확인을 눌러주세요.', function (result) {
                    if (result) {
                        member_multi_sms(popup_params);
                    } else {
                        layer_close();
                    }
                });
            }
        },
        goods: function () {
            if (popup_params.searchType === 'select') {
                var $targets = $($popup_button.data('target-selector'));   // 재입고 알림 관련 정보를 담고있는 태그
                if ($targets.length === 0) {
                    dialog_alert('선택된 신청자가 없습니다.');
                    return false;
                }

                $targets.each(function (idx, item) {
                    popup_params.receiverKeys.push(item.value); // 재입고 알림 번호
                });
            }
            else {
                if (popup_params.searchType === 'all') {
                    popup_params.receiverSearch = $($popup_button.data('target-selector')).find(":input:not('[type=radio]')").serializeObject();
                    popup_params.searchType = 'search';
                }
                else {
                    popup_params.receiverSearch = $($popup_button.data('target-selector')).serializeObject();
                }
            }

            member_multi_sms(popup_params);
        }
    };

    return {
        call_opener_action: function (e) {
            init_popup_params();
            $popup_button = $(e.target);
            popup_params.opener = $popup_button.data('opener');
            popup_params.searchType = $popup_button.data('type');
            opener_action[popup_params.opener]();
        },
        get_popup_params: function () {
            return popup_params;
        },
        is_open: function () {
            return popup_params.opener !== '';
        }
    };
})();

$(document).ready(function () {
    "use strict";
    // sms 팝업 이벤트 바인딩
    $('.js-sms-send').click(gd_sms_send_popup.call_opener_action);
});
