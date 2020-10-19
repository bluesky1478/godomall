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

class MenuConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $postRequest = Request::post()->toArray();

        // 모듈호출
        $adminMenu = new AdminMenu();

        // --- 상품 데이터
        try {
            if (empty($postRequest['menuNo']) === false) {
                $getData = $adminMenu->getAdminMenuInfo($postRequest['menuNo']);
                $getData['adminMenuPrefix'] = substr_replace($getData['adminMenuNo'], '', -5);
                if ($getData) {
                    $mode = 'modifyMenu';
                }
            } else {
                $mode = 'insertMenu';
            }
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
        $adminMenuProductCode = [
            'godomall' => __('고도'),
        ];
        $adminMenuSettingType = [
            'd' => __('기본'),
            'p' => __('플러스샵'),
        ];
        $adminMenuDepth = [
            '1' => sprintf(__('%d차 메뉴'), 1),
            '2' => sprintf(__('%d차 메뉴'), 2),
            '3' => sprintf(__('%d차 메뉴'), 3),
        ];
        $adminMenuDisplayType = [
            'y' => __('메뉴 노출'),
            'n' => __('메뉴 숨김'),
        ];
        $adminMenuEcKind = [
            'a' => __('솔루션 전체'),
            'f' => __('솔루션 무료형(standard)'),
            'r' => __('솔루션 임대형(pro)'),
        ];

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', $getData);
        $this->setData('mode', $mode);
        $this->setData('adminMenuProductCode', $adminMenuProductCode);
        $this->setData('adminMenuSettingType', $adminMenuSettingType);
        $this->setData('adminMenuDepth', $adminMenuDepth);
        $this->setData('adminMenuDisplayType', $adminMenuDisplayType);
        $this->setData('adminMenuEcKind', $adminMenuEcKind);
    }
}
