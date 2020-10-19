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

use Component\Member\Manager;
use Exception;
use Request;
use Session;

/**
 * 대표관리자 정보 입력 안내 팝업
 *
 * Class ManageSecurityController
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class LayerSuperAdminSecurityController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Exception
     */
    public function index()
    {
        if (Session::get('manager.isSuper') != 'y' || Session::get('manager.scmKind') != 'c') {
            exit;
        }

        $manager = new Manager();
        $super = $manager->getManagerAuthData();

        if ($super['isSmsAuth'] == 'y' || $super['isEmailAuth'] == 'y') {
            exit;
        }

        $manageModifyUrl = '../policy/manage_register.php?sno=' . $super['sno'];
        $this->getView()->setData('manageModifyUrl', $manageModifyUrl);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
