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

use Globals;

class WarningPremiumController extends \Controller\Admin\Controller
{
    public function index()
    {

/**
 * 프리미엄경고창
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */



//--- 모듈 호출


//--- 관리자 디자인 템플릿


$this->getView()->setDefine('layout','layout_blank.php');
$this->getView()->setDefine('layoutContent',Request::getDirectoryUri().'/'.Request::getFileUri());

$this->setData('closeMode',$_GET['closeMode']);



    }
}
