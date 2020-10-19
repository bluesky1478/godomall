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

use Component\Admin\AdminMenu;

/**
 * 관리자 사이트 맵 - 메뉴순
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SitemapController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        //--- 페이지 데이터
        try {
            $adminMenu = new AdminMenu();
            if (gd_is_provider() === true) {
                $adminMenuType = 's';
                $adminMenuLink = URI_PROVIDER;
            } else {
                $adminMenuType = 'd';
                $adminMenuLink = URI_ADMIN;
            }

            $menuList = $adminMenu->getAdminMenuList($adminMenuType);
            $menuTreeList = $adminMenu->getAdminMenuTreeList($menuList);

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_fluid.php');
            $this->setData('menuTreeList', gd_isset($menuTreeList));
            $this->setData('adminMenuLink', gd_isset($adminMenuLink));
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
