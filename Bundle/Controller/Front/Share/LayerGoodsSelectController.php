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



class LayerGoodsSelectController extends \Controller\Front\Controller
{
    /**
     * index
     * 레이어-상품선택
     */
    public function index()
    {
        $bdId = \Request::post()->get('bdId');
        $bdSno = \Request::post()->get('bdSno');
        $isPlusReview = \Request::post()->get('isPlusReview');
        $this->setData('bdId',$bdId);
        $this->setData('bdSno',$bdSno);
        $this->setData('isPlusReview', $isPlusReview);
        $cateId = \Request::post()->get('selectId', null);
        $cate = \App::load('\\Component\\Category\\Category');
        $cateDisplay = $cate->getMultiCategoryBox($cateId,null,null,true);
        $this->setData('cateDisplay', gd_isset($cateDisplay));
    }
}
