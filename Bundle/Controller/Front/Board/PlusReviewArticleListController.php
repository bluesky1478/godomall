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

class PlusReviewArticleListController extends \Controller\Front\Controller
{
    public function index()
    {

        $get = \Request::get()->all();
        $plusReviewArticle = new PlusReviewArticleFront();
        $get['listLength']=300;
        $data = $plusReviewArticle->getArticleList($get,true);
        $data['pagination'] = $data['paging']->getPage('goAjaxPage(\'PAGELINK\')');
        $auth['canWrite'] = $plusReviewArticle->canWrite();
        $auth['canWriteMemo'] = $plusReviewArticle->canWriteMemo();
        $this->setData('auth', $auth);
        $this->setData('req',$get);
        $this->setData('data',$data);
        $this->setData('plusReviewConfig',$plusReviewArticle->getConfig());
        if($get['isMypage'] == 'y'){
            $this->setData('isMypage',$get['isMypage']);
        }

    }
}
