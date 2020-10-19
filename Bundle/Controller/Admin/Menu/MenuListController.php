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

namespace Bundle\Controller\Admin\Menu;

use Component\Admin\AdminMenu;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Request;

class MenuListController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('menu', 'menu', 'menu');

        // 모듈호출
        $adminMenu = new AdminMenu();

        $getValue = Request::get()->toArray();
        gd_isset($getValue['adminMenuType'], 'd');

        // --- 상품 리스트 데이터
        try {
            $getData = $adminMenu->getAdminMenuList($getValue['adminMenuType']);
            $jsonData = $adminMenu->getAdminMenuTreeJsonList($getData);
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        // 스크립트 로드
        $this->addScript(
            [
                'tui/code-snippet.min.js',
                'tui.component.tree/tree.js',
            ]
        );

        // CSS 로드
        $this->addCss(
            [
                'tree.css',
            ]
        );

        // --- 관리자 디자인 템플릿
        $this->setData('jsonData', $jsonData);
    }
}
