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

class MenuPsController extends \Controller\Admin\Controller
{

    /**
     * index
     * - 개발중
     *
     * @throws LayerNotReloadException
     */
    public function index()
    {
        try {
            $postValue = Request::post()->toArray();
            $adminMenu = new AdminMenu();
            switch ($postValue['mode']) {
                case 'insertMenu':
                case 'modifyMenu':
                    $adminMenu->setAdminMenu($postValue);
                    $this->layer(__('메뉴 등록!'), 'top.location.reload();');
                    break;
            }
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
