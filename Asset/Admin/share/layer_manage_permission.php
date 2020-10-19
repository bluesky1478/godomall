<div class="mgt10"></div>
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
                    <input type="radio" name="permissionFl" value="s"
                           onclick="permission_toggle(this, 'hide');" <?= gd_isset($checked['permissionFl']['s']); ?> <?= gd_isset($disabled['permissionFl']); ?> />
                    전체권한
                </label>
                <label class="radio-inline">
                    <input type="radio" name="permissionFl" value="l"
                           onclick="permission_toggle(this, 'show');" <?= gd_isset($checked['permissionFl']['l']); ?> <?= gd_isset($disabled['permissionFl']); ?> />
                    선택권한
                </label>
                <label>
                    <button type="button" class="btn btn-sm btn-gray manage-btn" onclick="call_manage_select();" <?= gd_isset($disabled['settingItem']); ?> data-issuper="<?= gd_isset($isSuper); ?>">기존 운영자 권한 불러오기</button>
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
            <button type="button" class="btn btn-sm btn-black permission-initialization-btn" style="height: 27px !important;" data-issuper="<?= gd_isset($isSuper); ?>" >권한 초기화</button>
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
                <option value="3" <?= gd_isset($selected['viewMemuDepth']['3']); ?>>3차 메뉴 기준으로 보기</option>
                <option value="2" <?= gd_isset($selected['viewMemuDepth']['2']); ?>>2차 메뉴 기준으로 보기</option>
                <option value="1" <?= gd_isset($selected['viewMemuDepth']['1']); ?>>1차 메뉴 기준으로 보기</option>
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
                    <select class="form-control" id="batch_permission" name="batch_permission" <?= gd_isset($disabled['settingItem']); ?> >
                        <option value="none">=권한 선택=</option>
                        <option value="">권한없음</option>
                        <option value="readonly">읽기</option>
                        <option value="writable">읽기+쓰기</option>
                    </select>
                    <button type="button" id="set_batch_permission" class="btn btn-sm btn-black" style="height: 27px !important;" <?= gd_isset($disabled['settingItem']); ?> >일괄적용</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="menuList" class="mgb15">
    <?php
    if ($isMenuList === true) {
        include gd_admin_skin_path('policy/_manage_permission_menu_list.php');
    } else {
        echo '<div class="text-orange-red center bold mgt24">정상적으로 서비스가 제공되지 않습니다. 잠시 후 다시 시도해주세요.<br>문제가 지속될 경우 1:1문의 게시판에 문의해주세요.</div>';
    }
    ?>
</div>

<div class="center">
    <input type="button" value="취소" class="btn btn-lg btn-white js-layer-close"/>
    <input type="button" value="확인" class="btn btn-lg btn-black js-parent-action"/>
</div>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 권한설정 변경여부 초기화
        sessionStorage.removeItem('isChangedPermission');

        // 권한 초기화 이벤트
        $('.permission-initialization-btn').click(permission_initialization);

        // 노출 메뉴 선택 이벤트
        $(document).on('click.dropdown touchstart.dropdown.data-api', '.manager-dropdown .dropdown-menu', function (e) { e.stopPropagation() });
        $('#view_exposure_top_menu').click(view_exposure_top_menu);

        // 메뉴 기준으로 보기 이벤트
        $('#view_menu_depth').change(view_menu_depth);

        // 선택한 메뉴에 권한 일괄적용 이벤트
        $('#set_batch_permission').click(set_batch_permission);

        // 운영자 권한 적용
        $('.js-parent-action').click(function () { set_parent_manage_permission('<?= $isSuper; ?>', <?= (gd_is_provider() ? 'true' : 'false'); ?>); });
    });
    //-->
</script>
