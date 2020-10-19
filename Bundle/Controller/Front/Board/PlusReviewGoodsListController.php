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

namespace Bundle\Controller\Front\Board;


use Component\PlusShop\PlusReview\PlusReviewArticleFront;

class PlusReviewGoodsListController extends \Controller\Front\Controller
{
    public function index()
    {
        $req = \Request::get()->all();
        gd_isset($req['page'], 1);
        $plusReviewArticle = new PlusReviewArticleFront();
        $req['listLength'] = 28;
        $data = $plusReviewArticle->getListGroupByGoodsNo($req, true);
        $data['pagination'] = $data['paging']->getPage('goAjaxPage(\'PAGELINK\')');
        $this->setData('data',$data);
        $this->setData('req',$req);

    }
}
