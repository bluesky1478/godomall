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
namespace Bundle\Controller\Mobile\Goods;

use Component\Board\Board;
use Component\Board\BoardAdmin;
use Component\Board\BoardList;
use Component\Page\Page;
use Framework\Debug\Exception\AlertOnlyException;
use Request;

class GoodsBoardListController extends \Controller\Mobile\Controller
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

            //상품상세페이지에서의 상품후기, 상품문의 게시판의 리스트 일시 페이지 노출 개수 적용
            $listCount = $boardList->getGoodsPageListNum('mobile', $req);
            $getData = $boardList->getList(true, $listCount);
            $bdList['cfg'] = $boardList->cfg;

            $pageData['total'] =  $getData['pagination']->recode['total'];
            $pageData['now'] =  $getData['pagination']->page['now'];
            $pageData['next'] =  $getData['pagination']->page['now']+1;
            if($pageData['next'] > ($getData['pagination']->recode['total'] / $listCount) + 1)  $pageData['next'] = 0;
            $pageData['listCount'] =  $getData['pagination']->page['now'] *  $listCount;
            if($pageData['listCount'] >$pageData['total'] )  $pageData['listCount'] =  $pageData['total'] ;

            $pagination = $getData['pagination'];
            $getData['pagination']->setBlockCount(Board::PAGINATION_MOBILE_BLOCK_COUNT);
            $getData['pagination']->setPage();
            $pagination = $pagination->getPage('goGoodsAjaxPage(\'' . $req['bdId'] . '\',\'PAGELINK\')');
            $bdList['isAdmin'] = $boardList->isAdmin;
            $bdList['list'] = $getData['data'];
            $bdList['noticeList'] = $getData['noticeData'];
            //상품상세페이지 이용후기에서의 공지사항은 무조건 첫 페이지에만 노출
            if($req['bdId'] === 'goodsreview' && (int)$req['page'] > 1){
                unset($bdList['noticeList'], $getData['noticeData']);
            }

            $bdList['categoryBox'] = $boardList->getCategoryBox($req['category'], ' onChange="this.form.submit();" ');

            if ($req['bdId'] == Board::BASIC_GOODS_QA_ID) {
                $pageName = 'goods_board_qa_list.html';
            } else {
                $pageName = 'goods_board_review_list.html';
            }


            $this->setData('bdList', $bdList);
            $this->setData('pageData', $pageData);
            $this->setData('pagienation', $pagination);
            $this->setData('req', gd_htmlspecialchars($boardList->req));
            $this->setData('queryString', Request::getQueryString());
            $this->getView()->setDefine('tpl', 'goods/' . $pageName);
        } catch (\Exception $e) {
            throw new AlertOnlyException($e->getMessage());
        }
    }

}

