var member2 = (function ($, _, member, window, document, BootstrapDialog, undefined) {
    //@formatter:off
    var key_code = {
        ALT: 18, BACKSPACE: 8, CAPS_LOCK: 20, COMMA: 188, COMMAND: 91, COMMAND_LEFT: 91, COMMAND_RIGHT: 93, CONTROL: 17, DELETE: 46, DOWN: 40, END: 35, ENTER: 13, ESCAPE: 27, HOME: 36, INSERT: 45, LEFT: 37, MENU: 93, NUMPAD_ADD: 107, NUMPAD_DECIMAL: 110, NUMPAD_DIVIDE: 111, NUMPAD_ENTER: 108, NUMPAD_MULTIPLY: 106, NUMPAD_SUBTRACT: 109, PAGE_DOWN: 34, PAGE_UP: 33, PERIOD: 190, RIGHT: 39, SHIFT: 16, SPACE: 32, TAB: 9, UP: 38, WINDOWS: 91
    };
    //@formatter:on

    var form, validate;
    var policy = {};
    var datepickerOptions = {"widgetPositioning": {horizontal: "auto", vertical: "auto"}};
    var is_my_page = false;
    var validate_settings = {
        defaults: {
            ignore: ".ignore",
            dialog: false,
            focusCleanup: true,
            rules: {
                memId: {
                    required: true
                },
                memNm: {
                    required: true
                },
                memPw: {
                    required: true
                },
                memPwRe: {
                    equalTo: "[name=memPw]"
                },
                fullBusiNo: {
                    rangelength: function () {
                        return [$('#busiNo').data('charlen'), $('#busiNo').data('charlen')];
                    }
                }
            },
            messages: {
                memPwRe: {
                    equalTo: "비밀번호가 서로 다릅니다."
                },
                fullBusiNo: {
                    rangelength: function () {
                        return $('#busiNo').data('charlen') + "자로 입력해야 합니다."
                    }
                }
            },
            errorElement: 'div',
            errorClass: 'error c-red',
            showErrors: function (errorMap, errorList) {
                if (errorList.length > 0) {
                    var tableHeadText = $(errorList[0].element).closest('td').prev('th').text();
                    var dialogMessage = errorList[0].message;
                    if (tableHeadText.length > 0) {
                        dialogMessage = tableHeadText + ' ' + dialogMessage;
                    }
                    BootstrapDialog.show({
                        title: '경고',
                        message: dialogMessage,
                        buttons: [{
                            label: '확인',
                            cssClass: 'btn-black',
                            hotkey: 32,
                            size: BootstrapDialog.SIZE_LARGE,
                            action: function (dialog) {
                                dialog.close();
                                validate.focusInvalid();
                            }
                        }]
                    });
                }
            },
            onkeyup: onkeyup,
            success: function (label, element) {
                var $label = $(label);
                $label.removeClass('error c-red').addClass('c-blue').css('display', '');
            }
        }
    };

    var messages = {
        required: "필수항목 입니다.",
        email: "정확한 이메일 주소를 입력해주시기 바랍니다.",
        number: "Please enter a valid number.",
        digits: "Please enter only digits.",
        equalTo: $.validator.format("입력 값이 서로 다릅니다."),
        maxlength: $.validator.format("최대 {0} 자리 까지 입력가능 합니다."),
        minlength: $.validator.format("최소 {0} 자리 까지 입력하셔야 합니다."),
        rangelength: $.validator.format("허용 범위를 초과한 값입니다. {0} ~ {1} 까지 입력해주세요.")
    };

    var focusout_event = {
        memId: function (element) {
            ajax_validate(element, {
                memId: element.value, mode: "overlapMemId"
            });
        },
        memPw: function (element) {
            ajax_validate(element, {
                memPw: element.value, mode: "validateMemberPassword"
            });
        },
        "email[]": function (element) {
            ajax_validate(element, {
                memId: $('input[name=memId]').val(), emailAddress: $('#emailAddress').val(), emailDomain: $('#emailDomain').val(), mode: "overlapEmail"
            });
        },
        nickNm: function (element) {
            ajax_validate(element, {
                memId: $('input[name=memId]').val(), nickNm: element.value, mode: "overlapNickNm"
            });
        },
        recommId: function (element) {
            ajax_validate(element, {
                memId: $('input[name=memId]').val(), recommId: element.value, mode: "checkRecommendId"
            });
        },
        "busiNo[]": function (element) {
            var $busiNo = $('#busiNo');
            if ($busiNo.data('overlap-businofl') === 'y') {
                ajax_validate(element, {
                    memId: $('input[name=memId]').val(), busiNo: $busiNo.val(), charlen: $busiNo.data('charlen'), mode: "overlapBusiNo"
                });
            }
        }
    };

    function ajax_validate(element, data) {
        var $target = $(element);
        if ($target.attr('name') === 'busiNo[]') {
            $target = $('#busiNo');
        }
        var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: data});
        $ajax.done(function (data) {
            var code = data.code;
            var message = data.message;
            if (_.isUndefined(code) && _.isUndefined(message)) {
                alert(data);
                if ($target.hasClass('error')) {
                    $target.removeClass('error');
                    if ($target.attr('id') == 'emailAddress') {
                        $('#emailDomain').removeClass('error');
                    }
                }
            } else {
                if($('#emailDomain, #busiNo').hasClass('error')){
                    $('#emailDomain, #busiNo').removeClass('error');
                }
                $target.closest('td').find('.c-blue').addClass('c-red');
                $target.closest('td').find('.c-blue').removeClass('c-blue');
                alert(message);
            }
        });
    }

    function onkeyup(element, event) {
        if (check_key_code2(event)) {
            return true;
        }
        if ($.isFunction(replace_keyup[$(element).data('pattern')])) {
            replace_keyup[$(element).data('pattern')](element);
        }
    }

    var replace_keyup = {
        gdEngNum: function (element) {
            element.value = replace_pattern(element.value, /[^\da-zA-Z]/g, '');
        },
        gdEngKor: function (element) {
            // IE11에서 초중종성 분리되는 현상때문에 test 후 replace 처리로 변경
            if (regexp_test(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/)) {
                element.value = replace_pattern(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/g, '');
            }

        },
        gdEngKorNum: function (element) {
            // IE11에서 초중종성 분리되는 현상때문에 test 후 replace 처리로 변경
            if (regexp_test(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣0-9\u119E\u11A2]/)) {
                element.value = replace_pattern(element.value, /[^a-zA-Zㄱ-ㅎㅏ-ㅣ가-힣\u119E\u11A2]/g, '');
            }

        },
        gdNum: function (element) {
            element.value = replace_pattern(element.value, /[^\d]/g, '');
        },
        gdEng: function (element) {
            element.value = replace_pattern(element.value, /[^a-zA-Z]/g, '');
        },
        gdKor: function (element) {
            if (regexp_test(element.value, /[^가-힣ㄱ-ㅎㅏ-ㅣ\u119E\u11A2]/)) {
                element.value = replace_pattern(element.value, /[^가-힣ㄱ-ㅎㅏ-ㅣ\u119E\u11A2]/g, '');
            }
        },
        gdMemberId: function (element) {
            element.value = replace_pattern(element.value, /[^\da-zA-Z\.\-_@]/g, '');
        },
        gdMemberNmGlobal: function (element) {
            // IE11에서 초중종성 분리되는 현상때문에 test 후 replace 처리로 변경
            if (regexp_test(element.value, /[\/\'\"\\\|]/)) {
                element.value = replace_pattern(element.value, /[\/\'\"\\\|]/g, '');
            }
        }
    };

    function onfocusout(element) {
        if (element.value.length && $(element).valid()) {
            if ($.isFunction(focusout_event[element.name])) {
                focusout_event[element.name](element);
            }
        }
    }

    function check_key_code2(event) {
        // Avoid revalidate the field when pressing one of the following keys
        /* Shift       => 16 Ctrl        => 17 Alt         => 18
         Caps lock   => 20 End         => 35 Home        => 36
         Left arrow  => 37 Up arrow    => 38 Right arrow => 39
         Down arrow  => 40 Insert      => 45 Num lock    => 144 AltGr key   => 225*/
        var excludedKeys = [
            16, 17, 18, 20, 35, 36, 37,
            38, 39, 40, 45, 144, 225
        ];

        return event.which === 9 || $.inArray(event.keyCode, excludedKeys) !== -1;
    }

    function set_defaults_validator(settings) {
        $.extend($.validator.messages, messages);
        $.extend(true, settings, validate_settings.defaults);
    }

    function remove_default_settings() {
        delete $.validator.defaults.invalidHandler;
        delete $.validator.defaults.onkeyup;
        delete $.validator.defaults.onfocusout;
    }

    function load() {
        var params = [];
        params.push({name: "mode", value: "getJoinPolicy"});
        var $ajax = $.ajax('../member/member_ps.php', {type: "post", data: params, async: false});
        $ajax.done(function (data) {
            policy = data;
        });
    }

    function add_rules() {
        var settings = validate_settings.defaults;
        var defaultRequired = {required: true};
        $.each(policy, function (idx, item) {
            if (settings.rules[idx] === undefined) {
                settings.rules[idx] = {};
            }
            if (item.require === 'y') {
                settings.rules[idx].required = true;
                if ($('[name="' + idx + '[]"]').length > 0) {
                    delete settings.rules[idx];
                    settings.rules['"' + idx + '[]"'] = defaultRequired;
                }
            }
            if (item.minlen > 0) {
                settings.rules[idx].minlength = item.minlen;
            }
            if (item.maxlen > 0) {
                settings.rules[idx].maxlength = item.maxlen;
            }
            if (item.minlen > 0 && item.maxlen > 0) {
                settings.rules[idx].rangelength = [item.minlen, item.maxlen];
            }
        });
        if (settings.rules.address !== undefined && settings.rules.address.required) {
            settings.rules.zonecode = defaultRequired;
            settings.rules.addressSub = defaultRequired;
        }
        if (settings.rules.comAddress !== undefined && settings.rules.comAddress.required) {
            settings.rules.comZonecode = defaultRequired;
            settings.rules.comAddressSub = defaultRequired;
        }
    }

    function validation_submit(func) {
        validate = form.validate();
        set_defaults_validator(validate.settings);
        if (is_my_page) {
            $('input[name="memId"]').rules("remove");
            var $memPw = $('input[name="memPw"]');
            var $memPwRe = $('input[name="memPwRe"]');
            if ($memPw.val() === '') {
                $memPw.rules("remove");
            }
            if ($memPw.val() === '' && $memPwRe.val() === '') {
                $memPwRe.rules("remove");
            }
        }
        if ($('input[name="memberFl"]:checked').val() !== 'business') {
            $('.div-business').find('input').val('');
        }

        validate.settings.submitHandler = func;
        if (validate.form()) {
            form.submit();
        }
    }

    var regexp_test = function (string, pattern) {
        if (string === undefined || string.length < 1) {
            return false;
        }
        //console.log('regexp_test', string, pattern, pattern.test(string));
        return pattern.test(string);
    };

    var regexp_match = function (string, pattern) {
        if (string === undefined || string.length < 1) {
            return;
        }
        var result = string.match(pattern);
        //console.log('regexp_match', string, pattern, result);
        return result ? result.join('') : '';
    };

    var replace_pattern = function (string, pattern, c) {
        //console.log('replace_pattern', string, pattern);
        if (string === undefined || string.length < 1) {
            return '';
        }
        return string.replace(pattern, c);
    };

    return {
        init: function (form) {
            remove_default_settings();
            validate = form.validate(validate_settings);
            load();
            //add_rules();
            //set_defaults_validator(validate.settings);
            this.set_form(form);

            $('#overlap_memId').click(function () {
                var $memId = $('#memId');
                $memId.valid();
                onfocusout($memId[0]);
            });
            $('#overlap_nickNm').click(function () {
                var $nickNm = $('#nickNm');
                $nickNm.valid();
                onfocusout($nickNm[0]);
            });
            $('#overlap_email').click(function () {
                var $emailAddress = $('#emailAddress');
                var $emailDomain = $('#emailDomain');
                if ($emailAddress.valid()) {
                    if ($emailDomain.valid()) {
                        onfocusout($emailDomain[0]);
                    }
                }
            });
            $('#overlap_busiNo').click(function () {
                var $busiNo1 = $('input[name="busiNo[]"]:eq(0)');
                var $busiNo2 = $('input[name="busiNo[]"]:eq(1)');
                var $busiNo3 = $('input[name="busiNo[]"]:eq(2)');
                if ($busiNo1.valid() && $busiNo2.valid() && $busiNo3.valid()) {
                    onfocusout($busiNo1[0]);
                }
            });
            $('#btnRecommendCheck').click(function () {
                var $recommId = $('#recommId');
                $recommId.valid();
                onfocusout($recommId[0]);
            });

            // 아이디, 닉네임, 이메일 주소가 변경된 경우 재검증 처리
            $('#memId, #nickNm, #emailAddress, #emailDomain, #recommId, #email_site, input[name="busiNo[]"]').change(function () {
                var $target = $(this);
                if ($target.attr('id') === 'email_site' || $target.attr('id') === 'emailAddress') {
                    $target = $('#emailDomain');
                }
                if ($target.val().length != 0) {
                    if ($target.attr('name') === 'busiNo[]' && $('#busiNo').data('overlap-businofl') === 'y') {
                        $('#busiNo').addClass('error');
                    } else {
                        $target.addClass('error');
                    }
                }
            });

            // 수정일 경우
            if ($('input[name="mode"]').val() == 'modify') {
                // 추천 받은 리스트 보기
                $('#recommendList').on('click', {
                    memNo: "<?= $data['memNo']; ?>"
                }, member_recommId);
            }

            $('input[name=marriFl]').on('change', {value: "y"}, member.target_disable_toggle).filter(':checked').trigger('change');

            $('input[name=memberFl]').on('change', function () {
                var $businessinfo = $('.div-business');
                if (this.value == 'business') {
                    $businessinfo.removeClass('display-none');
                    $businessinfo.find('input, select').removeClass('ignore');
                } else {
                    $businessinfo.addClass('display-none');
                    $businessinfo.find('input, select').addClass('ignore');
                }
            }).filter(':checked').trigger('change');

            $('select[name=pageNum]').on('change', this.get_history).trigger('change');

            // 수정 내역 페이징 이벤트
            $('#frmHistory').on('click', '.page_navi_number', this.get_history);

            // 도메인 선택 이벤트
            $('#email_site').change(function (e) {
                e.preventDefault();
                $('#emailDomain').val($(e.target).val());
            });

            // 사업자번호 입력 이벤트
            $('input[name="busiNo[]"]').on('keyup', function() {
                if ($('#busiNo').length) {
                    var busiNo = '';
                    $('input[name="busiNo[]"]').each(function() {
                        busiNo += $(this).val();
                    });
                    $('#busiNo').val(busiNo);
                }
            });
        },
        // 저장 버튼 이벤트
        save: function (e) {
            form = e.data.form;
            validation_submit(function (form) {
                var $memId = $('#memId');
                if ($memId.hasClass('error')) {
                    alert('아이디 중복확인이 필요합니다.');
                    return false;
                }
                var $nickNm = $('#nickNm');
                if ($nickNm.hasClass('error') && $nickNm.val().length > 0) {
                    alert('닉네임 중복확인이 필요합니다.');
                    return false;
                }
                var $emailAddress = $('#emailAddress');
                var $emailDomain = $('#emailDomain');
                var hasEmail = $emailAddress.hasClass('error') || $emailDomain.hasClass('error');
                if (hasEmail && $emailAddress.val().length > 0 && $emailDomain.val().length > 0 ) {
                    alert('이메일 중복확인이 필요합니다.');
                    return false;
                }
                var $busiNo = $('#busiNo');
                if ($busiNo.data('overlap-businofl') === 'y' && $busiNo.hasClass('error') && $busiNo.val().length > 0) {
                    alert('사업자번호 중복확인이 필요합니다.');
                    return false;
                }
                var $recommendId = $('#recommId');
                if ($recommendId.hasClass('error') && $recommendId.val().length > 0) {
                    alert('추천인아이디 확인이 필요합니다.');
                    return false;
                }
                var params = $(form).serializeArray();
                post_with_reload(form.action, params);

                // 추천인 등록시 저장 후 히든처리
                if ($recommendId.length > 0 && $recommendId.val() !== '') {
                    var addHtml = $recommendId.val();
                    addHtml += '<input type="hidden" name="recommId" value="' + $recommendId.val() + '"/>'
                    $('span#recomm').html(addHtml);
                }
            });
        }, set_form: function (form) {
            this.form = form;
        }, set_my_page: function (b) {
            is_my_page = b;
        },
        // 사용자 정보 수정 내역 히스토리 호출
        get_history: function (e) {
            e.preventDefault();
            var rowSrc = $('#history-row-template2').html();
            var paginationSrc = $('#history-pagination-template2').html();
            var rowTemplate = _.template(rowSrc);
            var paginationTemplate = _.template(paginationSrc);
            var historyPlaceHolder = $('#frmHistory tbody');
            var paginationPlaceHolder = $('#div-pagination');
            var memNo = $('input[name=memNo]').val();
            var page = $(e.target).is('a') ? $(e.target).text() : $('.pagination .active span').text();
            var pageNum = $('select[name=pageNum]').find(':selected').val();

            ajax_with_layer('../member/member_ps.php', {
                mode: 'history',
                memNo: memNo,
                page: page,
                pageNum: pageNum
            }, function (result) {
                historyPlaceHolder.html(rowTemplate(result));
                paginationPlaceHolder.html(paginationTemplate(result.page));
            });
        }
    };
})($, _, member, window, document, BootstrapDialog);
