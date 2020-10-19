/**
 * 공통 스크립트
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
//@formatter:off
var rootPath = get_script_dirpath('script/common.js');
var blankPath = rootPath + 'blank.php';

// Alert override
(function () {
    tmpAalert = window.alert;
    Type = {
        native: 'native',
        custom: 'custom'
    };
})();

(function (proxy) {
    proxy.alert = function () {
        var message = (!arguments[0]) ? 'null' : arguments[0];
        var type = (!arguments[1]) ? '' : arguments[1];
        if (type && type == 'native') {
            tmpAalert(message);
        } else {
            dialog_alert(message);
        }
    };
})(this);

/**
 * window.console 이 없는 경우에 대한 처리
 */
if (!window.console) console = {
    log: function () {
    },
    debug: function () {
    },
    error: function () {
    },
    info: function () {
    }
};

var logger = {
    level: {debug: "debug", error: "error", info: "info"},
    caller: [],
    display: "info",
    debug: function () {
        if (this.display === this.level.debug) {
            this.add_caller(this.display, arguments);
            console.debug(arguments);
        }
    },
    info: function () {
        if (this.display === this.level.debug || this.display === this.level.info) {
            this.add_caller(this.display, arguments);
            console.info(arguments);
        }
    },
    error: function () {
        if (this.display === this.level.debug || this.display === this.level.info || this.display === this.level.error) {
            this.add_caller(this.display, arguments);
            console.info(arguments);
        }
    },
    /**
     * 로그를 찍을 경우 레벨 및 호출함수명, 시간을 저장하는 함수
     * @param level
     * @param referenceArgs
     */
    add_caller: function (level, referenceArgs) {
        var date = new Date();
        try {
            this.caller.push({level: level, caller: referenceArgs.callee.caller.name, time: date.getTime()});
        } catch (e) {
            console.log(e.message, e);
        }
    },
    /**
     * 화면에 보여질 로그레벨을 변경하는 함수 debug 로 변경할 경우 debug 레벨 콘솔 로그도 보이게 된다.
     * @param level
     */
    set_level: function (level) {
        if (level === this.level.debug || level === this.level.error || level === this.level.info) {
            this.display = level;
        } else {
            console.error('not found log level [' + level + ']');
        }
    },
    /**
     * 로그를 저장하면서 쌓여진 caller 를 콘솔로그로 보여주는 함수
     */
    show_caller: function () {
        $.each(this.caller, function (idx, item) {
            console.log('[' + item.time + '] logger.' + item.level + ', caller: ' + item.caller);
        });
    }
};


/**
 * datetimepicker 초기화
 *
 */
function init_datetimepicker() {

    // 날짜 픽커
    // http://eonasdan.github.io/bootstrap-datetimepicker/Options/#viewmode
    if ($('.js-datepicker').length) {
        var defaultOptions = {
            locale: 'ko',
            format: 'YYYY-MM-DD',
            dayViewHeaderFormat: 'YYYY년 MM월',
            viewMode: 'days',
            ignoreReadonly: true
        };
        var options = $('.js-datepicker').data('options');
        options = $.extend(true, {}, defaultOptions, options);
        $('.js-datepicker').datetimepicker(options);
        //날짜 체크 정규식 /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;

        //이중 선택으로 변경
        $('.js-datepicker input').off('focus').click(function () {
            $(this).parent().data("DateTimePicker").show();
        });
    }

    // 시간 픽커
    if ($('.js-timepicker').length) {
        $('.js-timepicker').datetimepicker({
            locale: 'ko',
            format: 'HH:mm',
            dayViewHeaderFormat: 'YYYY년 MM월',
            ignoreReadonly: true
        });
    }

    // 날짜/시간 픽커 (분단위까지)
    if ($('.js-datetimepicker').length) {
        $('.js-datetimepicker').datetimepicker({
            locale: 'ko',
            format: 'YYYY-MM-DD HH:mm',
            dayViewHeaderFormat: 'YYYY년 MM월',
            ignoreReadonly: true
        });

        $('.js-datetimepicker input').off('focus').click(function () {
            $(this).parent().data("DateTimePicker").show();
        });
    }

    // 날짜/시간 픽커 (초단위까지)
    if ($('.js-fulltimepicker').length) {
        $('.js-fulltimepicker').datetimepicker({
            locale: 'ko',
            format: 'YYYY-MM-DD HH:mm:ss',
            dayViewHeaderFormat: 'YYYY년 MM월',
            ignoreReadonly: true
        });

        $('.js-fulltimepicker input').off('focus').click(function () {
            $(this).parent().data("DateTimePicker").show();
        });
    }

    // 기간설정 버튼 액션
    if ($('.js-dateperiod').length) {
        $('.js-dateperiod label').click(function (e) {
            var $startDate = '',
                $endDate = '',
                $period = $(this).children('input[type="radio"]').val(),
                $elements = $('input[name*=\'' + $(this).closest('.js-dateperiod').data('target-name') + '\']'),
                $inverse = $('input[name*=\'' + $(this).closest('.js-dateperiod').data('target-inverse') + '\']'), $format = $($elements[0]).parent().data('DateTimePicker').format();

            if ($period >= 0) {
                // 달력 일 기준 변경(관리자로그)
                if ($(this).data('type') == 'calendar') {
                    $startDate = $period.substring(0,4) + '-' + $period.substring(4,6) + '-' + $period.substring(6,8);
                    $endDate = moment().format($format);
                } else {
                if ($inverse.length) {
                    $period = '-' + $period;
                }
                if ($inverse.length) {
                    $startDate = moment().hours(23).minutes(59).seconds(0).subtract($period, 'days').format($format);
                } else {
                    $startDate = moment().hours(0).minutes(0).seconds(0).subtract($period, 'days').format($format);
                }

                // 주문/배송 > 송장일괄등록 등록일 검색시 현재시간까지 검색
                if ($('.js-datetimepicker').length && $('input[name="searchPeriod"]').length) {
                    $endDate = moment().format($format);
                } else {
                    $endDate = moment().hours(0).minutes(0).seconds(0).format($format);
                }
            }
            }

            if ($inverse.length) {
                $($elements[1]).val($startDate);
                $($elements[0]).val($endDate);
            } else {
                $($elements[0]).val($startDate);
                $($elements[1]).val($endDate);
            }

        });
        // 버튼 활성 초기화
        $.each($('.js-dateperiod'), function (idx) {
            var $elements = $('input[name*=\'' + $(this).data('target-name') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format();
            if ($('.js-datetimepicker').length && $('input[name="searchPeriod"]').length) {
                var $endDate = moment().format($format);
            } else {
                var $endDate = moment().hours(0).minutes(0).seconds(0).format($format);
            }

            if ($elements.data('init') != 'n') {
                if ($elements.length && $elements.val() != '') {
                    if (moment($($elements[1]).val()).format('YYYY-MM-DD') === moment($endDate).format('YYYY-MM-DD')) {
                        if ($($elements).val().length <= 10) {
                            var $interval = moment($($elements[1]).val()).diff(moment($($elements[0]).val()), 'days');
                            $(this).find('label input[type="radio"][value="' + $interval + '"]').trigger('click');
                        }
                    }
                } else {
                    var $this = $(this);
                    var $activeRadio = $this.find('label input[type="radio"][value="-1"]');
                    if ($activeRadio.length < 1) {
                        $activeRadio = $this.find('label input[type="radio"][value="6"]');
                    }
                    $activeRadio.trigger('click');
                }
            }
        });
    }
    // 기간설정 통계용(오늘은 데이터가 없으므로 오늘은 나오지 않음) 버튼 액션
    if ($('.js-dateperiod-statistics').length) {
        $('.js-dateperiod-statistics label').click(function (e) {
            var $startDate = '',
                $endDate = '',
                $period = $(this).children('input[type="radio"]').val(),
                $elements = $('input[name*=\'' + $(this).closest('.js-dateperiod-statistics').data('target-name') + '\']'),
                $inverse = $('input[name*=\'' + $(this).closest('.js-dateperiod-statistics').data('target-inverse') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format();
            if ($period >= 0) {
                if ($inverse.length) {
                    $period = '-' + $period;
                }
                $startDate = moment().hours(0).minutes(0).seconds(0).subtract($period, 'days').format($format);
                $endDate = moment().hours(0).minutes(0).seconds(0).subtract(1, 'days').format($format);
            }

            if ($inverse.length) {
                $($elements[1]).val($startDate);
                $($elements[0]).val($endDate);
            } else {
                $($elements[0]).val($startDate);
                $($elements[1]).val($endDate);
            }
        });
        // 버튼 활성 초기화
        $.each($('.js-dateperiod-statistics'), function (idx) {
            var $elements = $('input[name*=\'' + $(this).data('target-name') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format(),
                $endDate = moment().hours(-24).minutes(0).seconds(0).format($format);
            if ($elements.length && $elements.val() != '') {
                if (moment($($elements[1]).val()).format('YYYY-MM-DD') === moment($endDate).format('YYYY-MM-DD')) {
                    var $interval = moment($($elements[1]).val()).diff(moment($($elements[0]).val()), 'days') + 1;
                    $(this).find('label input[type="radio"][value="' + $interval + '"]').trigger('click');
                }
            } else {
                $(this).find('label input[type="radio"][value="7"]').trigger('click');
            }
        });
    }
}

/**
 * datetimepicker 초기화 - 년,월만 노출
 *
 */
function init_datepicker_months() {
    if ($('.js-datepicker-months').length) {
        var defaultOptions = {
            locale: 'ko',
            format: 'YYYY-MM',
            dayViewHeaderFormat: 'YYYY년 MM월',
            viewMode: 'months',
            ignoreReadonly: true
        };
        var options = $('.js-datepicker-months').data('options');
        options = $.extend(true, {}, defaultOptions, options);
        $('.js-datepicker-months').datetimepicker(options);
        //날짜 체크 정규식 /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1])$/;

    }

    // 기간설정 버튼 액션
    if ($('.js-dateperiod-months').length) {
        $('.js-dateperiod-months label').click(function (e) {
            var $startDate = '',
                $endDate = '',
                $period = $(this).children('input[type="radio"]').val(),
                $elements = $('input[name*=\'' + $(this).closest('.js-dateperiod-months').data('target-name') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format();

            if ($period >= 0) {
                // 달력 일 기준 변경(관리자로그)
                if ($(this).data('type') == 'calendar') {
                    $startDate = $period.substring(0,4) + '-' + $period.substring(4,6) + '-' + $period.substring(6,8);
                    $endDate = moment().format($format);
                } else {
                    $startDate = moment().hours(0).minutes(0).seconds(0).subtract($period, 'months').format($format);

                    // 주문/배송 > 송장일괄등록 등록일 검색시 현재시간까지 검색
                    if ($('.js-datetimepicker').length && $('input[name="searchPeriod"]').length) {
                        $endDate = moment().format($format);
                    } else {
                        $endDate = moment().hours(0).minutes(0).seconds(0).format($format);
                    }
                }
            }

            $($elements[0]).val($startDate);
            $($elements[1]).val($endDate);

        });
        // 버튼 활성 초기화
        $.each($('.js-dateperiod-months'), function (idx) {
            var $elements = $('input[name*=\'' + $(this).data('target-name') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format();
            if ($('.js-datetimepicker').length && $('input[name="searchPeriod"]').length) {
                var $endDate = moment().format($format);
            } else {
                var $endDate = moment().hours(0).minutes(0).seconds(0).format($format);
            }

            if ($elements.data('init') != 'n') {
                if ($elements.length && $elements.val() != '') {
                    if (moment($($elements[1]).val()).format('YYYY-MM') === moment($endDate).format('YYYY-MM')) {
                        var $interval = moment($($elements[1]).val()).diff(moment($($elements[0]).val()), 'month');
                        $(this).find('label input[type="radio"][value="' + $interval + '"]').trigger('click');
                    }
                } else {
                    var $this = $(this);
                    var $activeRadio = $this.find('label input[type="radio"][value="-1"]');
                    if ($activeRadio.length < 1) {
                        $activeRadio = $this.find('label input[type="radio"][value="6"]');
                    }
                    $activeRadio.trigger('click');
                }
            }
        });
    }

    if ($('.js-dateperiod-statistics-months').length) {
        $('.js-dateperiod-statistics-months label').click(function (e) {
            var $startDate = '',
                $endDate = '',
                $period = $(this).children('input[type="radio"]').val(),
                $elements = $('input[name*=\'' + $(this).closest('.js-dateperiod-statistics-months').data('target-name') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format();
            if ($period >= 0) {
                if (moment().hours(0).minutes(0).seconds(0).format('DD') == '01') {
                    $period++;
                }
                $startDate = moment().hours(0).minutes(0).seconds(0).subtract($period, 'months').format($format);
                $endDate = moment().hours(0).minutes(0).seconds(0).subtract(1, 'days').format($format);
            }

            $($elements[0]).val($startDate);
            $($elements[1]).val($endDate);
        });
        // 버튼 활성 초기화
        $.each($('.js-dateperiod-statistics-months'), function (idx) {
            var $elements = $('input[name*=\'' + $(this).data('target-name') + '\']'),
                $format = $($elements[0]).parent().data('DateTimePicker').format(),
                $endDate = moment().hours(-24).minutes(0).seconds(0).format($format);
            if ($elements.length && $elements.val() != '') {
                if (moment($($elements[1]).val()).format('YYYY-MM') === moment($endDate).format('YYYY-MM')) {
                    var $interval = moment($($elements[1]).val()).diff(moment($($elements[0]).val()), 'days') - 1;
                    $(this).find('label input[type="radio"][value="' + $interval + '"]').trigger('click');
                }
            } else {
                $(this).find('label input[type="radio"][value="7"]').trigger('click');
            }
        });
    }
}

/**
 * 체크박스
 *
 */
function init_checkbox_style() {

    // 체크박스 전체
    if ($('.js-checkall').length > 0) {
        $('.js-checkall').click(function () {
            var usePart = false;
            if($(location).attr("href").match(/order_view.php/)){
                usePart = true;
            }

            if ($(this).data('target-name')) {
                if(usePart === true){
                    $(this).closest("table").find('input:checkbox[name*=\'' + $(this).data('target-name') + '\']:not(:disabled)').prop('checked', this.checked);
                }
                else {
                    $('input:checkbox[name*=\'' + $(this).data('target-name') + '\']:not(:disabled)').prop('checked', this.checked);
                }
            } else if ($(this).data('target-id')) {
                if(usePart === true){
                    $(this).closest("table").find('input:checkbox[id*=\'' + $(this).data('target-id') + '\']:not(:disabled)').prop('checked', this.checked);
                }
                else {
                    $('input:checkbox[id*=\'' + $(this).data('target-id') + '\']:not(:disabled)').prop('checked', this.checked);
                }
            } else {
                // 테이블에서만 사용 가능
                var name = $(this).closest('table').find('thead input:checkbox').data('target-name');
                if (!_.isUndefined(name)) {
                    $('input:checkbox[name*=\'' + name + '\']:not(:disabled)').prop('checked', this.checked);
                }
            }
        });
    }


    // 체크박스 전체 (검색과 같은 체크박스리스트에서 전체 선택시 사용)
    if ($('.js-not-checkall').length > 0) {
        $('.js-not-checkall').each(function (idx) {
            var $firstCheckbox = $(this);
            var $allCheckbox = $('input:checkbox[name*=\'' + $(this).data('target-name') + '\']');
            var $restCheckbox = $allCheckbox.not(':eq(0)');
            var isChecked = function (e, callback) {
                if (!$allCheckbox.is(':checked')) {
                    $(e.target).prop('checked', false);
                    $firstCheckbox.prop('checked', true);
                    //alert('최소 1개 이상 선택하셔야 합니다.');
                } else {
                    callback;
                }
            };
            $firstCheckbox.click(function (e) {
                isChecked(e, $restCheckbox.prop('checked', false));
            });
            $restCheckbox.click(function (e) {
                isChecked(e, $firstCheckbox.prop('checked', false));
            });
        });
    }

}


/**
 * 파일박스
 *
 * @see http://markusslima.github.io/bootstrap-filestyle/
 */
function init_file_style() {
    var files = $(":file").not('.no-filestyle');
    if (files.length > 0) {
        files.filestyle({
            icon: false,
            buttonText: '찾아보기',
            buttonName: 'btn-gray',
            buttonBefore: true,
            size: 'sm'
        });
    }
}

function init_file_style_destroy() {
    var files = $(":file").not('.no-filestyle');
    if (files.length > 0) {
        files.filestyle('destroy');
    }
}

/**
 * 이메일이 선택되어 있는 경우 트리거 처리
 */
function init_email_select() {
    if ($('.js-email-select select').length) {
        $('.js-email-select select').trigger('change');
    }
}

/**
 * 구글 번역 연결
 */
function init_translate() {

    //구글 번역 연결
    if ($('.js-translate-google').length) {
        $('.js-translate-google').click(function (e) {
            var targetName = $(this).data('target-name');
            if (targetName) {
                var language = $(this).data('language');
                switch (language) {
                    case 'us' :
                        language = 'en';
                        break;
                    case 'cn' :
                        language = 'zh-CN';
                        break;
                    case 'jp' :
                        language = 'ja';
                        break;
                }
                show_popup('https://translate.google.com/#auto/' + language + '/' + $("input[name='" + targetName + "']").val());
            } else {
                return false;
            }
        });
    }

}

/**
 * 상세검색 초기화
 *
 * @author oneorzero
 */
function init_detail_search() {
    //상세검색 초기화
    $('.js-search-toggle').on('click', function (e) {
        var $form = $(this).closest('form');
        var $tbodyObj = $form.find('.js-search-detail');
        var $inputObj = $form.find('input[name=detailSearch]');
        var $inputVal = $inputObj.val();

        if (e.isTrigger == undefined) $inputObj.val($inputVal == 'y' ? 'n' : 'y');
        if ($inputObj.val() == 'y') {
            $tbodyObj.show();
            $(this).find('span').text('닫힘');
            $(this).addClass('opened');
        } else {
            $tbodyObj.hide();
            $(this).find('span').text('펼침');
            $(this).removeClass('opened');
        }
    });
    $('.js-search-toggle').trigger('click');
}

/**
 * 다이얼로그 내 아이프레임 리사이즈 처리
 * @param obj
 */
function layer_resize_in_iframe(obj) {
    obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
}

/**
 * 아이프레임 크기 리사이징
 *
 * @param int size 세로사이즈(-1 = iframe 내부 세로크기)
 */
function resizeInParent(size) {
    if (window.frameElement == null) return;

    var thisFrame = window.frameElement;
    if (navigator.appName == "Microsoft Internet Explorer") {

        if (size != -1) {
            thisFrame.style.height = size + "px";
        }
        else {
            thisFrame.style.height = document.body.scrollHeight + "px";
        }
    }
    else {
        if (size != -1) {
            thisFrame.style.height = size + "px";
        }
        else {
            thisFrame.style.height = document.documentElement.scrollHeight + "px";
        }
    }
}

/**
 * 다이얼로그 사이즈 반환
 *
 * @param size
 */
function get_layer_size(size) {
    var dialogSize = 'normal';
    switch (size) {
        default:
        case 'normal':
            dialogSize = BootstrapDialog.SIZE_NORMAL;
            break;
        case 'small':
            dialogSize = BootstrapDialog.SIZE_SMALL;
            break;
        case 'wide':
            dialogSize = BootstrapDialog.SIZE_WIDE;
            break;
        case 'wide-lg':
            dialogSize = BootstrapDialog.SIZE_WIDE_LARGE;
            break;
        case 'wide-xlg':
            dialogSize = BootstrapDialog.SIZE_WIDE_XLARGE;
            break;
        case 'wide-sm':
            dialogSize = BootstrapDialog.SIZE_WIDE_SMALL;
            break;
        case 'large'://텍스트만 커지는 경우
            dialogSize = BootstrapDialog.SIZE_LARGE;
            break;
    }

    return dialogSize;
}

/**
 * 다이얼로그 창 닫기
 */
function layer_close() {
    if (!_.isUndefined(BootstrapDialog)) {
        var dialogs = BootstrapDialog.dialogs;
        if ($.isEmptyObject(dialogs)) {
            var parents = [];
            for (var i = 0; i < 10; i++) {
                parents.push('parent');
                dialogs = eval(parents.join('.') + '.BootstrapDialog.dialogs');
                if (!$.isEmptyObject(dialogs)) {
                    break;
                }
            }
        }

        for (var index in dialogs) {
            var dialog = dialogs[index];
            if (dialog.isOpened()) {
                dialog.close();
            }
        }
    }
}

/**
 * 프레임 다이얼로그
 *
 * @author sunny
 * @param string src 경로
 * @param string title 타이틀
 * @param string size 창 사이즈 (normal|small|wide|large)
 */
function frame_popup(src, title, size, isReload) {
    if (typeof isReload == 'undefined') {
        isReload = true;
    }
    var path = get_script_dirpath('script/common.js');
    var iframe = '<div id="iframe-loading" class="text-center" style="height:300px;padding-top:50px;"><img src="' + path + 'img/icon_loading.gif"></div><iframe id="ifrmLayer" name="ifrmLayer" width="100%" src="' + src + '" scrolling="no" frameborder="0" onload="layer_resize_in_iframe(this)"  ></iframe>';
    BootstrapDialog.show({
        title: title,
        size: get_layer_size(size),
        message: $(iframe),
        closable: true,
        onhide: function (dialog) {
            if (isReload) {
                self.location.reload();
            }
        },
        onhidden: function (dialog) {
            if (callback) callback();
        },
        onshown: function (dialog) {
            $('#iframe-loading').hide();
        }
    });
};

/**
 * 레이어 다이얼로그
 *
 * @author sunny
 * @param mixed msg 메시지
 * @param int width 넓이
 * @param int height 높이
 */
function layer_popup(message, title, size, dataSortID, callback) {
    BootstrapDialog.show({
        title: title,
        size: get_layer_size(size),
        message: $(message),
        closable: true,
        //size: BootstrapDialog.SIZE_WIDE,
        onhidden: function (dialog) {
            // 데이타 소트 여부
            if (typeof dataSortID == 'string' && dataSortID.trim() != '') {
                add_data_sortable(dataSortID);
            }
            if (callback) callback(dialog);
        }
    });
};

/**
 * 레이어UI 다이얼로그
 *
 * @author sunny
 * @param mixed msg 메시지
 * @param int width 넓이
 * @param int height 높이
 */
function layer_ui(message, title) {
    BootstrapDialog.show({
        title: title,
        message: $(message),
        closable: false
    });
};

/**
 * 프리미엄경고창 다이얼로그
 *
 * @author sunny
 * @param string closeMode 닫기모드 (back,close)
 */
function premium_ui(closeMode) {
    frame_popup('../share/warning_premium.php?closeMode=' + closeMode, '프리미엄 서비스 안내');
};

/**
 * 상품 추가 레이어창
 *
 * @author artherot
 * @param string fileCd 레이어창 화일명 코드
 * @param string layerFormID 레이어창 ID
 * @param string parentFormID 추가될 부모창의 상품정보 ID
 * @param string dataFormID 추가된 상품 정보의 span ID
 * @param mixed dataInputNm 추가된 상품 정보의 hidden input의 goodsNo name 값
 * @param string layerTitle 레이어창 이름
 * @param string dataSort 데이타 소트 여부
 */
function layer_add_info(fileCd, addParam) {
    if ($.type(addParam) != 'object') var addParam = {};

    if (addParam['layerFormID'] == undefined) addParam['layerFormID'] = 'addSearchForm';
    if (addParam['dataFormID'] == undefined) addParam['dataFormID'] = 'info_' + fileCd;
    if (addParam['parentFormID'] == undefined) addParam['parentFormID'] = fileCd + 'Layer';
    if (addParam['dataInputNm'] == undefined) addParam['dataInputNm'] = '';

    var loadChk = $('#' + addParam['layerFormID']).length;
    var title = '';
    var dataInputNm = addParam['dataInputNm'];

    switch (fileCd) {
        case 'scm':
            title = "공급사 선택";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : fileCd + "No";
            break;
        case 'brand':
            title = "브랜드";
            addParam['size'] = "wide";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : fileCd + "Cd";
            break;
        case 'goods':
            title = "상품";
            addParam['size'] = "wide";
            break;
        case 'category':
            title = "카테고리";
            addParam['size'] = "wide";
            break;
        case 'category_batch':
            title = "카테고리 일괄선택";
            addParam['size'] = "wide";
            break;
        case 'member_group':
            title = "회원 등급";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : 'memberGroupNo';
            break;
        case 'coupon':
            title = "쿠폰 선택";
            addParam['size'] = "wide";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : fileCd + "No";
            break;
        case 'delivery':
            title = "배송비 선택";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : fileCd + "No";
            addParam['size'] = "wide";
            break;
        case 'board':
            title = "게시판";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : fileCd + "No";
            break;
        case 'goods_option':
            title = "옵션선택";
            break;
        case 'excel':
            title = "엑셀 다운로드";
            addParam['size'] = "wide";
            break;
        case 'sms_contents':
            title = "SMS / LMS 문구";
            break;
        case 'must_info':
            title = "상품 필수정보";
            break;
        case 'display_main':
            title = "기존 진열 상품 선택";
            break;
        case 'detail_info':
            title = addParam['detailInfoTitle'];
            break;
        case 'hscode':
            title = addParam['detailInfoTitle'];
            addParam['size'] = "wide";
            break;
        case 'purchase':
            title = "매입처";
            addParam['size'] = "wide";
            addParam['dataInputNm'] = dataInputNm != '' ? dataInputNm : fileCd + "No";
            break;
        case 'naver_stats':
            title = "네이버 쇼핑 노출상품 현황";
            addParam['size'] = "wide";
            break;
        case 'daum_stats':
            title = "쇼핑하우 노출상품 현황";
            addParam['size'] = "wide";
            break;
        case 'event_select':
            //기획전 관련 설정, 상품일괄관리에서 검색용 으로사용
            title = "기획전 선택";
            addParam['size'] = "wide";
            break;
        case 'comeback_coupon_result':
            addParam['size'] = 'wide';
            break;
        //주문리스트 그리드 설정
        case 'order_grid_config' :
        //상품리스트 그리드 설정
        case 'goods_grid_config' :
            title = "조회항목 설정";
            break;
        case 'goods_restock_batch':
            addParam['size'] = 'wide';
            break;
        case 'goods_benefit':
            title = "상품 혜택";
            addParam['size'] = "wide";
            break;
        case 'manage':
            title = "운영자선택";
            break;
        default:
            title = "";
    }
    if (addParam['layerTitle'] == undefined) {
        addParam['layerTitle'] = title;
    }

    $.ajax({
        url: '../share/layer_' + fileCd + '.php',
        type: 'get',
        data: addParam,
        async: false,
        success: function (data) {
            if (loadChk == 0) {
                data = '<div id="' + addParam['layerFormID'] + '">' + data + '</div>';
            }
            var layerForm = data;
            var configure = {
                title: addParam['layerTitle'],
                size: get_layer_size(addParam['size']),
                message: $(layerForm),
                closable: true
            };
            if (typeof addParam['events'] == 'object') {
                BootstrapDialog.show($.extend({}, configure, addParam['events']));
            } else {
                BootstrapDialog.show(configure);
            }

        }
    });
}

/**
 * 회원검색 레이어
 * @param keyword
 */
function layer_member_search(keyword, key) {
    var loadChk = $('div#layerSearchMember').length;
    if(_.isUndefined(key)) {
        key = 'memId';
    }
    $.get('../share/layer_member_search.php', {keyword: keyword, key: key}, function (data) {
        if (loadChk === 0) {
            data = '<div id="layerSearchMember">' + data + '</div>';
        }

        var dialog = BootstrapDialog.show({
            name: "layer_member_search",
            title: "회원검색",
            size: BootstrapDialog.SIZE_WIDE,
            message: $(data),
            closable: true
        });

        dialog.$modalBody.on('click', '.pagination a', function (e) {
            e.preventDefault();
            var $target = $(e.target);
            var page = $target.data('page');
            if (typeof page == 'undefined') {
                page = $target.closest('a').data('page');
            }
            var params = {
                key: $('select[name="key"] :selected', dialog.$modalBody).val(),
                keyword: $('input[name=\'keyword\']', dialog.$modalBody).val(),
                page: $.trim(page)
            };
            $.get($('input[name=\'keyword\']', dialog.$modalBody).data('uri') + 'share/layer_member_search.php', params, function (data) {
                $('div#layer-wrap', dialog.$modalBody).html($(data).children());
            });
        }).on('keyup', '#keyword', function (e) {
            if (e.which == 13) {
                $('#btnMemberSearch').trigger('click');
            }
        }).on('click', '#btnMemberSearch', function () {
            var params = {
                key: $('select[name="key"] :selected', dialog.$modalBody).val(),
                keyword: $('input[name=\'keyword\']', dialog.$modalBody).val()
            };
            $.get($('input[name=\'keyword\']', dialog.$modalBody).data('uri') + 'share/layer_member_search.php', params, function (data) {
                $('div#layer-wrap', dialog.$modalBody).html($(data).children());
            });
        });
    });
}

/**
 * 발송대기 SMS 내용 수정 레이어
 * @param sno
 */
function layer_sms_contents(sno) {
    var loadChk = $('div#layerSmsContents').length;
    $.get('../share/layer_standby_sms.php', {sno: sno}, function (data) {
        if (loadChk === 0) {
            data = '<div id="#formStandbySms">' + data + '</div>';
        }

        var dialog = BootstrapDialog.show({
            name: "layer_sms_contents",
            title: "SMS 내용 수정",
            size: BootstrapDialog.SIZE_WIDE,
            message: $(data),
            closable: false
        });

        dialog.$modalBody.on('click', '#modify', function () {
            var params = [{name: "mode", value: "modify"}, {
                name: "contents",
                value: $('#contents', dialog.$modalBody).val()
            }, {name: "sno", value: sno}];
            ajax_with_layer('../member/sms_log_ps.php', params);
        }).on('click', '#cancel', function () {
            layer_close();
            location.reload(true);
        });
    });
}

/**
 * SMS 발송 내역 상세보기
 * @param sno
 */
function layer_sms_send_list(sno) {
    var addParam = {};
    addParam['layerFormID'] = 'layerSmsSendList';
    addParam['smsLogSno'] = sno;

    var loadChk = $('#' + addParam['layerFormID']).length;

    console.log(addParam);
    $.ajax({
        url: '../share/layer_sms_send_list',
        type: 'get',
        data: addParam,
        async: false,
        success: function (data) {
            if (loadChk == 0) {
                data = '<div id="' + addParam['layerFormID'] + '">' + data + '</div>';
            }
            var layerForm = data;
            BootstrapDialog.show({
                title: 'SMS 발송 내역 상세보기',
                size: BootstrapDialog.SIZE_WIDE,
                message: $(layerForm),
                closable: true,
            });
        }
    });
}

/**
 * SMS 발송 내역 상세보기
 * @param sno
 */
function layer_kakao_send_list(sno, sendKey, status) {
    var addParam = {};
    addParam['layerFormID'] = 'layerKakaoSendList';
    addParam['smsLogSno'] = sno;
    addParam['smsSendKey'] = sendKey;
    addParam['sendStatus'] = status;

    var loadChk = $('#' + addParam['layerFormID']).length;

    console.log(addParam);
    $.ajax({
        url: '../share/layer_kakao_send_list',
        type: 'get',
        data: addParam,
        async: false,
        success: function (data) {
            if (loadChk == 0) {
                data = '<div id="' + addParam['layerFormID'] + '">' + data + '</div>';
            }
            var layerForm = data;
            BootstrapDialog.show({
                title: '카카오 알림톡 발송 내역 상세보기',
                size: BootstrapDialog.SIZE_WIDE,
                message: $(layerForm),
                closable: true,
            });
        }
    });
}

function layer_member_mileage() {
    var loadChk = $('div#layerMemberMileage').length;
    $.get('../share/layer_member_mileage.php', {}, function (data) {
        if (loadChk === 0) {
            data = '<div id="#layerMemberMileage">' + data + '</div>';
        }

        var dialog = BootstrapDialog.show({
            name: "layer_member_mileage",
            title: "마일리지 지급/차감",
            size: BootstrapDialog.SIZE_WIDE,
            message: $(data),
            closeable: false
        });
    });
}

function layer_coupon_info(couponNo) {
    var loadChk = $('div#layerCouponInfo').length;
    $.get('../share/layer_coupon_info.php', {couponNo: couponNo}, function (data) {
        if (loadChk === 0) {
            data = '<div id="#layerCouponInfo">' + data + '</div>';
        }

        var dialog = BootstrapDialog.show({
            name: "layer_coupon_info",
            title: "쿠폰 정보",
            size: BootstrapDialog.SIZE_WIDE,
            message: $(data),
            closeable: false
        });
    });
}

function layer_member_deposit() {
    var loadChk = $('div#layerMemberDeposit').length;
    $.get('../share/layer_member_deposit.php', {}, function (data) {
        if (loadChk === 0) {
            data = '<div id="#layerMemberDeposit">' + data + '</div>';
        }

        var dialog = BootstrapDialog.show({
            name: "layer_member_deposit",
            title: "예치금 지급/차감",
            size: BootstrapDialog.SIZE_WIDE,
            message: $(data),
            closeable: false
        });
    });
}

/**
 * 컨트롤러 실행 후 레이어띄우는 함수
 * 레이어를 띄울 태그에 data-target-url 로 url을 지정하여야 한다.
 */
function layer_remote() {
    var $this = $(this);
    logger.debug($this);
    var layer_key = 'remote';
    if (typeof $this.attr('id') === 'string') {
        layer_key = $this.attr('id');
    } else if (typeof $this.attr('name') === 'string') {
        layer_key = $this.attr('name');
    }
    var layer_length = $('div#layer_' + layer_key).length;
    $.get($this.data('target-url'), {}, function () {
        logger.info('layer load ' + arguments[1], 'layer_key ' + layer_key, 'layer_length ' + layer_length);
        if (layer_length === 0) {
            var html = '<div id="layer_' + layer_key + '">' + arguments[0] + '</div>';
            if (arguments[1] == 'error') {
                logger.error('layer remote error');
            }
            var default_options = {};
            if ($this.data('title')) {
                default_options.title = $this.data('title');
            }
            if ($this.data('size')) {
                default_options.size = $this.data('size');
            }
            $.extend(default_options, {
                message: $(html),
                name: 'layer_' + layer_key
            });
            logger.debug('default options', default_options);
            if (layer_length === 0) {
                BootstrapDialog.show(default_options);
            }
        }
    });
}

/**
 * 추가 상품 이동
 *
 * @param string parentFormID 추가된 부모창의 상품정보 ID
 */
function add_data_sortable(parentFormID) {
    if ($('#' + parentFormID).html().trim() != '') {
        $('#' + parentFormID + '>span').css('cursor', 'pointer');
        $('#' + parentFormID).sortable();
        $('#' + parentFormID).disableSelection();
    }
}

/**
 * WebFTP창 호출
 *
 * @author sunny
 */
function webftp_tool() {
    win = popup({
        url: '../share/popup_webftp.php',
        target: 'webftpTool',
        width: 1024,
        height: 750,
        scrollbars: 'yes',
        resizable: 'no'
    });
    win.focus();
}

/**
 * Imagehost WebFTP창 호출
 *
 * @author sunny
 */
function img_webftp_tool() {
    win = popup({
        url: 'http://image.godo.co.kr/login/imghost_login.php',
        target: 'ImgWebftpTool',
        width: 980,
        height: 700,
        resizable: 'no'
    });
    win.focus();
}

/**
 * 쿠폰 관리 팝업
 *
 * @author sj
 * @param int memNo 회원번호
 * @param bool bPopup 팝업창 여부
 */
function view_coupon_log(memNo, bPopup) {
    if (bPopup)
        window.open('../share/member_coupon.php?memNo=' + memNo, 'member_coupon', 'width=600, height=500, scrollbars=yes');
    else
        frame_popup('../share/member_coupon.php?memNo=' + memNo, '600', '500', '쿠폰 내역');
}

/**
 * 개발소스관리 팝업
 *
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
function development_manager(page) {
    if (page === undefined || page === '') {
        page = 'index.php';
    }
    window.open('/development/' + page, 'development_manager', 'width=1100,height=720,scrollbars=yes');
}

/**
 * CRM 내역 팝업
 *
 * @author sj
 * @param int memNo 회원번호
 * @param bool bPopup 팝업창 여부
 */
function member_crm(memNo) {
    window.open('/share/member_crm.php?popupMode=yes&memNo=' + memNo, 'member_crm', 'width=1200,height=850,scrollbars=yes');
}

/**
 * 메일 보내기 팝업
 * @param memNo 회원번호
 */
function member_mail(memNo) {
    window.open('../member/mail_send.php?popupMode=yes&memNo=' + memNo, 'mail_form', 'width=1024,height=768,scrollbars=yes');
}

/**
 * 상담 등록 팝업
 * @param memNo 회원번호
 */
function member_counsel(memNo,sno) {
    if(sno) {
        window.open('../share/popup_counsel_write.php?popupMode=yes&memNo=' + memNo + '&sno=' + sno, 'counsel_form', 'width=520,height=768');
    }
    else {
        window.open('../share/popup_counsel_write.php?popupMode=yes&memNo=' + memNo, 'counsel_form', 'width=520,height=768');
    }
}

/**
 * SMS 보내기 팝업
 *
 * @author sj
 * @param string memNo 회원번호
 * @param string memNm 회원이름
 * @param string cellPhone 휴대폰
 */
function member_sms(memNo, memNm, cellPhone) {
    console.log(memNo, memNm, cellPhone);
    if (!memNo) memNo = '';
    if (!memNm) memNm = '';
    if (!cellPhone) cellPhone = '';

    win = popup({
        url: '../member/sms_send.php?receiverMemNo=' + memNo + '&receiverNm=' + memNm + '&receiverPhone=' + cellPhone,
        target: 'smsSendPopup',
        width: 1200,
        height: 600,
        scrollbars: 'yes',
        resizable: 'no'
    });
    win.focus();
    return win;
}

/**
 * SMS 여러사람에게 보내기 팝업
 * 단, guest 모드로만 전송된다.
 *
 * @param params
 * @returns {Object|*}
 */
function member_multi_sms(params) {
    var url = '../member/sms_send.php?';
    if (_.isString(params.opener)) {
        url += 'opener=' + params.opener;
        $.each(params.receiverKeys, function (index) {
            url += '&receiverKeys[]=' + params.receiverKeys[index] + '&';
        });
    }

    win = popup({
        url: url,
        target: 'smsSendPopup',
        width: 1200,
        height: 600,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
}

/**
 * 파워메일 팝업창
 *
 * @author artherot
 * @param string sendUrl Url
 */
function power_mail_popup(sendUrl) {
    var path = get_script_dirpath('script/common.js');

    win = popup({
        url: sendUrl,
        target: 'powerMail',
        width: 860,
        height: 700,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
};

// 자동 SMS 설정 팝업창
function sms_auto_popup() {
    var $js_link_sms_auto = $('.js-link-sms-auto');
    var hash = '';
    if ($js_link_sms_auto.length > 0) {
        $js_link_sms_auto.click(function (e) {
            if ($(this).attr('href')) {
                hash = $(this).attr('href');
            }
            window.open('../member/sms_auto.php?popupMode=yes' + hash, 'sms_auto', 'width=1400, height=700, scrollbars=no');
        });
    }
}

// 자동 메일 설정 팝업창
function mail_config_auto_popup() {
    var $js_link_mail_auto = $('.js-link-mail-auto');
    var hash = '';
    if ($js_link_mail_auto.length > 0) {
        $js_link_mail_auto.click(function (e) {
            if ($(this).attr('href')) {
                hash = $(this).attr('href');
            }
            window.open('../member/mail_config_auto.php?popupMode=yes' + hash, 'mail_auto', 'width=1400, height=700, scrollbars=no');
        });
    }
}

/**
 * 추천받은아이디보기 팝업
 *
 * @author sj
 * @param int memNo 회원번호
 */
function member_recommId(memNo) {
    frame_popup('../share/layer_member_recommId.php?memNo=' + memNo, '600', '500');
}

/**
 * 주문 내역 보기
 *
 * @author artherot
 * @param int memNo 회원번호
 * @param bool bPopup 팝업창 여부
 */
function view_member_order_list(memNo, bPopup) {
    if (bPopup)
        window.open('../share/member_order_list.php?memNo=' + memNo, 'member_order_list', 'width=600, height=450, scrollbars=yes');
    else
        frame_popup('../share/member_order_list.php?memNo=' + memNo, '600', '500', '회원 주문 내역 보기');
}

/**
 * 현금영수증 발급요청서 뷰 및 처리
 *
 * @author artherot
 * @param string orderNo 주문번호
 */
function cash_receipt_process(orderNo, sno) {
    $.get('./frame_cash_receipt_view.php?sno=' + sno + '&orderNo=' + orderNo, function (data) {
        BootstrapDialog.show({
            size: BootstrapDialog.SIZE_WIDE_SMALL,
            title: '현금영수증 신청/발급 정보',
            message: $(data)
        });
    });
}

/**
 * 상품 등록/수정 팝업창
 *
 * @author artherot
 * @param string orderNo 주문 번호
 */
function goods_register_popup(goodsNo, isProvider, page) {
    if (isProvider) var url = '/provider/goods/goods_register.php?popupMode=yes&goodsNo=' + goodsNo;
    else var url = '/goods/goods_register.php?popupMode=yes&goodsNo=' + goodsNo;

    if (page) url += page;

    win = popup({
        url: url,
        target: '',
        width: 1110,
        height: 800,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
};

/**
 * 추가상품 등록/수정 팝업창
 *
 * @author artherot
 * @param string orderNo 주문 번호
 */
function addgoods_register_popup(goodsNo, isProvider) {

    var path = get_script_dirpath('script/common.js');
    if (isProvider) var url = '/provider/goods/add_goods_register.php?popupMode=yes&addGoodsNo=' + goodsNo;
    else var url = '/goods/add_goods_register.php?popupMode=yes&addGoodsNo=' + goodsNo;

    win = popup({
        url: url,
        target: '',
        width: 860,
        height: 600,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
}

/**
 * 주문상세 팝업창
 *
 * @author artherot
 * @param string orderNo 주문 번호
 */
function order_view_popup(orderNo, isProvider) {
    var path = get_script_dirpath('script/common.js'),
        url = '/order/order_view.php?popupMode=yes&orderNo=' + orderNo;
    if (isProvider) {
        url = '/provider/order/order_view.php?popupMode=yes&orderNo=' + orderNo;
    }
    win = popup({
        url: url,
        target: 'order_view',
        width: 1124,
        height: 800,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
}

/**
 * 주문 프린트 팝업창
 *
 * @author artherot
 * @param string orderNo 주문 번호
 */
function order_print_popup(printMode, orderPrintFrmNm, checkboxFrmNm, orderNoCheckboxNm, isProvider) {
    var printForm = $('#' + orderPrintFrmNm);
    var checkboxNm = $('#' + checkboxFrmNm + ' input[name*=\'' + orderNoCheckboxNm + '\']:checked');
    var printCode = $('#' + orderPrintFrmNm + ' input[name=\'orderPrintCode\']');

    // 항목 체크
    if (checkboxNm.length == 0) {
        alert('선택된 항목이 없습니다.');
        return false;
    }

    // 인쇄 종류 선택
    if (printMode == '' || typeof printMode == 'undefined') {
        alert('인쇄 종류를 선택해 주세요.');
        return false;
    }

    // 선택된 주문 번호
    printCode.val('');
    var fields = checkboxNm.serializeArray();
    jQuery.each(fields, function (i, field) {
        if (field.value.match(/\|\|/)) {
            var newCode = field.value.split('||');
            var orderNo = newCode[0];
        }
        else {
            var orderNo = field.value;
        }
        printCode.val(printCode.val() + '||' + orderNo);
    });

    // 새창에 form 값을 전송
    var orderPrint = window.open('', 'orderPrintPopup', 'width=992,height=600,menubar=yes,scrollbars=yes');
    $('#' + orderPrintFrmNm + ' input[name=\'orderPrintMode\']').val(printMode);
    printForm.attr('target', 'orderPrintPopup');
    if (isProvider) {
        printForm.attr('action', '/provider/order/order_print.php');
    } else {
        printForm.attr('action', '/order/order_print.php');
    }
    printForm.submit();
};

/**
 * 품목별 상품 정보 고시 내용 보기 팝업창
 *
 * @author artherot
 */
function goods_must_info_popup() {
    //var path = get_script_dirpath('script/common.js');

    win = popup({
        url: '../goods/layer_goods_must_info.php',
        target: '',
        width: 600,
        height: 650,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
}

/**
 * 치환코드 팝업
 * @param type string  치환코드 타입 공백일 경우 쇼핑몰 기본 설정 값이 뵤여진다.
 * @param defaultCode array   쇼핑몰 기본 설정 값에서 표시할 치환코드 리스트 ex) ['{rc_mallNm},{rc_companyNm}']
 */
function replace_code_popup(type, defaultCode) {
    var path = get_script_dirpath('script/common.js');
    var params = [];
    if (_.isUndefined(type) === false) {
        params.push('type=' + type || '');
    }
    console.log(defaultCode.length);
    if (_.isUndefined(defaultCode) === false && _.isArray(defaultCode) === true) {
        for (var i = 0; i < defaultCode.length; i++) {
            params.push('defaultCode[]=' + defaultCode[i]);

        }
    }
    var query = params.join('&');
    var targetUrl = path + '../../../share/replace_code.php?' + query;
    var win = popup({
        url: targetUrl,
        target: '',
        width: 800,
        height: 500,
        scrollbars: 'yes',
        resizable: 'no'
    });
    win.focus();
}

/**
 * 컴백 쿠폰 정보 팝업창
 *
 * @author haky
 * @param string couponNo 쿠폰 번호
 */
function comeback_coupon_popup(couponNo) {
    var url = '/promotion/comeback_coupon_regist.php?popupMode=yes&sno=' + couponNo;

    var win = popup({
        url: url,
        target: '',
        width: 860,
        height: 600,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
}

/**
 * 고도 기획코너
 *
 * @author sunny
 * @param string idnm Element Id
 * @param string section 기획코너섹션
 */
function panel(idnm, section) {
    if (idnm == '' || $('#' + idnm).length == 0) {
        return;
    }
    $.post('../share/panel.php', {
        'idnm': idnm,
        'section': section
    }, function (data) {
        if (data == '') return;
        var obj = $('#' + idnm);
        obj.html(data);
    });
}

/**
 * PNG 보정
 *
 * @author sunny
 * @param obj 이미지 객체
 * @return
 */
function set_png24(obj) {
    obj.width = obj.height = 1;
    obj.className = obj.className.replace(/\bpng24\b/i, '');
    obj.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + obj.src + "',sizingMethod='image');";
    obj.src = '';
    return '';
}

/**
 * 다이얼로그 형 경고창
 *
 * @param message
 * @param title
 * @param options
 */
function dialog_alert(message, title, options) {
    if (_.isUndefined(title)) {
        title = '경고';
    }
    BootstrapDialog.show({
        title: title,
        message: message,
        buttons: [{
            label: '확인',
            cssClass: 'btn-black',
            hotkey: 13,
            size: BootstrapDialog.SIZE_LARGE,
            action: function (dialog) {
                dialog.close();
                if (!_.isUndefined(options)) {
                    if (options.isReload) {
                        window.location.reload();
                    } else if (options.location) {
                        window.location.href = options.location;
                    } else if ($.isFunction(options.callback)) {
                        options.callback();
                    }

                }
            }
        }]
    });
}

/**
 * 다이얼로그 형 확인창 (comfirm)
 *
 * @param message
 * @param title
 * @param callback
 * @returns {boolean}
 */
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

/**
 * 관리자용 ajax post 후 페이지 리로드
 * @param url
 * @param data
 * @param reloadUrl 공백이거나 undefined일 경우 현재 페이지 리로드
 */
function post_with_reload(url, data, reloadUrl) {
    var settings = $.extend({}, {type: "post", data: data});
    var $ajax = $.ajax(url, settings);
    $ajax.done(function (data, textStatus, jqXHR) {
        console.info('post_with_reload', data);
        var code = data.code;
        var message = data.message;
        var dialogSettings = {};
        if (_.isUndefined(code) && _.isUndefined(message)) {
            dialogSettings = {message: data, isReload: true};
        } else {
            dialogSettings = {message: message, isReload: false};
        }
        if (reloadUrl !== undefined || reloadUrl !== '') {
            dialogSettings = $.extend(dialogSettings, {reloadUrl: reloadUrl});
        }

        layer_close();
        var settings = $.extend({}, {timeout: 2000, isReload: false, isLocation: false, reloadUrl: ''}, dialogSettings);
        settings.onshown = function (dialog) {
            setTimeout(function () {
                dialog.close();
            }, settings.timeout);
        };
        settings.onhidden = function (dialog) {
            if ($.isFunction(settings.hiddenCallback)) {
                settings.hiddenCallback();
            } else if (settings.isReload === true) {
                if (settings.reloadUrl !== '') {
                    top.location = settings.reloadUrl;
                } else {
                    top.location.reload(true);
                }
            }
        };
        top.BootstrapDialog.show(settings);
    });
}

/**
 * 관리자용 ajax
 * @param url
 * @param data
 * @param success
 * @param error
 * @param options
 */
function ajax_with_layer(url, data, success, error, options) {
    //console.log('ajax_with_layer', url, data, success, error, options);
    var settings = $.extend({}, {type: "post", data: data}, options);
    var $ajax = $.ajax(url, settings);
    $ajax.done(function (data, textStatus, jqXHR) {
        //console.log('ajax_with_layer', data, textStatus, jqXHR);
        var code = data.code;
        var message = data.message;
        //console.log(code, message);
        if (_.isUndefined(code) && _.isUndefined(message)) {
            if ($.isFunction(success)) {
                success(data, textStatus, jqXHR);
            } else {
                layer_close();
                alert(data);
            }
        } else {
            if ($.isFunction(error)) {
                error(message, data, textStatus, jqXHR);
            } else {
                layer_close();
                alert(message);
            }
        }
    });
}

var coupon_reload = function (couponEventType) {
    var $ajax = $.ajax('../promotion/coupon_ps.php', {type: "post", data: {mode: "selectCouponNameList", couponEventType: couponEventType}});
    var $this = $(this);
    $ajax.done(function (data, textStatus, jqXHR) {
        var options = [];
        $.each(data, function (idx, item) {
            options.unshift('<option value="' + idx + '">' + item + '</option>');
        });
        $('#benefitCouponSno').html(options.join(''));
    });
};

/**
 * 전역 Ajax 에러/성공 실행 함수
 */
$(document).ajaxError(function (event, XMLHttpRequest, ajaxOptions, thrownError) {
    if (XMLHttpRequest.status == '404' && XMLHttpRequest.statusText == 'Not Found') {
        console.log('[404 Not Found]\nThe requested URL was not found.', XMLHttpRequest.responseText);
    }
    else if (XMLHttpRequest.status == '500' && XMLHttpRequest.statusText == 'Internal Server Error' && XMLHttpRequest.responseText != '') {
        console.log('[500 Internal Error]\nServer was internal error.', XMLHttpRequest.responseText);
    }
    console.log('Ajax Error:', ajaxOptions.url, XMLHttpRequest.responseText, thrownError);
});

// GLOBAL Ajax Complete Handler
$(document).ajaxComplete(function (event, xhr, settings) {
    if (settings.global_complete !== false) {
        // 날짜/시간픽커 초기화
        $(function () {
            setTimeout(function () {
                init_datetimepicker();
                //init_detail_search();
                init_file_style();
                init_checkbox_style();
                init_email_select();
                // 팝오버 초기화
                if ($('.js-popover').length > 0) {
                    $('.js-popover').popover({trigger: 'hover', container: '#content',});
                }
                $('input.js-number').one('keyup', function (e) {
                    $(this).number_only();
                });

            }, 500);
        });
    }
});

// 문서 로딩 후 실행
$(function () {
    // 페이지 이동전 Form 데이터 변경여부 확인
    /*
     var isPageChange = false;
     $("form.content-form input, form.content-form select").change(function () {
     isPageChange = true;
     });
     $(window).on("beforeunload", function () {
     if (isPageChange) {
     return "저장되지 않은 내용이 있습니다. 내용을 저장하고 화면을 이동하시겠습니까?";
     }
     });
     $(".content-form :submit").click(function () {
     isPageChange = false;
     });
     */

    // 관리자 메뉴 레이아웃 토글
    $(document).on('click', '.js-adminmenu-toggle', function (e) {
        if (!$('#content-wrap > .js-adminmenu-toggle').length) {
            $('#content-wrap').prepend($(this).clone().addClass('active'));
        } else {
            $('#content-wrap > .js-adminmenu-toggle').toggleClass('active');
        }
        $('body').toggleClass('menu-no-border');
        $(window).trigger('resize');
    });

    // 관리자 메뉴 리스트 전체 토글
    $(document).on('click', '.js-listgroup-toggle', function (e) {
        $(this).toggleClass('active');
        var menu = $('#menu .panel-heading').not('.active');

        if ($(this).hasClass('active')) {
            menu.removeClass('menu-icon-minus');
            menu.addClass('menu-icon-plus');
            menu.next('.list-group').hide();
        } else {
            menu.removeClass('menu-icon-plus');
            menu.addClass('menu-icon-minus');
            menu.next('.list-group').show();
        }
    });

    // 관리자 메뉴 헤더 토글
    $('#menu .panel-heading').click(function (e) {
        //$(this).toggleClass('active').next('.list-group').toggle();
        $(this).next('.list-group').toggle();

        if ($(this).hasClass('menu-icon-plus')) {
            $(this).removeClass('menu-icon-plus');
            $(this).addClass('menu-icon-minus');
        } else {
            $(this).removeClass('menu-icon-minus');
            $(this).addClass('menu-icon-plus');

        }
    });

    // 탭 초기화
    if ($('[data-toggle="tab"]').length > 0) {
        $('[data-toggle="tab"]').tab();
    }

    var option = {
        trigger: 'hover',
        container: '#content',
    };
    // 팝오버 초기화
    if ($('.js-popover').length > 0) {
        $('.js-popover').popover(option);
    }

    // 팝오버 초기화
    if ($('.js-html-popover').length > 0) {
        option.html = true;
        $('.js-html-popover').popover(option);
    }


    // 툴팁 초기화
    if ($('.js-tooltip').length > 0) {
        var option = {
            trigger: 'hover',
            container: '#content',
        };
        $('.js-tooltip').tooltip(option);
    }

    // 새로고침해도 폼값 유지시키기
    if ($('.js-keepform input, .js-keepform select').length > 0) {
        $('.js-keepform input, .js-keepform select').DataSaver({
            timeout: 1000,
            events: "change keyup"
        });
    }

    $(document).on('submit reset', '.js-keepform', function (e) {
        $('.js-keepform input, .js-keepform select').DataSaver('remove');
    });

    // 날짜/시간픽커 초기화
    init_datetimepicker();

    // 날짜/시간픽커 초기화 - 년,월만 노출
    init_datepicker_months();

    //상세검색 초기화
    init_detail_search();

    // 저장버튼 스크롤
    if ($('.page-header.js-affix').length > 0) {

        var $content = $('#content .col-xs-12');
        if ($content.length < 1) {
            $content = $('#content .col-xs-12');
            if ($content.length < 1) {
                $content = $('#content');
            }
        }
        $('.page-header.js-affix').width($content.width());

        $('.js-affix').affix({
            offset: {
                top: ($('#header').length > 0 ? 129 : 10),
                bottom: function () {
                    return (this.bottom = $('#footer').innerHeight(true));
                }
            }
        });
        $('.js-affix').bind('affix.bs.affix', function (e) {
            if (!$('body').hasClass('menu-no-border')) {
                $('.page-header.js-affix').css({left: '211px'});
            } else {
                $('.page-header.js-affix').css({left: '0px'});
            }
            $('.page-header.js-affix').css({width: $content.outerWidth() + 'px'});
            $('#content').css({paddingTop: '102px'});
        }).bind('affixed-top.bs.affix', function (e) {
            $('.page-header.js-affix').css({width: $content.width() + 'px', left: 'auto'});
            $('#content').removeAttr('style');
        });
        if ($(document).scrollTop() > 130) {
            $('.js-affix').trigger('affix.bs.affix');
        }
    }

    $(window).resize(function (e) {
        var $content = $('#content');
        if (!$('.page-header').hasClass('affix')) {
            $('.page-header.js-affix').css({width: 'auto'});
        } else {
            $('.page-header.js-affix').css({width: $content.width()});
        }
    });

    // 토글 show/hide 버튼
    $('.js-toggle-view').click(function () {
        var $this = $(this);
        var target = $this.data('target');
        if (target) {
            $(target).toggle();
        }
        var text = $this.data('text');
        if (text) {
            $this.data('text', $this.text());
            $this.text(text);
            if ($._data(this, 'events')['data-text-change']) {
                $this.trigger('data-text-change');
            }
        }
    });

    // 파일박스
    init_file_style();

    //체크박스
    init_checkbox_style();

    // 테이블 크로스 오버 UI
    if ($('.table-cross-hover').length) {
        $('.table-cross-hover td, .table-cross-hover th').hover(function () {
            $('.table-cross-hover td:nth-child(' + ($(this).index() + 1) + ')').addClass('hover');
        }, function () {
            $('.table-cross-hover td:nth-child(' + ($(this).index() + 1) + ')').removeClass('hover');
        });
    }

    // 복사 기능
    // https://clipboardjs.com
    if ($('.js-clipboard').length) {
        var clipboard = new Clipboard('.js-clipboard');
        clipboard.on('success', function (e){
            var title = $(e.trigger).attr('title') == undefined ? '' : $(e.trigger).attr('title');
            alert('[' + title + '] 정보를 클립보드에 복사했습니다.\n<code>Ctrl+V</code>를 이용해서 사용하세요.');
            e.clearSelection();
        });
        clipboard.on('error', function (e) {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
        });
    }

    // 이메일 선택 폼
    $(document).on('change', '.js-email-select select', function (e) {
        var $emailSelect = $(this).closest(".js-email-select");
        var $originData = '';
        if (!_.isNull($emailSelect.data('origin-data'))) {
            $originData = $emailSelect.data('origin-data');
        }
        var $targetParent = '';
        if (!_.isNull($emailSelect.data('target-parent')) && typeof $emailSelect.data('target-parent') != 'undefined') {
            $targetParent = $emailSelect.data('target-parent');
        }

        var $target = $($targetParent + ' input[name="' + $emailSelect.data('target-name') + '"]').last();
        if ($(this).val() != '' && $(this).val() != 'self') {
            $target.attr('disbled', false);
            $target.css('display', 'none');
            $target.val($(this).val());
        } else if ($(this).val() == 'self' || $(this).val() == '') {
            $target.css('display', 'inline');
            if (!_.isEmpty($target.val())) {
                $originData = $target.val();
            }
            $target.val($originData);
        }
    });
    init_email_select();

    init_translate();

    // 정렬버튼 및 버튼 자동 체크
    $('.js-sort').click(function (e) {
        $('input[name=\'sort\[name\]\']').val($(this).data('sort-name'));
        $('input[name=\'sort\[mode\]\']').val($(this).data('sort-mode'));
        $(this).closest('form').submit();
    });
    $('.js-sort').each(function () {
        if ($(this).data('sort-name') == $('input[name=\'sort\[name\]\']').val()
            && $(this).data('sort-mode') == $('input[name=\'sort\[mode\]\']').val()) {
            $(this).addClass('active');
            return false;
        }
    });

    // 자리수 표기 (.js-maxlength를 표기하지 않으면 표시되지 않음)
    if ($('input[maxlength]').length > 0) {
        $('input[maxlength]').maxlength({
            showOnReady: true,
            alwaysShow: true
        });
    }
    if ($('textarea[maxlength]').length > 0) {
        $('textarea[maxlength]').maxlength({
            placement: 'bottom-left-inside',
            showOnReady: true,
            alwaysShow: true
        });
    }


    //영어랑숫자만입력
    $("input.js-type-normal").bind('keyup', function () {
        $(this).val($(this).val().replace(/[^a-z0-9_]*/gi, ''));
    });

    // 숫자만 입력하기 원하는 경우
    if ($('input.js-number').length > 0) {
        $('input.js-number').each(function () {
            if (typeof $(this).data('number') != 'undefined') {
                $(this).number_only.apply($(this), $(this).data('number').length > 1 ? $(this).data('number').split(',') : [$(this).data('number')]);
            } else {
                $(this).number_only();
            }
        });
    }

    // 숫자만 입력하기 원하는 경우 ,(쉼표) .(콤마) -(마이너스) 입력안됨
    if ($('input.js-number-only').length > 0) {
        $('input.js-number-only').each(function () {
            if (typeof $(this).data('number') != 'undefined') {
                var params = $(this).data('number').length > 1 ? $(this).data('number').split(',') : [$(this).data('number')];
                params.push('d');
                $(this).number_only.apply($(this), params);
            } else {
                $(this).number_only("d");
            }
        });
    }

    // 전화번호만 입력하기 원하는 경우
    if ($('input.js-tel').length > 0) {
        $('input.js-tel').each(function () {
            // 일반전화 /^(0(2|3[1-3]|4[1-4]|5[1-5]|6[1-4]))-(\d{3,4})-(\d{4})$/
            // 휴대전화 /^(?:(010-\d{4})|(01[1|6|7|8|9]-\d{3,4}))-(\d{4})$/
        });
    }

    //data-regex에 정규식 넣고 체크
    $("input.js-type-valid").bind('keyup', function () {
        var regex = new RegExp($(this).data('regex'), 'gi');
        $(this).val($(this).val().replace(regex, ''));
    });

    // 레이어 경고창
    $('.js-alert').click(function (e) {
        dialog_alert($(this).data('message'), $(this).attr('title'));
    });

    // 레이어 다이얼로그
    $('.js-dialog').click(function (e) {
        BootstrapDialog.show({
            title: 'Default Title',
            message: 'Click buttons below.',
            buttons: [{
                label: 'Title 1',
                action: function (dialog) {
                    dialog.setTitle('Title 1');
                }
            }, {
                label: 'Title 2',
                action: function (dialog) {
                    dialog.setTitle('Title 2');
                }
            }]
        });
    })

    // 레이어 확인창
    $('.js-confirm').click(function (e) {
        BootstrapDialog.confirm({
            title: $(this).attr('title'),
            message: $(this).data('message'),
            callback: function (result) {
                if (result) {
                    alert('확인');
                } else {
                    alert('취소');
                }
            }
        });
    });

    // 레이어 닫기 (BootstrapDialog)
    $(document).on('click', '.js-layer-close', function (e) {
        console.log(BootstrapDialog.dialogs[BootstrapDialog.currentId]);
        var dialog = BootstrapDialog.dialogs[BootstrapDialog.currentId];
        if (dialog) {
            dialog.close();     // 지정된 다이얼로그 닫기
        } else {
            layer_close();      // 현재 열려있는 전체 레이어 닫기
        }
    });

    // 공급사 선택과 같은 파라미터가 필요한 레이어 창
    $(document).on('click', '.js-layer-register', function (e) {
        $(this).prop('disabled', true);

        // 마지막 radio 체크
        $(this).siblings('label:last').children().prop('checked', true);

        var params = {};
        params.mode = $(this).data('mode');
        params.disabled = (!_.isUndefined($(this).data('disabled')) ? $(this).data('disabled') : undefined);
        params.size = (!_.isUndefined($(this).data('size')) ? $(this).data('size') : undefined);
        params.sno = (!_.isUndefined($(this).data('sno')) ? $(this).data('sno') : undefined);
        params.layerFormID = (!_.isUndefined($(this).data('layer-id')) ? $(this).data('layer-id') : undefined);
        params.dataFormID = (!_.isUndefined($(this).data('id')) ? $(this).data('id') : undefined);
        params.parentFormID = (!_.isUndefined($(this).data('parent-id')) ? $(this).data('parent-id') : undefined);
        params.dataInputNm = (!_.isUndefined($(this).data('input-name')) ? $(this).data('input-name') : undefined);
        params.layerTitle = (!_.isUndefined($(this).data('title')) ? $(this).data('title') : undefined);
        params.callFunc = (!_.isUndefined($(this).data('call-func')) ? $(this).data('call-func') : undefined);
        params.couponSaveType = (!_.isUndefined($(this).data('coupon-save-type')) ? $(this).data('coupon-save-type') : undefined);
        params.orderGridMode = (!_.isUndefined($(this).data('order-grid-mode')) ? $(this).data('order-grid-mode') : undefined);
        params.goodsGridMode = (!_.isUndefined($(this).data('goods-grid-mode')) ? $(this).data('goods-grid-mode') : undefined);
        params.scmCommissionSet = (!_.isUndefined($(this).data('scm-commission-set')) ? $(this).data('scm-commission-set') : undefined);


        layer_add_info($(this).data('type'), params);

        // 2번 클릭하는 경우 2번 열리는 오류 수정을 위해 활성/비활성
        $(this).prop('disabled', false);
    });

    // 클래스명 js-page-number 셀렉트박스의 이벤트
    var $js_page_number = $('select.js-page-number');
    if ($js_page_number.length > 0) {
        $(document).on('change', 'select.js-page-number', function (e) {
            e.preventDefault();
            var $input = $('input[name=\'pageNum\']');
            $input.val($js_page_number.find(':selected').val());
            $('.content-form[method="get"]:eq(0)').submit();
        });
    }

    // 클래스명 js-page-sort 셀렉트박스의 이벤트
    var $js_page_sort = $('select.js-page-sort');
    if ($js_page_sort.length > 0) {
        $(document).on('change', 'select.js-page-sort', function (e) {
            e.preventDefault();
            var $input = $('input[name=\'sort\']');
            $input.val($js_page_sort.find(':selected').val());
            $('.content-form[method="get"]:eq(0)').submit();
        });
    }

    // 엘리먼트 삭제
    $(document).on('click', '[data-toggle=delete]', function (e) {
        if ($(this).closest('.selected-btn-group').length > 0) {
            if ($(this).closest('.selected-btn-group').find('div.btn-group').length == 1) {
                $(this).closest('.selected-btn-group').removeClass('active').empty();
                var nonTextId = $(this).attr('data-none');
                if(nonTextId) {
                    $(nonTextId).html($(nonTextId).html().replace('선택한 ', ''));
                }
            }
        }
        var tbodyID = $($(this).data('target')).closest('tbody').prop('id');
        $($(this).data('target')).remove();
        $.each($('#' + tbodyID).find('.number'), function (index) {
            $(this).html(index + 1);
        });
    });

    // CRM 레이어 출력 (이름/아이디 클릭시)
    var $js_layer_crm = $('.js-layer-crm');
    if ($js_layer_crm.length > 0) {
        $js_layer_crm.hover(function () {
            $(this).addClass('btn-link');
        }, function () {
            $(this).removeClass('btn-link');
        });

        $js_layer_crm.click(function (e) {
            var $target = $(e.target);
            var memNo = $target.data('member-no');
            if (_.isUndefined(memNo)) {
                memNo = $target.closest('tr').data('member-no');
            }
            member_crm(memNo);
        });
    }

    var $js_layer_search_member = $('.js-btn-layer-search-member');
    if ($js_layer_search_member.length > 0) {
        $js_layer_search_member.click(function (e) {
            e.preventDefault();
            layer_member_search();
        });
    }

    var $js_link_sms_charge = $('.js-link-sms-charge');
    if ($js_link_sms_charge.length > 0) {
        $js_link_sms_charge.click(function (e) {
            window.open('../member/sms_charge.php?popupMode=yes', 'sms_charge', 'width=1400, height=700, scrollbars=no');
        });
    }

    var $js_link_order = $('.js-link-order');
    if ($js_link_order.length > 0) {
        $js_link_order.click(function (e) {
            e.preventDefault();
            if (typeof open_order_link != 'function') {
                order_view_popup($(e.target).data('order-no'), $(e.target).data('is-provider'));
            }
        });
    }

    var $js_coupon_register = $('.js-coupon-register');
    if ($js_coupon_register.length > 0) {
        $js_coupon_register.click(function (e) {
            if ($js_coupon_register.data('type')) {
                var couponType = $js_coupon_register.data('type');
                window.open('../promotion/coupon_regist.php?couponSaveType=auto&couponEventType=' + couponType + '&callback=coupon_reload');
            } else {
                window.open('../promotion/coupon_regist.php?couponSaveType=auto&couponEventType=attend&callback=coupon_reload');
            }
        });
    }

    var $btn_layer_remote = $('.js-btn-layer-remote');
    if ($btn_layer_remote) {
        $btn_layer_remote.click(layer_remote);
    }

    // jquery Validator 전역 설정
    if (typeof $.validator != 'undefined') {
        $.validator.setDefaults({
            onfocusout: false,
            onclick: false,
            onkeyup: false,
            errorPlacement: function (error, element) {
                // do nothing
            },
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    alert(validator.errorList[0].message);
                    validator.errorList[0].element.focus();
                }
            },
            submitHandler: function (form) {
                form.submit();
            }
        });
    }


    // 프린트 하기
    $(document).on('click', '.js-print', function (e) {
        window.print();
    });


    //$('tbody td').hover(function() {
    //    var $el = $(this);
    //    if ($el.parent().has('td[rowspan]').length == 0) {
    //        $el.parent().prevAll('tr:has(td[rowspan]):first').find('td[rowspan]').addClass("hover");
    //    }
    //}, function() {
    //    var $el = $(this);
    //    $el.parent().prevAll('tr:has(td[rowspan]):first').find('td[rowspan]').removeClass("hover");
    //});


    // 엑셀다운로드
    $(document).on('click', '.js-excel-download', function (e) {
        if ($(this).data('function-auth') == 'deny') {
            dialog_alert("권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.");
            return false;
        }
        var addParam = {
            "targetListForm": $(this).data('target-list-form'),
            "targetListSno": $(this).data('target-list-sno'),
            "targetForm": $(this).data('target-form'),
            "searchCount": $(this).data('search-count'),
            "totalCount": $(this).data('total-count')
        };

        if ($(this).data('state-code')) addParam.orderStateMode = $(this).data('state-code');
        // 고객 교환/반품/환불신청 관리 탭 페이지 변수
        if ($(this).data('target-list-tabview'))addParam.currentTabView = $(this).data('target-list-tabview');

        layer_add_info('excel', addParam);
    });

    sms_auto_popup();

    mail_config_auto_popup();

    //브랜드 미지정 상품 클릭시 선택된 브랜드 초기화
    /*$("input[name='brandNoneFl']").click(function(e){
        if($(this).is(":checked") === true){
            $("#brandLayer").empty();
        }
    });*/

	 // 상품리스트 - 옵션 재고 보기 - 그리드 항목 옵션재고보기 버튼
    $('button.js-layer-grid-option').click(function (e) {
        var optionGoodsNo = $(this).attr('data-goods-option');
        var params = {
            goodsNo: optionGoodsNo
        };
        $.post('layer_goods_list_option_simple.php', params, function (data) {
            layer_popup(data, '옵션 재고 보기', 'wide-sm');
        });
    });

    // 상품리스트 - 그리드 항목 관리자메모보기 버튼
    $('button.js-layer-goods-memo').on({
        'click' :function (e) { // 메모보기 클릭 시
            var goodsNo = $(this).attr('data-goods-memo');
            var params = {
                goodsNo: goodsNo
            };
            $.post('layer_goods_list_memo.php', params, function (data) {
                layer_popup(data, '관리자 메모');
            });
        }
    });

    // 상품리스트 - 추가진열항목 삭제 - 그리드 항목 추가진열
    $('.js-delete-grid-display').click(function() {
        var goodsNo = $(this).attr('data-add-list-goodsno');
        var representCateCd = $(this).attr('data-add-list-catecd'); // 대표카테고리
        var deleteCateCode = $('.goods_grid_' + goodsNo + ' > input[name="cateCode[]"]:checked').map(function() {
            return this.value;
        }).get().join(',');
        var deleteMainCode  = $('.goods_grid_' + goodsNo + ' > input[name="mainCode[]"]:checked').map(function() {
            return this.value;
        }).get().join(',');
        var deleteBestCode  = $('.goods_grid_' + goodsNo + ' > input[name="bestCode[]"]:checked').map(function() {
            return this.value;
        }).get().join(',');

        if(!deleteCateCode && !deleteMainCode && !deleteBestCode) {
            alert('선택된 삭제 항목이 없습니다.');
            return;
        }
        var parameters = {
            mode :'goods_list_add_grid_del',
            goodsNo : goodsNo,
            representCateCd : representCateCd,
            cateCode : deleteCateCode,
            mainCode : deleteMainCode,
            bestCode : deleteBestCode
        };
        dialog_confirm('진열 연결 삭제를 진행 하시겠습니까?', function (result) {
            if(result) {
                $.ajax({
                    method: "POST",
                    data: parameters,
                    url: "../goods/goods_ps.php",
                    success: function (data) {
                        if (data == true) {
                            dialog_alert('삭제 되었습니다.', '알림', {isReload: true});
                            setTimeout(function(){
                                location.reload(true);
                            }, 2000)
                        }
                    },
                    error: function (data) {
                    }
                });
            }
        });
    });
});

/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
/** DEPRECATED **/
function parseScript(_source) {
    var source = _source;
    var scripts = new Array();

    // Strip out tags
    while (source.toLowerCase().indexOf("<script") > -1 || source.toLowerCase().indexOf("</script") > -1) {
        var s = source.toLowerCase().indexOf("<script");
        var s_e = source.indexOf(">", s);
        var e = source.toLowerCase().indexOf("</script", s);
        var e_e = source.indexOf(">", e);

        // Add to scripts array
        scripts.push(source.substring(s_e + 1, e));
        // Strip from source
        source = source.substring(0, s) + source.substring(e_e + 1);
    }

    // Loop through every script collected and eval it
    for (var i = 0; i < scripts.length; i++) {
        try {
            if (scripts[i] != '') {
                try {          //IE
                    execScript(scripts[i]);
                }
                catch (ex)           //Firefox
                {
                    window.eval(scripts[i]);
                }

            }
        }
        catch (e) {
            // do what you want here when a script fails
            // window.alert('Script failed to run - '+scripts[i]);
            if (e instanceof SyntaxError) console.log(e.message + ' - ' + scripts[i]);
        }
    }
    // Return the cleaned source
    return source;
}
function getQueryParams(qs) {
    qs = qs.split('+').join(' ');

    var params = {},
        tokens,
        re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
    }

    return params;
}
/**
 * 해당 ID 제거/삭제
 * 대신 data-toggle="delete" data-target="엘리먼트ID"를 사용하세요
 *
 * @deprecated
 * @author artherot
 * @param string thisID 해당 ID
 */
function field_remove(thisID) {
    var tbodyID = $('#' + thisID).closest('tbody').prop('id');
    $('#' + thisID).remove();
    $.each($('#' + tbodyID).find('.number'), function (index) {
        $(this).html(index + 1);
    });
}

/**
 * 리스트 정렬
 *
 * @author artherot
 * @param string fieldName 정렬 대상
 * @param string sortMode 정렬 방법
 * @param string formID form ID
 */
function list_sort(fieldName, sortMode, formID) {
    $('input[name=\'sort\[name\]\']').val(fieldName);
    $('input[name=\'sort\[mode\]\']').val(sortMode);
    $('#' + formID).submit();
}


/**
 * 일반 팝업창
 *
 * @author artherot
 * @param string orderNo 주문 번호
 */
function show_popup(url) {

    win = popup({
        url: url,
        target: '',
        width: 860,
        height: 600,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
};

function go_to_by_scroll(object) {
    $('html, body').animate({
        scrollTop: $(object).offset().top - 100
    }, 'fast');
}

/**
 * 고도몰 이동 링크
 *
 * @param string type 타입
 */
function gotoGodomall(type) {
    var url = "";
    switch (type) {
        case 'domain' :
            url = 'https://www.godo.co.kr/mygodo/myGodo_Domain_change.php';
            $('form[name=\'frmGotoGodomall\'] input[name=\'mode\']').val('domain');
            break;

        case 'disk' :
            url = 'https://www.godo.co.kr/mygodo/plusDisk_rental.php';
            break;

        case 'transfer' :
            url = 'https://www.godo.co.kr/mygodo_transfer/trans_step2.php';
            break;

        case 'extend' :
            url = 'https://www.godo.co.kr/mygodo/myGodo_shopExten.php';
            break;

        case 'main' :
            url = 'https://www.godo.co.kr/mygodo/myGodo_shopMain.php';
            break;
    }
    $('form[name=\'frmGotoGodomall\']').attr('method', 'post');
    $('form[name=\'frmGotoGodomall\']').attr('target', '_blank');
    $('form[name=\'frmGotoGodomall\']').attr('action', url);
    $('form[name=\'frmGotoGodomall\']').submit();
}

function initDepthToggle(scmNo) {

    /* 4뎁스 토클메뉴 */
    $('.depth-toggle-button').click(function () {

        var toggleName = $(this).attr('depth-name');

        if ($('#depth-toggle-hidden-' + toggleName).val() == 1) {
            $('#depth-toggle-hidden-' + toggleName).val(0);
            $('#depth-toggle-layer-' + toggleName).removeClass('display-none');
            $('#depth-toggle-line-' + toggleName).addClass('display-none');
            $(this).find('span').text('닫힘');
            $(this).removeClass('opened');

        } else {
            $('#depth-toggle-hidden-' + toggleName).val(1);
            $('#depth-toggle-layer-' + toggleName).addClass('display-none');
            $('#depth-toggle-line-' + toggleName).removeClass('display-none');
            $(this).find('span').text('펼침');
            $(this).addClass('opened');
        }

        // !중요! 숨겨진 엘리먼트를 보여지게 할 경우 maxlength 표시 부분의 위치가 어긋난다. 이에 아래 트리거를 사용해 위치를 재 설정한다.
        $('.js-maxlength').trigger('maxlength.reposition');

        $.get('../share/toggle_ps.php', {
            mode: 'set',
            scmNo: scmNo,
            toggleName: toggleName,
            hidden: $('#depth-toggle-hidden-' + toggleName).val()
        });

        /* IE에서 에디터가 display:none 상태일때 로드가 안되는 현상 때문에 evnet 이후 editorLoad 되게 처리 */
        if (toggleName == 'detailInfo' && !$('#depth-toggle-layer-' + toggleName).hasClass('display-none')) {
            infoToggleEditor($(":radio[name='detailInfoDeliveryFl']:checked").val(), 'detailInfoDelivery');
            infoToggleEditor($(":radio[name='detailInfoASFl']:checked").val(), 'detailInfoAS');
            infoToggleEditor($(":radio[name='detailInfoRefundFl']:checked").val(), 'detailInfoRefund');
            infoToggleEditor($(":radio[name='detailInfoExchangeFl']:checked").val(), 'detailInfoExchange');
        }

    });

    $.each($('.depth-toggle-button'), function (index, value) {
        var toggleName = $(this).attr('depth-name');
        if ($('#depth-toggle-hidden-' + toggleName).val() == 1 && toggleName != 'goodsDetail' && toggleName.indexOf('Gnb') < 0) { //에디터존재색션 display
            $('#depth-toggle-layer-' + toggleName).addClass('display-none');
            $('#depth-toggle-line-' + toggleName).removeClass('display-none');
            $(this).find('span').text('펼침');
            $(this).addClass('opened');
        } else {
            $('#depth-toggle-layer-' + toggleName).removeClass('display-none');
            $('#depth-toggle-line-' + toggleName).addClass('display-none');
            $(this).find('span').text('닫힘');
            $(this).removeClass('opened');
        }
    });

}
function toggleSelectionDisplay(selection) {
    $.each($('.depth-toggle-button'), function (index, value) {
        var toggleName = $(this).attr('depth-name');
        if (toggleName == selection) {
            if ($('#depth-toggle-hidden-' + toggleName).val() == 1) {
                $('#depth-toggle-layer-' + toggleName).addClass('display-none');
                $('#depth-toggle-line-' + toggleName).removeClass('display-none');
                $(this).find('span').text('펼침');
                $(this).addClass('opened');
            } else {
                $('#depth-toggle-layer-' + toggleName).removeClass('display-none');
                $('#depth-toggle-line-' + toggleName).addClass('display-none');
                $(this).find('span').text('닫힘');
                $(this).removeClass('opened');
            }
            return;
        }
    });
}

/* 검색설정값 저장*/
function set_search_config(form) {
    dialog_confirm('현재 설정된 검색조건을 저장하시겠습니까? 저장 후 기본 검색설정으로 사용됩니다.<br> (직접 입력한 검색어와 조회기간은 저장되지 않습니다.)<br><br>검색결과의 <strong>"정렬조건"</strong>과 <strong>"페이지당 출력수 설정"</strong>(20개 보기 권장)도 같이 저장되며,<br><span style="color:red;">출력수가 많을 경우 리스트 접근 시, 부하문제로 페이지로딩이 오래 걸릴 수 있습니다.</span>', function (result) {
        if (!result) {
            return false;
        }
        else {
            form.action = '../share/search_config_ps.php';
            form.target = 'ifrmProcess';
            form.submit();
        }
    });
}

function goList(href) {
    self.location.href = href;
}

function dateGetTime(date){

    var stringDt = date;
    var arrStrDt = stringDt.split(' ');

    var tempDt1 = arrStrDt[0].split('-');
    var tempDt2 = arrStrDt[1].split(':');

    var tempdt = new Date(tempDt1[0],tempDt1[1],tempDt1[2],tempDt2[0],tempDt2[1]);
    return tempdt.getTime();
}

/**
 * 처리중 레이어를 닫는 함수
 */
function close_validate_process_dialog() {
    var dialogs = top.BootstrapDialog.dialogs;
    $('.bootstrap-dialog-message:contains("처리중")').each(function (idx, item) {
        var modal_id = $(item).closest('div.modal').attr('id');
        if (typeof dialogs[modal_id] !== 'undefined') {
            dialogs[modal_id].close();
        }
    });
}

var gdAjaxUpload = {
    isSuccess: false,
    upload: function (data) {
        var formData = new FormData();
        for (var k in data.params) {
            if (data.params.hasOwnProperty(k)) {
                formData.append(k, data.params[k]);
            }
        }
        if (data.onbeforeunload) {
            window.onbeforeunload = data.onbeforeunload;
            data.formObj.on("submit", function () {
                window.onbeforeunload = null;
            });
        }

        if (data.formObj.find('[name=uploadType][value=ajax]').length < 1) {
            data.formObj.append('<input type="hidden"  name="uploadType" value="ajax" >');
        }
        var index = data.thisObj.closest('form').find('input:file').index(data.thisObj);
        formData.append('uploadFile', data.thisObj[0].files[0]);

        $.ajax({
            url: data.actionUrl,
            type: 'POST',
            data: formData,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (returnData) {
                returnData['index'] = index;
                if (returnData.result == 'ok') {
                    gdAjaxUpload.isSuccess = true;
                    if ($('input[name="uploadFileNm[' + index + ']"]').length == 0) {
                        data.formObj.append("<input type='hidden' name='uploadFileNm[" + index + "]' value='" + returnData.uploadFileNm + "'>");
                        data.formObj.append("<input type='hidden' name='saveFileNm[" + index + "]' value='" + returnData.saveFileNm + "'>");
                    }
                    else {
                        $("input[name='uploadFileNm[" + index + "]']").val(returnData.uploadFileNm);
                        $("input[name='saveFileNm[" + index + "]']").val(returnData.saveFileNm);
                    }
                    if (typeof data.successAfter == 'function') {
                        data.successAfter(returnData);
                    }
                }
                else if (returnData.result == 'cancel') {
                    if ($("input[name='uploadFileNm[" + index + "]']").length > 0) {
                        $("input[name='uploadFileNm[" + index + "]']").remove();
                        $("input[name='saveFileNm[" + index + "]']").remove();
                    }
                }
                else {
                    gdAjaxUpload.isSuccess = false;
                    data.thisObj.closest('li').find('[type=text]').val('');
                    alert(returnData.errorMsg);
                    if (typeof data.failAfter == 'function') {
                        data.failAfter(returnData);
                    }
                }
            }
        });
    }
}

/**
 * 주문상세페이지에서 주문상태 변경에 사용되는 팝업창
 *
 * @author artherot
 * @param string actionType 액션타입
 * @param string orderNo 주문 번호
 * @param string isProvider
 *
 */
function order_view_status_popup(actionType, orderNo, isProvider)
{
    var impossibleMessage = '';
    switch(actionType){
        //취소복원 클릭시
        case 'restore' :
            dialog_confirm('주문 전체 상태가 처음 취소 이전으로 복원됩니다.<br/><br/>진행 하시겠습니까?', function (result) {
                if (result) {
                    var orderNo = $('#frmOrder > input[name="orderNo"]').val();
                    $.ajax({
                        method: "POST",
                        data: {
                            'mode': 'set_order_cancel_restore',
                            'orderNo': orderNo,
                        },
                        cache: false,
                        async: false,
                        url: "../order/order_change_ps.php",
                        success: function (data) {
                            if (data == 'ok') {
                                dialog_alert('취소 복원되었습니다.', '확인', {isReload: true});
                            } else {
                                dialog_alert(data, '경고', {isReload: true});
                            }
                        },
                    });
                } else {
                    return false;
                }
            });
            return false;
            break;

        // 상품추가 클릭시
        case 'add' :
            $('#inc_order_view tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-status-mode");
                if(statusMode != 'o' && statusMode != 'c'){
                    impossibleMessage = "상품추가는 입금 전에만 가능합니다.";
                    return false;
                }
                var settleKind = $(this).data("settle-kind");
                if (settleKind === 'pv' || settleKind === 'ev' || settleKind === 'fv') {
                    impossibleMessage = "가상계좌 결제 방법은 상품추가가 불가능 합니다.";
                    return false;
                }
            });
            break;

        // 취소 클릭시
        case 'cancel' :
            var ableToCancel = false;
            $('#inc_order_view tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-status-mode");
                var handleMode = $(this).attr("data-handle-mode");

                if(statusMode === 'o'){
                    ableToCancel = true;
                }
            });

            if(ableToCancel === false){
                impossibleMessage = "입금대기 주문상품이 존재하지 않습니다.";
            }
            break;


        //교환철회 클릭시
        case 'exchangeCancel' :
            /*
            var statusCheckBox = $('input[name*=\'bundle[statusCheck]\']:checkbox:checked');
            var statusCheckCnt = statusCheckBox.length;
            if (statusCheckCnt > 0) {
                $(statusCheckBox).each(function () {
                    var statusMode = $(this).closest('tr').attr("data-status-mode");
                });
            }

            $('tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-status-mode");
                if(statusMode !== 'e' && statusMode !== 'o' && statusMode !== 'p' && statusMode !== 'g'){
                    impossibleMessage = "교환신규 상품이 출고전 상태일때만 교환철회가 가능합니다.";
                    return false;
                }
            });
             */
            break;

        //반품 클릭시
        case 'back' :
            var backCheck = true;
            $('#inc_order_view tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-status-mode");
                if(statusMode === 'd' || statusMode === 's'){
                    backCheck = false;
                }
            });
            if (backCheck) {
                impossibleMessage = "배송중/배송완료/구매확정 상태일때만 반품이 가능합니다.";
                return false;
            }
            break;

        //환불 클릭시
        case 'refund' :
            var refundCheck = true;
            $('#inc_order_view tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-status-mode");
                if(statusMode === 'p' || statusMode === 'g'){
                    refundCheck = false;
                }
            });
            if (refundCheck) {
                impossibleMessage = "결제완료/상품준비중 상태일때만 환불이 가능합니다.";
                return false;
            }
            break;

        //반품철회 클릭시
        case 'backCancel' :
            $('tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-original-status-mode");
                if(statusMode !== 'b1' && statusMode !== 'b2' && statusMode !== 'b3'){
                    impossibleMessage = "반품완료 전 상태일때만 반품철회가 가능합니다.";
                    return false;
                }
            });
            break;

        //환불철회 클릭시
        case 'refundCancel' :
            $('tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-original-status-mode");
                if(statusMode !== 'r1' && statusMode !== 'r2'){
                    impossibleMessage = "환불완료 전 상태일때만 환불철회가 가능합니다.";
                    return false;
                }
            });
            break;

        //환불완료 클릭시
        case 'refundComplete' :
            $('tr[id*=\'statusCheck_\']').each(function () {
                var statusMode = $(this).attr("data-original-status-mode");
                if(statusMode !== 'r1' && statusMode !== 'r2'){
                    impossibleMessage = "환불완료 전 상태일때만 환불완료가 가능합니다.";
                    return false;
                }
            });
            break;
    }

    if(impossibleMessage){
        alert(impossibleMessage);
        return;
    }

    if (isProvider) {
        var url = '/provider/order/popup_order_view_status.php?orderNo=' + orderNo + '&actionType=' + actionType;
    }
    else {
        var url = '/order/popup_order_view_status.php?orderNo=' + orderNo + '&actionType=' + actionType;
    }

    win = popup({
        url: url,
        target: 'popup_order_view_status',
        width: 1024,
        height: 800,
        scrollbars: 'yes',
        resizable: 'yes'
    });
    win.focus();
    return win;
}

/**
 * PG 관련 영수증 보기 show_receipt.php는 전역에서 사용할 수 있는 PG 관련 영수증 정보 화일입니다.(관리자용)
 *
 * @author artherot
 * @param string modeStr 카드, 현금영수증 종류 (card, cash)
 * @param string orderNo 주문 번호
 */
function pg_receipt_view_admin(modeStr, orderNo, sno)
{
    // 사이즈를 미리 설정 - 자동으로 창이 커지지 않아 미리 설정함
    var preWidth = 430;
    var preHeight = 700;

    // 미리 팝업창을 띄우기
    var prePopupData = {
        'url': 'about:blank',
        'target': 'show_receipt',
        'width': preWidth,
        'height': preHeight
    };
    var show_receipt = popup(prePopupData);

    // 각 PG별 영수증 팝업창
    $.post('/share/show_receipt.php', {
        mode: modeStr,
        orderNo: orderNo,
        sno: sno
    }, function(data)
    {
        var infoData = data;
        if (typeof infoData['error'] == 'undefined') {
            popup(infoData);
        }
        else {
            alert(infoData['error']);
            show_receipt.close();
        }
    }, 'json');
}

jQuery.fn.center = function() {
    var top = ($(window).height() - this.outerHeight()) / 2;
    var left = ($(window).width() - this.outerWidth()) / 2;

    this.css({
        position:'absolute',
        margin:0,
        top: (top > 0 ? top : 0) + 'px',
        left: (left > 0 ? left : 0) + $(window).scrollLeft() + 'px'
    });

    return this;
};

//@formatter:on
(function (global, factory) {
    var _godo = global.godo || {};
    typeof exports === "object" && typeof module !== "undefined" ? module.exports = factory() :
        typeof define === "function" && define.amd ? define(factory) :
            _godo.layer = factory();
    global.godo = _godo;
}(this, function () {
    "use strict";

    var login_security = {
        panel: function (options) {
            if ($(".login-security").length < 1) {
                var panel = $("<div class=\"login-security\">" + options.message + "</div>");
                panel.find(".layer_administrator_add").css({top: -50, left: 500});
                panel.find(".bot_gray_close, .btn-close").addClass("display-none");
                options.target.append(panel);
            }
        }, dialog: function (options) {
            var dialog_options = {
                message: $(options.message),
                closable: false,
                closeByBackdrop: false,
                closeByKeyboard: false,
                onhidden: function () {
                    login_security.panel(options);
                }
            };

            var login_dialog = new BootstrapDialog(dialog_options);
            login_dialog.realize();
            login_dialog.getModalBody().css({"padding": 0});
            login_dialog.getModalContent().on("click", ".check-week-close", function () {
                adminPanelCookie("login_security", 7, this);
                login_dialog.close();
            }).on("click", ".check-close, .btn-close", function () {
                login_dialog.close();
            });
            login_dialog.getModalContent().css({"padding": 0, "border": 0});
            login_dialog.getModalHeader().hide();
            login_dialog.getModalFooter().hide();
            login_dialog.open();
        }, show: function (options) {
            if ($.cookie("adminPanel_login_security")) {
                this.panel(options);
            } else {
                this.dialog(options);
            }
        }
    };

    var security_agreement = {
        show: function (options) {
            var dialog_options = {message: $(options.message)};
            if (options.super === 'y') {
                dialog_options.closable = false;
                dialog_options.closeByBackdrop = false;
                dialog_options.closeByKeyboard = false;
            }
            var dialog = new BootstrapDialog(dialog_options);
            dialog.realize();

            dialog.is_agreement = function () {
                return dialog.getModalContent().find("#administratorAdd").prop("checked");
            };

            dialog.show_require_dialog = function () {
                var require_dialog = new BootstrapDialog({
                    message: "관리자 운영 보안 필수 설정 내용 확인에 대한 동의가 필요합니다.",
                    buttons: [{
                        label: "확인",
                        cssClass: "btn-black",
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (dialog) {
                            dialog.close();
                        }
                    }]
                });
                require_dialog.realize();
                require_dialog.getModalDialog().css("margin-left", dialog.getModalDialog().css("margin-left"));
                require_dialog.open();
            };

            dialog.save_agreement = function (guide, callback) {
                $.ajax("../policy/manage_security_ps.php", {
                    method: "post",
                    data: {
                        mode: "securityAgreement",
                        guide: guide,
                        securityAgreementFl: dialog.getModalContent().find("#administratorAdd").val()
                    }
                }).done(function () {
                    var response = arguments[0];
                    if (response.error === 0) {
                        if (typeof callback === "function") {
                            callback();
                        } else {
                            BootstrapDialog.closeAll();
                        }
                    } else {
                        alert(response.message);
                    }
                });
            };

            dialog.show_guide_dialog = function () {
                var message = "<div>관리자 보안 로그인과 관리자 IP 접속제한이 모두 사용안함으로 설정된 경우<br/>관리자 운영 보안 설정 관련 안내가 모든 본사 운영자에게 표시됩니다.<p>허용함으로 저장하시겠습니까?</p>";
                var end_dialog = new BootstrapDialog({
                    message: message,
                    buttons: [{
                        label: "확인",
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (ref_dialog) {
                            ref_dialog.close();
                            dialog.save_agreement('y');
                        }
                    }, {
                        label: "관리자 운영 보안 설정 바로가기",
                        cssClass: "btn-red",
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (ref_dialog) {
                            ref_dialog.close();
                            dialog.save_agreement('y', function () {
                                window.location.href = "../policy/manage_security.php";
                            });
                        }
                    }]
                });
                end_dialog.realize();
                end_dialog.getModalDialog().css("margin-left", dialog.getModalDialog().css("margin-left"));
                end_dialog.open();
            };

            dialog.show_guide_end_dialog = function () {
                var message = "<div>관리자 운영 보안설정 관련 안내가 더 이상 표시되지 않습니다.<br/>허용안함으로 저장하시겠습니까?<br/>";
                message += "<span class='mgt5 text-blue'>* 관리자 운영 보안 설정은 필요에 따라,<br/>[기본설정 > 관리 정책 > 운영 보안 설정]에서 사용함 설정이 가능합니다.</span></div>";
                var end_dialog = new BootstrapDialog({
                    message: message,
                    buttons: [{
                        label: "취소",
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (dialog) {
                            dialog.close();
                        }
                    }, {
                        label: "확인",
                        cssClass: "btn-black",
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (ref_dialog) {
                            ref_dialog.close();
                            dialog.save_agreement('n');
                        }
                    }]
                });
                end_dialog.realize();
                end_dialog.getModalDialog().css("margin-left", dialog.getModalDialog().css("margin-left"));
                end_dialog.open();
            };

            dialog.getModalBody().css({"padding": 0});
            dialog.getModalContent().css({"padding": 0, "border": 0});
            dialog.getModal().on("click", function (e) {
                if (dialog.getModalContent().find(".super-manager-guide").length > 0 && (dialog.getModalContent().find(e.target).length < 1 || $(e.target).hasClass("btn-close"))) {
                    var close_dialog = new BootstrapDialog({
                        message: "관리자 운영 보안 설정 안내에 대한 허용여부를 설정해야 합니다.",
                        buttons: [{
                            label: "확인",
                            cssClass: "btn-black",
                            size: BootstrapDialog.SIZE_LARGE,
                            action: function (dialog) {
                                dialog.close();
                            }
                        }]
                    });
                    close_dialog.realize();
                    close_dialog.getModalDialog().css("margin-left", dialog.getModalDialog().css("margin-left"));
                    close_dialog.open();
                } else if ($(e.target).hasClass("btn-close")) {
                    dialog.close();
                }
            });

            dialog.getModalContent().on("click", ".btn-guide-end", function () {
                if (dialog.is_agreement()) {
                    dialog.show_guide_end_dialog();
                } else {
                    dialog.show_require_dialog();
                }
            });

            dialog.getModalContent().on("click", ".btn-guide", function () {
                if (dialog.is_agreement()) {
                    dialog.show_guide_dialog();
                } else {
                    dialog.show_require_dialog();
                }
            });
            dialog.getModalHeader().hide();
            dialog.getModalFooter().hide();
            dialog.open();
        }
    };

    var move_manage_security = {
        show: function () {
            BootstrapDialog.closeAll();
            var message = "<div>저장이 완료되었습니다.</div>";
            message += "<div class='mgt10'>정보통신망 이용촉진 및 정보보호 등에 관한 법률에 따라,";
            message += "<br/>쇼핑몰 내 개인정보 보호를 위한 관리자 운영 보안을 설정하시기 바랍니다.";
            message += "<br/><a href='../policy/manage_security.php'><span class='mgt5 text-blue'>관리자 운영 보안 설정 바로가기 ></span></a></div>";
            BootstrapDialog.show({
                message: message,
                buttons: [{
                    label: "확인",
                    cssClass: "btn-black",
                    size: BootstrapDialog.SIZE_LARGE,
                    action: function (dialog) {
                        dialog.close();
                        window.location.reload(true);
                    }
                }]
            });
        }
    };

    var confirm_manage_security = {
        show: function () {
            BootstrapDialog.closeAll();
            var message = "<div>저장이 완료되었습니다.</div>";
            message += "<div class=\"mgt10 notice-info\">정보통신망 이용촉진 및 정보보호 등에 관한 법률에 따라,";
            message += "<br/>쇼핑몰 내 개인정보 보호를 위한 관리자 운영 보안을 설정하시기 바랍니다.</div>";
            BootstrapDialog.show({
                message: message,
                buttons: [{
                    label: "확인",
                    cssClass: "btn-black",
                    size: BootstrapDialog.SIZE_LARGE,
                    action: function (dialog) {
                        dialog.close();
                        window.location.reload(true);
                    }
                }]
            });
        }
    };

    return {
        "open": {},
        "login_security": login_security,
        "security_agreement": security_agreement,
        "move_manage_security": move_manage_security,
        "confirm_manage_security": confirm_manage_security,
        "auth_login": function () {
            if (typeof top.BootstrapDialog === "undefined") {
                alert("페이지 오류입니다.");
                return false;
            }

            top.BootstrapDialog.closeAll();

            (function (open) {
                $.get("layer_sms_auth.php", function (data) {
                    console.log(arguments);
                    open.auth_login = BootstrapDialog.show({
                        title: "로그인 보안 인증",
                        message: $(data),
                        onhide: function () {
                            clearInterval(window["timer_Mm_timer"]);
                            var $form = $("form[name=frmLogin]");
                            if (typeof $form !== "undefined") {
                                $form[0].reset();
                            }
                        }
                    });
                }).fail(function () {
                    alert("로그인 인증화면을 불러올 수 없습니다.");
                });
            }(this.open));
        },
        "point_charge": function () {
            if (typeof top.BootstrapDialog === "undefined") {
                alert("페이지 오류입니다.");
                return false;
            }

            top.BootstrapDialog.closeAll();

            (function (open) {
                $.get("../member/sms_charge.php", function (data) {
                    open.point_charege = BootstrapDialog.show({
                        size: BootstrapDialog.SIZE_WIDE,
                        closeByBackdrop: false,
                        message: $("<div id='content'></div>").append(data),
                        show: function (dialog) {
                            var btn = dialog.$modalHeader.find("button");
                            btn.addClass("btn mgt5");
                            btn.attr("type", "button");
                            btn.on("click", function () {
                                top.BootstrapDialog.closeAll();
                            });
                            dialog.$modalContent.find(".page-header .btn-group").append(btn);
                            dialog.getModalHeader().hide();
                        }
                    });
                }).fail(function () {
                    alert("SMS 포인트 충전화면을 불러올 수 없습니다.");
                });
            }(this.open));
        }
    }
}));
