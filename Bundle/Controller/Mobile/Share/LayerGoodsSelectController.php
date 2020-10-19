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
namespace Bundle\Controller\Mobile\Share;


class LayerGoodsSelectController extends \Controller\Mobile\Controller
{
    public function index()
    {
        $bdId = \Request::post()->get('bdId');
        $bdSno = \Request::post()->get('bdSno');
        $isPlusReview = \Request::post()->get('isPlusReview');
        $this->setData('bdId',$bdId);
        $this->setData('bdSno',$bdSno);
        $this->setData('isPlusReview', $isPlusReview);
    }
}
