var gd_layer_manager_cs = (function ($, _, ClipboardJS) {
    "use strict";
    var elements = {btn_cs_layer: '', btn_cs_create: '', btn_cs_search: '', select_scm: ''};
    var layers = {cs_list: '', cs_create: ''};
    var modify_cs = {};
    var scm_list = [];
    var selected_scm = 0;
    var authorization = {};
    var version = '';
    var previous_clip = null;
    var policy = {};
    var function_auth = {};
    var use_app_codes = {
        codes: [],
        use_app: function (plus_code) {
            var used = false;
            var length = this.codes.length;
            for (var i = 0; i < length; i++) {
                if (this.codes[i] === plus_code) {
                    used = true;
                    break;
                }
            }
            return used;
        }
    };

    var create_selected = {
        depth1: {no: '', ele: {}},
        depth2: {no: '', ele: {}},
        depth3: {no: '', ele: {}},
        list: [],
        ele: {},
        change_select: function ($target) {
            if ($target.val().indexOf('godo') !== -1) {
                if ($target.data('depth') === 1) {
                    this.depth1.no = $target.val();
                    this.depth2.no = '';
                    this.depth3.no = '';
                    this.depth2.ele.find('option:gt(0)').remove();
                    this.depth3.ele.find('option:gt(0)').remove();
                    this.depth2.ele.val('');
                    this.depth3.ele.val('');
                    get_access(2);
                } else if ($target.data('depth') === 2) {
                    this.depth2.no = $target.val();
                    this.depth3.no = '';
                    this.depth3.ele.find('option:gt(0)').remove();
                    this.depth3.ele.val('');
                    get_access(3);
                } else if ($target.data('depth') === 3) {
                    this.depth3.no = $target.val();
                } else {
                    console.error($target);
                    alert('권한 선택 중 오류가 발생하였습니다.');
                }
            } else {
                this.depth2.ele.find('option:gt(0)').remove();
                this.depth3.ele.find('option:gt(0)').remove();
            }
        },
        get_selected_depth_no: function () {
            var selected = [];
            if (this.depth1.no !== '') {
                selected.push(this.depth1.no);
            }
            if (this.depth2.no !== '') {
                selected.push(this.depth2.no);
            }
            if (this.depth3.no !== '') {
                selected.push(this.depth3.no);
            }

            return selected;
        },
        /**
         * 선택된 접근권한 제거
         * @param del
         */
        delete_list: function (del) {
            var length = this.list.length;

            if (length < 2 && !_.isUndefined(modify_cs.sno)) {
                alert('접근 권한 설정은 소메뉴 중 최소 1개 이상 설정하셔야 합니다.');
                return false;
            }

            for (var i = 0; i < length; i++) {
                var current = this.list[i];

                if (del[0] === current[0] && del[1] === current[1] && del[2] === current[2]) {
                    this.list.splice(i, 1);
                    break;
                }
            }
        },
        set_list: function (list) {
            if (Array.isArray(list)) {
                var length = list.length;
                this.list = [];
                for (var i = 0; i < length; i++) {
                    if (access_menu.has_menu(list[i])) {
                        console.log(list[i]);
                        this.list.push(list[i]);
                    }
                }
            } else {
                console.warn('wrong parameter create_selected set_list');
            }
        }
    };

    var access_menu = {
        depth1: [], depth2: {}, depth3: {},
        set_access_menu: function (access) {
            function delete_unused_app_menu(depth) {
                /**
                 * @param {{ adminMenuSettingType: String, adminMenuPlusCode: String }} item
                 */
                return _.filter(depth, function (item) {
                    return item.adminMenuSettingType === 'd' || (use_app_codes.use_app(item.adminMenuPlusCode));
                });
            }

            this.depth1 = delete_unused_app_menu(access.depth1);
            this.depth2 = _.object(_.map(access.depth2, function (item, idx) {
                return [idx, delete_unused_app_menu(item)];
            }));
            this.depth3 = _.object(_.map(access.depth3, function (item, idx) {
                return [idx, delete_unused_app_menu(item)];
            }));
        },
        get_menu_by_parent: function (parent) {
            var result = [];
            if (Array.isArray(parent)) {
                if (parent.length === 1) {
                    var depth3 = this.depth3;
                    _.each(this.depth2[parent[0]], function (item2) {
                        _.each(depth3[item2.adminMenuNo], function (item3) {
                            result.push([parent[0], item2.adminMenuNo, item3.adminMenuNo]);
                        });
                    });
                } else if (parent.length === 2) {
                    _.each(this.depth3[parent[1]], function (item) {
                        result.push([parent[0], parent[1], item.adminMenuNo]);
                    });
                }
            }

            return result;
        },
        has_menu: function (menu) {
            var find1 = _.findWhere(this.depth1, {adminMenuNo: menu[0]}) !== undefined;
            var find2 = _.findWhere(this.depth2[menu[0]], {adminMenuNo: menu[1]}) !== undefined;
            var find3 = _.findWhere(this.depth3[menu[1]], {adminMenuNo: menu[2]}) !== undefined;

            return find1 && find2 && find3;
        }
    };

    var view_create = {
        ele: {},
        current: 1,
        limit: {row: 10, page: 10},
        total: {list: 0, page: 0},
        /**
         * 접근메뉴 리스트 페이징 화면 처리
         * @returns {boolean}
         */
        draw_pagination: function () {
            this.total.list = create_selected.list.length;
            this.total.page = Math.ceil(this.total.list / this.limit.row) + 1;
            var start = (this.current - (this.current % this.limit.page) + 1);

            if ((this.current % this.limit.page) < 1) {
                start = this.current - 9;
            }

            var end = start + this.limit.row;

            if (end > this.total.page) {
                end = this.total.page;
            }

            var params = {
                total_page: this.total.page - 1,
                limit_page: this.limit.page,
                current: this.current,
                start: start,
                end: end
            };
            var pagination = _.template($('#pagination').html())(params);
            this.ele.html(pagination);
        },
        /**
         * 접근메뉴 리스트 화면처리
         */
        draw_list: function () {
            create_selected.ele.find('tr').remove();
            var length = create_selected.list.length;

            if (length > 0) {
                var start = (this.current - 1) * this.limit.row;
                var end = start + this.limit.row;

                if (end >= length || length < this.limit.row) {
                    end = length;
                }

                var html = [];
                var slice_list = create_selected.list.slice(start, end);
                console.log(slice_list);
                var slice_length = slice_list.length;

                if (slice_length < 1) {
                    this.current -= 1;
                    this.draw_list();
                } else {
                    for (var i = 0; i < slice_length; i++) {
                        var access = slice_list[i];
                        var path = access_to_path(access);
                        html.push('<tr><td class="text-left">' + path.join(' > ') + '</td><td><button class="btn btn-white btn-sm btn-icon-minus" data-access="' + access.join(',') + '">삭제</button></td></tr>');
                    }
                    create_selected.ele.html(html.join(''));
                }
            } else {
                create_selected.ele.append('<tr class="text-center"><td colspan="2">선택된 메뉴가 없습니다.</td></tr>');
            }
        }
    };

    var cs_list = {
        ele: {},
        list: [],
        search_list: [],
        set_list: function (list) {
            view_cs.total.list = list.length;
            _.each(list, function (item) {
                item.sno *= 1;
                item.scmNo *= 1;
            });
            this.list = list;
        },
        get_display_list: function () {
            if (this.search_list.length > 0) {
                return this.search_list;
            } else {
                return this.list;
            }
        }
    };

    var view_cs = {
        ele: {},
        current: 1,
        limit: {row: 10, page: 10},
        total: {list: 0, page: 0},
        /**
         * CS 계정 리스트 페이징 화면처리
         * @returns {boolean}
         */
        draw_pagination: function () {
            var display_list = cs_list.get_display_list();
            this.total.list = display_list.length;
            this.total.page = Math.ceil(this.total.list / this.limit.row) + 1;
            var start = (this.current - (this.current % this.limit.page) + 1);
            var end = start + this.limit.row;

            if (end > this.total.page) {
                end = this.total.page;
            }

            var pagination = _.template($('#pagination').html())({
                total_page: this.total.page - 1,
                limit_page: this.limit.page,
                current: this.current,
                start: start,
                end: end
            });
            this.ele.html(pagination);
        },
        /**
         * CS 계정 리스트 화면처리
         */
        draw_list: function () {
            var display_list = cs_list.get_display_list();
            var length = display_list.length;

            if (length > 0 && !_.isUndefined(display_list[0])) {
                var start = (this.current - 1) * this.limit.row;
                var end = start + this.limit.row;

                if (end >= length || length < this.limit.row) {
                    end = length;
                }

                var slice_list = display_list.slice(start, end);
                var items = [];
                /**
                 * @param {{ sno: Integer, scmNo: Integer,
                 * permissionFl: String, csId: String, csPw: String
                 * expireDate: String }} item
                 */
                _.each(slice_list, function (item) {
                    items.push({
                        sno: item.sno,
                        scm_name: elements.select_scm.find('option[value=' + item.scmNo + ']').text(),
                        permission_fl: item.permissionFl,
                        cs_id: item.csId,
                        cs_pw: item.csPw,
                        expire_date: item.expireDate
                    })
                });
                cs_list.ele.html(_.template($('#tbodyCsList').html())({list: items}));
            } else {
                cs_list.ele.html('<tr class="text-center"><td colspan="4">검색된 CS 계정이 없습니다.</td></tr>');
            }
        }
    };

    function modify_manager_cs() {
        selected_scm = modify_cs.scmNo;

        if (authorization.value !== 'all' && create_selected.list.length < 1) {
            alert('접근 권한 설정은 소메뉴 중 최소 1개 이상 설정하셔야 합니다.');
            return false;
        }

        if (selected_scm < 1) {
            alert('공급사 구분 값이 없습니다.');
            return false;
        }

        var data = {
            mode: 'modify',
            scm_no: selected_scm,
            sno: modify_cs.sno,
            permission_menu: create_selected.list,
            function_auth: function_auth,
            permission_fl: authorization.value
        };

        $.post('../policy/layer_manager_cs_ps.php', data).done(function () {
            console.info('modify cs manager', arguments, data);
            /**
             * @param {{ csList: Array, message: String }} response
             */
            var response = arguments[0];

            if (response.error === 0) {
                cs_list.set_list(response.csList);
                BootstrapDialog.show({
                    title: '확인',
                    message: response.message,
                    buttons: [{
                        label: '확인',
                        cssClass: 'btn-black',
                        hotkey: 13,
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (dialog) {
                            dialog.close();
                        }
                    }],
                    onhide: function () {
                        layers.cs_create.close();
                        view_cs.draw_list();
                        view_cs.draw_pagination();
                    }
                });
            } else {
                alert(response.message);
            }
        });
    }

    /**
     * CS 관리자 생성
     * @returns {boolean}
     */
    function create_manager_cs() {
        if (authorization.value !== 'all' && create_selected.list.length < 1) {
            alert('접근 권한 설정은 소메뉴 중 최소 1개 이상 설정하셔야 합니다.');
            return false;
        }

        if (selected_scm < 1) {
            alert('공급사 구분 값이 없습니다.');
            return false;
        }

        var data = {
            mode: 'register',
            scm_no: selected_scm,
            permission_menu: create_selected.list,
            function_auth: function_auth,
            permission_fl: authorization.value
        };

        var find = _.findWhere(cs_list.list, {scmNo: selected_scm}) || {};
        if (find.scmNo > 0) {
            data.has_cs = true;
        }

        $.post('../policy/layer_manager_cs_ps.php', data).done(function () {
            console.debug('register cs manager', arguments);
            var response = arguments[0];

            if (response.error === 0) {
                cs_list.set_list(response.csList);
                BootstrapDialog.show({
                    title: '확인',
                    message: response.message,
                    buttons: [{
                        label: '확인',
                        cssClass: 'btn-black',
                        hotkey: 13,
                        size: BootstrapDialog.SIZE_LARGE,
                        action: function (dialog) {
                            dialog.close();
                        }
                    }],
                    onhide: function () {
                        cs_list.search_list = [];
                        layers.cs_create.close();
                        view_cs.draw_list();
                        view_cs.draw_pagination();
                    }
                });
            } else {
                alert(response.message);
            }
        });
    }

    /**
     * 단계별 접근메뉴 화면처리
     * @param ele
     * @param access
     */
    function append_options(ele, access) {
        /**
         * @param int idx
         * @param {{ adminMenuNo: String, adminMenuName: String }} item
         */
        ele.find(':gt(0)').remove();
        console.log(access);
        _.each(access, function (item) {
            if (item instanceof Object) {
                ele.append('<option value="' + item.adminMenuNo + '">' + item.adminMenuName + '</option>');
            }
        });
    }

    /**
     * 접근 메뉴 조회
     * @param depth
     */
    function get_access(depth) {
        var depth1 = access_menu.depth1;
        var depth2 = access_menu.depth2;
        var depth3 = access_menu.depth3;
        var selected_depth1 = create_selected.depth1.no;
        var selected_depth2 = create_selected.depth2.no;

        if (depth === 1 && depth1.length > 0) {
            append_options(create_selected.depth1.ele, depth1);
        } else if (depth === 2 && typeof depth2[selected_depth1] === 'object' && depth2[selected_depth1].length > 0) {
            append_options(create_selected.depth2.ele, depth2[selected_depth1]);
        } else if (depth === 3 && typeof depth3[selected_depth2] === 'object' && depth3[selected_depth2].length > 0) {
            append_options(create_selected.depth3.ele, depth3[selected_depth2]);
        }
    }

    /**
     * 선택된 접근권한 추가
     * @param add
     */
    function has_selected(add) {
        var length = create_selected.list.length;
        var has_access = false;

        for (var i = 0; i < length; i++) {
            var current = create_selected.list[i];

            if (add[0] === current[0] && add[1] === current[1] && add[2] === current[2]) {
                has_access = true;
                break;
            }
        }

        return has_access;
    }

    /**
     * 로컬 저장소 호출
     */
    function load_storage_menu(callback) {
        var key = 'G5MenuS';

        if (selected_scm < 2) {
            key = 'G5MenuD';
        }

        var menu = localStorage.getItem(key);
        menu = JSON.parse(menu);

        if (menu === null || menu.version !== version || menu.app_cnt !== use_app_codes.codes.length) {
            var request_url = '../policy/layer_manager_cs_ps.php?mode=getAccess&scmNo=' + selected_scm;
            console.info('reload access menu type ' + key, request_url);
            $.getJSON(request_url).done(function () {
                var storage_menu = {
                    version: version,
                    app_cnt: use_app_codes.codes.length,
                    access: arguments[0]
                };
                localStorage.setItem(key, JSON.stringify(storage_menu));
                access_menu.set_access_menu(storage_menu.access);

                if (_.isFunction(callback)) {
                    callback();
                }
            });
        } else {
            access_menu.set_access_menu(menu.access);
            if (_.isFunction(callback)) {
                callback();
            }
        }
    }

    /**
     * 접근 권한 경로 반환
     * @param access
     * @returns {Array}
     */
    function access_to_path(access) {
        var path = [];

        if (Array.isArray(access) && access.length === 3) {
            var depth1 = access_menu.depth1;
            for (var i = 0; i <= depth1.length; i++) {
                if (depth1[i].adminMenuNo === access[0]) {
                    path.push(depth1[i].adminMenuName);
                    break;
                }
            }

            var depth2 = access_menu.depth2;
            for (i = 0; i < depth2[access[0]].length; i++) {
                if (depth2[access[0]][i].adminMenuNo === access[1]) {
                    path.push(depth2[access[0]][i].adminMenuName);
                    break;
                }
            }

            var depth3 = access_menu.depth3[access[1]];
            for (i = 0; i < depth3.length; i++) {
                if (depth3[i].adminMenuNo === access[2]) {
                    path.push(depth3[i].adminMenuName);
                    break;
                }
            }
        }

        return path;
    }

    /**
     * CS 계정생성 레이어 발생
     * @param option bootstrap 옵션
     */
    function open_cs_create(option) {
        var scm = {
            scmNo: 0,
            functionAuth: {
                goodsDelete: 'n',
                goodsExcelDown: 'n',
                goodsCommission: 'n',
                goodsNm: 'n',
                addGoodsNm: 'n',
                goodsPrice: 'n',
                goodsSalesDate: 'n',
                addGoodsCommission: 'n',
                orderState: 'n',
                orderExcelDown: 'n',
                boardDelete: 'n',
                goodsStockModify: 'n'
            }
        };
        var length = scm_list.length;

        for (var i = 0; i < length; i++) {
            if ((scm_list[i].scmNo * 1) === selected_scm) {
                scm = _.clone(scm_list[i]);
                scm.scmNo *= 1;
                var parse = JSON.parse(scm.functionAuth);
                if (parse !== null) {
                    scm.functionAuth = parse.functionAuth;
                } else {
                    scm.functionAuth = null;
                }
                break;
            }
        }
        // console.debug(scm_list, scm, selected_scm);

        var compiled = _.template($('#layerCsCreate').html());
        var default_option = {
            title: 'CS 계정 생성',
            message: compiled({
                use_bankda: policy.bankda.use, scm: scm, plusshop: {scm: use_app_codes.use_app('cGodo_Scm')}
            }),
            onshow: function (dialog) {
                on_show_cs_create(dialog);
                dialog.$modalContent.find('.btn-red').removeClass('modify');
            },
            onshown: function (dialog) {
                dialog.$modalContent.find('input[name=authorization]:eq(0)').trigger('click');
            },
            onhide: function () {
                create_selected.set_list([]);
                function_auth = {};
                view_create.current = 1;
                modify_cs = {};
            }
        };
        console.log(scm);
        var dialog_option = $.extend({}, default_option, option || {});
        // console.debug('open_cs_create', dialog_option, selected_scm);
        load_storage_menu(function (option) {
            layers.cs_create = BootstrapDialog.show(option);
        }.bind(this, dialog_option));
    }

    /**
     * CS 계정생성 레이어 발생 시 onshow 시점에 호출되는 함수
     * @param dialog
     */
    function on_show_cs_create(dialog) {
        create_selected.depth1.ele = dialog.$modalContent.find('#access1');
        create_selected.depth2.ele = dialog.$modalContent.find('#access2');
        create_selected.depth3.ele = dialog.$modalContent.find('#access3');
        create_selected.ele = dialog.$modalContent.find('.table-rows tbody');
        view_create.ele = dialog.$modalContent.find('.pagination');

        dialog.$modalContent.on('click', 'input[name=authorization]', function (e) {
            // console.debug('authorization click', arguments);
            var $tabs = $('.nav-tabs, .tab-content');
            $tabs.addClass('display-none');
            if (e.target.value === 'select') {
                $tabs.removeClass('display-none');
                get_access(1);
            }
            authorization = e.target;
        }).on('click', '#tabAuthorization a', function (e) {
            e.preventDefault();
            $(this).tab('show');
            var $tab_function = $('#tabFunction');
            if ($tab_function.hasClass('active') && $tab_function.find('.empty-function-auth').length === 0) {
                var rows = $tab_function.find('tbody td .row');
                _.each(rows, function (item) {
                    var $item = $(item);
                    if ($item.find(':checkbox').length < 1) {
                        $item.remove();
                    }
                });
                var table_rows = $tab_function.find('tbody tr');
                _.each(table_rows, function (item) {
                    var $item = $(item);
                    if ($item.find('.row').length < 1) {
                        $item.remove();
                    }
                });
            }
        }).on('change', '.multiple-select', function (e) {
            create_selected.change_select($(e.target));
        }).on('click', '.btn-select-access', function () {
            var current_selected = create_selected.get_selected_depth_no();
            if (current_selected.length === 3) {
                if (has_selected(current_selected)) {
                    alert('이미 선택된 메뉴입니다.');
                } else {
                    create_selected.list.unshift(current_selected);
                    view_create.draw_list();
                    view_create.draw_pagination();
                }
            } else if (current_selected.length > 0) {
                var menuByParent = access_menu.get_menu_by_parent(current_selected);
                _.each(menuByParent, function (item) {
                    if (!has_selected(item)) {
                        create_selected.list.unshift(item);
                    }
                });
                view_create.draw_list();
                view_create.draw_pagination();
            } else {
                alert('접근 권한 설정은 소메뉴 중 최소 1개 이상 설정하셔야 합니다.');
            }
        }).on('click', '.pagination li a', function () {
            $('.pagination li').removeClass('active');
            $(this).closest('li').addClass('active');
            view_create.current = $(this).data('page');
            view_create.draw_list();
            view_create.draw_pagination();
        }).on('click', '.btn-icon-minus', function () {
            create_selected.delete_list($(this).data('access').split(','));
            view_create.draw_list();
            view_create.draw_pagination();
        }).on('click', '.btn-red', function () {
            if ($(this).hasClass('modify')) {
                modify_manager_cs();
            } else {
                create_manager_cs();
            }
        }).on('change', ':checkbox[name^=functionAuth]', function () {
            var name = $(this).attr('name');
            name = name.substring(name.indexOf('[') + 1, name.lastIndexOf(']'));

            if ($(this).prop('checked')) {
                function_auth[name] = 'y';
            } else {
                delete function_auth[name];
            }
        });
    }

    /**
     * CS 계정 수정버튼 클릭 이벤트
     * @returns {boolean}
     */
    function click_cs_modify() {
        var close_row = $(this).closest('tr');
        var sno = close_row.data('sno') * 1;

        if (sno < 0) {
            alert('수정 요청 처리 중 오류가 발생하였습니다.');
            return false;
        }

        modify_cs = _.findWhere(cs_list.list, {sno: sno}) || {};
        selected_scm = modify_cs.scmNo * 1;
        var permission_fl = modify_cs.permissionFl;

        if (permission_fl === 'l') {
            open_cs_create({
                title: 'CS 계정 수정', onshow: function (dialog) {
                    on_show_cs_create(dialog);
                    dialog.$modalContent.find('.btn-red').text('수정').addClass('modify');
                }, onshown: function (dialog) {
                    dialog.$modalContent.find('input[name=authorization][value=select]').trigger('click');

                    if (typeof modify_cs.functionAuth === 'string') {
                        modify_cs.functionAuth = JSON.parse(modify_cs.functionAuth);
                        if (Array.isArray(modify_cs.functionAuth) && modify_cs.functionAuth.length < 1) {
                            modify_cs.functionAuth = {};
                        }
                    }

                    if (typeof modify_cs.permissionMenu === 'string') {
                        modify_cs.permissionMenu = JSON.parse(modify_cs.permissionMenu);
                    }

                    _.each(modify_cs.functionAuth, function (item, idx) {
                        if (item === 'y') {
                            dialog.$modalContent.find(':checkbox[name=\'functionAuth\[' + idx + '\]\']').prop('checked', true);
                        }
                    });

                    console.debug('click_cs_modify', modify_cs);
                    function_auth = _.clone(modify_cs.functionAuth);
                    create_selected.set_list(_.clone(modify_cs.permissionMenu.menu));
                    view_create.draw_list();
                    view_create.draw_pagination();
                }
            });
        } else {
            open_cs_create({
                title: 'CS 계정 수정', onshow: function (dialog) {
                    on_show_cs_create(dialog);
                    dialog.$modalContent.find('.btn-red').text('수정').addClass('modify');
                }
            });
        }
    }

    /**
     * CS 계정 검색 버튼 클릭 이벤트
     */
    function click_cs_search() {
        if (selected_scm < 1) {
            cs_list.search_list = [];
        } else {
            cs_list.search_list = [_.findWhere(cs_list.list, {scmNo: selected_scm})];
        }

        view_cs.current = 1;
        view_cs.draw_list();
        view_cs.draw_pagination();
    }

    /**
     * CS 계정 생성 버튼 클릭 이벤트
     * @returns {boolean}
     */
    function click_cs_create() {
        if (selected_scm < 1) {
            alert('선택된 공급사가 없습니다.');
            return false;
        }

        var find = _.findWhere(cs_list.list, {scmNo: selected_scm}) || {};
        console.debug('click_cs_create.find', find);
        if (find.sno > 0 && moment([]).isBefore(find.expireDate)) {
            alert('이미 CS 계정이 생성된 공급사입니다.');
            return false;
        }

        open_cs_create();
    }

    /**
     * CS 관리자 레이어 처리
     */
    function click_cs_layer() {
        layers.cs_list = BootstrapDialog.show({
            title: "CS 계정 관리",
            size: BootstrapDialog.SIZE_WIDE_LARGE,
            message: _.template($('#layerCsList').html()),
            onshow: function (dialog) {
                localStorage.removeItem('G5MenuS');
                localStorage.removeItem('G5MenuD');
                view_cs.ele = $(dialog.$modalContent.find('.pagination'));
                cs_list.ele = $(dialog.$modalContent.find('.table-rows tbody'));
                elements.select_scm = $(dialog.$modalContent.find('#selectScm'));
                elements.select_scm.change(function () {
                    selected_scm = $(this).val() * 1;
                });

                if (scm_list.length > 0) {
                    elements.select_scm.find('option:gt(0)').remove();
                    $.each(scm_list, function (idx, item) {
                        elements.select_scm.append('<option value="' + item.scmNo + '">' + item.companyNm + '</option>');
                    });
                }

                view_cs.draw_pagination();
                view_cs.draw_list();

                $(dialog.$modalContent)
                    .on('click', '.btn-cs-modify', click_cs_modify)
                    .on('click', '.btn-cs-create', click_cs_create)
                    .on('click', '.btn-cs-search', click_cs_search)
                    .on('click', '.pagination li a', function () {
                        view_cs.current = $(this).data('page');
                        view_cs.draw_list();
                        view_cs.draw_pagination();
                    });
            },
            onshown: function (dialog) {
                if (previous_clip) {
                    previous_clip.destroy();
                }
                previous_clip = new ClipboardJS('.btn-copy', {
                    text: function (trigger) {
                        return trigger.dataset.clipboardText;
                    },
                    container: dialog.$modalBody[0]
                });
                previous_clip.on('success', function () {
                    alert('클립보드에 복사했습니다.\n<code>Ctrl+V</code>를 이용해서 사용하세요.');
                    arguments[0].clearSelection();
                });
                previous_clip.on('error', function (e) {
                    alert('복사 실패');
                    console.error('Action:', e.action);
                    console.error('Trigger:', e.trigger);
                });
            },
            onhide: function () {
                cs_list.search_list = [];
                selected_scm = 0;
            }
        });
    }

    return function () {
        version = $('.footer .version').text();
        $('.btn-cs-layer').click(click_cs_layer);

        return {
            set_policy: function (value) {
                policy = value;
            },
            set_scm_list: function (list) {
                scm_list = list;
            },
            set_cs_list: function (list) {
                cs_list.set_list(list);
            },
            set_use_app_codes: function (codes) {
                use_app_codes.codes = codes;
            },
            get_layers: function () {
                return layers;
            },
            get_access_menu: function () {
                return access_menu;
            }
        }
    }
})($, _, ClipboardJS);


