<form id="frmMenuConfig" name="frmMenuConfig" method="post" action="menu_ps.php">
    <input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
    <input type="hidden" name="adminMenuNo" value="<?php echo $data['adminMenuNo']; ?>"/>
    <input type="hidden" name="adminMenuType" value="<?php echo $data['adminMenuType']; ?>"/>

    <table class="table table-cols">
        <colgroup>
            <col class="width-lg"/>
            <col/>
        </colgroup>
        <tr>
            <th>관리자 메뉴 고유번호</th>
            <td><?php echo $data['adminMenuNo']; ?></td>
        </tr>
        <tr>
            <th>관리자 메뉴 Prefix 코드</th>
            <td>
                <input type="text" name="adminMenuPrefix" value="<?php echo $data['adminMenuPrefix']; ?>" class="form-control width-lg"/> - godo 는 고도몰의 자체 코드로 사용 하실 수 없습니다.
            </td>
        </tr>
        <tr>
            <th>관리자 메뉴 셋팅 타입</th>
            <td>
                <?= gd_select_box('adminMenuSettingType', 'adminMenuSettingType', $adminMenuSettingType, null, $data['adminMenuSettingType'], null, null, 'form-control width30p'); ?>
            </td>
        </tr>
        <tr class="plus-shop">
            <th>관리자 메뉴 플러스샵 제작사 코드</th>
            <td>
                <?= gd_select_box('adminMenuProductCode', 'adminMenuProductCode', $adminMenuProductCode, null, $data['adminMenuProductCode'], null, null, 'form-control width30p'); ?>
            </td>
        </tr>
        <tr class="plus-shop">
            <th>관리자 메뉴 플러스(앱) 코드</th>
            <td>
                <input type="text" name="adminMenuPlusCode" value="<?php echo $data['adminMenuPlusCode']; ?>" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th>관리자 메뉴 코드</th>
            <td>
                <input type="text" name="adminMenuCode" value="<?php echo $data['adminMenuCode']; ?>" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th>관리자 메뉴 뎁스</th>
            <td>
                <?= gd_select_box('adminMenuDepth', 'adminMenuDepth', $adminMenuDepth, null, $data['adminMenuDepth'], null, null, 'form-control width30p'); ?>
            </td>
        </tr>
        <tr class="depth-2 depth-3">
            <th>관리자 메뉴 상위 메뉴 고유번호</th>
            <td>
                <input type="text" name="adminMenuParentNo" value="<?php echo $data['adminMenuParentNo']; ?>" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th>관리자 메뉴 타이틀</th>
            <td>
                <input type="text" name="adminMenuName" value="<?php echo $data['adminMenuName']; ?>" class="form-control width-lg"/>
            </td>
        </tr>
        <tr class="depth-3">
            <th>관리자 메뉴 링크</th>
            <td>
                <input type="text" name="adminMenuUrl" value="<?php echo $data['adminMenuUrl']; ?>" class="form-control width-lg"/>
            </td>
        </tr>
        <tr>
            <th>관리자 메뉴 노출 여부</th>
            <td>
                <?= gd_select_box('adminMenuDisplayType', 'adminMenuDisplayType', $adminMenuDisplayType, null, $data['adminMenuDisplayType'], null, null, 'form-control width30p'); ?>
            </td>
        </tr>
        <tr>
            <th>관리자 메뉴 고도5 상품군에 따른 기능 여부</th>
            <td>
                <?= gd_select_box('adminMenuEcKind', 'adminMenuEcKind', $adminMenuEcKind, null, $data['adminMenuEcKind'], null, null, 'form-control width30p'); ?>
            </td>
        </tr>
    </table>
</form>
<script language="javascript" type="text/javascript">
    $(document).ready(function () {
        $('#adminMenuSettingType').click(function (e) {
            displayPlusShop();
        });
        $('#adminMenuDepth').click(function (e) {
            displayDepth();
        });
        displayPlusShop();
        displayDepth();
    });
    function displayPlusShop() {
        if($('#adminMenuSettingType').val() == 'p') {
            $('.plus-shop').show();
        } else {
            $('.plus-shop').hide();
        }
    }
    function displayDepth() {
        if($('#adminMenuDepth').val() == '3') {
            $('.depth-2').hide();
            $('.depth-3').show();
        } else if($('#adminMenuDepth').val() == '2') {
            $('.depth-3').hide();
            $('.depth-2').show();
        } else {
            $('.depth-3').hide();
            $('.depth-2').hide();
        }
    }
</script>
