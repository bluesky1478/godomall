<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Share;

use Framework\Debug\Exception\LayerException;
use Component\Admin\AdminMenu;
use Component\Member\Manager;
use Globals;

/**
 * 메뉴 권한 설정 레이어
 *
 * @author Sunny <bluesunh@godo.co.kr>
 */
class LayerManagePermissionController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');

        $_managerClass = new Manager();

        // 실행 조건 검증
        $isMenuList = ($this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true ? true : false);
        $this->setData('isMenuList', $isMenuList);

        // 운영자 정보
        if ($request->post()->get('mode') === 'register') {
            $scmNo = $request->post()->get('scmNo');
            $isSuper = $request->post()->get('isSuper');
        } else if ($request->post()->get('mode') === 'modify' && $request->post()->get('sno') != '') {
            $data = $_managerClass->getManagerInfo($request->post()->get('sno'));
            $scmNo = $data['scmNo'];
            $isSuper = $data['isSuper'];
        }

        // 본사 + 공급사 구분 정의
        if ($request->post()->get('mode') === 'register') {
            if ($request->post()->get('scmFl') !== 'y') { // 본사에서 본사 체크시
                $adminMenuType = 'd';
            } else { // 본사에서 공급사 체크시 or 공급사에서 아무런 값이 없을 시
                $adminMenuType = 's';
            }
            if (((int) $scmNo === DEFAULT_CODE_SCMNO) || !Manager::useProvider()) {
                $adminMenuType = 'd';
            }
        } elseif ($request->post()->get('mode') === 'modify') {
            if ((int) $scmNo === DEFAULT_CODE_SCMNO) {
                $adminMenuType = 'd';
            } else {
                $adminMenuType = 's';
            }
        }
        $scmFl = ($adminMenuType == 'd' ? 'n' : 'y');

        // 메뉴 리스트(본사,공급사)
        $adminMenu = new AdminMenu();
        $menuList = $adminMenu->getAdminMenuList($adminMenuType);
        $menuTreeList = $adminMenu->getAdminMenuTreeList($menuList);

        // 메뉴 리스트 필터
        if (method_exists($adminMenu, 'getMenuTreeListFilter') === true) {
            $menuTreeList = $adminMenu->getMenuTreeListFilter($menuTreeList);
        }

        // 설정된 권한정보(permissionFl,permissionMenu, functionAuth, writeEnabledMenu)로 selected 정의
        $existingPermission = json_decode($request->post()->get('existingPermission'), true);
        if (method_exists($adminMenu, 'getAdminMenuPermissionSelected') === true) {
            $selected = $adminMenu->getAdminMenuPermissionSelected($existingPermission, $menuTreeList);
        }

        // 기능 리스트(본사,공급사)
        if (method_exists($adminMenu, 'getMenuFunction') === true) {
            $functionList = $adminMenu->getMenuFunction($adminMenuType);
        }
        $checked = [];
        foreach ($existingPermission['functionAuth']['functionAuth'] as $functionKey => $functionVal) {
            $checked['functionAuth'][$functionKey][$functionVal] = 'checked="checked"';
        }

        // 공급사 부운영자 등록/수정 일 경우
        if ($adminMenuType == 's' && $isSuper != 'y') {
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            // 공급사(대표운영자) 기능 리스트
            $scmFunctionAuth = $scmAdmin->getScmFunctionAuth($scmNo);
            $this->setData('scmFunctionAuth', $scmFunctionAuth['functionAuth']);
            // 공급사 부운영자 메뉴권한 설정범위 정의
            if (method_exists($adminMenu, 'getAdminMenuScmPermissionDisabled') === true) {
                $scmSuperData = $scmAdmin->getScmSuperManager($scmNo);
                $adminMenu->getAdminMenuScmPermissionDisabled($scmSuperData, $menuTreeList, $selected);
            }
        }

        // 1차 메뉴 목록
        $menuTopList = [];
        foreach($menuTreeList['top'] as $key => $val) {
            $menuTopList[$key] = $val['name'];
            unset($key, $val);
        }

        // 권한 범위 및 설정 기능 disabled 여부
        // 본사 최고운영자 또는 공급사 ADMIN 대표운영자 수정 경우 권한 범위 disabled
        if (($adminMenuType == 'd' && $isSuper == 'y') || ($adminMenuType == 's' && $isSuper == 'y' && gd_is_provider())) {
            $disabled['permissionFl'] = 'disabled="disabled"';
        }
        // 본사 최고운영자 또는 공급사 ADMIN 대표운영자 수정 또는 전체권한 경우 설정 기능 disabled
        if (($adminMenuType == 'd' && $isSuper == 'y') || ($adminMenuType == 's' && $isSuper == 'y' && gd_is_provider()) || $existingPermission['permissionFl'] == 's') {
            $disabled['settingItem'] = 'disabled="disabled"';
        }

        // 메뉴 기준으로 보기 (1차 메뉴 기준으로 보기-1, 2차 메뉴 기준으로 보기-2, 3차 메뉴 기준으로 보기-3)
        $viewMemuDepth = 1;
        $selected['viewMemuDepth'][$viewMemuDepth] = 'selected="selected"';

        $checked['permissionFl'][$existingPermission['permissionFl']] = 'checked="checked"';

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setPageName('share/layer_manage_permission.php');

        $this->setData('mode', $request->post()->get('mode'));
        $this->setData('adminMenuType', $adminMenuType);
        $this->setData('isSuper', $isSuper);
        $this->setData('menuTopList', $menuTopList);
        $this->setData('menuTreeList', $menuTreeList);
        $this->setData('functionList', $functionList);
        $this->setData('permissionFl', $existingPermission['permissionFl']);
        $this->setData('selected', $selected);
        $this->setData('checked', $checked);
        $this->setData('disabled', $disabled);
        $this->setData('sno', $request->post()->get('sno'));
        $this->setData('scmFl', $scmFl);
        $this->setData('scmNo', $scmNo);
        $this->setData('viewMemuDepth', $viewMemuDepth);
        $this->setData('reCall', $request->post()->get('reCall'));
    }
}