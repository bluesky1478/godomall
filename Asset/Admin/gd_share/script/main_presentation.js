/**
 * Created by yjwee on 2016-09-07.
 */
var gd_main_presentation = (function ($, tui) {
    "use strict";
    var main_tabs = {
        sales: {name: "sales", tab: "매출"},
        order: {name: "order", tab: "주문", table_header: ["날짜", "판매금액", "구매건수", "구매개수"]},
        visit: {name: "visit", tab: "방문자", table_header: ["날짜", "방문자수", "페이지뷰"]},
        member: {name: "member", tab: "회원"}
    };
    var sub_tabs = {
        sales_total: {name: "salesTotal", table_header: ["날짜", "매출금액", "판매금액", "환불금액"]},
        sales_goods: {name: "salesGoods", table_header: ["순위", "상품명", "판매금액", "구매수량", "구매건수"]},
        member_total: {name: "memberTotal", table_header: ["날짜", "전체", "신규", "탈퇴", "휴면"]},
        mileage: {name: "mileage", table_header: ["날짜", "잔여마일리지", "지급건수", "지급금액", "사용건수", "사용금액"]},
        deposit: {name: "deposit", table_header: ["날짜", "잔여예치금", "지급건수", "지급금액", "사용건수", "사용금액"]},
    };
    var _default = {
        theme: {series: {colors: ["#ff0000", "#f4bf75", "#009900"]}},
        options: {
            chart: {width: 320}, yAxis: {min: 0}, series: {hasDot: false}, tooltip: {suffix: ""}, legend: {align: "bottom"}, theme: "memberTheme"
        }
    };

    /**
     * 차트 그리는 함수
     */
    function line_chart(presentation_chart, chart_data) {
        var chart_container = presentation_chart;
        chart_container.html('');
        if (chart_container.width() > _default.options.chart) {
            _default.options.chart.width = chart_container.width();
        }
        _default.options.width = _default.options.width - 50;
        _default.options.chart.height = chart_container.height();
        try {
            //console.log(_default);
            tui.chart.registerTheme('memberTheme', _default.theme);
            tui.chart.lineChart(chart_container[0], chart_data, _default.options);
        } catch (e) {
            //console.log('데이터가 없습니다.', e.message, e);
        }
    }

    /**
     * 차트 테이블의 바디 설정
     * @param key 현재 선택된 탭
     * @param chart_table_body_container 차트 테이블 바디
     * @param chart_data 차트 데이터
     * @param chart_table_data 차트 테이블 데이터
     */
    function chart_table_body(key, chart_table_body_container, chart_data, chart_table_data) {
        var tables = [];
        var series = chart_data.series;
        var categories = chart_data.categories;
        var column_css_class = 'font-num ta-r';
        $.each(categories, function (idx, dateKey) {
            var row_css_class = '';
            var span_css_class = 'js-mark';
            if (idx === (categories.length - 1)) {
                tables.push('<tr class="highlight today-background' + row_css_class + '">');
                tables.push('<td class="bold"><span class="font-date">' + dateKey + '</span></td>');
            } else {
                tables.push('<tr class="' + row_css_class + '">');
                tables.push('<td class=""><span class="font-date">' + dateKey + '</span></td>');
            }
            tables.push('<td class="bold ' + column_css_class + '"><span class="' + span_css_class + '">' + numberWithCommas(series[0].data[idx]) + '</span></td>');
            tables.push('<td class=" ' + column_css_class + '"><span class="' + span_css_class + '">' + numberWithCommas(series[1].data[idx]) + '</span></td>');
            if (typeof series[2] !== 'undefined') {
                tables.push('<td class=" ' + column_css_class + '"><span class="' + span_css_class + '">' + numberWithCommas(series[2].data[idx]) + '</span></td>');
            }
            if (typeof series[3] !== 'undefined') {
                tables.push('<td class=" ' + column_css_class + '"><span class="' + span_css_class + '">' + numberWithCommas(series[3].data[idx]) + '</span></td>');
            }
            tables.push('</tr>');
        });

        switch (key) {
            case sub_tabs.sales_total.name:
                tables.push('<tr class="active">');
                tables.push('<td>7일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.total) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.sales) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.refund) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td>15일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.total) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.sales) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.refund) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td>30일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.total) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.sales) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.refund) + '</td>');
                tables.push('</tr>');
                break;
            case main_tabs.order.name:
                tables.push('<tr class="active">');
                tables.push('<td>7일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.goodsPrice) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.orderCnt) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.goodsCnt) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td>15일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.goodsPrice) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.orderCnt) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.goodsCnt) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td>30일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.goodsPrice) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.orderCnt) + '</td><td class="' + column_css_class + '">' + chart_table_data.last_month.goodsCnt + '</td>');
                tables.push('</tr>');
                break;
            case main_tabs.visit.name:
                tables.push('<tr class="active">');
                tables.push('<td>7일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.visitCount) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.pv) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td>15일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.visitCount) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.pv) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td>30일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.visitCount) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.pv) + '</td>');
                tables.push('</tr>');
                break;
            case sub_tabs.member_total.name:
                tables.push('<tr class="active">');
                tables.push('<td colspan="2">7일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.newTotal) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.hackOut) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_week.sleep) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td colspan="2">15일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.newTotal) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.hackOut) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_15th.sleep) + '</td>');
                tables.push('</tr>');
                tables.push('<tr class="active">');
                tables.push('<td colspan="2">30일합계</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.newTotal) + '</td>');
                tables.push('<td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.hackOut) + '</td><td class="' + column_css_class + '">' + numberWithCommas(chart_table_data.last_month.sleep) + '</td>');
                tables.push('</tr>');
                break;
        }
        chart_table_body_container.html(tables.join(''));
    }

    /**
     * 차트 테이블의 증감 표시
     * @param chart_table_body_container
     */
    function mark_table_value(chart_table_body_container) {
        var tr = chart_table_body_container.find('tr:not(.active)');
        tr.each(function (idx, item) {
            var before_row = tr[idx - 1];
            if (typeof before_row === "undefined") {
                return true;
            }
            var current = $(item).find('.js-mark');
            var before = $(before_row).find('.js-mark');
            $.each(current, function (idx2, item2) {
                var before_item2 = before[idx2];
                var parseItem2 = parseInt(item2.innerHTML.replace(/,/g, ""));
                var parseBeforeItem2 = parseInt(before_item2.innerHTML.replace(/,/g, ""));
                if (parseItem2 < parseBeforeItem2) {
                    item2.classList.add('val-down');
                } else if (parseItem2 > parseBeforeItem2) {
                    item2.classList.add('val-up');
                }
            });
        });
    }

    /**
     * 통계 데이터 요청 키 값 반환
     * @param main_tab 선택된 메인 탭
     * @param sub_tab 선택된 서브 탭. 매출 외에는 false
     * @returns {*}
     */
    function get_key_by_tab(main_tab, sub_tab) {
        return sub_tab === false ? main_tab : sub_tab;
    }

    return function () {
        var main_tab, sub_tab, tab_data, table_header_text, section_index, search_period, sub_tab_table_data;
        var chart_data = {categories: {}, series: {}};
        var chart_table_data = {last_week: {}, last_month: {}};
        var presentation_section = $('#presentationSection');
        var presentation_main_tab = $('#presentationMainTab');
        var presentation_chart, type_selected, sub_tab_container, main_tab_container, fail_layer, fail_layer_backdrop, more_link;
        var chart_table_header_container, chart_table_body_container, sub_tab_table_header_container, sub_tab_table_body_container, sub_tab_display_period;

        /**
         * 데이터가 표시될 엘리먼트를 선택하여 변수에 설정
         * @param index 현재 선택된 탭 순번
         */
        function set_elements(index) {
            chart_table_header_container = presentation_section.find('.main-tab-pane:eq(' + index + ') thead:eq(0)');
            chart_table_body_container = presentation_section.find('.main-tab-pane:eq(' + index + ') tbody:eq(0)');
            presentation_chart = presentation_section.find('.graph:eq(' + index + ')');
            type_selected = presentation_section.find('.js-device:eq(' + index + ')');
            main_tab_container = presentation_section.find('.main-tab-pane:eq(' + index + ')');
            more_link = main_tab_container.find('a.btn-link');
            fail_layer = $('.js-layer-presentation:not(.clone)').clone();
            fail_layer.addClass('clone');
            fail_layer_backdrop = $('<div class="presentation-backdrop"></div>');
            if (index === 0 || index === 3) {
                sub_tab_container = presentation_section.find('ul.gd5-tabnav a[role="tab"]');
            }
        }

        /**
         * 데이터 조회 실패 레이어 팝업
         * @param tab_pane
         * @param re_ajax
         */
        function layer_fail(tab_pane, re_ajax) {
            //console.log('layer_fail', tab_pane);
            tab_pane.css('position', 'relative');
            append_fail_layer_backdrop(tab_pane);
            append_fail_layer(tab_pane, 150, 250, re_ajax);
        }

        /**
         * 매출 조회 서브 탭 실패 레이어 팝업
         * @param tab_pane
         * @param re_ajax
         */
        function layer_fail_by_sub_tab(tab_pane, re_ajax) {
            //console.log('layer_fail_by_sub_tab', tab_pane);
            tab_pane.css('position', 'relative');
            append_fail_layer_backdrop(tab_pane);
            append_fail_layer(tab_pane, 0, 0, re_ajax);
        }

        function append_fail_layer_backdrop(tab_pane) {
            fail_layer_backdrop.css({
                position: "absolute",
                top: 0,
                left: 0,
                width: tab_pane.width(),
                height: tab_pane.height(),
                "z-index": 8888,
                "background-color": "#000000",
                opacity: 0.5
            });
            fail_layer_backdrop.appendTo(tab_pane);
        }

        function append_fail_layer(tab_pane, top, left, re_ajax) {
            // console.log(fail_layer);
            fail_layer.appendTo(fail_layer_backdrop);
            if (top === 0 || left === 0) {
                top = ( tab_pane.innerHeight() / 2) - (fail_layer.height() / 2);
                left = (tab_pane.innerWidth() / 2) - (fail_layer.width() / 2);
            }
            fail_layer.css({
                position: "absolute",
                top: top,
                left: left,
                "z-index": 9999
                //"background-color": "#ffffff"
            });
            fail_layer.removeClass('display-none');
            fail_layer.one('click', 'button', function () {
                $.ajax(re_ajax);
            });
        }

        /**
         * 데이터 조회 실패 레이어 팝업 닫는 함수
         * @param tab_pane
         */
        function close_layer_fail(tab_pane) {
            var layer = tab_pane.find('.js-layer-presentation');
            var backdrop = tab_pane.find('.presentation-backdrop');
            if (layer.length > 0 || backdrop.length > 0) {
                // console.log('close_layer_fail', tab_pane);
                layer.remove();
                backdrop.remove();
            }
        }

        return {
            /**
             * 현재 선택된 탭의 엘리먼트 설정
             */
            init_section_elements: function () {
                var key = get_key_by_tab(main_tab, sub_tab);
                section_index = 0;
                switch (key) {
                    case sub_tabs.sales_total.name:
                        table_header_text = sub_tabs.sales_total.table_header;
                        section_index = 0;
                        break;
                    case main_tabs.order.name:
                        section_index = 1;
                        table_header_text = main_tabs.order.table_header;
                        break;
                    case main_tabs.visit.name:
                        section_index = 2;
                        table_header_text = main_tabs.visit.table_header;
                        break;
                    case sub_tabs.member_total.name:
                        section_index = 3;
                        table_header_text = sub_tabs.member_total.table_header;
                        break;
                    case sub_tabs.deposit.name:
                        section_index = 3;
                        table_header_text = sub_tabs.deposit.table_header;
                        break;
                    case sub_tabs.mileage.name:
                        section_index = 3;
                        table_header_text = sub_tabs.mileage.table_header;
                        break;
                }
                set_elements(section_index);
            },
            /**
             * 선택된 탭의 엘리먼트에 이벤트 바인드
             */
            bind_event_elements: function () {
                var obj = this;
                type_selected.change(function (e) {
                    if (sub_tab === false || sub_tab === sub_tabs.sales_total.name) {
                        obj.load_chart();
                    } else {
                        obj.load_sub_table();
                    }
                });
                if (sub_tab_container) {
                    // console.log(sub_tab_container);
                    sub_tab_container.click(function (e) {
                        var target = $(e.target);
                        more_link.attr('href', '../statistics/' + target.data('link') + '.php');
                        sub_tab = target.data('mode');
                        sub_tab_display_period = presentation_section.find('.tab-pane:eq(0) .js-display-search-period');
                        if (sub_tab === sub_tabs.sales_total.name) {
                            sub_tab_display_period.addClass('display-none');
                            obj.load_chart();
                        } else {
                            sub_tab_display_period.removeClass('display-none');
                            obj.load_sub_table();
                        }
                        var container = $(target.attr('href'));
                        sub_tab_table_header_container = container.find('thead');
                        sub_tab_table_body_container = container.find('tbody');
                    });
                }
            },
            /**
             * 차트 데이터 조회
             */
            load_chart: function () {
                var mode = get_key_by_tab(main_tab, sub_tab);
                var params = {
                    mode: mode,
                    search: type_selected.val(),
                };
                // 차트 데이터 요청
                $.ajax('/share/presentation_ps.php', {
                    cache: false,
                    method: "get",
                    data: params,
                    is_success: false,
                    success: function () {
                        var response = arguments[0];
                        if (response.success == 'OK') {
                            this.is_success = true;
                            chart_data.categories = response.result.categories;
                            chart_data.series = response.result.series;
                            chart_table_data.last_week = response.result.lastWeek;
                            chart_table_data.last_15th = response.result.last15th;
                            chart_table_data.last_month = response.result.lastMonth;
                            line_chart(presentation_chart, chart_data);
                            chart_table_body(get_key_by_tab(main_tab, sub_tab), chart_table_body_container, chart_data, chart_table_data);
                            mark_table_value(chart_table_body_container);
                            // comma_table_value(chart_table_body_container);
                        }
                    },
                    complete: function () {
                        var tab_pane = $(presentation_section).find('.main-tab-pane:eq(' + section_index + ')');
                        //console.log(arguments, section_index, tab_pane);
                        if (this.is_success === false) {
                            layer_fail(tab_pane, this);
                        } else {
                            close_layer_fail(tab_pane);
                        }
                    }
                });
            },
            /**
             * 매출의 서브탭 데이터 조회
             */
            load_sub_table: function () {
                var mode = get_key_by_tab(main_tab, sub_tab);
                var params = {
                    mode: mode,
                    search: type_selected.val(),
                };
                // 차트 데이터 요청
                $.ajax('/share/presentation_ps.php', {
                    cache: false,
                    method: "get",
                    data: params,
                    is_success: false,
                    success: function () {
                        var response = arguments[0];
                        if (response.success == 'OK') {
                            this.is_success = true;
                            switch (sub_tab) {
                                case sub_tabs.sales_total.name:
                                    chart_data.categories = response.result.categories;
                                    chart_data.series = response.result.series;
                                    chart_table_data.last_week = response.result.lastWeek;
                                    chart_table_data.last_15th = response.result.last15th;
                                    chart_table_data.last_month = response.result.lastMonth;
                                    line_chart(presentation_chart, chart_data);
                                    chart_table_body(get_key_by_tab(main_tab, sub_tab), chart_table_body_container, chart_data, chart_table_data);
                                    mark_table_value(chart_table_body_container);
                                    break;
                                case sub_tabs.sales_goods.name:
                                    search_period = response.searchPeriod;
                                    sub_tab_table_data = response.result;
                                    sub_tab_display_period.html(search_period);
                                    var tables = [];
                                    var j = 1;
                                    $.each(sub_tab_table_data, function (idx, item) {
                                        tables.push('<tr>');
                                        tables.push('<td>' + j + '</td>');
                                        tables.push('<td>' + item.goodsNm + '</td>');
                                        tables.push('<td>' + numberWithCommas(item.goodsPrice) + '</td>');
                                        tables.push('<td>' + numberWithCommas(item.goodsCnt) + '</td>');
                                        tables.push('<td>' + numberWithCommas(item.orderCnt) + '</td>');
                                        tables.push('</tr>');
                                        j++;
                                    });
                                    sub_tab_table_body_container.html(tables.join(''));
                                    break;
                                case sub_tabs.member_total.name:
                                    chart_data.categories = response.result.categories;
                                    chart_data.series = response.result.series;
                                    chart_table_data.last_week = response.result.lastWeek;
                                    chart_table_data.last_15th = response.result.last15th;
                                    chart_table_data.last_month = response.result.lastMonth;
                                    line_chart(presentation_chart, chart_data);
                                    chart_table_body(get_key_by_tab(main_tab, sub_tab), chart_table_body_container, chart_data, chart_table_data);
                                    mark_table_value(chart_table_body_container);
                                    break;
                                case sub_tabs.mileage.name:
                                    sub_tab_table_data = response.result;
                                    var tables = [];
                                    $.each(sub_tab_table_data, function (idx, item) {
                                        tables.push('<tr>');
                                        tables.push('<td>' + idx + '</td>');
                                        tables.push('<td>' + item.total + '</td>');
                                        tables.push('<td>' + item.saveCount + '</td>');
                                        tables.push('<td>' + item.saveMileage + '</td>');
                                        tables.push('<td>' + item.useCount + '</td>');
                                        tables.push('<td>' + item.useMileage + '</td>');
                                        tables.push('</tr>');
                                    });
                                    tables.push('<tr>');
                                    tables.push('<td class="active" colspan="2">7일 합계</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.saveCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.saveMileage) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.useCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.useMileage) + '</td>');
                                    tables.push('</tr>');
                                    tables.push('<tr>');
                                    tables.push('<td class="active" colspan="2">15일 합계</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.saveCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.saveMileage) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.useCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.useMileage) + '</td>');
                                    tables.push('</tr>');
                                    tables.push('<tr>');
                                    tables.push('<td class="active" colspan="2">30일 합계</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.saveCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.saveMileage) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.useCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.useMileage) + '</td>');
                                    tables.push('</tr>');
                                    sub_tab_table_body_container.html(tables.join(''));
                                    break;
                                case sub_tabs.deposit.name:
                                    sub_tab_table_data = response.result;
                                    var tables = [];
                                    $.each(sub_tab_table_data, function (idx, item) {
                                        tables.push('<tr>');
                                        tables.push('<td>' + idx + '</td>');
                                        tables.push('<td>' + item.total + '</td>');
                                        tables.push('<td>' + item.saveCount + '</td>');
                                        tables.push('<td>' + item.saveDeposit + '</td>');
                                        tables.push('<td>' + item.useCount + '</td>');
                                        tables.push('<td>' + item.useDeposit + '</td>');
                                        tables.push('</tr>');
                                    });
                                    tables.push('<tr>');
                                    tables.push('<td class="active" colspan="2">7일 합계</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.saveCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.saveDeposit) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.useCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_week.useDeposit) + '</td>');
                                    tables.push('</tr>');
                                    tables.push('<tr>');
                                    tables.push('<td class="active" colspan="2">15일 합계</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.saveCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.saveDeposit) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.useCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_15th.useDeposit) + '</td>');
                                    tables.push('</tr>');
                                    tables.push('<tr>');
                                    tables.push('<td class="active" colspan="2">30일 합계</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.saveCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.saveDeposit) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.useCount) + '</td>');
                                    tables.push('<td>' + numberWithCommas(response.result_month.useDeposit) + '</td>');
                                    tables.push('</tr>');
                                    sub_tab_table_body_container.html(tables.join(''));
                                    break;
                                default:
                                    break;
                            }
                        }
                    },
                    complete: function () {
                        var tab_pane = $(presentation_section).find('.main-tab-pane:eq(' + section_index + ')');
                        // console.log(arguments, section_index, tab_pane);
                        if (this.is_success === false) {
                            layer_fail_by_sub_tab(tab_pane, this);
                        } else {
                            close_layer_fail(tab_pane);
                        }
                    }
                });
            },

            /**
             * 매출의 서브탭 데이터 조회
             */
            load_data: function () {
                var mode = 'dataReload';
                var params = {
                    mode: mode,
                };
                $.ajax('/share/presentation_ps.php', {
                    cache: false,
                    method: "get",
                    data: params,
                    is_success: false,
                    success: function () {
                        var response = arguments[0];
                        if (response.success == 'OK') {
                            if (response.result === false) {
                                // 통계 새로고침 실패
                            } else {
                                this.is_success = true;
                                $.each(presentation_main_tab.find('li'), function (idx, dateKey) {
                                    if ((response.result.sales) && ($(this).data('name') == 'sales')) {
                                        $(this).find('.count').text(response.result.sales);
                                    }
                                    if ((response.result.order) && ($(this).data('name') == 'order')) {
                                        $(this).find('.count').text(response.result.order);
                                    }
                                    if ((response.result.visit) && ($(this).data('name') == 'visit')) {
                                        $(this).find('.count').text(response.result.visit);
                                    }
                                    if ((response.result.member) && ($(this).data('name') == 'member')) {
                                        $(this).find('.count').text(response.result.member);
                                    }
                                });
                            }
                        }
                    },
                    complete: function () {
                        var tab_pane = $(presentation_section).find('.main-tab-pane:eq(' + section_index + ')');
                        if (this.is_success === false) {
                            layer_fail(tab_pane, this);
                        } else {
                            close_layer_fail(tab_pane);
                        }
                    }
                });
                return true;
            },
            set_main_tab: function (tab) {
                main_tab = tab;
            },
            set_sub_tab: function (tab) {
                sub_tab = tab;
            },
            main_tabs: main_tabs,
            sub_tabs: sub_tabs,
            get_tab_pane: function () {
                var tab_pane = $(presentation_section).find('.main-tab-pane:eq(' + section_index + ')');
                return tab_pane;
            },
            get_main_tab: function () {
                return main_tab;
            },
            get_sub_tab: function () {
                return sub_tab;
            }
        };
    };
})($, tui);

var presentations = [];
var reload_time = 0;
var reloadTimeCount;
$(document).ready(function () {
    var main_tab = $('#presentationMainTab > li.active').data('name');
    var sub_tab = false;
    if (main_tab == 'sales') {
        sub_tab = 'salesTotal';
    } else if (main_tab == 'member') {
        sub_tab = 'memberTotal';
    }
    var presentation = new gd_main_presentation();
    presentation.set_main_tab(main_tab);
    presentation.set_sub_tab(sub_tab);
    presentation.init_section_elements();
    presentation.bind_event_elements();
    presentation.load_chart();
    presentations[main_tab] = presentation;

    $('#presentationMainTab > li').click(function (e) {
        var periodIconSrc = $('#presentationMainTab > li.active img').attr('src');
        var urlSrc = periodIconSrc.split('/');
        urlSrc.pop();
        var periodIconSrc = urlSrc.join('/');
        var periodIconSrcOff = periodIconSrc + '/ico_' + $('#presentationMainTab').data('period') + 'days_off.png';
        var periodIconSrcOn = periodIconSrc + '/ico_' + $('#presentationMainTab').data('period') + 'days_on.png';
        $('.js-period-icon').attr('src', periodIconSrcOff);
        $(this).find('img').attr('src', periodIconSrcOn);
        var main_tab = $('#presentationMainTab > li.active').data('name');
        var new_main_tab = $(this).data('name');
        var new_sub_tab = false;
        if (new_main_tab == 'member') {
            new_sub_tab = 'memberTotal';
        } else if (new_main_tab == 'sales') {
            new_sub_tab = 'salesTotal';
        }
        if (new_main_tab != main_tab) {
            presentation.set_main_tab(new_main_tab);
            presentation.set_sub_tab(new_sub_tab);
            presentation.init_section_elements();
            presentation.bind_event_elements();
            presentation.load_chart();
            presentations[main_tab] = presentation;
        }
    });

    function set_reload_time() {
        if (reload_time > 60) {
            clearInterval(reloadTimeCount);
            reload_time = 0;
        } else {
            reload_time++;
        }
    }

    var gd_main_layer = {
        presentation: {id: "presentation", selector: "#layerPresentation", title: "주요현황 조회설정"},
    };
    var _bootstrap_dialog = BootstrapDialog;
    $('.js-setting-presentation').click(function () {
        $.post('../share/layer_presentation_setting.php', {mode: gd_main_layer.presentation.id}, function (data) {
            var options = {title: gd_main_layer.presentation.title, message: $(data), size: _bootstrap_dialog.SIZE_WIDE};
            _bootstrap_dialog.show(options);
        });
    });

    $('.js-real-update').click(function () {
        if (reload_time === 0) {
            reloadTimeCount = setInterval(set_reload_time, 1000);
            if(presentation.load_data()) {
                main_tab = presentation.get_main_tab();
                sub_tab = presentation.get_sub_tab();
                presentation.set_main_tab(main_tab);
                presentation.set_sub_tab(sub_tab);
                presentation.init_section_elements();
                presentation.bind_event_elements();
                if (sub_tab === false) {
                    presentation.load_chart();
                } else {
                    presentation.load_sub_table();
                }
                presentations[main_tab] = presentation;
            }
        } else if (reload_time <= 60) {
            var fail_layer = $('.js-layer-reload');
            if (fail_layer.hasClass('display-none')) {
                var fail_layer_backdrop = $('<div class="presentation2-backdrop"></div>');
                var tab_pane = $('.statics-box');
                tab_pane.css('position', 'relative');
                fail_layer_backdrop.css({
                    position: "absolute",
                    top: 0,
                    left: 0,
                    width: tab_pane.width(),
                    height: 473,
                    "z-index": 8888,
                    "background-color": "#000000",
                    opacity: 0.5
                });
                fail_layer_backdrop.appendTo(tab_pane);
                fail_layer.appendTo(fail_layer_backdrop);

                var top = 170;
                var left = 200;
                if (top === 0 || left === 0) {
                    top = ( tab_pane.innerHeight() / 2) - (fail_layer.height() / 2);
                    left = (tab_pane.innerWidth() / 2) - (fail_layer.width() / 2);
                }
                fail_layer.css({
                    position: "absolute",
                    top: top,
                    left: left,
                    "z-index": 9999
                    //"background-color": "#ffffff"
                });
                fail_layer.removeClass('display-none');
                fail_layer.one('click', 'button', function () {
                    fail_layer.addClass('display-none');
                    fail_layer_backdrop.addClass('display-none');
                });
            }
        }
    });
});

/**
 * 콤마 - 소수점 유지
 * @param x
 */
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}
