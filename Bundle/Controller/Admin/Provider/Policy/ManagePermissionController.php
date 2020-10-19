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
namespace Bundle\Controller\Admin\Provider\Policy;

//use Globals;
//use Request;
//use Session;

/**
 * 운영자 권한 설정
 *
 * @author Sunny <bluesunh@godo.co.kr>
 */
class ManagePermissionController extends \Controller\Admin\Policy\ManagePermissionController
{
    /**
     * index
     *
     */
    public function index()
    {
        parent::index();
        $this->getView()->setPagename('policy/manage_permission.php');
    }
}
