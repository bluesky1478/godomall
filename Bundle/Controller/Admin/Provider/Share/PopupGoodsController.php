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
namespace Bundle\Controller\Admin\Provider\Share;

use Component\Board\ArticleListAdmin;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Request;

/**
 * Class PopupGoodsController
 *
 * @package Bundle\Controller\Admin\Provider\Share
 */
class PopupGoodsController extends \Controller\Admin\Controller
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

            Request::get()->set('applyFl', 'y');
            $mobileFl = Request::get()->get('mobileFl');

            switch ($mobileFl) {
                case 'all':
                    Request::get()->set('goodsDisplayMobileFl', 'y');
                    Request::get()->set('goodsDisplayFl', '');

                    Request::get()->set('goodsSellMobileFl', 'y');
                    Request::get()->set('goodsSellFl', '');

                    Request::get()->set('soldOut', '');

                    break;
                case 'y':
                    Request::get()->set('goodsDisplayMobileFl', 'y');
                    Request::get()->set('goodsSellMobileFl', 'y');
                    break;
                case 'n':
                    Request::get()->set('goodsDisplayFl', 'y');
                    Request::get()->set('goodsSellFl', 'y');
                    break;
            }

            $postValue = Request::post()->toArray();
            if ($postValue) {
                foreach ($postValue as $k => $v) {
                    Request::get()->set($k, $v);
                }
            }

            $getData = $goods->getAdminListGoods();
            $page = \App::load('\\Component\\Page\\Page');

            $this->getView()->setDefine('layout', 'layout_blank.php');

            $this->addCss([
                'goodsChoiceStyle.css?' . time(),
            ]);
            $this->addScript([
                'goodsChoice.js?' . time(),
                'jquery/jquery.multi_select_box.js',
            ]);


            if (Request::get()->get('checkType') == 'radio') {
                $this->setData('checkType', 'radio');
            } else {
                $this->setData('checkCheckboxType', true);
                $this->setData('checkType', 'checkbox');
            }
            $this->setData('data', gd_htmlspecialchars($getData['data']));
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

            $this->setData('timeSaleFl', Request::get()->get('timeSaleFl'));

            $this->setData('setGoodsList', gd_isset(urldecode($postValue['setGoodsList'])));

            $this->setData('relationFl', Request::get()->get('relationFl')); // 관련상품 수동선택 여부

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('share/popup_goods.php');

        } catch (\Exception $e) {

            throw new AlertCloseException($e->ectMessage);
        }
    }
}
