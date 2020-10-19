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

use Framework\Debug\Exception\Except;
use Globals;

class MemberOrderListController extends \Controller\Admin\Controller
{
    public function index()
    {

/**
 * 주문 리스트 페이지
 *
 * [관리자 모드] 주문 리스트 페이지
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */



//--- 모듈 호출




//--- 모듈 호출
$order		= \App::load('\\Component\\Order\\OrderAdmin');

//--- 주문 데이터
try {
	ob_start();

	//--- 주문 데이터
	$getData	= $order->getOrderListForCrm($_GET['memNo']);
	$page		= \App::load('\\Component\\Page\\Page');	// 페이지 재설정

	if ($out = ob_get_clean()) {
		throw new Except('ECT_LOAD_FAIL',$out);
	}
}
catch (Except $e) {
	$e->actLog();
	//echo ($e->ectMessage);
}

//--- 관리자 디자인 템플릿


$this->getView()->setDefine('layout','layout_blank.php');
$this->getView()->setDefine('layoutContent',\Request::getDirectoryUri().'/'.\Request::getFileUri());

$this->setData('page',$page);
$this->setData('data',$getData['data']);
$this->setData('info',$getData['info']);



    }
}
