/**
 * design Tree
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
(function ($) {
    /**
     * designTree.init() 클래스의 Property 정의
     */
    designTree = {
        skin: null // 스킨명
        , ele: null // Element
        , treeUse: 'move' // 트리 용도(move:이동, edit:편집)
        , treeEle: null // 트리 element
        , menuEle: null // 트리메뉴 element
        , treeObj: {} // 트리 object
        , selected: null // 선택된 트리 object
        , treeCallback: null // 트리 call back
        , skinType: '' // 스킨타입 (front , mobile)
        , mallCnt: 1 //멀티프론트 갯수
        , mallData: '' //멀티프론트 정보
        , pathShare: '' //멀티프론트 이미지 경로

        /**
         * designTree.init() 클래스
         * @classDescription designTree 출력하는 클래스
         * @param {Object} ele Element
         * @param {Array} opts 옵션
         * @return {Object} new instance 리턴
         * @constructor
         */
        , init: function (ele, opts, skinType) {
            opts = opts || {};
            $.extend(this, opts);
            this.ele = ele;
            this.skinType = skinType;
            this.start();
        }

        /**
         * 시작
         */
        , start: function () {
            var self = this;
            var result = true;
            var time = new Date().getTime(); // common_ax.php 캐시 사용 X
            // 스킨명
            $.ajax({
                type: 'GET'
                , url: 'common_ax.php?ts=' + time + '&mode=init&skinType=' + this.skinType
                , dataType: 'json'
                , async: false
                , success: function (data) {
                    if (data.code == 'error') {
                        result = false;
                        alert(data.message);
                    } else {
                        // 스킨 타입
                        self.skin = data.skin;
                        self.mallCnt = data.mallCnt;
                        self.mallData = data.mallData;
                    }
                }
            });

            if (result == true) {
                // 기본 구조 배치
                this.set_layout();
                // 트리
                this.set_tree();
            }
        }

        /**
         * 기본 구조 배치
         */
        , set_layout: function () {
            var self = this;
            this.menuEle = $('<div />').addClass('menu').appendTo($(this.ele));
            this.skinEle = $('<div />').addClass('skin_name').appendTo($(this.ele));
            this.treeEle = $('<div />').addClass('tree').appendTo($(this.ele));

            var menuHtml = '';
            if (self.mallCnt > 1) {
                menuHtml += '<div class="mall-info"><span class="flag flag-16 flag-' + self.mallData['domainFl'] + '"></span>[<span class="mall-name">' + self.mallData['mallName'] + '</span>] 작업스킨</div>';
            }
            menuHtml += '<div><a href="#" rel="design_page_create" class="design_page_create btn btn-gray btn-sm" title="새로운 페이지 추가하기">새로운 페이지 추가</a>';
            menuHtml += '<a href="#" rel="open_all" class="open_all" title="스킨 트리 모두 확장"></a>';
            menuHtml += '<a href="#" rel="close_all" class="close_all" title="스킨 트리 모두 접기"></a>';
            menuHtml += '<a href="#" rel="refresh" class="refresh" title="스킨 트리 새로고침"></a></div>';
            this.menuEle.append(menuHtml);

            var skinHtml = '';
            skinHtml += '<span class="skin_name">' + self.skin + '</span>';
            this.skinEle.append(skinHtml);
        }

        /**
         * 트리
         */
        , set_tree: function () {
            var self = this;

            // 트리 call back
            this.treeCallback = $.extend({}, this.treeCallback, {
                error: function(TEXT, TREE_OBJ) {
                    switch (TEXT) {
                        case 'CREATE: NO NODE SELECTED':
                            TEXT = '새로운 페이지 생성을 위해 폴더를 먼저 선택하세요.';
                            break;
                        case 'CREATE: CANNOT CREATE IN NODE':
                            TEXT = '새로운 페이지 생성할 수 있는 폴더가 아닙니다.';
                            break;
                        case 'SELECT: STOPPED BY USER':
                            return;
                            break;
                    }
                    BootstrapDialog.show({
                        title: '안내',
                        type: BootstrapDialog.TYPE_WARNING,
                        message: TEXT,
                    });
                    //alert(TEXT);
                }
                , onselect: function (NODE, TREE_OBJ) {
                    self.selected = $(NODE);
                }
                , beforedata: function (NODE, TREE_OBJ) {
                    return {id: $(NODE).attr('id') || ''};
                }
                , beforedelete: function (NODE, TREE_OBJ) {
                }
                , ondelete: function (NODE, TREE_OBJ, RB) {
                }
                , onrename: function (NODE, TREE_OBJ, RB) {
                }
                , onchange: function (NODE, TREE_OBJ) { // 디자인 페이지 이동하기
                    if ($(NODE).attr('rel') == 'designPage') {
                        if (TREE_OBJ.defaultId != $(NODE).attr('id') && $(NODE).attr('source') == 'true') {
                            self.location_move();
                        }
                    }
                }
            });

            // 트리 실행
            $.tree.plugins.contextmenu.defaults.items = null;
            this.treeObj = $.tree.create();
            this.treeObj.skin = this.skin;
            this.treeObj.init(this.treeEle, {
                data: {
                    type: 'json'
                    , async: true
                    , opts: {
                        url: 'common_ax.php?mode=getDesignTreeData&skinType=' + this.skinType
                    }
                }
                , ui: {
                    theme_name: 'summer'
                }
                , plugins: {
                    // 오른쪽 마우스버튼 메뉴접근처리
                    contextmenu: {
                        items: {
                            create: {
                                label: '새로운 페이지 추가하기'
                                , visible: function (NODE, TREE_OBJ) {
                                    if (NODE.length != 1) return false;
                                    return TREE_OBJ.check('creatable', NODE);
                                }
                                , action: function (NODE, TREE_OBJ) {
                                    if (TREE_OBJ.selected === undefined || TREE_OBJ.selected === false || $(NODE).attr('id') != TREE_OBJ.selected.attr('id')) {
                                        TREE_OBJ.select_branch(NODE);
                                    }
                                    TREE_OBJ.design_page_create();
                                }
                                , separator_after: true
                            }
                        }
                    }
                }
                , rules: {
                    draggable: false
                }
                , types: {
                    'default': {
                        draggable: false
                        , creatable: false
                        , renameable: false
                        , deletable: false
                    }
                    , directory: {
                        creatable: true
                    }
                }
                , callback: this.treeCallback
            });

            // 새로운 페이지 추가하기
            this.treeObj.design_page_create = function () {
                var self = this;
                var ref_node = this.selected;
                var dirPath = '';

                // 선택을 안하고 새로운 페이지 추가시
                if(!ref_node || !ref_node.size()) {
                    return this.error('CREATE: NO NODE SELECTED');
                }

                // 화일을 선택후 새로운 페이지 추가시
                if (this.check('creatable', ref_node) !== true) {
                    return this.error('CREATE: NO NODE SELECTED');
                }

                // 부모 디렉토리 경로
                var tmp = new Array();
                $(ref_node.parents('li.open')).each(function () {
                    tmp.push($(this).find('a').eq(0).text());
                });
                tmp.push($(ref_node).find('a').eq(0).text());
                var dirText = tmp.join(' > ');

                // 경로 설정
                dirPath = ref_node.attr('linkid');
                dirType = ref_node.attr('rel');
                var arrDir = dirPath.split('/');

                // 틀정 폴더 제외
                if (dirType == 'directory' && arrDir.length == 1 && arrDir[0] == 'outline') {
                    return this.error('CREATE: CANNOT CREATE IN NODE');
                }

                // 스킨 타입에 따른 경로
                if (typeof $('input[name=\'skinType\']').val() == 'string') {
                    var skinType = $('input[name=\'skinType\']').val();
                } else {
                    var skinType = 'front';
                }

                var params = {
                    dirPath: dirPath ,
                    dirText: dirText ,
                    skinType: skinType,
                    saveMode: 'create'
                };

                var create_url = 'layer_design_page_create.php';

                $.get('./' + create_url, params, function (data) {
                    BootstrapDialog.show({
                        title: '새로운 페이지 추가하기',
                        message: $(data),
                        closable: true
                    });
                });
            };

            // jstree상단에 있는 버튼들에 대한 설정
            $(this.menuEle)
                .find('a').not('.lang')
                .bind('click', function () {
                    self.treeObj[$(this).attr('rel')]();
                    this.blur();
                })
                .end().end()
                .children('.cmenu')
                .hover(function () {
                    $(this).addClass('hover');
                }, function () {
                    $(this).removeClass('hover');
                });
        }

        /**
         * 선택시 해당 디자인 페이지로 이동
         */
        , location_move: function () {
            var NODE = this.treeObj.selected;
            if (this.skinType == 'front') {
                //var path = '../design/design_page_edit.php?%sId='.replace(/%s/gi, $(NODE).attr('linkType'));
                var path = 'design_page_edit.php?%sId='.replace(/%s/gi, $(NODE).attr('linkType'));
            } else {
                var path = '../' + this.skinType + '/design_page_edit.php?%sId='.replace(/%s/gi, $(NODE).attr('linkType'));
            }
            path += $(NODE).attr('linkId');
            try {
                document.location.href = path;
            } catch (e) {
            }
        }
    };

    /**
     * designTree.init() 클래스의 Property 설정
     */
    designTree.init.prototype = designTree;

    /**
     * designTree.init() 클래스 인스턴스 생성
     * @param {Array} options 옵션
     * @return {Object} designTree.init() 클래스 인스턴스
     */
    jQuery.fn.designTree = function (options) {
        var firstEle = $(this)[0];
        var skinType = 'front';

        // 스킨 타입
        if (typeof $('input[name=\'skinType\']').val() == 'string' && $('input[name=\'skinType\']').val() != '') {
            skinType = $('input[name=\'skinType\']').val();
        }

        // 디자인 트리 시작
        return new designTree.init(firstEle, options, skinType);
    };

})(jQuery);
