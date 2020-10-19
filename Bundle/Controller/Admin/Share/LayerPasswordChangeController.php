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

/**
 * Class LayerPasswordChangeController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerPasswordChangeController extends \Controller\Admin\Controller
{
    public function index()
    {
        /** @var  \Bundle\Controller\Admin\Controller $this */
        $this->setData('managerNm', \Session::get(Manager::SESSION_MANAGER_LOGIN . '.managerNm'));
        $this->setData('isProvider', \Session::get(Manager::SESSION_MANAGER_LOGIN . '.isProvider'));
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
