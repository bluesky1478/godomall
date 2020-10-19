/**
 * Schedule
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
(function ($) {
    var self;
    /**
     * Schedule.init() 클래스의 Property 정의
     */
    Schedule = {
        ele: null // Element
        , now: new Date() // 날짜
        , staticNow: new Date() // 고정(오늘)날짜
        , week: new Array('일', '월', '화', '수', '목', '금', '토')
        , imgPath: '' // 이미지경로

        /**
         * Schedule.init() 클래스
         * @classDescription Coverflow 출력하는 클래스
         * @param {Object} ele Element
         * @param {Array} opts 옵션
         * @return {Object} new instance 리턴
         * @constructor
         */
        , init: function (ele, opts) {
            self = this;
            opts = opts || {};
            $.extend(this, opts);
            this.ele = ele;
            this.imgPath = get_script_dirpath('script/schedule.js') + 'img/';
            this.start();
        }

        /**
         * 시작
         */
        , start: function () {
            // 년월영역 정의
            if ($('.wc_select_month', this.ele).length == 0) {
                $('<div/>').addClass('wc_select_month').prependTo(this.ele);
            }
            if ($('.wc_select_month .day', this.ele).length == 0) {
                $('<div/>').addClass('day').prependTo($('.wc_select_month', this.ele));
            }
            // 월전후이동 정의
            //if ($('.wc_select_month .wcsm_prev', this.ele).length == 0) {
            //	allowTagL = '<img src="../img/icon_arrow_calendar_l.gif" >';
            //	allowTagR = '../img/icon_arrow_calendar_r.gif';
            //		$('<a/>').addClass('wcsm_prev').prependTo($('.month',this.ele)).html('<span>'+allowTagL+'</span>').click(function() {
            //			self.calender('prevMonth');
            //		});
            //	//}
            ////	if ($('.wc_select_month .wcsm_next', this.ele).length == 0) {
            //		$('<a/>').addClass('wcsm_next').appendTo($('.month',this.ele)).html('<span>></span>').click(function() {
            //			self.calender('nextMonth');
            //		});
            //	//}
            // 알람설정 정의
            /*	if ($('.wc_set_alarm', this.ele).length == 0) {
             $('<div/>').addClass('wc_set_alarm').appendTo(this.ele);
             $('<a/>').appendTo($('.wc_set_alarm',this.ele)).html('<span>알람설정</span>').click(function() {
             frame_popup('../base/layer_schedule_setalarm.php', '알람설정');
             });
             }*/
            // 캘린더영역 정의
            if ($('.wc_days', this.ele).length == 0) {
                $('<div/>').addClass('wc_days').appendTo(this.ele);
            }
            // 스케줄영역 정의
            if ($('.wc_contents', this.ele).length == 0) {
                $('<div/>').addClass('wc_contents').appendTo(this.ele);
            }
            if ($('.wc_contents .wcc_date', this.ele).length == 0) {
                $('<div/>').addClass('wcc_date').appendTo($('.wc_contents', this.ele));
            }
            if ($('.wc_contents .wcc_subject', this.ele).length == 0) {
                $('<div/>').addClass('wcc_subject').appendTo($('.wc_contents', this.ele));
            }
            if ($('.wc_contents .wcc_contents', this.ele).length == 0) {
                $('<div/>').addClass('wcc_contents').appendTo($('.wc_contents', this.ele));
            }

            $('.wc_days').after('<div class="clear-both"></div>');
            // 캘린더 출력
            this.calender();
            // 오늘날짜 스케줄 출력
            //	this.pnt_contents(this.now.getDate());
            // 알람 팝업창	TODO:변경
//			this.alarm_popup();
        }

        /**
         * 캘린더 출력
         * @param string moveCd 이동코드
         */
        , calender: function (moveCd) {
            //--- 이동코드(월계산)
            switch (moveCd) {
                case 'prevMonth':
                    this.now.setMonth(this.now.getMonth() - 1);
                    break;
                case 'nextMonth':
                    this.now.setMonth(this.now.getMonth() + 1);
                    break;
                case 'prevYear':
                    this.now.setMonth(this.now.getMonth() - 12);
                    break;
                case 'nextYear':
                    this.now.setMonth(this.now.getMonth() + 12);
                    break;
            }

            //--- 년월출력
            var year = this.now.getFullYear().toString();
            var month = (this.now.getMonth() + 1).toString();
            //	monthStr += (tmp.length == 1 ? '0' : '') + tmp;
            //	alert(tmp);
            //	var monthImg = '';
            //for (i = 0; i < monthStr.length; i++){
            //	monthImg += '<img src="' + this.imgPath + 'main/diary_' + monthStr.charAt(i) + '.gif" alt=""/>';//이미지 삭제함
            //}

            var leftHtml = "<div class='year'>" +
                "<img src=" + this.imgPath + "icon_arrow_calendar_year_l.gif class='yearLeftArrow'>" + year +
                "<img src=" + this.imgPath + "icon_arrow_calendar_year_r.gif class='yearRightArrow'></div>" +
                "<div class='month'>" + month + "</div>";


            $('.wc_select_month .day', this.ele).html(leftHtml);

            //년전후 이동 추가
            $('.yearLeftArrow').click(function () {
                self.calender('prevYear');
            });

            $('.yearRightArrow').click(function () {
                self.calender('nextYear');
            });


            // 월전후이동 정의
            //if ($('.wc_select_month .wcsm_prev', this.ele).length == 0) {
            var allowTagL = '<img src="' + this.imgPath + 'icon_arrow_calendar_l.gif" >';
            var allowTagR = '<img src="' + this.imgPath + 'icon_arrow_calendar_r.gif" >';
            $('<a/>').addClass('wcsm_prev').prependTo($('.month', this.ele)).html('<span>' + allowTagL + '</span>').click(function () {
                self.calender('prevMonth');
            });
            //}
            //	if ($('.wc_select_month .wcsm_next', this.ele).length == 0) {
            $('<a/>').addClass('wcsm_next').appendTo($('.month', this.ele)).html('<span>' + allowTagR + '</span>').click(function () {
                self.calender('nextMonth');
            });
            //}

            //--- 달력
            var lastDate = new Date(this.now.getFullYear(), this.now.getMonth() + 1, 1 - 1); // 마지막 일자
            var firstDate = new Date(this.now.getFullYear(), this.now.getMonth(), 1); // 처음일자 요일
            var calender_area = '<table border="0" cellspacing="0" cellpadding="0">';
            calender_area += '<tr>';

            // 요일출력
            for (i = 0; i < this.week.length; i++) {
                calender_area += '<td style="color:#666">' + this.week[i] + '</td>';
            }
            calender_area += '</tr><tr>';

            // 시작빈칸출력
            for (i = 1; i <= firstDate.getDay(); i++) {
                calender_area += '<td><div></div></td>';
            }

            // 일자출력
            var z = (i - 1);
            for (i = 1; i <= lastDate.getDate(); i++) {
                z++;
                p = z % 7;
                // 오늘표시
                var clsArr = new Array();
                if (i == this.now.getDate() && this.now.getFullYear() == this.staticNow.getFullYear() && this.now.getMonth() == this.staticNow.getMonth()) {
                    clsArr.push('yel');
                }
                // 일요일표시
                if (p == 1) {
                    clsArr.push('redday');
                }
                // 출력
                var clsTxt = (clsArr.length ? ' class="' + clsArr.join(' ', clsArr) + '"' : '');
                calender_area += '<td' + clsTxt + ' id="wc_day_' + i + '"><div><a href="javascript:void(0)">' + i + '</a></div></td>';
                if (p == 0 && lastDate.getDate() != i) {
                    calender_area += '</tr><tr>';
                }
            }

            // 끝빈칸출력
            if (p != 0) {
                for (i = p; i < 7; i++) {
                    calender_area += '<td><div></div></td>';
                }
            }

            // 달력출력
            calender_area += '</tr></table>';
            $('.wc_days', this.ele).html(calender_area);

            //--- 스케줄있는 월별 일자 표시
            $.ajax({
                url: 'schedule_ps.php'
                , data: 'mode=getExistDay&year=' + this.now.getFullYear() + '&month=' + (this.now.getMonth() + 1)
                , dataType: 'json'
                , success: function (res) {
                    $.each(res, function (idx, day) {
                        day = parseInt(day);
                        //$('<sup class="sch"></sup>').appendTo($('#wc_day_'+day+' div',$('.wc_days', self.ele)));
                        $('#wc_day_' + day + ' div').addClass('active');
                    });
                }
            });

            //--- 이벤트정의
            $('td[id^=wc_day_]').click(function () {
                var day = $('a', this).text().trim();
                self.viewDateList(day);
            });
        }

        /**
         * 스케줄정보 출력
         * @param string day 일자
         */
        , pnt_contents: function (day, year, month) {
            if (typeof year == 'undefined') {
                var year = this.now.getFullYear();
            }
            if (typeof month == 'undefined') {
                var month = (this.now.getMonth() + 1).toString();
                month = (month.length == 1 ? '0' : '') + month;
            }
            day = (day.length == 1 ? '0' : '') + day;
            var scdDt = year + '-' + month + '-' + day;
            // 제목&내용 출력
            $.ajax({
                method: 'get',
                url: '../base/layer_schedule_register.php',
                data: {scdDt: scdDt}
                , dataType: 'text'
                , success: function (data) {
                    BootstrapDialog.show({
                        title: '일정관리',
                        message: $(data)
                    });
                }
            });
        }
        , addToday: function (date) {
            // 요청 날짜의 유무
            if (typeof date == 'undefined') {
                var today = new Date();
                var day = today.getDate();
                var month = today.getMonth() + 1; //January is 0!
                var year = today.getFullYear();
            } else {
                var requestDate = new Date(date);
                var day = requestDate.getDate();
                var month = requestDate.getMonth() + 1;
                var year = requestDate.getFullYear();
            }
            this.pnt_contents(day, year, month);
        }

        /**
         * 해당 날짜의 스케줄리스트 출력
         * @param string day 일자
         */
        , viewDateList: function (day, year, month) {
            if (typeof year == 'undefined') {
                var year = this.now.getFullYear();
            }
            if (typeof month == 'undefined') {
                var month = (this.now.getMonth() + 1).toString();
                month = (month.length == 1 ? '0' : '') + month;
            }
            day = (day.length == 1 ? '0' : '') + day;
            var scdDt = year + '-' + month + '-' + day;

            $.ajax({
                method: 'get',
                url: '../base/layer_schedule_list.php',
                data: {scdDt: scdDt},
                dataType: 'text',
                success: function (data) {
                    $("div.memo").html(data);
                }
            });
        }

        /**
         * 스케줄작성 후처리
         * @param string day 일자
         */
        , post_write: function (mode, res, day) {
            var year = this.now.getFullYear();
            var month = (this.now.getMonth() + 1).toString();
            var msg = '';
            if (mode == 'register') {
                msg = year + '-' + month + '-' + day + ' 일정이 등록되었습니다.';
            } else {
                msg = year + '-' + month + '-' + day + ' 일정이 수정되었습니다.';
            }
            if (res == 'fail:oldday') {
                msg += '\nSMS알람서비스는 기본설정이 오늘날짜보다 적어 발송되지 않습니다.';
            }
            alert(msg);
            day = parseInt(day);
            $('<sup class="sch"></sup>').appendTo($('#wc_day_' + day + ' div', $('.wc_days', this.ele)));
            //$('#wc_day_'+day+' div').addClass('active');
            this.pnt_contents(day);
        }
    };

    /**
     * Schedule.init() 클래스의 Property 설정
     */
    Schedule.init.prototype = Schedule;

    /**
     * Schedule.init() 클래스 인스턴스 생성
     * @param {Array} options 옵션
     * @return {Object} Schedule.init() 클래스 인스턴스
     */
    jQuery.fn.schedule = function (options) {
        options = $.extend({
            useCaption: true
            , useSlider: true
            , beginNo: 0
            , distance: 65
            , interval: 60
        }, options);
        return new Schedule.init(this, options);
    };

})(jQuery);
