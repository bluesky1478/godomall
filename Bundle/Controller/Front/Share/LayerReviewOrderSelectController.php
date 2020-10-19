<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Share;


use Component\PlusShop\PlusReview\PlusReviewArticleFront;

class LayerReviewOrderSelectController extends \Controller\Front\Controller
{
    /**
     * index
     * 레이어-주문선택
     */
    public function index()
    {
        $plusReviewArticle = new PlusReviewArticleFront();
        $req = \Request::post()->all();
        $this->setData('req',$req);
        $this->setData('plusReviewConfig',$plusReviewArticle->getConfig());
    }
}
