<!-- //@formatter:off -->
<form id="frmManagerPermission" name="frmManagerPermission" action="manage_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="setManagePermission"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <button type="submit" class="btn btn-red">저장</button>
        </div>
    </div>

    <div class="container-batch-permission">
        <div id="managerSearch">
            <div class="table-title gd-help-manual mgb10">운영자 검색</div>

            <div>
                <table class="table table-cols no-title-line">
                    <colgroup>
                        <col class="width-xs"/>
                        <col/>
                        <col class="width-3xs"/>
                    </colgroup>
                    <?php if (gd_use_provider()){ ?>
                        <?php if (!gd_is_provider()){ ?>
                            <tr>
                                <th>공급사 구분</th>
                                <td colspan="3" class="form-inline">
                                    <label class="radio-inline">
                                        <input type="radio" name="scmFl" value="n" onclick="scm_toggle('n');" /> 본사
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="scmFl" value="y"  onclick="scm_toggle('y');" /> 공급사
                                    </label>
                                    <label>
                                        <button type="button" class="btn btn-sm btn-gray" onclick="scm_toggle('y');">공급사 선택</button>
                                    </label>
                                    <div id="scmLayer" class="selected-btn-group">
                                        <h5>선택된 공급사 : </h5>
                                        <div id="info_scm" class="btn-group btn-group-xs">
                                            <input type="hidden" name="scmNo" value=""/>
                                            <span class="btn"><!-- 선택된 공급사명 --></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                    <tr>
                        <th>검색어</th>
                        <td class="form-inline">
                            <?= gd_select_box(
                                'managerSearchKey', 'managerSearchKey', [
                                'managerId'     => '아이디',
                                'managerNm'     => '이름',
                                'email'         => '이메일',
                                'managerNickNm' => '닉네임',
                                'phone'         => '전화번호',
                                'cellPhone'     => '휴대폰번호',
                            ], '', '', '=통합검색=', null, 'form-control'
                            ); ?>
                            <input type="text" name="managerSearchKeyword" value="" class="form-control"/>
                        </td>
                        <td>
                            <input type="button" value="검색" class="btn btn-black btn-hf"  onclick="call_manager_search(); ">
                        </td>
                    </tr>
                </table>
            </div>

            <div id="managerList"><!-- 운영자 리스트 --></div>
        </div>

        <div id="managerPermission">
            <div class="table-title gd-help-manual mgb10">메뉴 권한 설정</div>

            <div class="permission-flag">
                <table class="table table-cols no-title-line">
                    <colgroup>
                        <col class="width-md"/>
                        <col/>
                    </colgroup>
                    <tbody>
                    <tr>
                        <th>권한 범위</th>
                        <td class="form-inline">
                            <label class="radio-inline">
                                <input type="radio" name="permissionFl" value="s" onclick="permission_toggle(this, 'hide');"  />
                                전체권한
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="permissionFl" value="l" onclick="permission_toggle(this, 'show');" />
                                선택권한
                            </label>
                            <label>
                                <button type="button" class="btn btn-sm btn-gray manage-btn" onclick="call_manage_select();" data-issuper="">기존 운영자 권한 불러오기</button>
                            </label>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div id="scmSuperNotice" class="notice-danger notice-info mgl5 display-none">공급사 대표운영자의 전체권한 범위에는 '추가설정' 권한이 포함되지 않으므로 별도로 설정하셔야 합니다.</div>

            <div class="table-header table-header-tab" style="overflow:visible !important;">
                <div class="pull-left" style="padding: 10px 5px 10px !important;">
                    <div class="form-inline">
                        <button type="button" class="btn btn-sm btn-black permission-initialization-btn" style="height: 27px !important;" data-issuper="" >권한 초기화</button>
                    </div>
                </div>
                <div class="pull-right" style="overflow:visible !important;">
                    <div class="form-inline">
                        <div class="dropdown display-inline manager-dropdown exposure-menu-top-selection">
                            <button type="button" class="btn btn-white dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false">노출 메뉴 선택</button>
                            <div class="dropdown-menu">
                                <div class="dropdown-header">노출 메뉴 선택</div>
                                <div class="exposure-top-menu-list"><!-- 노출 메뉴 리스트 --></div>
                                <div class="dropdown-footer"><button type="button" id="view_exposure_top_menu" class="btn btn-white">선택 메뉴 보기</button></div>
                            </div>
                        </div>

                        <select class="form-control" id="view_menu_depth" name="view_menu_depth" >
                            <option value="3">3차 메뉴 기준으로 보기</option>
                            <option value="2">2차 메뉴 기준으로 보기</option>
                            <option value="1">1차 메뉴 기준으로 보기</option>
                        </select>
                    </div>
                </div>
            </div>

            <div style="clear: both;">
                <div class="table-action-dropdown">
                    <div class="table-action mgt0 mgb0">
                        <div class="pull-right">
                            <div class="form-inline">
                                선택한 메뉴에
                                <select class="form-control" id="batch_permission" name="batch_permission" >
                                    <option value="none">=권한 선택=</option>
                                    <option value="">권한없음</option>
                                    <option value="readonly">읽기</option>
                                    <option value="writable">읽기+쓰기</option>
                                </select>
                                <button type="button" id="set_batch_permission" class="btn btn-sm btn-black" style="height: 27px !important;">일괄적용</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="menuList"><!-- 메뉴 리스트 출력 --></div>
        </div>

        <div class="clear-both"></div>
    </div>
</form>
<!-- //@formatter:on -->

<script type="text/javascript">
    <!--
    var isManagerPermissionPage = true; // 운영자 권한 설정 페이지 여부
    $(document).ready(function () {
        // 운영자 권한 JS Load 실패
        if (typeof call_manager_search == 'undefined') {
            $("#frmManagerPermission").validate().destroy();
            $("#frmManagerPermission").submit(function(){ dialog_alert("오류가 발생했습니다. 잠시 후 다시 시도해주세요.<br>문제가 지속될 경우 1:1문의 게시판에 문의해주세요."); return false; });
            $("#menuList").html('<div class="text-orange-red center bold mgt24">오류가 발생했습니다. 잠시 후 다시 시도해주세요.<br>문제가 지속될 경우 1:1문의 게시판에 문의해주세요.</div>');
            return;
        } else {
            $("#managerList").addClass('loading height400');
            $("#menuList").addClass('loading height400');
        }

        // 정보 저장
        $("#frmManagerPermission").validate({
            submitHandler: function (form) {
                if ($('input:checkbox[name="manage_sno[]"]:checked').length == 0) {
                    alert('운영자를 선택해 주세요.');
                    return false;
                }

                if ($('input[name="permissionFl"][value="l"]').is(':checked')) {
                    if ($('select[id^="permission_"] option[value="readonly"]:selected, select[id^="permission_"] option[value="writable"]:selected').length == 0) {
                        alert("메뉴 권한은 최소 1개 이상 '읽기' 또는 '읽기+쓰기'로 설정하셔야 합니다.");
                        return false;
                    }
                }

                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
            },
            messages: {
            }
        });

        // 권한설정 변경여부 초기화
        sessionStorage.removeItem('isChangedPermission');
        sessionStorage.removeItem('isChangedUnitPermission');

        // 디폴트(본사) 기준으로 운영자 검색 및 메뉴 권한 설정 레이아웃 구성
        init_scmFl();
        call_manager_search();

        // 권한 초기화 이벤트
        $('.permission-initialization-btn').click(permission_initialization);

        // 노출 메뉴 선택 이벤트
        $(document).on('click.dropdown touchstart.dropdown.data-api', '.manager-dropdown .dropdown-menu', function (e) { e.stopPropagation() });
        $('#view_exposure_top_menu').click(view_exposure_top_menu);

        // 메뉴 기준으로 보기 이벤트
        $('#view_menu_depth').change(view_menu_depth);

        // 선택한 메뉴에 권한 일괄적용 이벤트
        $('#set_batch_permission').click(set_batch_permission);
    });

    /**
     * 운영자 번호 리턴
     * @returns {string}
     */
    function get_manager_sno() {
        var sno = '';

        // 개별 권한보기 한 경우
        if ( $('#managerList tr.permission-view-on').length == 1 ) {
            sno = $('#managerList tr.permission-view-on').data('sno');
        }

        return sno;
    }

    /**
     * 공급사 번호 리턴
     * @returns {string}
     */
    function get_scmno() {
        var scmNo = '<?= Session::get('manager.scmNo'); ?>'; // 기본값
        if ($('#frmManagerPermission input:hidden[name="scmNo"]').length) { // 공급사 사용 중이고 공급사 관리모드가 아닌 경우
            if ($('#frmManagerPermission input:hidden[name="scmNo"]').val()) {
                scmNo = $('#frmManagerPermission input:hidden[name="scmNo"]').val();
            }
        }
        return scmNo;
    }

    /**
     * 공급사 구분 리턴
     * @returns {string}
     */
    function get_scmfl() {
        var scmFl = 'n'; // 기본값
        if ($('#frmManagerPermission input[name=scmFl]:checked').length == 1) { // 공급사 사용 중이고 공급사 관리모드가 아닌 경우
            scmFl = $('#frmManagerPermission input[name=scmFl]:checked').val();
        }
        if (scmFl == 'n' && get_scmno() != <?= DEFAULT_CODE_SCMNO; ?>) { // 본사가 아닌 경우
            scmFl = 'y';
        } else if (scmFl == 'y' && get_scmno() == <?= DEFAULT_CODE_SCMNO; ?>) { // 본사 인 경우
            scmFl = 'n';
        }
        return scmFl;
    }
//-->
</script>