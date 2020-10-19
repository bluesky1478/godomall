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


use Component\PlusShop\PlusReview\PlusReviewArticleFront;

class PlusReviewArticleListController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $get = \Request::get()->all();
        $plusReviewArticle = new PlusReviewArticleFront();

        $get['listLength']=110;
        $data = $plusReviewArticle->getArticleList($get,true);
        $this->setData('req',$get);
        $this->setData('data',$data);
        $this->setData('plusReviewConfig',$plusReviewArticle->getConfig());
    }
}
