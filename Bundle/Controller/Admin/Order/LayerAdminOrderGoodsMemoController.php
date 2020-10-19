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

use Exception;
use Request;

/**
 * Class LayerAdminOrderGoodsMemoController
 * 상품º주문번호별 메모
 *
 * @package Bundle\Controller\Admin\Order
 * @author  choisueun <cseun555@godo.co.kr>
 */
class LayerAdminOrderGoodsMemoController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $requestGetParams['page'] = 0;
            $requestGetParams['pageNum'] = 10;
            $requestGetParams['sort'] = 'regDt DESC';
            $requestGetParams['orderNo'] = $postValue['orderNo'];

            $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');

            // 상품º주문번호별 메모 데이터 가져오기
            $memoData = $orderAdmin->getAdminOrderGoodsMemoData($requestGetParams);
            $page = $orderAdmin->getPage($requestGetParams, Request::getQueryString());
            $this->setData('memoData', $memoData);
            $this->setData('page', $page);

            /*$this->setData('cnt', $memoData['cnt']);
            /$this->setData('pagination', $memoData['pagination']);*/

            $this->getView()->setDefine('layout', 'layout_layer.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/layer_admin_order_goods_memo.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
