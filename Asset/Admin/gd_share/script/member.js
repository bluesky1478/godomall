/**
 * 관리자 회원
 **/
var member = {};

(function (root) {
    /**
     * 검색회원 전체적용일 경우 리스트의 체크박스 비활성 처리
     */
    root.toggleEventApplyQuery = function () {
        var $applyQuery = $('.js-apply-query');
        if ($applyQuery.length > 0) {
            if ($applyQuery.prop('tagName') == 'INPUT') {
                $('form').on('click', '.js-apply-query', function (e) {
                    var $tableRows = $('table.table-rows');
                    var $target = $(e.target);
                    if ($target.val() === 'query') {
                        $tableRows.find(':checkbox').prop('disabled', true);
                    } else {
                        $tableRows.find(':checkbox').prop('disabled', false);
                    }
                });
            } else if ($applyQuery.prop('tagName') == 'OPTION') {
                var $target = $applyQuery.closest('select');
                $target.on('change', function (e) {
                    var $tableRows = $('table.table-rows');
                    if ($target.val() === 'query') {
                        $tableRows.find(':checkbox').prop('disabled', true);
                    } else {
                        $tableRows.find(':checkbox').prop('disabled', false);
                    }
                });
            }
            $applyQuery.find(':checked').trigger('click');
        }
    };

    root.get_member_attribute = function (e, attr) {
        var $checkbox = $(e.target).closest('tr').find(':checkbox');
        var res;
        if (attr) {
            res = $checkbox.attr(attr);
        } else {
            res = $checkbox.val();
        }
        return res;
    };

    root.batch_member = (function (data) {
        ajax_with_layer('../member/member_batch_ps.php', data, function (result) {
            alert(result.message);
            if (result.result) {
                location.reload();
            }
        });
    });

    root.alert_check = (function (frm, msg) {
        if ($(':checkbox:checked', frm).length === 0) {
            member.dialog({message: msg === undefined ? '선택된 항목이 없습니다.' : msg});
            return false;
        }
        return true;
    });

    root.page_number = (function (e) {
        $('input[name=\'pageNum\']').val($(this).val());
        $(e.data.targetForm).submit();
    });

    root.page_sort = (function (e) {
        $('input[name=\'sort\']').val($(this).val());
        $(e.data.targetForm).submit();
    });

    // 현재 선택된 라디오 버튼의 data-target 외의 data-target display none 처리
    root.target_display_switch = (function (e) {
        var $target = $(e.target);
        var $switchTarget = $(':radio[name=' + $target.attr('name') + ']');
        $.each($switchTarget, function (idx, item) {
            var $item = $(item);
            if ($item.prop('checked')) {
                $($item.attr('data-target')).css('display', '');
            } else {
                $($item.attr('data-target')).css('display', 'none');
            }
        });
    });

    // 토글형 체크박스 값 변환
    root.checkbox_yn = (function (e) {
        var $target = $(e.target);
        if ($target.prop('checked')) {
            $target.val('y');
        } else {
            $target.val('n');
        }
    });

    // 라디오 버튼 클릭에 따른 대상 비활성화
    root.target_disable_toggle = (function (e) {
        var $target = $(e.target);
        var $toggleTarget = $($target.attr('data-target'));
        if ($target.val() === e.data.value) {
            $toggleTarget.prop('disabled', false);
        } else {
            $toggleTarget.prop('disabled', true);
        }
    });

    // 라디오 버튼 클릭에 따른 대상 미표시
    root.target_display_toggle = function (e) {
        var $target = $(e.target);
        var $toggleTarget = $($target.attr('data-target'));

        if ($target.val() === e.data.value) {
            $toggleTarget.css('display', '');
        } else {
            $toggleTarget.css('display', 'none');
        }
    };

    root.init = function () {
        $('div#content select, :text').each(function (idx, item) {
            var $item = $(item);
            if ($item.hasClass('form-control') === false) {
                $item.addClass('form-control');
            }
        });
        $('a[name=btnPopupRealName]').click(function (e) {
            e.preventDefault();
            window.popup();
        });
        $('a[name=btnPopupIpin]').click(function (e) {
            e.preventDefault();
        });
    };
    root.dialog = function (options) {
        layer_close();
        var settings = $.extend({}, {timeout: 2000, isReload: false, isLocation: false, reloadUrl: ''}, options);
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
    };
    /**
     * 관리자 컨펌 창
     * @param title 제목
     * @param message 문구
     * @param callback
     * @param options
     */
    root.confirm = (function (title, message, callback, options) {
        layer_close();
        var settings = $.extend({}, {title: title, message: message, callback: callback}, options);
        top.BootstrapDialog.confirm(settings);
    });
})(member);

/**
 * Created by yjwee on 2015-10-30.
 */
$(document).ready(function () {
    member.init();
});
