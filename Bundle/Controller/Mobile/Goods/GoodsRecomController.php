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

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Framework\Utility\DateTimeUtils;
use Message;
use Globals;
use Request;
use Session;

class GoodsRecomController extends \Controller\Mobile\Controller
{
    /**
     * 추천상품 더보기 페이지
     *
     * @author atomyang
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {

        $getValue = Request::get()->toArray();

        try {
            // 카테고리 정보
            if($getValue['brandCd'])  {
                $cateCd =  $getValue['brandCd'];
                $cateType = "brand";
            } else {
                $cateCd = $getValue['cateCd'];
                $cateType = "cate";
            }


            if($cateType =='brand') $cate = \App::load('\\Component\\Category\\Brand');
            else $cate = \App::load('\\Component\\Category\\Category');

            // 카테고리 정보
            $cateInfo = $cate->getCategoryGoodsList($cateCd,'y');

            if($cateInfo['cateHtml2'] =='<p>&nbsp;</p>') unset($cateInfo['cateHtml2']);

            $this->setData('cateCd', gd_isset($cateCd));
            $this->setData('cateType', gd_isset($cateType));
            $this->setData('themeInfo', gd_isset($cateInfo));
            $this->setData('gPageName', __("추천상품"));


        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }


    }
}
