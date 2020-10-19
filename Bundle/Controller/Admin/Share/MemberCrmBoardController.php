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

use Component\Validator\Validator;
use Component\Board\ArticleListAdmin;
use Component\Board\Board;
use Component\Board\BoardAdmin;
use Component\PlusShop\PlusReview\PlusReviewArticleAdmin;
use Framework\Utility\ArrayUtils;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Exception;
use Request;
use Session;

class MemberCrmBoardController extends \Controller\Admin\Board\ArticleListController
{
    public function index()
    {
        //$this->setData('memberData', $memberData);
        $getValue = Request::get()->toArray();
        $this->setData('getValue', $getValue);
        $boardAdmin = new BoardAdmin();

        if (!$getValue['memNo']) {
            throw new Exception(__('회원을 찾을 수 없습니다.'));
        }

        gd_isset($getValue['bdId'], Board::BASIC_GOODS_REIVEW_ID); // 게시판 아이디가 없을 경우 리뷰 게시판

        // 부모 컨트롤러 index 호출
        parent::index();
        $parentGetData = $this->getdata(); // 부모컨트롤러 변수 로드
        krsort($parentGetData['boards']); // 게시판리스트 변수 키 역정렬
        $this->setData('boards', $parentGetData['boards']); // 게시판리스트 boards로 재할당

        // 게시판 검색 selectBox
        $boardSearchField = [
            "subject" => "제목",
            "contents" => "내용",
            "subject_contents" =>"제목+내용",
            "goodsNm" => "상품명",
            "goodsNo" => "상품코드",
            "goodsCd" => "자체상품코드"
        ];
        $this->setData('boardSearchField', $boardSearchField);
        $this->setData('bdCnt', $boardAdmin->getBoardCntByMemberSno($getValue['memNo']));
        $this->getView()->setPageName('share/member_crm_board.php');
    }

    public function post()
    {
        // CRM 접근 권한 체크 (부모 메소드(parent::index)에서 callMenu 호출하여 별도로 CRM 체크)
        if (method_exists($this->getData('naviMenu'), 'getAccessMenuStatus')) {
            $crmAccess = $this->getData('naviMenu')->getAccessMenuStatus('member', 'member', 'crm', gd_is_provider());
            if ($crmAccess == '') {
                $this->getView()->setDefine('layout', 'layout_basic_nohelp.php');
                $this->getView()->setData('title', '회원 관리(CRM)');
                $this->getView()->setDefine('layoutContent', 'base/_admin_menu_access.php');
            }
        }
    }
}
