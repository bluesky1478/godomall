/**
 * 데이터중복확인클래스
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
jQuery.fn.dataOverlapChk = function (opts) {
    var Overlap = function (ele, opts) {
        var self = this;
        opts = opts || {};
        $.extend(this, opts);
        this.ele = ele;
        // 초기화
        this.init();
        // Value Change Event
        this.ele.each(function () {
            $(this).change(function () {
                self.prepare();
            });
        });
        // Button Click Event
        $(this.btnEle).click(function () {
            self.checking();
        });
    };
    Overlap.prototype.init = function () {
        this.ele.each(function () {
            $(this).data('default', $(this).val()); // 초기값 보유
        });
        this.prepare();
    };
    Overlap.prototype.prepare = function () {
        var isChange = false;
        var valText = '';
        this.ele.each(function () {
            if ($(this).val() != $(this).data('default')) {
                isChange = true;
            }
            valText += $(this).val();
        });
        if (valText == '' || isChange === false) {
            $(this.chkEle).val('y');
            this.unchanged();
        } else {
            $(this.chkEle).val('');
            this.changed();
        }
    };
    Overlap.prototype.checking = function () {
        var self = this;
        var data = new Array();
        this.ele.each(function () {
            data.push($(this).attr('name') + '=' + $(this).val());
        });
        if (typeof(this.addData) == 'object') {
            $.each(this.addData, function (id, vals) {
                vals.each(function () {
                    data.push($(this).attr('name') + '=' + $(this).val());
                });
            });
        } else if (typeof(this.addData) == 'string') {
            data.push(this.addData);
        } else if (typeof(this.addData) == 'function') {
            data.push(this.addData());
        }
        $.post(this.url, data.join('&'), function (data) {
            console.log(data);
            if (data.result == false) {
                self.init();
                self.success(data);
            } else {
                self.fail(data);
            }
        });
    };
    Overlap.prototype.changed = function () {
    };
    Overlap.prototype.unchanged = function () {
    };
    Overlap.prototype.success = function (data) {
    };
    Overlap.prototype.fail = function (data) {
    };

    return new Overlap(this, opts);
};
