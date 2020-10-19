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

class MemberCouponController extends \Controller\Admin\Controller
{
    public function index()
    {

/**
 * 회원 마일리지 내역 조회
 *
 * [관리자 모드] 회원 마일리지 내역 조회
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */



//--- 모듈 호출




//--- 모듈 호출
$couponAdmin	= \App::load('\\Component\\Coupon\\CouponAdmin');

try {
	ob_start();

	// 마일리지 정보
	$getData = $couponAdmin->getMemberCouponGivelist($_GET);
	$pager = \App::load('\\Component\\Page\\Page',gd_isset($_GET['page']),gd_isset($getData['searchCnt']),gd_isset($getData['amountCnt']),gd_isset($_GET['perPage']));
	$pager->setUrl($_SERVER['QUERY_STRING']);
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
$this->getView()->setDefine('layoutContent',Request::getDirectoryUri().'/'.Request::getFileUri());

$this->setData('data',gd_isset($getData['list']));
$this->setData('pager',$pager);



    }
}
