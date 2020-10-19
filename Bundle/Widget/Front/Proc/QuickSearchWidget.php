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
namespace Bundle\Widget\Front\Proc;

use Session;
/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Outline
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 */

class QuickSearchWidget extends \Widget\Front\Widget
{

    public function index()
    {

        $quickConfig = gd_policy('search.quick');

        if(in_array('category',$quickConfig['searchType']) || in_array('brand',$quickConfig['searchType'])) {
            $this->setData('headerScript', [
                PATH_SKIN . 'js/gd_multi_select_box.js'
            ]);

            if(in_array('category',$quickConfig['searchType'])) {
                $cate = \App::load('\\Component\\Category\\Category');
                $cateDisplay = $cate->getMultiCategoryBox('quickCateGoods',null,null,true);
                $this->setData('cateDisplay', gd_isset($cateDisplay));
            }

            if(in_array('brand',$quickConfig['searchType'])) {
                $brand = \App::load('\\Component\\Category\\Brand');
                $brandDisplay = $brand->getMultiCategoryBox('quickBrandGoods',null,null,true);
                $this->setData('brandDisplay', gd_isset($brandDisplay));
            }
        } else {
            $this->setData('headerScript', []);
        }

        if(in_array('color',$quickConfig['searchType']))
        {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goodsColorList = $goods->getGoodsColorList();
            $this->setData('goodsColorList', gd_isset($goodsColorList));
        }

        if (Session::has(SESSION_GLOBAL_MALL)) {
            if(in_array('delivery',$quickConfig['searchType'])) {
                unset($quickConfig['searchType'][array_search('delivery', $quickConfig['searchType'])]);
            }
        }

        if(in_array('icon', $quickConfig['searchType']))
        {
            $goodsIcon = \App::load('\\Component\\Goods\\Goods');
            $goodsIconList = $goodsIcon->getIconSearchList();
            $this->setData('goodsIconList', gd_isset($goodsIconList));
        }

        $this->setData('quickConfig',gd_isset($quickConfig));


    }
}
