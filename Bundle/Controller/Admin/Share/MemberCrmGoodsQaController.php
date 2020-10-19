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

use Component\Board\Board;
use Component\Board\ArticleListAdmin;
use Component\Board\BoardAdmin;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Framework\Debug\Exception\AlertOnlyException;
use League\Flysystem\Exception;
use Request;

class MemberCrmGoodsQaController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->addScript(['jquery/jquery.dataOverlapChk.js', 'board.js']);

        // --- 페이지 데이터
        try {
            $boardAdmin = new BoardAdmin();
            $boardList = $boardAdmin->selectList();

            if(!Request::get()->has('memNo')) {
                throw new Exception('null 회원번호');
            }

            Request::get()->set('bdId',Board::BASIC_GOODS_QA_ID);
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
        $this->setData('pager', $articleListAdmin->getPage());
        $this->setData('boards', $boardList);
    }
}
