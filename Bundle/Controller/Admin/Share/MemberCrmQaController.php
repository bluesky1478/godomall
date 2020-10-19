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
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Exception;
use Framework\Utility\ArrayUtils;
use Request;

class MemberCrmQaController extends \Controller\Admin\Board\ArticleListController
{
    public function index()
    {
        $memberData = $this->getData('memberData');
        if (ArrayUtils::isEmpty($memberData) === true) {
            throw new Exception(__('회원을 찾을 수 없습니다.'));
        }

        Request::get()->set('bdId', Board::BASIC_QA_ID);
        parent::index();
        $this->setData('memberData', $memberData);
        $this->getView()->setPageName('share/member_crm_qa.php');

        $getRequest = Request::get()->toArray();
        if (!$getRequest['searchPeriod'] && (!$getRequest['rangDate'][0] && !$getRequest['rangDate'][1])) {
            $getRequest['rangDate'][0] = date('Y-m-d', strtotime('-6 day'));
            $getRequest['rangDate'][1] = date('Y-m-d');
        }

        $this->setData('searchPeriod', gd_isset($getRequest['searchPeriod'], 364));
        $this->setData('searchDate',$getRequest['rangDate']);

        /* $this->addScript(['jquery/jquery.dataOverlapChk.js', 'board.js']);

        // --- 페이지 데이터
        try {
            $boardAdmin = new BoardAdmin();
            $boards = $boardAdmin->getBoardList(false);

            if(!Request::get()->has('memNo')) {
                throw new Exception('null 회원번호');
            }

            Request::get()->set('bdId',Board::BASIC_QA_ID);
            $articleListAdmin = new ArticleListAdmin(Request::get()->toArray());
            $getData = $articleListAdmin->getList();
            // --- 페이지 설정
            $bdList['cfg'] = $articleListAdmin->cfg;
            $bdList['list'] = $getData['data'];
            if (Request::get()->has('category')) {
                $requestCategory = Request::get()->get('category');
            }
            $bdList['categoryBox'] = $articleListAdmin->getCategoryBox($requestCategory, ' onChange="this.form.submit();" ');
        } catch (\Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }
        // --- 관리자 디자인 템플릿
        $this->setData('bdList', $bdList);
        $this->setData('board', $articleListAdmin);
        $this->setData('req', gd_htmlspecialchars($articleListAdmin->req));
        $this->setData('boards', $boards['data']);*/
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
