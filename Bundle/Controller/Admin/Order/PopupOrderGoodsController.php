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
    namespace Bundle\Controller\Admin\Order;

use Component\Board\ArticleListAdmin;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Request;
use Globals;

/**
 * Class PopupOrderGoodsController
 *
 * @package Bundle\Controller\Admin\Share
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class PopupOrderGoodsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        try {

            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $brand = \App::load('\\Component\\Category\\BrandAdmin');
            $category = \App::load('\\Component\\Category\\categoryAdmin');

            Request::get()->set('applyFl','y');
            Request::get()->set('soldOut','n');
            Request::get()->set('goodsDisplayFl', '');
            Request::get()->set('goodsSellFl', '');

            $param = array();

            $memNo = Request::get()->get('memNo');
            $loadPageType = Request::get()->get('loadPageType');

            $postValue = Request::post()->toArray();
            if($postValue) {
                foreach($postValue as $k => $v) {
                    Request::get()->set($k,$v);
                }
            }

            $getData = $goods->getAdminListGoods(null, 5);
            $page = \App::load('\\Component\\Page\\Page');

            $this->getView()->setDefine('layout', 'layout_blank.php');

            $this->addCss([
                'goodsChoiceStyle.css?'.time(),
            ]);
            $this->addScript([
                'jquery/jquery.multi_select_box.js',
                'goodsChoice.js?'.time(),
            ]);

            if(gd_use_provider() === true){
                $this->setData('providerScmNo', \Session::get('manager.scmNo'));
                $this->setData('providerManagerNickNm', \Session::get('manager.managerNickNm'));
            }

            $this->setData('data', $getData['data']);
            $this->setData('search', $getData['search']);
            $this->setData('sort', $getData['sort']);
            $this->setData('checked', $getData['checked']);
            $this->setData('selected', $getData['selected']);
            $this->setData('page', $page);
            $this->setData('brand', $brand);
            $this->setData('category', $category);
            $this->setData('currency', Globals::get('gCurrency'));


            $this->setData('memData', gd_isset($memData));
            $this->setData('memGroup', gd_isset($memGroup));
            $this->setData('qnaList', gd_isset($qnaList));
            $this->setData('memNo', $memNo);
            $this->setData('loadPageType', $loadPageType);

            $this->setData('setGoodsList', gd_isset(urldecode($postValue['setGoodsList'])));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/popup_order_goods.php');
        } catch (\Exception $e) {

            throw new AlertCloseException($e->ectMessage);
        }
    }
}
