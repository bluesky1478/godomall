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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Component\Admin\AdminMenu;
use Component\Member\Manager;
use Component\Policy\MainSettingPolicy;

/**
 * Class LayerFavoriteMenuController
 * @package Bundle\Controller\Admin\Base
 * @author  yjwee
 */
class LayerFavoriteMenuController extends \Controller\Admin\Controller
{
    public function index()
    {
        \Logger::info(__METHOD__);
        $adminMenu = new AdminMenu();
        $adminMenuType = 'd';
        if (Manager::isProvider()) {
            $adminMenuType = 's';
        }
        $menuLists = $adminMenu->getAdminMenuList($adminMenuType);
        $menuJson = $adminMenu->getAdminMenuTreeJsonList($menuLists, false);
        $this->setData('menuJson', $menuJson);
        $mainSettingPolicy = new MainSettingPolicy();
        $favoriteMenus = $mainSettingPolicy->getFavoriteMenu(\Session::get(Manager::SESSION_MANAGER_LOGIN . '.sno'));
        if (empty($favoriteMenus['menus']) === false) {
            $this->setData('favoriteMenus', json_encode($favoriteMenus));
        }
        // 템플릿 정의
        $this->getView()->setDefine('layout', 'layout_layer.php');
        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('base/layer_favorite_menu.php');
    }
}
