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
namespace Bundle\Controller\Front\Goods;

use Component\Board\Board;
use Component\Board\BoardAdmin;
use Component\Board\BoardList;
use Component\Page\Page;
use Framework\Debug\Exception\AlertBackException;
use Request;

class GoodsBoardListController extends \Controller\Front\Controller
{

    /**
     * 상품 리뷰/QNA 보기
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        try {
            $this->addScript([
                'gd_board_list.js',
            ]);
            $req = Request::get()->toArray();
            gd_isset($req['page'], 1);
            $boardList = new BoardList($req);
            $auth = $boardList->canList();
            if ($auth == 'y') {
                //상품상세페이지에서의 상품후기, 상품문의 게시판 리스트 일시 페이지 노출 개수 적용
                $listCount = $boardList->getGoodsPageListNum('pc', $req);
                $getData = $boardList->getList(true,$listCount,80, false);
                $bdList['cfg'] = $boardList->cfg;
                $pager = $boardList->getPagination();
                $pager->setPage('list.php');
                $pager->setUrl(Request::getQueryString());
                $pagination = $pager->getPage('goGoodsAjaxPage(\'' . $req['bdId'] . '\',\'PAGELINK\')');

                $bdList['isAdmin'] = $boardList->isAdmin;
                $bdList['list'] = $getData['data'];
                $bdList['noticeList'] = $getData['noticeData'];
                $bdList['categoryBox'] = $boardList->getCategoryBox($req['category'], ' onChange="this.form.submit();" ');
                $this->setData('pagienation', $pagination);
                $this->setData('queryString', Request::getQueryString());
            }

            if ($req['bdId'] == Board::BASIC_GOODS_QA_ID) {
                $pageName = 'goods_board_qa_list.html';
            } else {
                $pageName = 'goods_board_review_list.html';
            }

            $this->setData('bdList', $bdList);
            $this->setData('auth', $auth);
            $this->setData('req', gd_htmlspecialchars($boardList->req));
            $this->getView()->setDefine('tpl', 'goods/' . $pageName);

        } catch (\Exception $e) {
            //debug($e->getMessage());
        }
    }
}

