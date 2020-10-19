<form id="frmMainSetting" name="frmBank" action="" method="post">
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-xs"/>
            <col/>
        </colgroup>
        <tr>
            <th>메뉴선택</th>
            <td>
                <div class="form-inline">
                    <div class="row">
                        <div class="col-xs-4">
                            <select class="form-control multiple-select" size="5" id="menu1" style="height:100px;">
                                <option value="">= 대메뉴 선택 =</option>
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <select class="form-control multiple-select" size="5" id="menu2" style="height:100px;">
                                <option value="">= 중메뉴 선택 =</option>
                            </select>
                        </div>
                        <div class="col-xs-4">
                            <select class="form-control multiple-select" size="5" id="menu3" style="height:100px;">
                                <option value="">= 페이지 선택 =</option>
                            </select>
                        </div>
                    </div>
                </div>
            </td>
            <td class="border-left">
                <button type="button" class="btn btn-lg btn-black" id="btnSelectMenu">선택</button>
            </td>
        </tr>
        <tr>
            <th>
                자주쓰는 메뉴<br>
                <p class="nobold">
                    (
                    <strong class="text-red" id="selectedMenu">0</strong>
                    / 10 선택)
                </p>
            </th>
            <td colspan="2">
                <p id="tableEmptyMessage" class="<?= empty($menus) ? '' : 'display-none' ?>">자주쓰는 메뉴를 선택해 주세요. (최대 10개)</p>
                <table class="table table-rows table-fixed <?= empty($menus) ? 'display-none' : '' ?>" id="tableFavoriteMenu">
                    <colgroup>
                        <col/>
                        <col class="width-xs"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th>선택 메뉴</th>
                        <th>삭제</th>
                    </tr>
                    </thead>
                    <tbody id="tbodyFavoriteMenu">
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    <div class="text-center">
        <button type="button" class="btn btn-lg btn-white js-layer-close">닫기</button>
        <input type="submit" value="저장" class="btn btn-lg btn-black"/>
    </div>
</form>

<script type="text/javascript">
    <!--
    var gd_favorite_menu = (function ($) {
        /**
         * 자주쓰는 메뉴 레이어 스크립트
         */
        "use strict";
        var admin_menu;

        /**
         * 셀렉트 박스 옵션 태그 생성
         * @param value
         * @param text
         * @returns {string}
         */
        function make_option_element(value, text) {
            return '<option value="' + value + '">' + text + '</option>';
        }

        /**
         * 선택된 자주쓰는 메뉴 테이블 바디에 추가
         * @param table
         * @param number
         * @param path
         */
        function append_row_by_table_body(table, number, path) {
            var row = '<tr data-number=' + number + ' class="js-menu-row"><td>' + path + '</td><td class="center"><button type="button" class="btn btn-sm btn-white js-btn-menu-remove">삭제</button></td></tr>';
            table.find('tbody').append(row);
        }

        /**
         * 셀렉트 박스의 마지막 옵션 태그에 태그를 붙이는 함수
         * @param menu_json
         * @param select
         */
        function after_option_by_last(menu_json, select) {
            $.each(menu_json, function (idx, item) {
                select.find('option:last').after(make_option_element(item.number, item.text));
            });
        }

        /**
         * 메뉴 번호를 이용하여 메뉴경로 명을 반환하는 함수
         * @param numbers
         * @returns {string}
         */
        function get_menu_path(numbers) {
            var depth2, depth3;
            var path = '';
            $.each(numbers, function (n, number) {
                if (depth3) {
                    $.each(depth3, function (idx, item) {
                        if (item.number === number) {
                            path += '>' + item.text;
                            return true;
                        }
                    });
                }
                if (depth2) {
                    $.each(depth2, function (idx, item) {
                        if (item.number === number) {
                            path += '>' + item.text;
                            depth3 = item.children;
                            return true;
                        }
                    });
                }
                $.each(admin_menu, function (idx, item) {
                    if (item.number === number) {
                        path += item.text;
                        depth2 = item.children;
                        return true;
                    }
                });
            });
            return path;
        }

        return function () {
            var select1, select2, select3, menu_table;
            var select1_value, select2_value, select3_value;
            var menu_json2 = {}, menu_json3 = {};
            var favorite_menus = {menus: []};

            function toggle_empty_message() {
                if (menu_table.find('tbody tr').length > 0) {
                    menu_table.removeClass('display-none');
                    $('#tableEmptyMessage').addClass('display-none');
                } else {
                    menu_table.addClass('display-none');
                    $('#tableEmptyMessage').removeClass('display-none');
                }
            }

            return {
                init: function () {
                    select1 = $('#menu1');
                    select2 = $('#menu2');
                    select3 = $('#menu3');
                    menu_table = $('#tableFavoriteMenu');
                    $.each(favorite_menus, function (idx, item) {
                        $.each(item, function (idx2, item2) {
                            var lastIdx = item2.length;
                            var path = get_menu_path(item2);
                            append_row_by_table_body(menu_table, item2[lastIdx - 1], path);
                        });
                    });
                    toggle_empty_message();
                    after_option_by_last(admin_menu, select1);
                    select1.change(function () {
                        select1_value = $(this).find(':selected').val();
                        $.each(admin_menu, function (idx, item) {
                            if (item.number === select1_value) {
                                menu_json2 = item.children;
                                select2.find('option:gt(0)').remove();
                                select3.find('option:gt(0)').remove();
                                after_option_by_last(menu_json2, select2);
                            }
                        });
                    });
                    select2.change(function () {
                        select2_value = $(this).find(':selected').val();
                        $.each(menu_json2, function (idx2, item2) {
                            if (item2.number === select2_value) {
                                menu_json3 = item2.children;
                                select3.find('option:gt(0)').remove();
                                after_option_by_last(menu_json3, select3);
                            }
                        });
                    });
                    select3.change(function () {
                        select3_value = $(this).find(':selected').val();
                    });
                },
                set_favorite_menus: function (menus) {
                    if (typeof menus === 'undefined') {
                        console.log('set_favorite_menus, menus argument undefined');
                    } else {
                        favorite_menus = menus;
                    }
                },
                save_menus: function () {
                    var resort_favorite_menu = [];
                    $('#tbodyFavoriteMenu tr').each(function (idx, item) {
                        var menu_number = item.dataset.number;
                        console.log(menu_number);
                        $(favorite_menus.menus).each(function (idx2, item2) {
                            var length_item2 = item2.length;
                            var last_menu_number = item2[length_item2 - 1];
                            if (menu_number == last_menu_number) {
                                resort_favorite_menu.push(item2);
                            }
                        });
                    });
                    favorite_menus.menus = resort_favorite_menu;
                    var params = favorite_menus;
                    params['mode'] = 'favoriteMenu';
                    $.ajax('../base/main_setting_ps.php', {
                        method: "post",
                        data: params,
                        success: function () {
                            var response = arguments[0];
                            var options = {};
                            if (response.success === 'OK') {
                                options.isReload = true;
                            }
                            layer_close();
                            dialog_alert(response.message, '확인', options);
                        }
                    });
                },
                select_menu: function () {
                    if (menu_table.find('tbody tr').length >= 10) {
                        alert('자주쓰는 메뉴는 10개까지 설정이 가능합니다.');
                        return false;
                    }
                    if (select1_value === '' || typeof select1_value === 'undefined') {
                        alert('메뉴를 선택해주세요.');
                        return false;
                    }
                    menu_table.removeClass('display-none');
                    $('#tableEmptyMessage').addClass('display-none');
                    var menu_number = '', menu_path = '';
                    $.each(admin_menu, function (idx, item) {
                        if (item.number === select1_value) {
                            menu_path += item.text;
                            menu_number = item.number;
                            if (select2_value !== '' && typeof select2_value === 'string') {
                                $.each(item.children, function (idx2, item2) {
                                    if (item2.number === select2_value) {
                                        menu_path += '>' + item2.text;
                                        menu_number = item2.number;
                                        if (select3_value !== '' && typeof select3_value === 'string') {
                                            $.each(item2.children, function (idx3, item3) {
                                                if (item3.number === select3_value) {
                                                    menu_path += '>' + item3.text;
                                                    menu_number = item3.number;
                                                }
                                            });
                                        }
                                    }
                                });
                            }
                        }
                    });
                    if (menu_table.find('tr[data-number="' + menu_number + '"]').length > 0) {
                        alert('이미 추가된 메뉴입니다.');
                        return false;
                    }
                    append_row_by_table_body(menu_table, menu_number, menu_path);
                    $('#selectedMenu').text(menu_table.find('tbody tr').length);
                    var selected_menus = [];
                    $('select.multiple-select option:selected').each(function (idx, item) {
                        selected_menus.push(item.value);
                    });
                    favorite_menus['menus'].push(selected_menus);
                },
                remove_row: function (row) {
                    var number = row.data('number');
                    menu_table.find(row).remove();
                    $.each(favorite_menus.menus, function (idx, item) {
                        var lastIdx = item.length - 1;
                        if (item[lastIdx] === number) {
                            var menu1 = favorite_menus.menus.slice(0, idx);
                            var menu2 = favorite_menus.menus.slice(idx + 1, favorite_menus.menus.length);
                            favorite_menus.menus = menu1.concat(menu2);
                            return false;
                        }
                    });
                    var tr_length = menu_table.find('tbody tr').length;
                    $('#selectedMenu').text(tr_length);
                    if (tr_length == 0) {
                        toggle_empty_message();
                    }
                },
                set_admin_menu: function (menu) {
                    if (typeof menu === 'undefined' || $.isEmptyObject(menu)) {
                        console.log('admin menu json is undefined');
                    } else {
                        admin_menu = menu;
                    }
                },
                admin_menu_json: admin_menu
            };
        }
    })($);

    $(document).ready(function () {
        var favorite_menu = new gd_favorite_menu($);
        favorite_menu.set_favorite_menus(<?= gd_isset($favoriteMenus) ?>);
        favorite_menu.set_admin_menu(<?= $menuJson; ?>);
        favorite_menu.init();

        $('#btnSelectMenu').click(function () {
            favorite_menu.select_menu();
        });

        $('#tbodyFavoriteMenu').on('click', '.js-btn-menu-remove', function () {
            favorite_menu.remove_row($(this).closest('tr'));
        });

        $('#tbodyFavoriteMenu').sortable({
            items: "> tr",
            appendTo: "parent",
            helper: "clone"
        }).disableSelection();

        // 체크박스 초기화
        $('#selectedMenu').text($('#tableFavoriteMenu tbody tr').length);

        // 폼체크
        $('#frmMainSetting').validate({
            submitHandler: function () {
                favorite_menu.save_menus();
            }
        });
    });
    //-->
</script>
