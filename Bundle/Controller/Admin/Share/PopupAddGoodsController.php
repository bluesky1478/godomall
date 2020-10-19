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
namespace Bundle\Controller\Admin\Share;

use Component\Board\ArticleListAdmin;
use Exception;
use Request;

class PopupAddGoodsController   extends \Controller\Admin\Controller
{

    public function index()
    {


        try {

            $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');
            $brand = \App::load('\\Component\\Category\\BrandAdmin');


            Request::get()->set('applyFl','y');
            Request::get()->set('searchPeriod','-1');
            $postValue = Request::post()->toArray();
            if($postValue) {
                foreach($postValue as $k => $v) {
                    Request::get()->set($k,$v);
                }
            }

            $getData = $addGoods->getAdminListAddGoods();
            $page = \App::load('\\Component\\Page\\Page');

            $this->addCss([
                'goodsChoiceStyle.css?'.time(),
            ]);
            $this->addScript([
                'goodsChoice.js?'.time(),
                'jquery/jquery.multi_select_box.js',
            ]);

            $this->getView()->setDefine('layout', 'layout_blank.php');

            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);
            $this->setData('page', $page);
            $this->setData('brand', $brand);
            $this->setData('category', $category);

            $this->setData('memData', gd_isset($memData));
            $this->setData('memGroup', gd_isset($memGroup));
            $this->setData('qnaList', gd_isset($qnaList));

            $this->setData('setGoodsList', gd_isset(urldecode($postValue['setGoodsList'])));
            $this->setData('selectedGoodsList', gd_isset($postValue['selectedGoodsList'])); //선택리스트

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/popup_add_goods.php');

        } catch (\Exception $e) {

        }
    }
}
