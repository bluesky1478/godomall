<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Board;


use Bundle\Component\PlusShop\PlusReview\PlusReviewArticleFront;
use App;
use Component\Board\Board;
use Component\Page\Page;

class PlusReviewPhotoListController extends \Controller\Mobile\Controller
{
    public function index()
    {

        $req = \Request::get()->all();
        gd_isset($req['page'], 1);
        $plusReviewArticle = new PlusReviewArticleFront();
        $data = $plusReviewArticle->getListPhotoByGoodsNo(null,$req['page'], null, null, $req);
        // 포토후기 페이징 처리 수정
        $data['paging']->setBlockCount(Board::PAGINATION_MOBILE_COUNT);
        $data['paging']->setPage();
        $data['pagination'] = $data['paging']->getPage();
        $this->setData('data',$data);
        $this->setData('gPageName', '포토리뷰');
        $this->setData('req',$req);

    }
}
