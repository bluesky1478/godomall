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

/**
 * Class LayerMemberRecommIdController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 * @deprecated 2018-05-18 미사용 클래스 삭제 필요. 사용하지 마세요.
 */
class LayerMemberRecommIdController extends \Controller\Admin\Controller
{
    public function index()
    {

/**
 * 회원 추천받은 아이디 내역 조회
 * @author sunny
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */



//--- 모듈 호출




//--- 페이지 데이터
try {
	//--- 회원 설정
	$member		= \App::load('\\Component\\Member\\MemberAdmin');
	$getData	= $member->getRecommIdList($_GET['memNo']);
	$page = \App::load(\Component\Page\Page::class);
}
catch (Except $e) {
	echo ($e->ectMessage);
}

//--- 관리자 디자인 템플릿


$this->getView()->setDefine('layout','layout_blank.php');
$this->getView()->setDefine('layoutContent',Request::getDirectoryUri().'/'.Request::getFileUri());

$this->setData('page',gd_isset($page));
$this->setData('data',gd_isset($getData['data']));
$this->setData('groups',gd_isset($getData['groups']));



    }
}
