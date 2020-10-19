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
namespace Bundle\Controller\Front\Share;


use Component\PlusShop\PlusReview\PlusReviewArticleFront;
use Component\Goods\AddGoodsAdmin;
use Component\Goods\Goods;
use Component\Order\OrderAdmin;

class LayerReviewOrderSearchController extends \Controller\Front\Controller
{
    public function index()
    {
        $plusReviewArticle = new PlusReviewArticleFront();
        $req = \Request::get()->all();
        $getValue['memNo'] = \Session::get('member.memNo');
        $pagingData['pageNum'] = 10;
        $pagingData['page'] =gd_isset($req['page'],1);
        $mode = empty($req['goodsNo']) ? 'popup' : 'goodsDetail';
        if ($mode == 'popup' && $plusReviewArticle->isPopupExceptMain() === false) {
            $isReviewPopup = true;
        } else {
            $isReviewPopup = false;
        }
        $orderGoodsData = $plusReviewArticle->getWritableOrderList($req['goodsNo'],$pagingData,$isReviewPopup, false);

        // 페이지 설정
        $page = \App::load('Component\\Page\\Page');
        $pagination = $page->getPage('goAjaxPaging(\'PAGELINK\')');
        $this->setData('pagination', gd_isset($pagination));
        $this->setData('total', $page->getTotal());
        $this->setData('data', gd_isset($orderGoodsData));
    }
}
